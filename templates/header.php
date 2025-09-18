<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <meta name="format-detection" content="telephone=no">
    <meta name="theme-color" content="#1a2a6c">
    <title><?= htmlspecialchars($title) ?> - <?= htmlspecialchars($config['site_name']) ?></title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
       
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        body {
            background: linear-gradient(135deg, #1a2a6c, #b21f1f, #fdbb2d);
            color: white;
            min-height: 100vh;
            padding: 20px;
            -webkit-tap-highlight-color: transparent; /* 移除移动端点击高亮 */
            -webkit-touch-callout: none; /* 禁用长按菜单 */
            -webkit-user-select: none; /* 禁用文本选择 */
            user-select: none;
        }
        
        /* 允许输入框文本选择 */
        input, textarea, select {
            -webkit-user-select: text;
            user-select: text;
        }
        
        .container {
            max-width: 1100px;
            margin: 0 auto;
            background: rgba(0, 0, 0, 0.7);
            border-radius: 15px;
            padding: 25px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.5);
        }
        
        header {
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
            padding: 0px;
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
            /* 移动端优化 */
            -webkit-touch-callout: none;
            -webkit-user-select: none;
            user-select: none;
            touch-action: manipulation; /* 禁用双击缩放 */
        }
        
        .btn:hover, .btn:active {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(0, 0, 0, 0.3);
        }
        
        /* 移动端按钮点击反馈 */
        .btn:active {
            transform: translateY(1px);
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.2);
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
                width: 95%;
            }
            
            .admin-entry {
                position: static;
                display: block;
                margin-bottom: 10px;
                text-align: center;
                width: 100%;
                box-sizing: border-box;
            }
            
            header {
                padding: 15px;
            }
            
            h1 {
                font-size: 1.5rem;
            }
            
            .content {
                padding: 15px;
            }
            
            .btn {
                padding: 12px 20px;
                font-size: 16px; /* 更大的触摸目标 */
                width: 100%;
                margin: 5px 0;
            }
            
            .form-group input,
            .form-group select,
            .form-group textarea {
                font-size: 16px; /* 防止iOS缩放 */
                padding: 12px;
            }
            
            .notification {
                padding: 12px;
                margin: 10px 0;
                font-size: 14px;
            }
        }
        
        /* 平板设备适配 */
        @media (min-width: 601px) and (max-width: 768px) {
            .container {
                padding: 20px;
                width: 90%;
            }
            
            header {
                padding: 18px;
            }
            
            h1 {
                font-size: 1.7rem;
            }
            
            .admin-entry {
                padding: 6px 14px;
                font-size: 13px;
            }
        }
        
        /* 大屏手机适配 */
        @media (min-width: 769px) and (max-width: 992px) {
            .container {
                padding: 22px;
            }
        }
        
        /* 横屏模式适配 */
        @media (max-width: 768px) and (orientation: landscape) {
            .container {
                margin-top: 5px;
                padding: 10px;
            }
            
            header {
                padding: 10px;
            }
            
            h1 {
                font-size: 1.3rem;
            }
            
            .admin-entry {
                padding: 5px 10px;
                font-size: 12px;
            }
        }
        
        /* 触摸手势支持 */
        .touch-gesture {
            position: fixed;
            bottom: 20px;
            right: 20px;
            width: 50px;
            height: 50px;
            background: rgba(0, 0, 0, 0.5);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 1.2rem;
            z-index: 1000;
            opacity: 0;
            transition: opacity 0.3s ease;
            pointer-events: none;
        }
        
        .touch-gesture.show {
            opacity: 1;
        }
        
        /* 滑动返回提示 */
        .swipe-back-indicator {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 4px;
            background: linear-gradient(90deg, #4b6cb7, #182848);
            transform: scaleX(0);
            transform-origin: left;
            transition: transform 0.3s ease;
            z-index: 1001;
        }
        
        .swipe-back-indicator.active {
            transform: scaleX(0.3);
        }
        
        /* 双击缩放提示 */
        .double-tap-indicator {
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            background: rgba(0, 0, 0, 0.7);
            color: white;
            padding: 10px 20px;
            border-radius: 20px;
            font-size: 14px;
            z-index: 1002;
            opacity: 0;
            transition: opacity 0.3s ease;
            pointer-events: none;
        }
        
        .double-tap-indicator.show {
            opacity: 1;
        }
        
        /* 长按菜单 */
        .long-press-menu {
            position: fixed;
            background: rgba(0, 0, 0, 0.8);
            border-radius: 8px;
            padding: 5px 0;
            z-index: 1003;
            display: none;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.3);
            min-width: 150px;
        }
        
        .long-press-menu.show {
            display: block;
        }
        
        .long-press-menu-item {
            padding: 10px 15px;
            color: white;
            font-size: 14px;
            cursor: pointer;
            transition: background 0.2s ease;
        }
        
        .long-press-menu-item:hover {
            background: rgba(255, 255, 255, 0.1);
        }
        
        .long-press-menu-item i {
            margin-right: 8px;
            width: 16px;
            text-align: center;
        }
    </style>
    <?= AssetManager::renderStyles() ?>
</head>
<body>
    <div class="container">
        <header>
            <?php if ($showAdminLink): ?>
                <?php if (isset($isAdminPage) && $isAdminPage): ?>
                    <!-- 管理员页面显示进入首页 -->
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
            
            <!-- 触摸手势支持元素 -->
            <div class="touch-gesture" id="touchGesture">
                <i class="fas fa-hand-pointer"></i>
            </div>
            
            <div class="swipe-back-indicator" id="swipeBackIndicator"></div>
            
            <div class="double-tap-indicator" id="doubleTapIndicator">
                双击缩放
            </div>
            
            <div class="long-press-menu" id="longPressMenu">
                <div class="long-press-menu-item" id="refreshItem">
                    <i class="fas fa-sync-alt"></i> 刷新
                </div>
                <div class="long-press-menu-item" id="backItem">
                    <i class="fas fa-arrow-left"></i> 返回
                </div>
                <div class="long-press-menu-item" id="homeItem">
                    <i class="fas fa-home"></i> 首页
                </div>
            </div>