<?php
// 系统配置
define('UPLOAD_DIR', 'uploads/');
define('DATA_DIR', 'data/');
define('CONFIG_FILE', DATA_DIR . 'config.json');
define('MAX_FILE_SIZE', 100 * 1024 * 1024); // 100MB
define('EXPIRE_DAYS', 1); // 默认1天过期
define('ADMIN_USERNAME', 'admin');
define('ADMIN_PASSWORD', 'admin');
define('CHUNK_SIZE', 1 * 1024 * 1024); // 分块大小1MB

// 创建必要目录
if (!file_exists(UPLOAD_DIR)) mkdir(UPLOAD_DIR, 0777, true);
if (!file_exists(DATA_DIR)) mkdir(DATA_DIR, 0777, true);

// 初始化配置文件
if (!file_exists(CONFIG_FILE)) {
    $defaultConfig = [
        'site_name' => '文件快递柜',
        'moderation_enabled' => true,
        'max_upload_size' => 100, // MB
        'allowed_types' => [
            'image/jpeg', 'image/png', 'image/gif', 
            'video/mp4', 'video/quicktime', 'text/plain', 'audio/mpeg'
        ]
    ];
    file_put_contents(CONFIG_FILE, json_encode($defaultConfig, JSON_PRETTY_PRINT));
}

// 文件信息存储结构
class FileInfo {
    public $id;
    public $name;
    public $type;
    public $size;
    public $upload_time;
    public $expire_time;
    public $pickup_code;
    public $status; // 0:未审核, 1:已审核, 2:已封禁
    public $path;
    public $orientation;
    public $is_indexed;
    
    public function __construct($file, $expireDays = EXPIRE_DAYS) {
        $this->id = uniqid();
        $this->name = $file['name'];
        $this->type = $file['type'];
        $this->size = $file['size'];
        $this->upload_time = time();
        $this->expire_time = time() + ($expireDays * 24 * 3600);
        $this->pickup_code = sprintf('%05d', rand(0, 99999));
        
        // 根据配置决定是否需要审核
        $config = loadConfig();
        $this->status = (isset($config['moderation_enabled']) && $config['moderation_enabled']) ? 0 : 1;
        
        // 使用安全的文件名
        $safeName = isset($file['safe_name']) ? $file['safe_name'] : basename($file['name']);
        $this->path = UPLOAD_DIR . $this->id . '_' . $safeName;
        $this->orientation = $this->detectOrientation($file);
        $this->is_indexed = false;
    }
    
    private function detectOrientation($file) {
        if (strpos($this->type, 'image/') === 0 && function_exists('exif_read_data')) {
            $exif = @exif_read_data($file['tmp_name']);
            if (!empty($exif['Orientation'])) {
                return in_array($exif['Orientation'], [5, 6, 7, 8]) ? 'vertical' : 'horizontal';
            }
        }
        return 'horizontal';
    }
    
    public function save() {
        $this->is_indexed = true;
        $data = json_encode($this);
        file_put_contents(DATA_DIR . $this->id . '.json', $data);
        return $this->id;
    }
    
    public static function load($id) {
        $file = DATA_DIR . $id . '.json';
        if (!file_exists($file)) return null;
        $data = json_decode(file_get_contents($file), true);
        if (!$data) return null;
        
        // 创建FileInfo对象并恢复属性
        $fileInfo = new self(['name' => '', 'type' => '', 'size' => 0, 'tmp_name' => '']);
        foreach ($data as $key => $value) {
            $fileInfo->$key = $value;
        }
        return $fileInfo;
    }
}

// 加载配置文件
function loadConfig() {
    if (!file_exists(CONFIG_FILE)) {
        return [
            'site_name' => '文件快递柜',
            'moderation_enabled' => true,
            'max_upload_size' => 100,
            'allowed_types' => [
                'image/jpeg', 'image/png', 'image/gif', 'image/webp',
                'video/mp4', 'video/quicktime', 'video/x-msvideo', 'video/x-matroska',
                'audio/mpeg', 'audio/wav', 'audio/ogg',
                'text/plain',
                'text/x-c', 'text/x-c++', 'text/x-python', 'text/x-java',
                'text/x-javascript', 'text/x-html', 'text/x-css', 'text/x-sql',
                'text/x-shellscript', 'text/x-perl', 'text/x-ruby', 'text/x-go',
                'text/x-rust', 'text/x-swift', 'text/x-kotlin', 'text/x-typescript',
                'text/x-markdown', 'text/x-yaml', 'text/x-json', 'text/x-xml',
                'text/x-php',
                'application/pdf', 'application/msword',
                'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                'application/vnd.ms-excel',
                'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                'application/vnd.ms-powerpoint',
                'application/vnd.openxmlformats-officedocument.presentationml.presentation',
                'application/zip', 'application/x-rar-compressed', 'application/x-7z-compressed', 'application/x-tar'
            ]
        ];
    }
    return json_decode(file_get_contents(CONFIG_FILE), true);
}

// 获取本月统计数据
function getMonthlyStats() {
    $currentMonth = date('Y-m');
    $stats = [
        'total_files' => 0,
        'pending_files' => 0,
        'approved_files' => 0,
        'blocked_files' => 0,
        'file_types' => [],
        'daily_uploads' => array_fill(1, 31, 0) // 按天统计
    ];
    
    foreach (glob(DATA_DIR . '*.json') as $file) {
        // 排除配置文件和取件码文件
        if (basename($file) === 'config.json' || strpos(basename($file), 'pickup_') === 0) {
            continue;
        }
        
        $fileInfo = FileInfo::load(basename($file, '.json'));
        if (!$fileInfo) continue;
        
        $fileMonth = date('Y-m', $fileInfo->upload_time);
        if ($fileMonth !== $currentMonth) continue;
        
        $stats['total_files']++;
        
        switch ($fileInfo->status) {
            case 0: $stats['pending_files']++; break;
            case 1: $stats['approved_files']++; break;
            case 2: $stats['blocked_files']++; break;
        }
        
        // 按文件类型统计
        $typeGroup = getFileTypeGroup($fileInfo->type);
        if (!isset($stats['file_types'][$typeGroup])) {
            $stats['file_types'][$typeGroup] = 0;
        }
        $stats['file_types'][$typeGroup]++;
        
        // 按天统计
        $day = (int)date('j', $fileInfo->upload_time);
        if ($day >= 1 && $day <= 31) {
            $stats['daily_uploads'][$day]++;
        }
    }
    
    return $stats;
}

// 获取文件类型分组
function getFileTypeGroup($mime) {
    if (strpos($mime, 'image/') === 0) return '图片';
    if (strpos($mime, 'video/') === 0) return '视频';
    if (strpos($mime, 'text/') === 0) return '文本';
    if (strpos($mime, 'application/pdf') === 0) return 'PDF';
    if (strpos($mime, 'application/msword') === 0 || 
        strpos($mime, 'application/vnd.openxmlformats') === 0) return '文档';
    if (strpos($mime, 'application/vnd.ms-excel') === 0) return '表格';
    if (strpos($mime, 'application/zip') === 0 || 
        strpos($mime, 'application/x-rar-compressed') === 0) return '压缩文件';
    return '其他';
}

// 辅助函数
function findFileByPickupCode($pickupCode) {
    $pickupFile = DATA_DIR . 'pickup_' . $pickupCode . '.json';
    if (!file_exists($pickupFile)) return null;
    
    $pickupData = json_decode(file_get_contents($pickupFile), true);
    return $pickupData['file_ids'][0]; // 返回第一个文件ID
}

// 检查文件状态并处理
function checkFileStatus($fileInfo, $isAdmin = false) {
    if (!$isAdmin) {
        // 检查审核模式是否启用
        $config = loadConfig();
        $moderationEnabled = isset($config['moderation_enabled']) && $config['moderation_enabled'];
        
        // 只有在审核模式启用时才检查文件状态
        if ($moderationEnabled && $fileInfo->status == 0) {
            showFileStatusPage('pending', $fileInfo);
            exit;
        }
        
        if ($fileInfo->status == 2) {
            showFileStatusPage('blocked', $fileInfo);
            exit;
        }
        
        if (time() > $fileInfo->expire_time) {
            showFileStatusPage('expired', $fileInfo);
            exit;
        }
    }
    return true;
}

function formatSize($bytes) {
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

function getFileType($mime) {
    if (strpos($mime, 'image/') === 0) return '图片';
    if (strpos($mime, 'video/') === 0) return '视频';
    if (strpos($mime, 'text/') === 0) return '文本';
    if (strpos($mime, 'audio/') === 0) return '音频';
    return '文件';
}

function getFileLink($fileId) {
    return (isset($_SERVER['HTTPS']) ? "https" : "http") . "://$_SERVER[HTTP_HOST]$_SERVER[SCRIPT_NAME]?action=download&id=$fileId";
}

// 文件状态页面（审核中/封禁/过期）
function showFileStatusPage($status, $fileInfo) {
    $title = '';
    $icon = '';
    $message = '';
    $details = '';
    
    if ($status === 'pending') {
        $title = '文件审核中';
        $icon = 'fas fa-hourglass-half';
        $message = '文件正在等待管理员审核';
        $details = '您的文件已成功上传，正在等待管理员审核。审核通过后即可访问。联系方式QQ:2277680934';
    } elseif ($status === 'blocked') {
        $title = '文件已被封禁';
        $icon = 'fas fa-ban';
        $message = '此文件因违反使用政策已被封禁';
        $details = '此文件因违反使用政策已被管理员封禁，无法访问。如需恢复访问，请联系客服：support@example.com';
    } elseif ($status === 'expired') {
        $title = '文件已过期';
        $icon = 'fas fa-clock';
        $message = '此文件已超过有效期';
        $details = '此文件已超过有效期，无法访问。请联系文件分享者重新上传。';
    }
    
    ?>
    <!DOCTYPE html>
    <html lang="zh-CN">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title><?= $title ?> - 文件快递柜</title>
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
        <style>
            * {
                margin: 0;
                padding: 0;
                box-sizing: border-box;
                font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            }
            
            body {
                background: linear-gradient(135deg, #6a11cb 0%, #2575fc 100%);
                min-height: 100vh;
                display: flex;
                flex-direction: column;
                align-items: center;
                padding: 20px;
                color: #333;
            }
            
            .container {
                width: 100%;
                max-width: 800px;
                background: white;
                border-radius: 16px;
                box-shadow: 0 8px 32px rgba(31, 38, 135, 0.37);
                overflow: hidden;
                margin-top: 30px;
            }
            
            header {
                background: linear-gradient(90deg, #4b6cb7 0%, #182848 100%);
                color: white;
                padding: 20px;
                text-align: center;
            }
            
            h1 {
                font-size: 1.8rem;
                margin-bottom: 5px;
            }
            
            .preview-container {
                padding: 40px 20px;
                text-align: center;
            }
            
            .status-notice {
                padding: 30px;
                border-radius: 8px;
                text-align: center;
                max-width: 500px;
                margin: 0 auto;
            }
            
            .pending-notice {
                background: #fff3cd;
                color: #856404;
                border: 1px solid #ffeeba;
            }
            
            .blocked-notice {
                background: #f8d7da;
                color: #721c24;
                border: 1px solid #f5c6cb;
            }
            
            .expired-notice {
                background: #f8d7da;
                color: #721c24;
                border: 1px solid #f5c6cb;
            }
            
            .status-notice h3 {
                margin-bottom: 15px;
                font-size: 1.5rem;
            }
            
            .status-notice i {
                font-size: 3rem;
                margin-bottom: 20px;
                display: block;
            }
            
            .file-details {
                background: rgba(255,255,255,0.7);
                padding: 15px;
                border-radius: 8px;
                margin-top: 20px;
                text-align: left;
                max-width: 500px;
                margin: 20px auto 0;
            }
            
            .file-details p {
                margin-bottom: 8px;
            }
            
            .back-link {
                display: inline-block;
                margin-top: 30px;
                padding: 10px 20px;
                background: #4b6cb7;
                color: white;
                border-radius: 50px;
                text-decoration: none;
                font-weight: 500;
                transition: all 0.3s ease;
            }
            
            .back-link:hover {
                background: #3a5ca0;
                transform: translateY(-2px);
            }
            
            @media (max-width: 600px) {
                .container {
                    margin-top: 10px;
                }
                
                .status-notice {
                    padding: 20px;
                }
            }
        </style>
     </head>
    <body>
        <div class="container">
            <header>
                <h1><i class="<?= $icon ?>"></i> <?= $title ?></h1>
                <p>文件快递柜</p>
            </header>
            
            <div class="preview-container">
                <div class="status-notice <?= $status === 'pending' ? 'pending-notice' : ($status === 'expired' ? 'expired-notice' : 'blocked-notice') ?>">
                    <i class="<?= $icon ?>"></i>
                    <h3><?= $message ?></h3>
                    <p><?= $details ?></p>
                    
                    <div class="file-details">
                        <p><strong>文件名:</strong> <?= htmlspecialchars($fileInfo->name) ?></p>
                        <p><strong>上传时间:</strong> <?= date('Y-m-d H:i', $fileInfo->upload_time) ?></p>
                        <p><strong>过期时间:</strong> <?= date('Y-m-d H:i', $fileInfo->expire_time) ?></p>
                    </div>
                    
                    <a href="?action=home" class="back-link">
                        <i class="fas fa-arrow-left"></i> 返回首页
                    </a>
                </div>
            </div>
        </div>
    </body>
    </html>
    <?php
    exit;
}

// 安全验证函数
class FileSecurityValidator {
    // 允许的文件类型及其对应的真实MIME类型
    private static $allowedMimeTypes = [
        'image/jpeg' => ['jpg', 'jpeg', 'jpe', 'jfif'],
        'image/png' => ['png'],
        'image/gif' => ['gif'],
        'video/mp4' => ['mp4'],
        'video/quicktime' => ['mov'],
        'text/plain' => ['txt'],
        'audio/mpeg' => ['mp3'],
        'text/x-c' => ['c', 'h'],
        'text/x-c++' => ['cpp', 'cxx', 'cc', 'cxx', 'h', 'hpp'],
        'text/x-python' => ['py'],
        'text/x-java' => ['java'],
        'text/x-javascript' => ['js'],
        'text/x-html' => ['html', 'htm'],
        'text/x-css' => ['css'],
        'text/x-sql' => ['sql'],
        'text/x-shellscript' => ['sh', 'bash'],
        'text/x-perl' => ['pl', 'pm'],
        'text/x-ruby' => ['rb'],
        'text/x-go' => ['go'],
        'text/x-rust' => ['rs'],
        'text/x-swift' => ['swift'],
        'text/x-kotlin' => ['kt'],
        'text/x-typescript' => ['ts'],
        'text/x-markdown' => ['md', 'markdown'],
        'text/x-yaml' => ['yaml', 'yml'],
        'text/x-json' => ['json'],
        'text/x-xml' => ['xml'],
        'text/x-php' => ['php'],
        'application/pdf' => ['pdf'],
        'application/msword' => ['doc'],
        'application/vnd.openxmlformats-officedocument.wordprocessingml.document' => ['docx'],
        'application/vnd.ms-excel' => ['xls'],
        'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' => ['xlsx'],
        'application/vnd.ms-powerpoint' => ['ppt'],
        'application/vnd.openxmlformats-officedocument.presentationml.presentation' => ['pptx'],
        'application/zip' => ['zip'],
        'application/x-rar-compressed' => ['rar'],
        'application/x-7z-compressed' => ['7z'],
        'application/x-tar' => ['tar']
    ];
    
    // 危险的文件扩展名
    private static $dangerousExtensions = [
        'php', 'php3', 'php4', 'php5', 'phtml', 'exe', 'bat', 'cmd', 
        'asp', 'aspx', 'jsp', 'cgi'
    ];
    
    public static function validateFile($file, $config, $skipUploadCheck = false) {
        error_log("开始验证文件: " . $file['name']);
        
        // 1. 检查基本文件信息
        if (!isset($file['tmp_name'])) {
            error_log("验证失败：缺少临时文件名");
            throw new Exception('无效的文件上传');
        }
        
        // 只在非测试环境下检查is_uploaded_file
        if (!$skipUploadCheck && !is_uploaded_file($file['tmp_name'])) {
            error_log("验证失败：无效的文件上传");
            throw new Exception('无效的文件上传');
        }
        
        // 2. 检查文件大小
        $maxSize = ($config['max_upload_size'] ?? 100) * 1024 * 1024;
        if ($file['size'] > $maxSize) {
            throw new Exception('文件大小超过限制');
        }
        
        // 3. 验证文件扩展名
        $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        if (in_array($extension, self::$dangerousExtensions)) {
            throw new Exception('不允许的文件类型');
        }
        
        // 4. 根据扩展名和MIME类型验证
        $realMimeType = '';
        
        // 首先根据扩展名确定MIME类型
        $extensionMimeMap = [
            'jpg' => 'image/jpeg',
            'jpeg' => 'image/jpeg',
            'jpe' => 'image/jpeg',
            'jfif' => 'image/jpeg',
            'zip' => 'application/zip',
            'rar' => 'application/x-rar-compressed',
            '7z' => 'application/x-7z-compressed',
            'tar' => 'application/x-tar',
            // ... 其他文件类型映射保持不变 ...
        ];
        
        // 如果是已知的文件类型，直接使用映射的MIME类型
        if (isset($extensionMimeMap[$extension])) {
            $realMimeType = $extensionMimeMap[$extension];
            error_log("使用扩展名映射的MIME类型: {$realMimeType}");
        } else {
            // 尝试使用finfo扩展
            if (function_exists('finfo_open')) {
                $finfo = finfo_open(FILEINFO_MIME_TYPE);
                $realMimeType = finfo_file($finfo, $file['tmp_name']);
                finfo_close($finfo);
            }
            // 如果finfo不可用，尝试使用mime_content_type
            elseif (function_exists('mime_content_type')) {
                $realMimeType = mime_content_type($file['tmp_name']);
            }
            
            // 如果检测到的MIME类型是通用二进制，尝试使用扩展名推断
            if (empty($realMimeType) || $realMimeType === 'application/octet-stream') {
                $extensionToMime = [
                    'jpg' => 'image/jpeg',
                    'jpeg' => 'image/jpeg',
                    'jpe' => 'image/jpeg',
                    'jfif' => 'image/jpeg',
                    'c' => 'text/x-c',
                    'cpp' => 'text/x-c++',
                    'h' => 'text/x-c',
                    'hpp' => 'text/x-c++',
                    'py' => 'text/x-python',
                    'java' => 'text/x-java',
                    'js' => 'text/x-javascript',
                    'html' => 'text/x-html',
                    'css' => 'text/x-css',
                    'sql' => 'text/x-sql',
                    'sh' => 'text/x-shellscript',
                    'rb' => 'text/x-ruby',
                    'go' => 'text/x-go',
                    'rs' => 'text/x-rust',
                    'swift' => 'text/x-swift',
                    'kt' => 'text/x-kotlin',
                    'ts' => 'text/x-typescript',
                    'md' => 'text/x-markdown',
                    'yaml' => 'text/x-yaml',
                    'yml' => 'text/x-yaml',
                    'json' => 'text/x-json',
                    'xml' => 'text/x-xml',
                    'php' => 'text/x-php',
                    'txt' => 'text/plain'
                ];
                
                $realMimeType = $extensionToMime[$extension] ?? 'text/plain';
            }
        }
        
        // 5. 检查MIME类型是否允许
        $allowedConfigTypes = $config['allowed_types'] ?? [];
        // 5. 检查MIME类型是否被允许
        $allowedConfigTypes = $config['allowed_types'] ?? [];
        
        error_log("验证MIME类型 - 文件: {$file['name']}, MIME类型: {$realMimeType}");
        error_log("允许的类型: " . implode(', ', $allowedConfigTypes));
        
        if (!in_array($realMimeType, $allowedConfigTypes)) {
            error_log("MIME类型验证失败: {$realMimeType} 不在允许列表中");
            throw new Exception("文件 {$file['name']} 的类型不被支持");
        }
        
        // 7. 检查文件内容（针对图片）
        if (strpos($realMimeType, 'image/') === 0) {
            if (!self::validateImageFile($file['tmp_name'])) {
                throw new Exception('无效的图片文件');
            }
        }
        
        // 8. 生成安全的文件名
        $safeFilename = self::generateSafeFilename($file['name'], $extension);
        
        return [
            'valid' => true,
            'mime_type' => $realMimeType,
            'extension' => $extension,
            'safe_filename' => $safeFilename
        ];
    }
    
    private static function validateImageFile($tmpName) {
        try {
            // 尝试获取图片尺寸
            $imageInfo = getimagesize($tmpName);
            if ($imageInfo === false) {
                error_log("图片验证失败：无法获取图片尺寸");
                return false;
            }
            
            // 检查是否为有效图片
            $allowedTypes = [IMAGETYPE_JPEG, IMAGETYPE_PNG, IMAGETYPE_GIF, IMAGETYPE_WEBP];
            if (!in_array($imageInfo[2], $allowedTypes)) {
                error_log("图片验证失败：不支持的图片类型 " . $imageInfo[2]);
                return false;
            }
            
            // 对于JPEG图片，进行额外的验证
            if ($imageInfo[2] === IMAGETYPE_JPEG) {
                // 检查文件内容是否为有效的JPEG
                $handle = fopen($tmpName, 'rb');
                if (!$handle) {
                    error_log("图片验证失败：无法打开文件");
                    return false;
                }
                
                // 读取文件开头几个字节
                $header = fread($handle, 3);
                fclose($handle);
                
                // JPEG文件开头应该是 0xFF 0xD8 0xFF
                if ($header === false || strlen($header) < 3 ||
                    ord($header[0]) !== 0xFF || ord($header[1]) !== 0xD8 || ord($header[2]) !== 0xFF) {
                    error_log("图片验证失败：无效的JPEG文件头");
                    return false;
                }
            }
            
            return true;
        } catch (Exception $e) {
            error_log("图片验证异常：" . $e->getMessage());
            return false;
        }
    }
    
    private static function generateSafeFilename($originalName, $extension) {
        // 移除特殊字符，只保留字母、数字、下划线和连字符
        $safeName = preg_replace('/[^a-zA-Z0-9_\-\s]/', '', $originalName);
        $safeName = preg_replace('/\s+/', '_', $safeName);
        
        // 如果文件名为空，使用时间戳
        if (empty($safeName)) {
            $safeName = 'file_' . time();
        }
        
        // 确保文件名不会太长
        $safeName = substr($safeName, 0, 50);
        
        return $safeName . '.' . $extension;
    }
}

// 改进的认证函数
class AuthManager {
    private static $sessionKey = 'admin_authenticated';
    private static $sessionTimeKey = 'admin_auth_time';
    private static $sessionTimeout = 3600; // 1小时超时
    
    public static function login($username, $password) {
        // 使用密码哈希验证
        if ($username === ADMIN_USERNAME && self::verifyPassword($password, ADMIN_PASSWORD)) {
            $_SESSION[self::$sessionKey] = true;
            $_SESSION[self::$sessionTimeKey] = time();
            return true;
        }
        return false;
    }
    
    public static function logout() {
        unset($_SESSION[self::$sessionKey]);
        unset($_SESSION[self::$sessionTimeKey]);
    }
    
    public static function isAuthenticated() {
        if (!isset($_SESSION[self::$sessionKey]) || !$_SESSION[self::$sessionKey]) {
            return false;
        }
        
        // 检查会话超时
        if (isset($_SESSION[self::$sessionTimeKey])) {
            $elapsed = time() - $_SESSION[self::$sessionTimeKey];
            if ($elapsed > self::$sessionTimeout) {
                self::logout();
                return false;
            }
            // 更新活动时间
            $_SESSION[self::$sessionTimeKey] = time();
        }
        
        return true;
    }
    
    private static function verifyPassword($input, $stored) {
        // 这里使用简单的验证，实际项目中应该使用password_verify()
        return $input === $stored;
    }
    
    public static function requireAuth() {
        if (!self::isAuthenticated()) {
            header('Location: ?action=admin');
            exit;
        }
    }
}
?>