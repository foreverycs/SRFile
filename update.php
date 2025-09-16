<?php
/**
 * 系统更新脚本
 * 用于更新现有代码以使用新的架构组件
 */

// 显示更新信息
echo "=== 文件快递柜系统架构更新 ===\n";
echo "正在更新系统架构...\n\n";

// 1. 创建必要的目录
$directories = [
    'includes',
    'templates',
    'uploads',
    'data'
];

foreach ($directories as $dir) {
    if (!file_exists($dir)) {
        mkdir($dir, 0755, true);
        echo "✓ 创建目录: $dir\n";
    }
}

// 2. 设置目录权限
$writableDirs = ['uploads', 'data'];
foreach ($writableDirs as $dir) {
    if (is_writable($dir)) {
        echo "✓ 目录 $dir 已可写\n";
    } else {
        echo "⚠ 目录 $dir 不可写，请手动设置权限\n";
    }
}

// 3. 检查必要文件
$requiredFiles = [
    'includes/AssetManager.php',
    'includes/DataRepository.php',
    'includes/ErrorHandler.php',
    'includes/ConfigManager.php',
    'includes/SessionManager.php',
    'includes/SecurityEnhancer.php',
    'includes/Controllers.php'
];

foreach ($requiredFiles as $file) {
    if (file_exists($file)) {
        echo "✓ 文件 $file 存在\n";
    } else {
        echo "✗ 文件 $file 不存在\n";
    }
}

// 4. 检查PHP版本
if (version_compare(PHP_VERSION, '7.0.0', '>=')) {
    echo "✓ PHP版本: " . PHP_VERSION . " (满足要求)\n";
} else {
    echo "✗ PHP版本: " . PHP_VERSION . " (需要7.0或更高版本)\n";
}

// 5. 检查必要扩展
$requiredExtensions = [
    'json',
    'session',
    'fileinfo',
    'gd'
];

foreach ($requiredExtensions as $ext) {
    if (extension_loaded($ext)) {
        echo "✓ 扩展 $ext 已加载\n";
    } else {
        echo "✗ 扩展 $ext 未加载\n";
    }
}

// 6. 清理临时文件
$tempFiles = [
    'data/rate_limit_*.json',
    'data/error.log',
    'data/security_events.log'
];

foreach ($tempFiles as $pattern) {
    $files = glob($pattern);
    foreach ($files as $file) {
        if (file_exists($file)) {
            unlink($file);
            echo "✓ 清理临时文件: $file\n";
        }
    }
}

// 7. 创建默认配置文件
if (!file_exists('data/config.json')) {
    $defaultConfig = [
        'site_name' => '文件快递柜',
        'site_description' => '安全、便捷的文件传输服务',
        'admin_username' => 'admin',
        'admin_password' => 'admin123',
        'max_upload_size' => 500,
        'expire_days' => 7,
        'moderation_enabled' => true,
        'allowed_types' => [
            'image/jpeg', 'image/png', 'image/gif', 
            'video/mp4', 'video/quicktime', 'text/plain',
            'audio/mpeg', 'application/pdf'
        ],
        'enable_csrf' => true,
        'enable_rate_limit' => true,
        'enable_cache' => true,
        'cache_time' => 3600,
        'enable_email' => false,
        'contact_email' => '',
        'contact_phone' => '',
        'contact_address' => '',
        'allowed_ips' => [],
        'blocked_ips' => []
    ];
    
    file_put_contents('data/config.json', json_encode($defaultConfig, JSON_PRETTY_PRINT));
    echo "✓ 创建默认配置文件\n";
}

// 8. 创建.htaccess文件
if (!file_exists('.htaccess')) {
    $htaccessContent = <<<HTACCESS
# 启用重写引擎
RewriteEngine On

# 阻止访问敏感文件
<FilesMatch "^(config\.php|.*\.log|.*\.json)$">
    Order allow,deny
    Deny from all
</FilesMatch>

# 阻止访问数据目录
<Directory "data">
    Order allow,deny
    Deny from all
</Directory>

# 阻止访问包含目录
<Directory "includes">
    Order allow,deny
    Deny from all
</Directory>

# 设置默认字符集
AddDefaultCharset UTF-8

# 启用压缩
<IfModule mod_deflate.c>
    AddOutputFilterByType DEFLATE text/plain
    AddOutputFilterByType DEFLATE text/html
    AddOutputFilterByType DEFLATE text/xml
    AddOutputFilterByType DEFLATE text/css
    AddOutputFilterByType DEFLATE application/xml
    AddOutputFilterByType DEFLATE application/xhtml+xml
    AddOutputFilterByType DEFLATE application/rss+xml
    AddOutputFilterByType DEFLATE application/javascript
    AddOutputFilterByType DEFLATE application/x-javascript
</IfModule>

# 设置缓存头
<IfModule mod_expires.c>
    ExpiresActive On
    ExpiresByType text/css "access plus 1 year"
    ExpiresByType application/javascript "access plus 1 year"
    ExpiresByType image/x-icon "access plus 1 year"
    ExpiresByType image/gif "access plus 1 year"
    ExpiresByType image/png "access plus 1 year"
    ExpiresByType image/jpg "access plus 1 year"
    ExpiresByType image/jpeg "access plus 1 year"
</IfModule>

# 安全头
<IfModule mod_headers.c>
    Header set X-Content-Type-Options "nosniff"
    Header set X-Frame-Options "SAMEORIGIN"
    Header set X-XSS-Protection "1; mode=block"
    Header set Referrer-Policy "strict-origin-when-cross-origin"
</IfModule>

# PHP设置
<IfModule mod_php7.c>
    php_flag display_errors Off
    php_value upload_max_filesize 100M
    php_value post_max_size 100M
    php_value memory_limit 256M
    php_value max_execution_time 300
</IfModule>
HTACCESS;
    
    file_put_contents('.htaccess', $htaccessContent);
    echo "✓ 创建.htaccess文件\n";
}

// 9. 创建robots.txt文件
if (!file_exists('robots.txt')) {
    $robotsContent = "User-agent: *\nDisallow: /data/\nDisallow: /includes/\nDisallow: /admin/\n";
    file_put_contents('robots.txt', $robotsContent);
    echo "✓ 创建robots.txt文件\n";
}

// 10. 显示完成信息
echo "\n=== 更新完成 ===\n";
echo "系统架构已成功更新！\n";
echo "\n主要改进：\n";
echo "• 创建了统一的资源管理器 (AssetManager)\n";
echo "• 建立了数据访问层 (DataRepository)\n";
echo "• 增强了错误处理机制 (ErrorHandler)\n";
echo "• 集中管理配置 (ConfigManager)\n";
echo "• 改进了会话管理 (SessionManager)\n";
echo "• 增强了安全性 (SecurityEnhancer)\n";
echo "• 重构了控制器架构\n";
echo "\n访问地址：\n";
echo "• 首页: http://localhost/test/\n";
echo "• 管理员: http://localhost/test/?action=admin\n";
echo "\n默认管理员账号：\n";
echo "• 用户名: admin\n";
echo "• 密码: admin123\n";
echo "\n请及时修改默认密码！\n";
?>