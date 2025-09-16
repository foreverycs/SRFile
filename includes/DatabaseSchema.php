<?php
/**
 * 数据库模型设计
 * 基于现有文件存储结构，设计更高效的数据库存储方案
 */

/**
 * 数据库表结构设计
 */

// 1. 文件信息表 (files)
$filesTableSchema = "CREATE TABLE IF NOT EXISTS files (
    id VARCHAR(32) PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    type VARCHAR(100) NOT NULL,
    size BIGINT NOT NULL,
    mime_type VARCHAR(100) NOT NULL,
    upload_time INT NOT NULL,
    expire_time INT NOT NULL,
    pickup_code VARCHAR(5) NOT NULL UNIQUE,
    status TINYINT NOT NULL DEFAULT 0, -- 0:未审核, 1:已审核, 2:已封禁
    file_path VARCHAR(512) NOT NULL,
    orientation VARCHAR(20) DEFAULT 'horizontal',
    is_indexed BOOLEAN DEFAULT TRUE,
    uploader_ip VARCHAR(45),
    user_agent TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    INDEX idx_upload_time (upload_time),
    INDEX idx_expire_time (expire_time),
    INDEX idx_pickup_code (pickup_code),
    INDEX idx_status (status),
    INDEX idx_uploader_ip (uploader_ip)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";

// 2. 取件码表 (pickup_codes)
$pickupCodesTableSchema = "CREATE TABLE IF NOT EXISTS pickup_codes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    pickup_code VARCHAR(5) NOT NULL UNIQUE,
    file_ids TEXT NOT NULL, -- JSON格式存储文件ID数组
    expire_time INT NOT NULL,
    is_used BOOLEAN DEFAULT FALSE,
    used_time INT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    INDEX idx_pickup_code (pickup_code),
    INDEX idx_expire_time (expire_time),
    INDEX idx_is_used (is_used)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";

// 3. 下载记录表 (downloads)
$downloadsTableSchema = "CREATE TABLE IF NOT EXISTS downloads (
    id INT AUTO_INCREMENT PRIMARY KEY,
    file_id VARCHAR(32) NOT NULL,
    download_time INT NOT NULL,
    downloader_ip VARCHAR(45),
    user_agent TEXT,
    session_id VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    INDEX idx_file_id (file_id),
    INDEX idx_download_time (download_time),
    INDEX idx_downloader_ip (downloader_ip),
    FOREIGN KEY (file_id) REFERENCES files(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";

// 4. 系统配置表 (config)
$configTableSchema = "CREATE TABLE IF NOT EXISTS config (
    id INT AUTO_INCREMENT PRIMARY KEY,
    config_key VARCHAR(100) NOT NULL UNIQUE,
    config_value TEXT,
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";

// 5. 管理员操作日志表 (admin_logs)
$adminLogsTableSchema = "CREATE TABLE IF NOT EXISTS admin_logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    admin_id VARCHAR(50) NOT NULL,
    action_type VARCHAR(50) NOT NULL,
    action_details TEXT,
    target_id VARCHAR(32),
    ip_address VARCHAR(45),
    user_agent TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    INDEX idx_admin_id (admin_id),
    INDEX idx_action_type (action_type),
    INDEX idx_created_at (created_at),
    INDEX idx_target_id (target_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";

// 6. 分享链接表 (shares)
$sharesTableSchema = "CREATE TABLE IF NOT EXISTS shares (
    id VARCHAR(32) PRIMARY KEY,
    file_id VARCHAR(32) NOT NULL,
    share_token VARCHAR(64) NOT NULL UNIQUE,
    expire_time INT NOT NULL,
    max_downloads INT DEFAULT NULL,
    download_count INT DEFAULT 0,
    is_active BOOLEAN DEFAULT TRUE,
    created_by VARCHAR(50),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    INDEX idx_share_token (share_token),
    INDEX idx_expire_time (expire_time),
    INDEX idx_file_id (file_id),
    INDEX idx_is_active (is_active),
    FOREIGN KEY (file_id) REFERENCES files(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";

// 7. 安全日志表 (security_logs)
$securityLogsTableSchema = "CREATE TABLE IF NOT EXISTS security_logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    log_type VARCHAR(50) NOT NULL,
    severity VARCHAR(20) NOT NULL,
    message TEXT NOT NULL,
    ip_address VARCHAR(45),
    user_agent TEXT,
    session_id VARCHAR(255),
    additional_data TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    INDEX idx_log_type (log_type),
    INDEX idx_severity (severity),
    INDEX idx_ip_address (ip_address),
    INDEX idx_created_at (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";

/**
 * 初始化配置数据
 */
function initDatabaseConfig($pdo) {
    $defaultConfigs = [
        [
            'config_key' => 'site_name',
            'config_value' => '文件快递柜',
            'description' => '站点名称'
        ],
        [
            'config_key' => 'moderation_enabled',
            'config_value' => 'true',
            'description' => '是否启用审核模式'
        ],
        [
            'config_key' => 'max_upload_size',
            'config_value' => '100',
            'description' => '最大上传大小(MB)'
        ],
        [
            'config_key' => 'expire_days',
            'config_value' => '7',
            'description' => '默认过期天数'
        ],
        [
            'config_key' => 'allowed_types',
            'config_value' => json_encode([
                'image/jpeg', 'image/png', 'image/gif', 'image/webp',
                'video/mp4', 'video/webm', 'video/quicktime',
                'audio/mp3', 'audio/wav', 'audio/mpeg',
                'text/plain', 'text/html', 'text/css', 'application/json',
                'application/pdf', 'application/zip'
            ]),
            'description' => '允许的文件类型'
        ],
        [
            'config_key' => 'admin_username',
            'config_value' => 'admin',
            'description' => '管理员用户名'
        ],
        [
            'config_key' => 'admin_password',
            'config_value' => password_hash('admin123', PASSWORD_DEFAULT),
            'description' => '管理员密码'
        ]
    ];
    
    foreach ($defaultConfigs as $config) {
        $stmt = $pdo->prepare("INSERT IGNORE INTO config (config_key, config_value, description) VALUES (?, ?, ?)");
        $stmt->execute([$config['config_key'], $config['config_value'], $config['description']]);
    }
}

/**
 * 数据库连接配置
 */
class DatabaseConfig {
    const HOST = 'localhost';
    const PORT = 3306;
    const DATABASE = 'file_express_cabinet';
    const USERNAME = 'root';
    const PASSWORD = '';
    const CHARSET = 'utf8mb4';
    const OPTIONS = [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ];
    
    public static function getDSN() {
        return sprintf(
            "mysql:host=%s;port=%d;dbname=%s;charset=%s",
            self::HOST,
            self::PORT,
            self::DATABASE,
            self::CHARSET
        );
    }
}

/**
 * 性能优化建议：
 * 1. 为大型文件表考虑分区策略
 * 2. 定期清理过期数据的存储过程
 * 3. 读写分离配置
 * 4. 数据库索引优化
 * 5. 查询缓存配置
 */

/**
 * 数据迁移策略：
 * 1. 保持现有文件存储系统运行
 * 2. 逐步迁移历史数据到数据库
 * 3. 双写模式确保数据一致性
 * 4. 验证数据完整性后切换到数据库模式
 */

?>