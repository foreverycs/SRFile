<?php
/**
 * 安全增强类
 * 提供额外的安全防护措施
 */
class SecurityEnhancer {
    /**
     * 检查请求方法是否安全
     */
    public static function isSafeMethod($method) {
        $safeMethods = ['GET', 'HEAD', 'OPTIONS'];
        return in_array(strtoupper($method), $safeMethods);
    }
    
    /**
     * 检查请求来源
     */
    public static function validateReferer() {
        if (!isset($_SERVER['HTTP_REFERER'])) {
            return false;
        }
        
        $referer = $_SERVER['HTTP_REFERER'];
        $host = $_SERVER['HTTP_HOST'];
        
        return strpos($referer, $host) !== false;
    }
    
    /**
     * 检查用户IP是否在允许列表中
     */
    public static function isAllowedIp($ip) {
        $allowedIps = ConfigManager::getSecuritySettings()['allowed_ips'] ?? [];
        
        if (empty($allowedIps)) {
            return true;
        }
        
        return in_array($ip, $allowedIps);
    }
    
    /**
     * 检查用户IP是否在阻止列表中
     */
    public static function isBlockedIp($ip) {
        $blockedIps = ConfigManager::getSecuritySettings()['blocked_ips'] ?? [];
        return in_array($ip, $blockedIps);
    }
    
    /**
     * 检查请求频率
     */
    public static function checkRequestFrequency($ip, $limit = 100, $timeWindow = 60) {
        $cacheFile = DATA_DIR . 'rate_limit_' . md5($ip) . '.json';
        
        if (!file_exists($cacheFile)) {
            $data = [
                'requests' => [],
                'blocked_until' => 0
            ];
        } else {
            $data = json_decode(file_get_contents($cacheFile), true);
        }
        
        $now = time();
        
        // 检查是否被临时阻止
        if ($data['blocked_until'] > $now) {
            return false;
        }
        
        // 清理过期的请求记录
        $windowStart = $now - $timeWindow;
        $data['requests'] = array_filter($data['requests'], function($timestamp) use ($windowStart) {
            return $timestamp > $windowStart;
        });
        
        // 检查是否超过限制
        if (count($data['requests']) >= $limit) {
            // 临时阻止该IP
            $data['blocked_until'] = $now + 300; // 5分钟
            file_put_contents($cacheFile, json_encode($data));
            
            // 记录安全事件
            ErrorHandler::logSecurityEvent('rate_limit_exceeded', [
                'ip' => $ip,
                'requests_count' => count($data['requests']),
                'limit' => $limit,
                'time_window' => $timeWindow
            ]);
            
            return false;
        }
        
        // 记录当前请求
        $data['requests'][] = $now;
        file_put_contents($cacheFile, json_encode($data));
        
        return true;
    }
    
    /**
     * 检查文件上传安全
     */
    public static function validateFileUpload($file, $config) {
        $errors = [];
        
        // 检查文件大小
        $maxSize = ConfigManager::getUploadLimit();
        if ($file['size'] > $maxSize) {
            $errors[] = '文件大小超过限制 (最大 ' . ($maxSize / 1024 / 1024) . 'MB)';
        }
        
        // 检查文件类型
        $allowedTypes = ConfigManager::getAllowedTypes();
        if (!in_array($file['type'], $allowedTypes)) {
            $errors[] = '文件类型不允许';
        }
        
        // 检查文件内容
        if (!self::isFileContentSafe($file['tmp_name'], $file['type'])) {
            $errors[] = '文件内容不安全';
        }
        
        // 检查文件名
        if (!self::isFileNameSafe($file['name'])) {
            $errors[] = '文件名不安全';
        }
        
        return $errors;
    }
    
    /**
     * 检查文件内容是否安全
     */
    public static function isFileContentSafe($filePath, $mimeType) {
        // 检查文件是否存在
        if (!file_exists($filePath)) {
            return false;
        }
        
        // 检查文件大小
        if (filesize($filePath) == 0) {
            return false;
        }
        
        // 对于图片文件，检查是否为有效的图片
        if (strpos($mimeType, 'image/') === 0) {
            return self::isValidImage($filePath);
        }
        
        // 对于文本文件，检查内容
        if (strpos($mimeType, 'text/') === 0) {
            return self::isTextContentSafe($filePath);
        }
        
        // 对于其他文件类型，进行基本检查
        return self::isGenericFileSafe($filePath);
    }
    
    /**
     * 检查是否为有效的图片
     */
    private static function isValidImage($filePath) {
        try {
            $imageInfo = getimagesize($filePath);
            if ($imageInfo === false) {
                return false;
            }
            
            // 检查图片类型
            $allowedTypes = [IMAGETYPE_GIF, IMAGETYPE_JPEG, IMAGETYPE_PNG];
            if (!in_array($imageInfo[2], $allowedTypes)) {
                return false;
            }
            
            // 检查图片尺寸
            if ($imageInfo[0] > 4096 || $imageInfo[1] > 4096) {
                return false;
            }
            
            return true;
        } catch (Exception $e) {
            return false;
        }
    }
    
    /**
     * 检查文本内容是否安全
     */
    private static function isTextContentSafe($filePath) {
        $content = file_get_contents($filePath);
        
        // 检查文件大小
        if (strlen($content) > 1024 * 1024) { // 1MB
            return false;
        }
        
        // 检查是否包含可疑内容
        $suspiciousPatterns = [
            '/<script/i',
            '/javascript:/i',
            '/vbscript:/i',
            '/onload=/i',
            '/onerror=/i',
            '/<\?php/i',
            '/<\%/i',
            '/\?>/i',
            '/%>/i',
            '/eval\s*\(/i',
            '/exec\s*\(/i',
            '/system\s*\(/i',
            '/shell_exec\s*\(/i',
            '/passthru\s*\(/i',
            '/base64_decode\s*\(/i'
        ];
        
        foreach ($suspiciousPatterns as $pattern) {
            if (preg_match($pattern, $content)) {
                return false;
            }
        }
        
        return true;
    }
    
    /**
     * 检查通用文件安全性
     */
    private static function isGenericFileSafe($filePath) {
        // 检查文件头
        $handle = fopen($filePath, 'rb');
        $header = fread($handle, 32);
        fclose($handle);
        
        // 检查是否为可执行文件
        $executableHeaders = [
            "\x7F\x45\x4C\x46", // ELF
            "\x4D\x5A",         // PE
            "\xCA\xFE\xBA\xBE", // Mach-O
            "\xFE\xED\xFA\xCE", // Mach-O
            "\xFE\xED\xFA\xCF"  // Mach-O
        ];
        
        foreach ($executableHeaders as $exeHeader) {
            if (strpos($header, $exeHeader) === 0) {
                return false;
            }
        }
        
        return true;
    }
    
    /**
     * 检查文件名是否安全
     */
    public static function isFileNameSafe($fileName) {
        // 检查文件名长度
        if (strlen($fileName) > 255) {
            return false;
        }
        
        // 检查文件名字符
        if (!preg_match('/^[\w\s\-\.]+$/u', $fileName)) {
            return false;
        }
        
        // 检查文件名是否包含路径遍历字符
        if (strpos($fileName, '..') !== false || 
            strpos($fileName, '/') !== false || 
            strpos($fileName, '\\') !== false) {
            return false;
        }
        
        // 检查是否为隐藏文件
        if (strpos($fileName, '.') === 0) {
            return false;
        }
        
        // 检查是否为系统文件
        $systemFiles = ['con', 'prn', 'aux', 'nul', 'com1', 'com2', 'com3', 'com4', 
                        'lpt1', 'lpt2', 'lpt3', 'lpt4'];
        $fileNameLower = strtolower($fileName);
        
        foreach ($systemFiles as $systemFile) {
            if ($fileNameLower === $systemFile) {
                return false;
            }
        }
        
        return true;
    }
    
    /**
     * 生成安全的文件名
     */
    public static function generateSafeFileName($originalName) {
        // 获取文件扩展名
        $extension = pathinfo($originalName, PATHINFO_EXTENSION);
        
        // 生成随机文件名
        $randomName = bin2hex(random_bytes(16));
        
        // 组合文件名
        if ($extension) {
            return $randomName . '.' . $extension;
        }
        
        return $randomName;
    }
    
    /**
     * 检查SQL注入
     */
    public static function detectSqlInjection($input) {
        $sqlPatterns = [
            '/\b(SELECT|INSERT|UPDATE|DELETE|DROP|CREATE|ALTER|TRUNCATE)\b/i',
            '/\b(UNION|JOIN|WHERE|OR|AND)\b/i',
            '/\b(IF|THEN|ELSE|END|CASE|WHEN)\b/i',
            '/\b(EXEC|EXECUTE|EXECSP)\b/i',
            '/\b(XP_|SP_)\b/i',
            '/--/',
            '/\/\*/',
            '/\*\/',
            '/;\s*$/',
            '/\'\s*OR\s*\'\s*\'\s*=/i',
            '/\'\s*OR\s*1\s*=\s*1/i',
            '/\'\s*AND\s*1\s*=\s*1/i',
            '/\'\s*AND\s*1\s*=\s*2/i'
        ];
        
        foreach ($sqlPatterns as $pattern) {
            if (preg_match($pattern, $input)) {
                return true;
            }
        }
        
        return false;
    }
    
    /**
     * 检查XSS攻击
     */
    public static function detectXss($input) {
        $xssPatterns = [
            '/<script/i',
            '/javascript:/i',
            '/vbscript:/i',
            '/onload=/i',
            '/onerror=/i',
            '/onclick=/i',
            '/onmouseover=/i',
            '/onfocus=/i',
            '/onblur=/i',
            '/<\?php/i',
            '/<\%/i',
            '/\?>/i',
            '/%>/i',
            '/<iframe/i',
            '/<object/i',
            '/<embed/i',
            '/<link/i',
            '/<meta/i',
            '/expression\s*\(/i',
            '/eval\s*\(/i'
        ];
        
        foreach ($xssPatterns as $pattern) {
            if (preg_match($pattern, $input)) {
                return true;
            }
        }
        
        return false;
    }
    
    /**
     * 检查CSRF攻击
     */
    public static function validateCsrfToken() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            return true;
        }
        
        $token = $_POST['csrf_token'] ?? '';
        
        if (!SessionManager::validateCsrfToken($token)) {
            ErrorHandler::logSecurityEvent('csrf_attack', [
                'ip' => $_SERVER['REMOTE_ADDR'] ?? 'unknown',
                'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'unknown',
                'request_uri' => $_SERVER['REQUEST_URI'] ?? 'unknown'
            ]);
            
            return false;
        }
        
        return true;
    }
    
    /**
     * 获取客户端信息
     */
    public static function getClientInfo() {
        return [
            'ip' => $_SERVER['REMOTE_ADDR'] ?? 'unknown',
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'unknown',
            'referer' => $_SERVER['HTTP_REFERER'] ?? 'unknown',
            'request_method' => $_SERVER['REQUEST_METHOD'] ?? 'unknown',
            'request_uri' => $_SERVER['REQUEST_URI'] ?? 'unknown',
            'request_time' => $_SERVER['REQUEST_TIME'] ?? time()
        ];
    }
    
    /**
     * 记录安全事件
     */
    public static function logSecurityEvent($event, $details = []) {
        $clientInfo = self::getClientInfo();
        
        $logEntry = [
            'timestamp' => date('Y-m-d H:i:s'),
            'event' => $event,
            'client_info' => $clientInfo,
            'details' => $details
        ];
        
        $logFile = DATA_DIR . 'security_events.log';
        file_put_contents($logFile, json_encode($logEntry) . "\n", FILE_APPEND);
        
        // 对于严重的安全事件，发送警报
        $criticalEvents = ['csrf_attack', 'sql_injection_attempt', 'xss_attempt', 'file_upload_rejected'];
        if (in_array($event, $criticalEvents)) {
            self::sendSecurityAlert($event, $logEntry);
        }
    }
    
    /**
     * 发送安全警报
     */
    private static function sendSecurityAlert($event, $details) {
        // 这里可以集成邮件发送、短信通知等
        // 目前只是记录到专门的警报文件
        $alertFile = DATA_DIR . 'security_alerts.log';
        
        $alertMessage = "【安全警报】" . date('Y-m-d H:i:s') . "\n";
        $alertMessage .= "事件类型: " . $event . "\n";
        $alertMessage .= "详细信息: " . json_encode($details, JSON_UNESCAPED_UNICODE) . "\n";
        $alertMessage .= "----------------------------------------\n";
        
        file_put_contents($alertFile, $alertMessage, FILE_APPEND);
    }
    
    /**
     * 清理过期的安全日志
     */
    public static function cleanupSecurityLogs($days = 30) {
        $files = [
            DATA_DIR . 'security_events.log',
            DATA_DIR . 'security_alerts.log'
        ];
        
        $cutoffTime = time() - ($days * 24 * 3600);
        
        foreach ($files as $file) {
            if (file_exists($file)) {
                $lines = file($file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
                $newLines = [];
                
                foreach ($lines as $line) {
                    $logEntry = json_decode($line, true);
                    if ($logEntry && isset($logEntry['timestamp'])) {
                        $logTime = strtotime($logEntry['timestamp']);
                        if ($logTime > $cutoffTime) {
                            $newLines[] = $line;
                        }
                    }
                }
                
                file_put_contents($file, implode("\n", $newLines) . "\n");
            }
        }
        
        // 清理频率限制文件
        $rateLimitFiles = glob(DATA_DIR . 'rate_limit_*.json');
        foreach ($rateLimitFiles as $file) {
            if (filemtime($file) < $cutoffTime) {
                unlink($file);
            }
        }
    }
}
?>