<?php
// 控制器文件
require_once 'includes/AdminOperationLogger.php';
require_once __DIR__ . '/../config.php';

// 基础控制器类
abstract class BaseController {
    protected $layout;
    protected $templateEngine;
    
    public function __construct() {
        $this->layout = new BaseLayout();
        $this->templateEngine = new TemplateEngine();
    }
    
    protected function render($template, $data = []) {
        $this->layout->renderHeader($data['title'] ?? '', $data);
        $this->templateEngine->display($template, $data);
        $this->layout->renderFooter($data);
    }
    
    protected function redirect($url) {
        header('Location: ' . $url);
        exit;
    }
    
    protected function jsonResponse($data, $statusCode = 200) {
        http_response_code($statusCode);
        header('Content-Type: application/json');
        echo json_encode($data);
        exit;
    }
    
    protected function validateCsrf() {
        // 简单的CSRF保护
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $token = $_POST['csrf_token'] ?? '';
            $sessionToken = $_SESSION['csrf_token'] ?? '';
            
            if (empty($token) || empty($sessionToken) || $token !== $sessionToken) {
                $this->jsonResponse(['success' => false, 'message' => 'CSRF验证失败'], 403);
            }
        }
    }
    
    protected function generateCsrfToken() {
        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['csrf_token'];
    }
    
    // 辅助函数
    protected function getFileType($mime) {
        if (strpos($mime, 'image/') === 0) return '图片';
        if (strpos($mime, 'video/') === 0) return '视频';
        if (strpos($mime, 'text/') === 0) return '文本';
        if (strpos($mime, 'audio/') === 0) return '音频';
        return '文件';
    }
    
    protected function getFileLink($fileId) {
        return (isset($_SERVER['HTTPS']) ? "https" : "http") . "://$_SERVER[HTTP_HOST]$_SERVER[SCRIPT_NAME]?action=download&id=$fileId";
    }
    
    protected function formatSize($bytes) {
        if ($bytes >= 1073741824) {
            return number_format($bytes / 1073741824, 2) . ' GB';
        } elseif ($bytes >= 1048576) {
            return number_format($bytes / 1048576, 2) . ' MB';
        } elseif ($bytes >= 1024) {
            return number_format($bytes / 1024, 2) . ' KB';
        } else {
            return $bytes . ' bytes';
        }
    }
}

// 首页控制器
class HomeController extends BaseController {
    public function index() {
        $config = loadConfig();
        
        $data = [
            'title' => $config['site_name'],
            'config' => $config,
            'uploadSuccess' => isset($_GET['upload_success']),
            'pickupCode' => $_GET['pickup_code'] ?? '',
            'pickupError' => isset($_GET['pickup_error']),
            'errorMessage' => $_GET['message'] ?? '',
            'csrf_token' => $this->generateCsrfToken()
        ];
        
        $this->render('home', $data);
    }
}

// 文件控制器
class FileController extends BaseController {
    public function upload() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->jsonResponse(['success' => false, 'message' => '无效的请求方法'], 400);
        }
        
        try {
            $this->validateCsrf();
            
            // 检查文件上传
            if (!isset($_FILES['file']) || $_FILES['file']['error'][0] !== UPLOAD_ERR_OK) {
                $this->jsonResponse(['success' => false, 'message' => '文件上传失败'], 400);
            }
            
            $files = [];
            $fileCount = count($_FILES['file']['name']);
            $config = loadConfig();
            
            // 处理每个文件
            for ($i = 0; $i < $fileCount; $i++) {
                if ($_FILES['file']['error'][$i] !== UPLOAD_ERR_OK) continue;
                
                $file = [
                    'name' => $_FILES['file']['name'][$i],
                    'type' => $_FILES['file']['type'][$i],
                    'tmp_name' => $_FILES['file']['tmp_name'][$i],
                    'error' => $_FILES['file']['error'][$i],
                    'size' => $_FILES['file']['size'][$i]
                ];
                
                // 使用安全验证器检查文件
                try {
                    $validation = FileSecurityValidator::validateFile($file, $config);
                    if (!$validation || !$validation['valid']) {
                        throw new Exception('文件验证失败');
                    }
                } catch (Exception $e) {
                    // 记录错误信息用于调试
                    error_log("文件验证异常: " . $e->getMessage() . " 文件: " . $file['name']);
                    throw new Exception($e->getMessage());
                }
                
                // 更新文件信息为验证后的安全信息
                $file['type'] = $validation['mime_type'];
                $file['safe_name'] = $validation['safe_filename'];
                
                $files[] = $file;
            }
            
            if (empty($files)) {
                $this->jsonResponse(['success' => false, 'message' => '没有有效的文件上传'], 400);
            }
            
            // 获取用户设置的过期天数
            $expireDays = isset($_POST['expire_days']) ? intval($_POST['expire_days']) : EXPIRE_DAYS;
            $expireDays = max(1, min(365, $expireDays));
            
            // 为所有文件生成同一个提取码
            $pickupCode = sprintf('%05d', rand(0, 99999));
            
            // 处理每个文件
            $fileIds = [];
            foreach ($files as $file) {
                // 创建文件信息
                $fileInfo = new FileInfo($file, $expireDays);
                $fileInfo->pickup_code = $pickupCode;
                
                // 保存文件
                if (!move_uploaded_file($file['tmp_name'], $fileInfo->path)) {
                    throw new Exception('文件保存失败');
                }
                
                // 保存文件信息
                $fileId = $fileInfo->save();
                $fileIds[] = $fileId;
                
                // 添加到发件记录
                $_SESSION['uploaded_files'][] = [
                    'id' => $fileInfo->id,
                    'name' => $fileInfo->name,
                    'upload_time' => $fileInfo->upload_time,
                    'pickup_code' => $fileInfo->pickup_code,
                    'expire_time' => $fileInfo->expire_time,
                    'status' => $fileInfo->status
                ];
            }
            
            // 保存提取码和文件ID的关联
            $pickupData = [
                'pickup_code' => $pickupCode,
                'file_ids' => $fileIds,
                'expire_time' => time() + ($expireDays * 24 * 3600)
            ];
            file_put_contents(DATA_DIR . 'pickup_' . $pickupCode . '.json', json_encode($pickupData));
            
            $this->jsonResponse(['success' => true, 'pickup_code' => $pickupCode]);
            
        } catch (Exception $e) {
            $this->jsonResponse(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }
    
    public function pickup() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('?action=home');
        }
        
        $pickupCode = $_POST['pickup_code'] ?? '';
        
        // 查找匹配的文件
        $pickupFile = DATA_DIR . 'pickup_' . $pickupCode . '.json';
        
        if (!file_exists($pickupFile)) {
            $this->redirect('?action=pickup&pickup_error=1&message=无效的取件码');
        }
        
        $pickupData = json_decode(file_get_contents($pickupFile), true);
        
        // 检查提取码是否过期
        if (time() > $pickupData['expire_time']) {
            $this->redirect('?action=pickup&pickup_error=1&message=文件已过期');
        }
        
        // 获取第一个文件ID
        $firstFileId = $pickupData['file_ids'][0];
        $fileInfo = FileInfo::load($firstFileId);
        
        // 检查文件审核状态
        if ($fileInfo) {
            checkFileStatus($fileInfo, false);
        }
        
        // 重定向到查看页面
        $this->redirect('?action=view&id=' . $firstFileId);
    }
    
    public function view() {
        $fileId = $_GET['id'] ?? '';
        
        if (!$fileId) {
            $this->render('error', ['title' => '错误', 'message' => '无效的文件ID', 'code' => 404]);
            return;
        }
        
        $fileInfo = FileInfo::load($fileId);
        
        if (!$fileInfo) {
            $this->render('error', ['title' => '错误', 'message' => '文件不存在', 'code' => 404]);
            return;
        }
        
        // 检查文件状态
        checkFileStatus($fileInfo, false);
        
        // 获取同批次的其他文件
        $allFiles = [];
        $pickupFile = DATA_DIR . 'pickup_' . $fileInfo->pickup_code . '.json';
        if (file_exists($pickupFile)) {
            $pickupData = json_decode(file_get_contents($pickupFile), true);
            foreach ($pickupData['file_ids'] as $id) {
                $file = FileInfo::load($id);
                if ($file) {
                    $allFiles[] = $file;
                }
            }
        }
        
        // 找到当前文件在列表中的位置
        $currentIndex = 0;
        foreach ($allFiles as $index => $file) {
            if ($file->id === $fileId) {
                $currentIndex = $index;
                break;
            }
        }
        
        $data = [
            'title' => '文件预览',
            'fileInfo' => $fileInfo,
            'allFiles' => $allFiles,
            'currentIndex' => $currentIndex,
            'config' => loadConfig(),
            'getFileType' => [$this, 'getFileType'],
            'getFileLink' => [$this, 'getFileLink'],
            'formatSize' => [$this, 'formatSize']
        ];
        
        $this->render('view', $data);
    }
    
    public function download() {
        $fileId = $_GET['id'] ?? '';
        
        if (!$fileId) {
            $this->render('error', ['title' => '错误', 'message' => '无效的文件ID', 'code' => 404]);
            return;
        }
        
        $fileInfo = FileInfo::load($fileId);
        
        if (!$fileInfo) {
            $this->render('error', ['title' => '错误', 'message' => '文件不存在', 'code' => 404]);
            return;
        }
        
        // 检查文件状态
        checkFileStatus($fileInfo, false);
        
        // 检查文件是否存在
        if (!file_exists($fileInfo->path)) {
            $this->render('error', ['title' => '错误', 'message' => '文件已被删除', 'code' => 404]);
            return;
        }
        
        // 添加到下载记录
        $_SESSION['downloaded_files'][] = [
            'id' => $fileInfo->id,
            'name' => $fileInfo->name,
            'download_time' => time(),
            'pickup_code' => $fileInfo->pickup_code
        ];
        
        // 设置响应头
        header('Content-Type: ' . $fileInfo->type);
        header('Content-Disposition: attachment; filename="' . $fileInfo->name . '"');
        header('Content-Length: ' . $fileInfo->size);
        header('Cache-Control: no-cache, no-store, must-revalidate');
        header('Pragma: no-cache');
        header('Expires: 0');
        
        // 输出文件内容
        readfile($fileInfo->path);
        exit;
    }
}

// 管理员控制器
class AdminController extends BaseController {
    public function dashboard() {
        if (!AuthManager::isAuthenticated()) {
            // 处理登录提交
            $error = "";
            if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['username'], $_POST['password'])) {
                if (AuthManager::login($_POST['username'], $_POST['password'])) {
                    logAdminOperation('login', [
                        'username' => $_POST['username'],
                        'login_time' => date('Y-m-d H:i:s')
                    ]);
                    $this->redirect('?action=admin');
                } else {
                    $error = "用户名或密码错误";
                }
            }
            
            $this->render('admin_login', ['title' => '管理员登录', 'error' => $error, 'isAdminPage' => true]);
            return;
        }
        
        // 获取当前选中的菜单
        $menu = $_GET['menu'] ?? 'files';
        
        // 处理清理请求
        if ($menu === 'records') {
            $type = $_GET['type'] ?? 'uploads';
            
            // 处理上传记录清理
            if (isset($_POST['clear_uploads']) && $type === 'uploads') {
                unset($_SESSION['uploaded_files']);
                logAdminOperation('clear_uploads', [
                    'action_time' => date('Y-m-d H:i:s'),
                    'cleared_count' => count($_SESSION['uploaded_files'] ?? [])
                ]);
                $this->redirect('?action=admin&menu=records&type=uploads&admin_action=1&status=success&message=上传记录已清理');
            }
            
            // 处理下载记录清理
            if (isset($_POST['clear_downloads']) && $type === 'downloads') {
                unset($_SESSION['downloaded_files']);
                logAdminOperation('clear_downloads', [
                    'action_time' => date('Y-m-d H:i:s'),
                    'cleared_count' => count($_SESSION['downloaded_files'] ?? [])
                ]);
                $this->redirect('?action=admin&menu=records&type=downloads&admin_action=1&status=success&message=下载记录已清理');
            }
        }
        
        $filter = $_GET['filter'] ?? 'all';
        $page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
        $perPage = 20;
        
        // 获取文件列表
        $files = [];
        $totalFiles = 0;
        $totalPages = 1;
        
        foreach (glob(DATA_DIR . '*.json') as $file) {
            if (basename($file) === 'config.json' || strpos(basename($file), 'pickup_') === 0) {
                continue;
            }
            
            $fileInfo = FileInfo::load(basename($file, '.json'));
            if (!$fileInfo) continue;
            
            // 应用过滤器
            if ($filter === 'pending' && $fileInfo->status != 0) continue;
            if ($filter === 'approved' && $fileInfo->status != 1) continue;
            if ($filter === 'blocked' && $fileInfo->status != 2) continue;
            
            $files[] = $fileInfo;
        }
        
        $totalFiles = count($files);
        $totalPages = max(1, ceil($totalFiles / $perPage));
        
        // 排序：最新的文件在前
        usort($files, function($a, $b) {
            return $b->upload_time - $a->upload_time;
        });
        
        // 分页
        $files = array_slice($files, ($page - 1) * $perPage, $perPage);
        
        // 获取统计信息（使用修复后的月度统计函数）
        $stats = getMonthlyStats();
        
        // 如果是统计页面，添加额外的系统统计信息
        if ($menu === 'stats') {
            // 添加一些基本的系统统计
            $basicStats = [
                'total_size' => 0,
                'expired_files' => 0,
                'today_uploads' => 0,
                'today_downloads' => 0
            ];
            
            // 计算总大小和过期文件
            foreach (glob(DATA_DIR . '*.json') as $file) {
                if (basename($file) === 'config.json' || strpos(basename($file), 'pickup_') === 0) {
                    continue;
                }
                
                $fileInfo = FileInfo::load(basename($file, '.json'));
                if (!$fileInfo) continue;
                
                $basicStats['total_size'] += $fileInfo->size;
                
                if (time() > $fileInfo->expire_time) {
                    $basicStats['expired_files']++;
                }
                
                if ($fileInfo->upload_time >= strtotime('today')) {
                    $basicStats['today_uploads']++;
                }
            }
            
            // 统计今日下载
            if (isset($_SESSION['downloaded_files']) && is_array($_SESSION['downloaded_files'])) {
                $today = strtotime('today');
                foreach ($_SESSION['downloaded_files'] as $download) {
                    if (isset($download['download_time']) && $download['download_time'] >= $today) {
                        $basicStats['today_downloads']++;
                    }
                }
            }
            
            $stats = array_merge($stats, $basicStats);
        }
        
        $data = [
            'title' => '管理员面板',
            'menu' => $menu,
            'files' => $files,
            'filter' => $filter,
            'currentPage' => $page,
            'totalPages' => $totalPages,
            'totalFiles' => $totalFiles,
            'config' => loadConfig(),
            'stats' => $stats,
            'isAdminPage' => true
        ];
        
        $this->render('admin_dashboard', $data);
    }
    
    public function approve() {
        AuthManager::requireAuth();
        
        $fileId = $_GET['id'] ?? '';
        $filter = $_GET['filter'] ?? 'all';
        
        if (!$fileId) {
            $this->redirect('?action=admin&menu=files&admin_action=1&status=error&message=无效的文件ID&filter=' . $filter);
        }
        
        $fileInfo = FileInfo::load($fileId);
        if (!$fileInfo) {
            $this->redirect('?action=admin&menu=files&admin_action=1&status=error&message=文件不存在&filter=' . $filter);
        }
        
        $fileInfo->status = 1; // 审核通过
        $fileInfo->save();
        
        logAdminOperation('approve_file', [
            'file_id' => $fileId,
            'file_name' => $fileInfo->name,
            'file_size' => $fileInfo->size,
            'action_time' => date('Y-m-d H:i:s')
        ]);
        
        $this->redirect('?action=admin&menu=files&admin_action=1&status=success&message=文件已审核通过&filter=' . $filter);
    }
    
    public function block() {
        AuthManager::requireAuth();
        
        $fileId = $_GET['id'] ?? '';
        $filter = $_GET['filter'] ?? 'all';
        
        if (!$fileId) {
            $this->redirect('?action=admin&menu=files&admin_action=1&status=error&message=无效的文件ID&filter=' . $filter);
        }
        
        $fileInfo = FileInfo::load($fileId);
        if (!$fileInfo) {
            $this->redirect('?action=admin&menu=files&admin_action=1&status=error&message=文件不存在&filter=' . $filter);
        }
        
        $fileInfo->status = 2; // 封禁
        $fileInfo->save();
        
        logAdminOperation('block_file', [
            'file_id' => $fileId,
            'file_name' => $fileInfo->name,
            'file_size' => $fileInfo->size,
            'action_time' => date('Y-m-d H:i:s')
        ]);
        
        $this->redirect('?action=admin&menu=files&admin_action=1&status=success&message=文件已封禁&filter=' . $filter);
    }
    
    public function delete() {
        AuthManager::requireAuth();
        
        $fileId = $_GET['id'] ?? '';
        $filter = $_GET['filter'] ?? 'all';
        
        if (!$fileId) {
            $this->redirect('?action=admin&menu=files&admin_action=1&status=error&message=无效的文件ID&filter=' . $filter);
        }
        
        $fileInfo = FileInfo::load($fileId);
        if (!$fileInfo) {
            $this->redirect('?action=admin&menu=files&admin_action=1&status=error&message=文件不存在&filter=' . $filter);
        }
        
        // 删除文件
        if (file_exists($fileInfo->path)) {
            unlink($fileInfo->path);
        }
        
        // 删除文件信息
        $infoFile = DATA_DIR . $fileId . '.json';
        if (file_exists($infoFile)) {
            unlink($infoFile);
        }
        
        logAdminOperation('delete_file', [
            'file_id' => $fileId,
            'file_name' => $fileInfo->name,
            'file_size' => $fileInfo->size,
            'file_path' => $fileInfo->path,
            'action_time' => date('Y-m-d H:i:s')
        ]);
        
        $this->redirect('?action=admin&menu=files&admin_action=1&status=success&message=文件已删除&filter=' . $filter);
    }
    
    public function saveConfig() {
        AuthManager::requireAuth();
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // 获取基本配置
            $config = [
                'site_name' => $_POST['site_name'] ?? '文件快递柜',
                'moderation_enabled' => isset($_POST['moderation_enabled']),
                'max_upload_size' => intval($_POST['max_upload_size']) ?: 500,
            ];
            
            // 处理允许的文件类型
            $allowedTypes = $_POST['allowed_types'] ?? [];
            if (is_array($allowedTypes)) {
                // 验证文件类型的安全性
                $validTypes = [
                    'image/jpeg', 'image/png', 'image/gif', 'image/webp',
                    'video/mp4', 'video/quicktime', 'video/x-msvideo', 'video/x-matroska',
                    'audio/mpeg', 'audio/wav', 'audio/ogg',
                    'text/plain',
                    'application/pdf', 'application/msword',
                    'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                    'application/vnd.ms-excel',
                    'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                    'application/vnd.ms-powerpoint',
                    'application/vnd.openxmlformats-officedocument.presentationml.presentation',
                    'application/zip', 'application/x-rar-compressed', 'application/x-7z-compressed', 'application/x-tar',
                    // 代码类型
                    'text/x-php', 'text/x-c', 'text/x-c++', 'text/x-python', 'text/x-java',
                    'text/x-javascript', 'text/x-html', 'text/x-css', 'text/x-sql',
                    'text/x-shellscript', 'text/x-perl', 'text/x-ruby', 'text/x-go',
                    'text/x-rust', 'text/x-swift', 'text/x-kotlin', 'text/x-typescript',
                    'text/x-markdown', 'text/x-yaml', 'text/x-json', 'text/x-xml'
                ];
                
                // 只保留有效的文件类型
                $config['allowed_types'] = array_intersect($allowedTypes, $validTypes);
                
                // 如果没有选择任何类型，使用默认类型
                if (empty($config['allowed_types'])) {
                    $config['allowed_types'] = [
                        'image/jpeg', 'image/png', 'image/gif',
                        'video/mp4', 'audio/mpeg', 'text/plain',
                        'application/pdf', 'application/msword',
                        'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                        'application/vnd.ms-excel',
                        'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                        'application/zip',
                        'text/x-php', 'text/x-python', 'text/x-javascript',
                        'text/x-html', 'text/x-css', 'text/x-sql',
                        'text/x-markdown', 'text/x-json', 'text/x-yaml'
                    ];
                }
            } else {
                // 默认文件类型
                $config['allowed_types'] = [
                    'image/jpeg', 'image/png', 'image/gif',
                    'video/mp4', 'audio/mpeg', 'text/plain',
                    'application/pdf', 'application/msword',
                    'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                    'application/vnd.ms-excel',
                    'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                    'application/zip',
                    'text/x-php', 'text/x-python', 'text/x-javascript',
                    'text/x-html', 'text/x-css', 'text/x-sql',
                    'text/x-markdown', 'text/x-json', 'text/x-yaml'
                ];
            }
            
            file_put_contents(CONFIG_FILE, json_encode($config, JSON_PRETTY_PRINT));
            
            logAdminOperation('config_update', [
                'config_changes' => $config,
                'update_time' => date('Y-m-d H:i:s')
            ]);
            
            $this->redirect('?action=admin&menu=config&admin_action=1&status=success&message=配置已保存');
        }
        
        $this->redirect('?action=admin&menu=config');
    }
    
    public function logout() {
        logAdminOperation('logout', [
            'logout_time' => date('Y-m-d H:i:s')
        ]);
        AuthManager::logout();
        $this->redirect('?action=admin');
    }
    
    public function adminPreview() {
        AuthManager::requireAuth();
        
        $fileId = $_GET['id'] ?? '';
        
        if (!$fileId) {
            $this->render('error', ['title' => '错误', 'message' => '无效的文件ID', 'code' => 404]);
            return;
        }
        
        $fileInfo = FileInfo::load($fileId);
        
        if (!$fileInfo) {
            $this->render('error', ['title' => '错误', 'message' => '文件不存在', 'code' => 404]);
            return;
        }
        
        // 管理员预览，不检查文件状态，但检查文件是否存在
        if (!file_exists($fileInfo->path)) {
            $this->render('error', ['title' => '错误', 'message' => '文件已被删除', 'code' => 404]);
            return;
        }
        
        // 获取同批次的其他文件
        $allFiles = [];
        $pickupFile = DATA_DIR . 'pickup_' . $fileInfo->pickup_code . '.json';
        if (file_exists($pickupFile)) {
            $pickupData = json_decode(file_get_contents($pickupFile), true);
            foreach ($pickupData['file_ids'] as $id) {
                $file = FileInfo::load($id);
                if ($file) {
                    $allFiles[] = $file;
                }
            }
        }
        
        // 找到当前文件在列表中的位置
        $currentIndex = 0;
        foreach ($allFiles as $index => $file) {
            if ($file->id === $fileId) {
                $currentIndex = $index;
                break;
            }
        }
        
        // 记录管理员预览操作
        logAdminOperation('view_file', [
            'file_id' => $fileId,
            'file_name' => $fileInfo->name,
            'file_size' => $fileInfo->size,
            'preview_time' => date('Y-m-d H:i:s')
        ]);
        
        $data = [
            'title' => '管理员文件预览',
            'fileInfo' => $fileInfo,
            'allFiles' => $allFiles,
            'currentIndex' => $currentIndex,
            'config' => loadConfig(),
            'getFileType' => [$this, 'getFileType'],
            'getFileLink' => [$this, 'getFileLink'],
            'formatSize' => [$this, 'formatSize'],
            'isAdmin' => true, // 标记为管理员预览
            'isAdminPage' => true
        ];
        
        $this->render('admin_preview', $data);
    }
  }
?>