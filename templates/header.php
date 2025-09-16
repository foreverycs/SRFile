<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($title) ?> - <?= htmlspecialchars($config['site_name']) ?></title>
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
            position: relative;
        }
        
        .admin-entry {
            position: absolute;
            top: 20px;
            right: 20px;
            background: rgba(255, 255, 255, 0.2);
            padding: 8px 16px;
            border-radius: 20px;
            text-decoration: none;
            color: white;
            font-size: 14px;
            transition: all 0.3s ease;
        }
        
        .admin-entry:hover {
            background: rgba(255, 255, 255, 0.3);
            transform: translateY(-2px);
        }
        
        h1 {
            font-size: 1.8rem;
            margin-bottom: 5px;
        }
        
        .content {
            padding: 20px;
        }
        
        .notification {
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
            text-align: center;
        }
        
        .notification.success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        
        .notification.error {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        
        .notification.warning {
            background: #fff3cd;
            color: #856404;
            border: 1px solid #ffeeba;
        }
        
        .btn {
            display: inline-block;
            padding: 10px 20px;
            background: #4b6cb7;
            color: white;
            border: none;
            border-radius: 50px;
            text-decoration: none;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.3s ease;
            margin: 5px;
        }
        
        .btn:hover {
            background: #3a5ca0;
            transform: translateY(-2px);
        }
        
        .btn-danger {
            background: #dc3545;
        }
        
        .btn-danger:hover {
            background: #c82333;
        }
        
        .btn-success {
            background: #28a745;
        }
        
        .btn-success:hover {
            background: #218838;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 5px;
            font-weight: 500;
        }
        
        .form-group input,
        .form-group select,
        .form-group textarea {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 8px;
            font-size: 16px;
        }
        
        .form-group textarea {
            resize: vertical;
            min-height: 100px;
        }
        
        .error {
            color: #dc3545;
            font-size: 14px;
            margin-top: 5px;
        }
        
        @media (max-width: 600px) {
            .container {
                margin-top: 10px;
            }
            
            .admin-entry {
                position: static;
                display: block;
                margin-bottom: 10px;
            }
        }
    </style>
    <?= AssetManager::renderStyles() ?>
</head>
<body>
    <div class="container">
        <header>
            <?php if ($showAdminLink): ?>
                <?php if (isset($isAdminPage) && $isAdminPage && AuthManager::isAuthenticated()): ?>
                    <!-- 后台界面显示进入首页 -->
                    <a href="?action=home" class="admin-entry">
                        <i class="fas fa-home"></i> 进入首页
                    </a>
                <?php else: ?>
                    <!-- 前台界面显示管理员入口 -->
                    <a href="?action=admin" class="admin-entry">
                        <i class="fas fa-lock"></i> 管理员入口
                    </a>
                <?php endif; ?>
            <?php endif; ?>
            <h1><?= htmlspecialchars($config['site_name']) ?></h1>
            <p>安全、便捷的文件传输服务</p>
        </header>
        
        <div class="content">