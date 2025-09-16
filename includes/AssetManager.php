<?php
/**
 * 公共样式组件库
 * 统一管理所有页面的样式和组件
 */
class AssetManager {
    private static $styles = [];
    private static $scripts = [];
    
    /**
     * 注册CSS样式
     */
    public static function addStyle($file, $priority = 10) {
        self::$styles[] = [
            'file' => $file,
            'priority' => $priority
        ];
        self::sortAssets(self::$styles);
    }
    
    /**
     * 注册JavaScript脚本
     */
    public static function addScript($file, $priority = 10) {
        self::$scripts[] = [
            'file' => $file,
            'priority' => $priority
        ];
        self::sortAssets(self::$scripts);
    }
    
    /**
     * 按优先级排序资源
     */
    private static function sortAssets(&$assets) {
        usort($assets, function($a, $b) {
            return $a['priority'] - $b['priority'];
        });
    }
    
    /**
     * 渲染所有CSS样式
     */
    public static function renderStyles() {
        $output = '';
        foreach (self::$styles as $style) {
            $output .= '<link rel="stylesheet" href="' . htmlspecialchars($style['file']) . '">' . "\n";
        }
        return $output;
    }
    
    /**
     * 渲染所有JavaScript脚本
     */
    public static function renderScripts() {
        $output = '';
        foreach (self::$scripts as $script) {
            $output .= '<script src="' . htmlspecialchars($script['file']) . '"></script>' . "\n";
        }
        return $output;
    }
    
    /**
     * 清空所有资源
     */
    public static function clear() {
        self::$styles = [];
        self::$scripts = [];
    }
}

/**
 * UI组件生成器
 */
class UIComponent {
    /**
     * 生成按钮
     */
    public static function button($text, $url = '#', $type = 'primary', $icon = '', $attributes = []) {
        $attrs = '';
        foreach ($attributes as $key => $value) {
            $attrs .= ' ' . $key . '="' . htmlspecialchars($value) . '"';
        }
        
        $iconHtml = $icon ? '<i class="' . htmlspecialchars($icon) . '"></i> ' : '';
        
        return sprintf(
            '<a href="%s" class="btn btn-%s"%s>%s%s</a>',
            htmlspecialchars($url),
            htmlspecialchars($type),
            $attrs,
            $iconHtml,
            htmlspecialchars($text)
        );
    }
    
    /**
     * 生成表单输入框
     */
    public static function input($name, $label, $type = 'text', $value = '', $attributes = []) {
        $attrs = '';
        foreach ($attributes as $key => $val) {
            $attrs .= ' ' . $key . '="' . htmlspecialchars($val) . '"';
        }
        
        $input = sprintf(
            '<input type="%s" name="%s" id="%s" value="%s" class="form-control"%s>',
            htmlspecialchars($type),
            htmlspecialchars($name),
            htmlspecialchars($name),
            htmlspecialchars($value),
            $attrs
        );
        
        return sprintf(
            '<div class="form-group">
                <label for="%s">%s</label>
                %s
            </div>',
            htmlspecialchars($name),
            htmlspecialchars($label),
            $input
        );
    }
    
    /**
     * 生成通知消息
     */
    public static function notification($message, $type = 'info', $dismissible = true) {
        $dismissBtn = $dismissible ? '<button type="button" class="close">&times;</button>' : '';
        
        return sprintf(
            '<div class="alert alert-%s" role="alert">%s%s</div>',
            htmlspecialchars($type),
            $dismissBtn,
            htmlspecialchars($message)
        );
    }
    
    /**
     * 生成文件卡片
     */
    public static function fileCard($fileInfo, $getFileType, $formatSize, $getFileLink) {
        $statusClass = $fileInfo->status == 1 ? 'success' : ($fileInfo->status == 2 ? 'danger' : 'warning');
        $statusText = $fileInfo->status == 1 ? '已通过' : ($fileInfo->status == 2 ? '已封禁' : '待审核');
        
        $expired = time() > $fileInfo->expire_time;
        if ($expired) {
            $statusClass = 'secondary';
            $statusText = '已过期';
        }
        
        $fileType = $getFileType($fileInfo->type);
        $fileSize = $formatSize($fileInfo->size);
        $fileLink = $getFileLink($fileInfo->id);
        
        return sprintf(
            '<div class="file-card">
                <div class="file-card-header">
                    <div class="file-info">
                        <div class="file-name">%s</div>
                        <div class="file-meta">
                            <span class="file-type">%s</span>
                            <span class="file-size">%s</span>
                            <span class="upload-time">%s</span>
                        </div>
                    </div>
                    <span class="status-badge status-%s">%s</span>
                </div>
                <div class="file-card-body">
                    <div class="pickup-code">
                        <span class="code-label">取件码:</span>
                        <span class="code-value">%s</span>
                        <button class="copy-btn" onclick="copyToClipboard(\'%s\')" title="复制取件码">
                            <i class="fas fa-copy"></i>
                        </button>
                    </div>
                    <div class="file-actions">
                        <a href="?action=view&id=%s" class="btn btn-sm btn-primary">
                            <i class="fas fa-eye"></i> 查看
                        </a>
                        <a href="?action=download&id=%s" class="btn btn-sm btn-success">
                            <i class="fas fa-download"></i> 下载
                        </a>
                        <button class="btn btn-sm btn-secondary" onclick="copyToClipboard(\'%s\')" title="复制链接">
                            <i class="fas fa-link"></i>
                        </button>
                    </div>
                </div>
            </div>',
            htmlspecialchars($fileInfo->name),
            htmlspecialchars($fileType),
            htmlspecialchars($fileSize),
            date('Y-m-d H:i', $fileInfo->upload_time),
            htmlspecialchars($statusClass),
            htmlspecialchars($statusText),
            htmlspecialchars($fileInfo->pickup_code),
            htmlspecialchars($fileInfo->pickup_code),
            htmlspecialchars($fileInfo->id),
            htmlspecialchars($fileInfo->id),
            htmlspecialchars($fileLink)
        );
    }
}

/**
 * 样式生成器
 */
class StyleGenerator {
    /**
     * 生成通用样式
     */
    public static function generateCommonStyles() {
        return <<<CSS
/* 通用样式重置和基础设置 */
* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
}

/* 文件卡片样式 */
.file-card {
    background: white;
    border-radius: 12px;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
    margin-bottom: 16px;
    overflow: hidden;
    transition: transform 0.2s ease, box-shadow 0.2s ease;
}

.file-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 16px rgba(0, 0, 0, 0.15);
}

.file-card-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 16px;
    border-bottom: 1px solid #eee;
}

.file-info {
    flex: 1;
}

.file-name {
    font-weight: 600;
    color: #333;
    margin-bottom: 4px;
    word-break: break-all;
}

.file-meta {
    display: flex;
    gap: 12px;
    font-size: 12px;
    color: #666;
}

.file-type, .file-size, .upload-time {
    display: flex;
    align-items: center;
    gap: 4px;
}

.status-badge {
    padding: 4px 8px;
    border-radius: 12px;
    font-size: 12px;
    font-weight: 500;
    text-transform: uppercase;
}

.status-success {
    background: #d4edda;
    color: #155724;
}

.status-warning {
    background: #fff3cd;
    color: #856404;
}

.status-danger {
    background: #f8d7da;
    color: #721c24;
}

.status-secondary {
    background: #e2e3e5;
    color: #383d41;
}

.file-card-body {
    padding: 16px;
}

.pickup-code {
    display: flex;
    align-items: center;
    gap: 8px;
    margin-bottom: 12px;
    padding: 8px;
    background: #f8f9fa;
    border-radius: 6px;
}

.code-label {
    font-size: 12px;
    color: #666;
    font-weight: 500;
}

.code-value {
    font-family: 'Courier New', monospace;
    font-weight: bold;
    color: #495057;
    flex: 1;
}

.copy-btn {
    background: none;
    border: none;
    color: #6c757d;
    cursor: pointer;
    padding: 4px;
    border-radius: 4px;
    transition: background-color 0.2s ease;
}

.copy-btn:hover {
    background: #e9ecef;
    color: #495057;
}

.file-actions {
    display: flex;
    gap: 8px;
    flex-wrap: wrap;
}

.btn-sm {
    padding: 6px 12px;
    font-size: 12px;
    border-radius: 6px;
}

/* 空状态样式 */
.empty-state {
    text-align: center;
    padding: 40px 20px;
    color: #6c757d;
}

.empty-state i {
    font-size: 48px;
    margin-bottom: 16px;
    color: #dee2e6;
}

.empty-state h3 {
    margin-bottom: 8px;
    color: #495057;
}

.empty-state p {
    color: #6c757d;
}

/* 响应式设计 */
@media (max-width: 768px) {
    .file-card-header {
        flex-direction: column;
        align-items: flex-start;
        gap: 8px;
    }
    
    .file-meta {
        flex-wrap: wrap;
    }
    
    .file-actions {
        justify-content: center;
    }
}

@media (max-width: 480px) {
    .file-card {
        margin: 0 -8px 16px;
        border-radius: 0;
    }
    
    .pickup-code {
        flex-direction: column;
        align-items: flex-start;
        gap: 4px;
    }
    
    .file-actions {
        justify-content: stretch;
    }
    
    .file-actions .btn {
        flex: 1;
        text-align: center;
    }
}
CSS;
    }
    
    /**
     * 生成动画样式
     */
    public static function generateAnimationStyles() {
        return <<<CSS
/* 动画效果 */
@keyframes fadeIn {
    from {
        opacity: 0;
        transform: translateY(20px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

@keyframes pulse {
    0%, 100% {
        transform: scale(1);
    }
    50% {
        transform: scale(1.05);
    }
}

@keyframes slideIn {
    from {
        transform: translateX(-100%);
        opacity: 0;
    }
    to {
        transform: translateX(0);
        opacity: 1;
    }
}

.fade-in {
    animation: fadeIn 0.5s ease-out;
}

.pulse {
    animation: pulse 2s infinite;
}

.slide-in {
    animation: slideIn 0.3s ease-out;
}

/* 工具类 */
.text-center {
    text-align: center;
}

.text-left {
    text-align: left;
}

.text-right {
    text-align: right;
}

.mb-1 {
    margin-bottom: 0.25rem;
}

.mb-2 {
    margin-bottom: 0.5rem;
}

.mb-3 {
    margin-bottom: 1rem;
}

.mb-4 {
    margin-bottom: 1.5rem;
}

.mt-1 {
    margin-top: 0.25rem;
}

.mt-2 {
    margin-top: 0.5rem;
}

.mt-3 {
    margin-top: 1rem;
}

.mt-4 {
    margin-top: 1.5rem;
}

.p-1 {
    padding: 0.25rem;
}

.p-2 {
    padding: 0.5rem;
}

.p-3 {
    padding: 1rem;
}

.p-4 {
    padding: 1.5rem;
}
CSS;
    }
}
?>