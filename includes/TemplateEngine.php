<?php
// 简单的模板引擎类
class TemplateEngine {
    private $templateDir;
    private $data = [];
    
    public function __construct($templateDir = 'templates') {
        $this->templateDir = $templateDir;
        if (!file_exists($this->templateDir)) {
            mkdir($this->templateDir, 0755, true);
        }
    }
    
    public function assign($key, $value) {
        $this->data[$key] = $value;
    }
    
    public function render($template, $data = []) {
        // 合并数据
        $data = array_merge($this->data, $data);
        
        // 提取变量到当前作用域
        extract($data);
        
        // 开始输出缓冲
        ob_start();
        
        // 包含模板文件
        $templateFile = $this->templateDir . '/' . $template . '.php';
        if (file_exists($templateFile)) {
            include $templateFile;
        } else {
            echo "模板文件不存在: " . htmlspecialchars($templateFile);
        }
        
        // 返回输出内容
        return ob_get_clean();
    }
    
    public function display($template, $data = []) {
        echo $this->render($template, $data);
    }
}

// 基础页面布局类
class BaseLayout {
    private $templateEngine;
    
    public function __construct() {
        $this->templateEngine = new TemplateEngine();
    }
    
    public function renderHeader($title = '', $data = []) {
        $defaultData = [
            'title' => $title,
            'showAdminLink' => true,
            'config' => loadConfig()
        ];
        
        $data = array_merge($defaultData, $data);
        $this->templateEngine->display('header', $data);
    }
    
    public function renderFooter($data = []) {
        $this->templateEngine->display('footer', $data);
    }
    
    public function renderError($message, $code = 404) {
        http_response_code($code);
        $this->renderHeader('错误');
        $this->templateEngine->display('error', ['message' => $message, 'code' => $code]);
        $this->renderFooter();
    }
    
    public function renderSuccess($message, $redirectUrl = null) {
        $this->renderHeader('成功');
        $this->templateEngine->display('success', [
            'message' => $message,
            'redirectUrl' => $redirectUrl
        ]);
        $this->renderFooter();
    }
}

// 公共样式和脚本管理
class AssetManager {
    private static $styles = [];
    private static $scripts = [];
    
    public static function addStyle($href, $attributes = []) {
        self::$styles[] = [
            'href' => $href,
            'attributes' => $attributes
        ];
    }
    
    public static function addScript($src, $attributes = []) {
        self::$scripts[] = [
            'src' => $src,
            'attributes' => $attributes
        ];
    }
    
    public static function renderStyles() {
        $output = '';
        foreach (self::$styles as $style) {
            $attrs = '';
            foreach ($style['attributes'] as $key => $value) {
                $attrs .= ' ' . $key . '="' . htmlspecialchars($value) . '"';
            }
            $output .= '<link rel="stylesheet" href="' . htmlspecialchars($style['href']) . '"' . $attrs . '>' . "\n";
        }
        return $output;
    }
    
    public static function renderScripts() {
        $output = '';
        foreach (self::$scripts as $script) {
            $attrs = '';
            foreach ($script['attributes'] as $key => $value) {
                $attrs .= ' ' . $key . '="' . htmlspecialchars($value) . '"';
            }
            $output .= '<script src="' . htmlspecialchars($script['src']) . '"' . $attrs . '></script>' . "\n";
        }
        return $output;
    }
    
    public static function clear() {
        self::$styles = [];
        self::$scripts = [];
    }
}

// 表单验证和处理类
class FormHandler {
    private $errors = [];
    private $data = [];
    
    public function __construct($data = []) {
        $this->data = $data;
    }
    
    public function validateRequired($field, $message = null) {
        if (!isset($this->data[$field]) || empty($this->data[$field])) {
            $this->errors[$field] = $message ?: "$field 是必填项";
            return false;
        }
        return true;
    }
    
    public function validateEmail($field, $message = null) {
        if (!filter_var($this->data[$field], FILTER_VALIDATE_EMAIL)) {
            $this->errors[$field] = $message ?: "$field 格式不正确";
            return false;
        }
        return true;
    }
    
    public function validateMinLength($field, $length, $message = null) {
        if (strlen($this->data[$field]) < $length) {
            $this->errors[$field] = $message ?: "$field 长度不能少于 $length 个字符";
            return false;
        }
        return true;
    }
    
    public function validateMaxLength($field, $length, $message = null) {
        if (strlen($this->data[$field]) > $length) {
            $this->errors[$field] = $message ?: "$field 长度不能超过 $length 个字符";
            return false;
        }
        return true;
    }
    
    public function validateNumeric($field, $message = null) {
        if (!is_numeric($this->data[$field])) {
            $this->errors[$field] = $message ?: "$field 必须是数字";
            return false;
        }
        return true;
    }
    
    public function validateRange($field, $min, $max, $message = null) {
        $value = $this->data[$field];
        if ($value < $min || $value > $max) {
            $this->errors[$field] = $message ?: "$field 必须在 $min 和 $max 之间";
            return false;
        }
        return true;
    }
    
    public function getErrors() {
        return $this->errors;
    }
    
    public function hasErrors() {
        return !empty($this->errors);
    }
    
    public function getFirstError() {
        return !empty($this->errors) ? reset($this->errors) : null;
    }
    
    public function getSanitizedData() {
        $sanitized = [];
        foreach ($this->data as $key => $value) {
            if (is_string($value)) {
                $sanitized[$key] = htmlspecialchars(trim($value));
            } else {
                $sanitized[$key] = $value;
            }
        }
        return $sanitized;
    }
}

// 数据库操作辅助类（为将来可能使用数据库做准备）
class TemplateDatabaseHelper {
    private static $instance = null;
    private $connection = null;
    
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    private function __construct() {
        // 目前使用文件系统，这个类为将来扩展准备
    }
    
    public function query($sql, $params = []) {
        // 占位符实现
        return [];
    }
    
    public function insert($table, $data) {
        // 占位符实现
        return true;
    }
    
    public function update($table, $data, $where) {
        // 占位符实现
        return true;
    }
    
    public function delete($table, $where) {
        // 占位符实现
        return true;
    }
}
?>