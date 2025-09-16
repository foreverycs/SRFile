<?php
// 新的入口文件
require_once 'config.php';
require_once 'includes/ErrorHandler.php';
require_once 'includes/TemplateEngine.php';
require_once 'includes/Controllers.php';

// 启动session
session_start();

// 初始化用户记录
if (!isset($_SESSION['uploaded_files'])) {
    $_SESSION['uploaded_files'] = [];
}
if (!isset($_SESSION['downloaded_files'])) {
    $_SESSION['downloaded_files'] = [];
}

// 路由处理
$action = $_GET['action'] ?? 'home';

try {
    switch ($action) {
        case 'home':
            $controller = new HomeController();
            $controller->index();
            break;
        case 'upload':
            $controller = new FileController();
            $controller->upload();
            break;
        case 'pickup':
            $controller = new FileController();
            $controller->pickup();
            break;
        case 'view':
            $controller = new FileController();
            $controller->view();
            break;
        case 'download':
            $controller = new FileController();
            $controller->download();
            break;
        case 'admin':
            $controller = new AdminController();
            $controller->dashboard();
            break;
        case 'approve':
            $controller = new AdminController();
            $controller->approve();
            break;
        case 'block':
            $controller = new AdminController();
            $controller->block();
            break;
        case 'delete':
            $controller = new AdminController();
            $controller->delete();
            break;
        case 'logout':
            $controller = new AdminController();
            $controller->logout();
            break;
        case 'save_config':
            $controller = new AdminController();
            $controller->saveConfig();
            break;
        case 'admin_preview':
            $controller = new AdminController();
            $controller->adminPreview();
            break;
        default:
            $controller = new HomeController();
            $controller->index();
    }
} catch (Exception $e) {
    // 错误处理
    $layout = new BaseLayout();
    $layout->renderHeader('错误');
    $templateEngine = new TemplateEngine();
    $templateEngine->display('error', [
        'message' => $e->getMessage(),
        'code' => 500
    ]);
    $layout->renderFooter();
}
?>