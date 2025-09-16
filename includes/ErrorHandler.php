<?php
// 错误处理和日志系统
class ErrorHandler {
    private static $instance = null;
    private $logFile;
    
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    private function __construct() {
        $this->logFile = DATA_DIR . 'error.log';
        if (!file_exists($this->logFile)) {
            touch($this->logFile);
        }
        
        // 设置错误处理器
        set_error_handler([$this, 'handleError']);
        set_exception_handler([$this, 'handleException']);
        register_shutdown_function([$this, 'handleShutdown']);
    }
    
    public function handleError($errno, $errstr, $errfile, $errline) {
        if (!(error_reporting() & $errno)) {
            return false;
        }
        
        $errorType = $this->getErrorType($errno);
        $message = "[$errorType] $errstr in $errfile on line $errline";
        
        $this->logError($message);
        
        if (ini_get('display_errors')) {
            echo "<div style='color: red; border: 1px solid red; padding: 10px; margin: 10px;'>";
            echo "<strong>Error:</strong> $message";
            echo "</div>";
        }
        
        return true;
    }
    
    public function handleException($exception) {
        $message = "Uncaught Exception: " . $exception->getMessage() . 
                  " in " . $exception->getFile() . " on line " . $exception->getLine();
        
        $this->logError($message);
        
        if (ini_get('display_errors')) {
            echo "<div style='color: red; border: 1px solid red; padding: 10px; margin: 10px;'>";
            echo "<strong>Exception:</strong> $message";
            echo "<pre>" . $exception->getTraceAsString() . "</pre>";
            echo "</div>";
        }
    }
    
    public function handleShutdown() {
        $error = error_get_last();
        if ($error !== null) {
            $message = "Fatal Error: " . $error['message'] . 
                      " in " . $error['file'] . " on line " . $error['line'];
            
            $this->logError($message);
            
            if (ini_get('display_errors')) {
                echo "<div style='color: red; border: 1px solid red; padding: 10px; margin: 10px;'>";
                echo "<strong>Fatal Error:</strong> $message";
                echo "</div>";
            }
        }
    }
    
    private function getErrorType($errno) {
        switch ($errno) {
            case E_ERROR:
                return 'Error';
            case E_WARNING:
                return 'Warning';
            case E_NOTICE:
                return 'Notice';
            case E_USER_ERROR:
                return 'User Error';
            case E_USER_WARNING:
                return 'User Warning';
            case E_USER_NOTICE:
                return 'User Notice';
            case E_STRICT:
                return 'Strict';
            case E_RECOVERABLE_ERROR:
                return 'Recoverable Error';
            case E_DEPRECATED:
                return 'Deprecated';
            case E_USER_DEPRECATED:
                return 'User Deprecated';
            default:
                return 'Unknown';
        }
    }
    
    private function logError($message) {
        $timestamp = date('Y-m-d H:i:s');
        $logMessage = "[$timestamp] $message\n";
        
        // 写入日志文件
        file_put_contents($this->logFile, $logMessage, FILE_APPEND);
        
        // 限制日志文件大小
        if (filesize($this->logFile) > 1024 * 1024) { // 1MB
            $this->rotateLog();
        }
    }
    
    private function rotateLog() {
        $backupFile = $this->logFile . '.' . date('Y-m-d-H-i-s');
        rename($this->logFile, $backupFile);
        touch($this->logFile);
        
        // 只保留最近5个日志文件
        $logFiles = glob(DATA_DIR . 'error.log.*');
        if (count($logFiles) > 5) {
            usort($logFiles, function($a, $b) {
                return filemtime($a) - filemtime($b);
            });
            unlink($logFiles[0]);
        }
    }
    
    public static function logUserAction($action, $details = []) {
        $logFile = DATA_DIR . 'user_actions.log';
        $timestamp = date('Y-m-d H:i:s');
        $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
        $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? 'unknown';
        
        $logEntry = [
            'timestamp' => $timestamp,
            'ip' => $ip,
            'user_agent' => $userAgent,
            'action' => $action,
            'details' => $details
        ];
        
        $logMessage = json_encode($logEntry) . "\n";
        file_put_contents($logFile, $logMessage, FILE_APPEND);
    }
    
    public static function logSecurityEvent($event, $details = []) {
        $logFile = DATA_DIR . 'security.log';
        $timestamp = date('Y-m-d H:i:s');
        $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
        
        $logEntry = [
            'timestamp' => $timestamp,
            'ip' => $ip,
            'event' => $event,
            'details' => $details
        ];
        
        $logMessage = json_encode($logEntry) . "\n";
        file_put_contents($logFile, $logMessage, FILE_APPEND);
        
        // 对于严重的安全事件，可以发送邮件通知
        if (in_array($event, ['failed_admin_login', 'file_upload_rejected', 'csrf_attack'])) {
            self::sendSecurityAlert($event, $details);
        }
    }
    
    private static function sendSecurityAlert($event, $details) {
        // 这里可以集成邮件发送功能
        // 目前只是记录日志
        $alertFile = DATA_DIR . 'security_alerts.log';
        $timestamp = date('Y-m-d H:i:s');
        
        $alertMessage = "[$timestamp] Security Alert: $event\n";
        $alertMessage .= "Details: " . json_encode($details) . "\n";
        
        file_put_contents($alertFile, $alertMessage, FILE_APPEND);
    }
}

// 自定义异常类
class FileUploadException extends Exception {}
class AuthenticationException extends Exception {}
class ValidationException extends Exception {}
class SecurityException extends Exception {}

// 应用程序配置类
class AppConfig {
    private static $config = null;
    
    public static function get($key, $default = null) {
        if (self::$config === null) {
            self::$config = loadConfig();
        }
        
        $keys = explode('.', $key);
        $value = self::$config;
        
        foreach ($keys as $k) {
            if (isset($value[$k])) {
                $value = $value[$k];
            } else {
                return $default;
            }
        }
        
        return $value;
    }
    
    public static function set($key, $value) {
        if (self::$config === null) {
            self::$config = loadConfig();
        }
        
        $keys = explode('.', $key);
        $config = &self::$config;
        
        foreach ($keys as $k) {
            if (!isset($config[$k])) {
                $config[$k] = [];
            }
            $config = &$config[$k];
        }
        
        $config = $value;
    }
    
    public static function save() {
        if (self::$config !== null) {
            file_put_contents(CONFIG_FILE, json_encode(self::$config, JSON_PRETTY_PRINT));
        }
    }
}

// 输入清理类
class InputSanitizer {
    public static function clean($input) {
        if (is_array($input)) {
            return array_map([self::class, 'clean'], $input);
        }
        
        return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
    }
    
    public static function cleanFilename($filename) {
        // 移除路径分隔符
        $filename = str_replace(['/', '\\'], '', $filename);
        
        // 移除特殊字符
        $filename = preg_replace('/[^\w\s\-\.]/u', '', $filename);
        
        // 移除连续空格和点
        $filename = preg_replace('/[\s\.]+/', ' ', $filename);
        
        // 截断长度
        $filename = mb_substr($filename, 0, 255);
        
        return trim($filename);
    }
    
    public static function validateEmail($email) {
        return filter_var($email, FILTER_VALIDATE_EMAIL);
    }
    
    public static function validateUrl($url) {
        return filter_var($url, FILTER_VALIDATE_URL);
    }
    
    public static function validateInt($value, $min = null, $max = null) {
        if (!is_numeric($value)) {
            return false;
        }
        
        $int = (int)$value;
        
        if ($min !== null && $int < $min) {
            return false;
        }
        
        if ($max !== null && $int > $max) {
            return false;
        }
        
        return true;
    }
}

// 初始化错误处理器
ErrorHandler::getInstance();
?>