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
            background: linear-gradient(135deg, #1a2a6c, #b21f1f, #fdbb2d);
            color: white;
            min-height: 100vh;
            padding: 20px;
        }
        
        .container {
            max-width: 1200px;
            margin: 0 auto;
            background: rgba(0, 0, 0, 0.7);
            border-radius: 15px;
            padding: 25px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.5);
        }
        
        header {
            background: rgba(0, 0, 0, 0.3);
            color: white;
            padding: 20px;
            text-align: center;
            position: relative;
            border-radius: 10px;
            margin-bottom: 20px;
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
            text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.5);
        }
        
        .content {
            padding: 20px;
        }
        
        .notification {
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
            text-align: center;
            animation: fadeIn 0.5s;
        }
        
        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }
        
        .notification.success {
            background: rgba(40, 167, 69, 0.2);
            color: #d4edda;
            border: 1px solid rgba(40, 167, 69, 0.5);
        }
        
        .notification.error {
            background: rgba(220, 53, 69, 0.2);
            color: #f8d7da;
            border: 1px solid rgba(220, 53, 69, 0.5);
        }
        
        .notification.warning {
            background: rgba(255, 193, 7, 0.2);
            color: #fff3cd;
            border: 1px solid rgba(255, 193, 7, 0.5);
        }
        
        .btn {
            display: inline-block;
            padding: 10px 20px;
            background: linear-gradient(90deg, #4b6cb7, #182848);
            color: white;
            border: none;
            border-radius: 50px;
            text-decoration: none;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.3s ease;
            margin: 5px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
        }
        
        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(0, 0, 0, 0.3);
        }
        
        .btn-danger {
            background: linear-gradient(90deg, #dc3545, #a71e2a);
        }
        
        .btn-danger:hover {
            background: linear-gradient(90deg, #c82333, #8b1a1a);
        }
        
        .btn-success {
            background: linear-gradient(90deg, #28a745, #1e7e34);
        }
        
        .btn-success:hover {
            background: linear-gradient(90deg, #218838, #155724);
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
            border: 1px solid rgba(255, 255, 255, 0.3);
            border-radius: 8px;
            font-size: 16px;
            background: rgba(255, 255, 255, 0.1);
            color: white;
            transition: all 0.3s ease;
        }
        
        .form-group input:focus,
        .form-group select:focus,
        .form-group textarea:focus {
            outline: none;
            border-color: #fdbb2d;
            background: rgba(255, 255, 255, 0.15);
            box-shadow: 0 0 0 2px rgba(253, 187, 45, 0.2);
        }
        
        .form-group textarea {
            resize: vertical;
            min-height: 100px;
        }
        
        .error {
            color: #f8d7da;
            font-size: 14px;
            margin-top: 5px;
        }
        
        @media (max-width: 600px) {
            .container {
                margin-top: 10px;
                padding: 15px;
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