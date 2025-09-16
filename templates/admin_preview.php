<?php
// 管理员文件预览模板
?>
<div class="admin-preview">
    <div class="preview-header">
        <div class="file-info">
            <h2><i class="fas fa-eye"></i> 文件预览</h2>
            <div class="file-details">
                <div class="detail-item">
                    <span class="label">文件名:</span>
                    <span class="value"><?= htmlspecialchars($fileInfo->name) ?></span>
                </div>
                <div class="detail-item">
                    <span class="label">文件类型:</span>
                    <span class="value"><?= getFileType($fileInfo->type) ?></span>
                </div>
                <div class="detail-item">
                    <span class="label">文件大小:</span>
                    <span class="value"><?= formatSize($fileInfo->size) ?></span>
                </div>
                <div class="detail-item">
                    <span class="label">上传时间:</span>
                    <span class="value"><?= date('Y-m-d H:i:s', $fileInfo->upload_time) ?></span>
                </div>
                <div class="detail-item">
                    <span class="label">过期时间:</span>
                    <span class="value"><?= date('Y-m-d H:i:s', $fileInfo->expire_time) ?></span>
                </div>
                <div class="detail-item">
                    <span class="label">取件码:</span>
                    <span class="value"><code><?= htmlspecialchars($fileInfo->pickup_code) ?></code></span>
                </div>
                <div class="detail-item">
                    <span class="label">状态:</span>
                    <span class="value">
                        <span class="status-badge status-<?= $fileInfo->status === 0 ? 'pending' : ($fileInfo->status === 1 ? 'approved' : 'blocked') ?>">
                            <?= $fileInfo->status === 0 ? '待审核' : ($fileInfo->status === 1 ? '已通过' : '已封禁') ?>
                        </span>
                    </span>
                </div>
            </div>
        </div>
        
        <div class="preview-actions">
            <a href="?action=admin&menu=files&filter=<?= $filter ?>" class="btn">
                <i class="fas fa-arrow-left"></i> 返回列表
            </a>
            <a href="?action=download&id=<?= $fileInfo->id ?>" class="btn btn-success">
                <i class="fas fa-download"></i> 下载文件
            </a>
            <?php if ($fileInfo->status === 0): ?>
                <a href="?action=approve&id=<?= $fileInfo->id ?>&filter=<?= $filter ?>" class="btn btn-success">
                    <i class="fas fa-check"></i> 审核通过
                </a>
                <a href="?action=block&id=<?= $fileInfo->id ?>&filter=<?= $filter ?>" class="btn btn-danger">
                    <i class="fas fa-ban"></i> 封禁文件
                </a>
            <?php elseif ($fileInfo->status === 1): ?>
                <a href="?action=block&id=<?= $fileInfo->id ?>&filter=<?= $filter ?>" class="btn btn-danger">
                    <i class="fas fa-ban"></i> 封禁文件
                </a>
            <?php else: ?>
                <a href="?action=approve&id=<?= $fileInfo->id ?>&filter=<?= $filter ?>" class="btn btn-success">
                    <i class="fas fa-check"></i> 审核通过
                </a>
            <?php endif; ?>
            <a href="?action=delete&id=<?= $fileInfo->id ?>&filter=<?= $filter ?>" class="btn btn-danger" onclick="return confirm('确定要删除这个文件吗？')">
                <i class="fas fa-trash"></i> 删除文件
            </a>
        </div>
    </div>
    
    <div class="preview-content">
        <h3><i class="fas fa-file"></i> 文件内容预览</h3>
        <div class="preview-container">
            <?php if (strpos($fileInfo->type, 'image/') === 0): ?>
                <!-- 图片预览 -->
                <div class="image-preview">
                    <img src="?action=download&id=<?= $fileInfo->id ?>" alt="<?= htmlspecialchars($fileInfo->name) ?>" class="preview-image">
                    <div class="image-controls">
                        <button class="btn btn-small" onclick="rotateImage(-90)">
                            <i class="fas fa-undo"></i> 左转
                        </button>
                        <button class="btn btn-small" onclick="rotateImage(90)">
                            <i class="fas fa-redo"></i> 右转
                        </button>
                        <button class="btn btn-small" onclick="toggleFullscreen()">
                            <i class="fas fa-expand"></i> 全屏
                        </button>
                    </div>
                </div>
            <?php elseif (strpos($fileInfo->type, 'text/') === 0): ?>
                <!-- 文本预览 -->
                <div class="text-preview">
                    <div class="text-controls">
                        <button class="btn btn-small" onclick="copyText()">
                            <i class="fas fa-copy"></i> 复制文本
                        </button>
                        <button class="btn btn-small" onclick="toggleLineNumbers()">
                            <i class="fas fa-list-ol"></i> 行号
                        </button>
                        <?php if (in_array($fileInfo->type, ['text/x-php', 'text/x-c', 'text/x-c++', 'text/x-python', 'text/x-java', 'text/x-javascript', 'text/x-html', 'text/x-css', 'text/x-sql', 'text/x-shellscript', 'text/x-perl', 'text/x-ruby', 'text/x-go', 'text/x-rust', 'text/x-swift', 'text/x-kotlin', 'text/x-typescript', 'text/x-markdown', 'text/x-yaml', 'text/x-json', 'text/x-xml'])): ?>
                        <button class="btn btn-small" onclick="toggleSyntaxHighlight()">
                            <i class="fas fa-palette"></i> 语法高亮
                        </button>
                        <?php endif; ?>
                    </div>
                    <pre class="preview-text" id="text-content" data-file-type="<?= htmlspecialchars($fileInfo->type) ?>"><?= htmlspecialchars(file_get_contents($fileInfo->path)) ?></pre>
                </div>
            <?php elseif (strpos($fileInfo->type, 'video/') === 0): ?>
                <!-- 视频预览 -->
                <div class="video-preview">
                    <video controls class="preview-video" id="video-player">
                        <source src="?action=download&id=<?= $fileInfo->id ?>" type="<?= $fileInfo->type ?>">
                        您的浏览器不支持视频播放。
                    </video>
                    <div class="video-controls">
                        <button class="btn btn-small" onclick="togglePictureInPicture()">
                            <i class="fas fa-external-link-alt"></i> 画中画
                        </button>
                        <button class="btn btn-small" onclick="toggleVideoFullscreen()">
                            <i class="fas fa-expand"></i> 全屏
                        </button>
                    </div>
                </div>
            <?php elseif (strpos($fileInfo->type, 'audio/') === 0): ?>
                <!-- 音频预览 -->
                <div class="audio-preview">
                    <audio controls class="preview-audio" id="audio-player">
                        <source src="?action=download&id=<?= $fileInfo->id ?>" type="<?= $fileInfo->type ?>">
                        您的浏览器不支持音频播放。
                    </audio>
                    <div class="audio-info">
                        <p>音频文件: <?= htmlspecialchars($fileInfo->name) ?></p>
                        <p>类型: <?= htmlspecialchars($fileInfo->type) ?></p>
                        <p>大小: <?= formatSize($fileInfo->size) ?></p>
                    </div>
                </div>
            <?php elseif ($fileInfo->type === 'application/pdf'): ?>
                <!-- PDF预览 -->
                <div class="pdf-preview">
                    <div class="pdf-controls">
                        <button class="btn btn-small" onclick="zoomPDF(0.1)">
                            <i class="fas fa-search-plus"></i> 放大
                        </button>
                        <button class="btn btn-small" onclick="zoomPDF(-0.1)">
                            <i class="fas fa-search-minus"></i> 缩小
                        </button>
                        <button class="btn btn-small" onclick="togglePDFFullscreen()">
                            <i class="fas fa-expand"></i> 全屏
                        </button>
                    </div>
                    <iframe src="?action=download&id=<?= $fileInfo->id ?>" class="preview-pdf" id="pdf-frame"></iframe>
                </div>
            <?php else: ?>
                <!-- 其他文件类型 -->
                <div class="file-preview">
                    <div class="file-icon">
                        <i class="fas fa-file"></i>
                    </div>
                    <p>此文件类型不支持在线预览</p>
                    <div class="file-info">
                        <p><strong>文件名:</strong> <?= htmlspecialchars($fileInfo->name) ?></p>
                        <p><strong>类型:</strong> <?= htmlspecialchars($fileInfo->type) ?></p>
                        <p><strong>大小:</strong> <?= formatSize($fileInfo->size) ?></p>
                    </div>
                    <a href="?action=download&id=<?= $fileInfo->id ?>" class="btn btn-success">
                        <i class="fas fa-download"></i> 下载文件
                    </a>
                </div>
            <?php endif; ?>
        </div>
    </div>
    
    <div class="file-metadata">
        <h3><i class="fas fa-info-circle"></i> 文件元数据</h3>
        <div class="metadata-grid">
            <div class="metadata-item">
                <span class="meta-label">文件ID:</span>
                <span class="meta-value"><code><?= htmlspecialchars($fileInfo->id) ?></code></span>
            </div>
            <div class="metadata-item">
                <span class="meta-label">MIME类型:</span>
                <span class="meta-value"><?= htmlspecialchars($fileInfo->type) ?></span>
            </div>
            <div class="metadata-item">
                <span class="meta-label">文件路径:</span>
                <span class="meta-value"><code><?= htmlspecialchars($fileInfo->path) ?></code></span>
            </div>
            <div class="metadata-item">
                <span class="meta-label">文件方向:</span>
                <span class="meta-value"><?= $fileInfo->orientation === 'vertical' ? '垂直' : '水平' ?></span>
            </div>
            <div class="metadata-item">
                <span class="meta-label">是否已索引:</span>
                <span class="meta-value"><?= $fileInfo->is_indexed ? '是' : '否' ?></span>
            </div>
            <div class="metadata-item">
                <span class="meta-label">文件状态:</span>
                <span class="meta-value">
                    <span class="status-badge status-<?= $fileInfo->status === 0 ? 'pending' : ($fileInfo->status === 1 ? 'approved' : 'blocked') ?>">
                        <?= $fileInfo->status === 0 ? '待审核' : ($fileInfo->status === 1 ? '已通过' : '已封禁') ?>
                    </span>
                </span>
            </div>
        </div>
    </div>
</div>

<style>
.admin-preview {
    max-width: 1200px;
    margin: 0 auto;
}

.preview-header {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    margin-bottom: 30px;
    padding: 20px;
    background: #f8f9fa;
    border-radius: 10px;
    border: 1px solid #e9ecef;
}

.file-info h2 {
    margin-bottom: 15px;
    color: #333;
    display: flex;
    align-items: center;
    gap: 10px;
}

.file-details {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 10px;
}

.detail-item {
    display: flex;
    align-items: center;
    gap: 10px;
}

.detail-item .label {
    font-weight: 600;
    color: #666;
    min-width: 80px;
}

.detail-item .value {
    color: #333;
}

.preview-actions {
    display: flex;
    flex-direction: column;
    gap: 10px;
    align-items: flex-start;
}

.preview-content {
    margin-bottom: 30px;
}

.preview-content h3 {
    margin-bottom: 20px;
    color: #333;
    display: flex;
    align-items: center;
    gap: 10px;
}

.preview-container {
    background: white;
    border-radius: 10px;
    padding: 20px;
    box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
    border: 1px solid #e9ecef;
}

.image-preview {
    text-align: center;
}

.preview-image {
    max-width: 100%;
    max-height: 600px;
    border-radius: 8px;
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
    transition: transform 0.3s ease;
}

.image-controls,
.text-controls,
.video-controls,
.pdf-controls {
    margin-top: 15px;
    padding: 15px;
    background: #f8f9fa;
    border-radius: 8px;
    border: 1px solid #e9ecef;
    display: flex;
    gap: 10px;
    flex-wrap: wrap;
    align-items: center;
}

.btn-small {
    padding: 6px 12px;
    font-size: 0.85rem;
    border-radius: 6px;
    text-decoration: none;
    color: white;
    background: #4b6cb7;
    border: none;
    cursor: pointer;
    transition: all 0.3s ease;
    display: inline-flex;
    align-items: center;
    gap: 5px;
}

.btn-small:hover {
    background: #3a5ca0;
    transform: translateY(-1px);
}

.btn-small.success {
    background: #28a745;
}

.btn-small.success:hover {
    background: #218838;
}

.btn-small.danger {
    background: #dc3545;
}

.btn-small.danger:hover {
    background: #c82333;
}

.text-preview {
    background: #f8f9fa;
    border-radius: 8px;
    padding: 20px;
    border: 1px solid #e9ecef;
}

.preview-text {
    white-space: pre-wrap;
    word-wrap: break-word;
    max-height: 500px;
    overflow-y: auto;
    font-family: 'Courier New', monospace;
    font-size: 14px;
    line-height: 1.5;
}

.video-preview {
    text-align: center;
}

.preview-video {
    max-width: 100%;
    max-height: 600px;
    border-radius: 8px;
}

.audio-preview {
    text-align: center;
    padding: 40px;
    background: #f8f9fa;
    border-radius: 8px;
}

.preview-audio {
    width: 100%;
    max-width: 500px;
}

.pdf-preview {
    text-align: center;
}

.preview-pdf {
    width: 100%;
    height: 600px;
    border: none;
    border-radius: 8px;
}

.file-preview {
    text-align: center;
    padding: 60px 20px;
    background: #f8f9fa;
    border-radius: 8px;
}

.file-preview .file-icon {
    font-size: 4rem;
    color: #666;
    margin-bottom: 20px;
}

.file-preview p {
    margin-bottom: 20px;
    color: #666;
}

.file-metadata {
    background: #f8f9fa;
    border-radius: 10px;
    padding: 20px;
    border: 1px solid #e9ecef;
}

.file-metadata h3 {
    margin-bottom: 20px;
    color: #333;
    display: flex;
    align-items: center;
    gap: 10px;
}

.metadata-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
    gap: 15px;
}

.metadata-item {
    display: flex;
    align-items: center;
    gap: 10px;
}

.meta-label {
    font-weight: 600;
    color: #666;
    min-width: 100px;
}

.meta-value {
    color: #333;
    font-family: 'Courier New', monospace;
    font-size: 0.9rem;
}

.status-badge {
    padding: 4px 8px;
    border-radius: 4px;
    font-size: 0.8rem;
    font-weight: 500;
}

.status-pending {
    background: #fff3cd;
    color: #856404;
}

.status-approved {
    background: #d4edda;
    color: #155724;
}

.status-blocked {
    background: #f8d7da;
    color: #721c24;
}

@media (max-width: 768px) {
    .preview-header {
        flex-direction: column;
        gap: 20px;
    }
    
    .preview-actions {
        flex-direction: row;
        flex-wrap: wrap;
        width: 100%;
    }
    
    .preview-actions .btn {
        flex: 1;
        min-width: 120px;
    }
    
    .file-details {
        grid-template-columns: 1fr;
    }
    
    .metadata-grid {
        grid-template-columns: 1fr;
    }
    
    .preview-image,
    .preview-video {
        max-height: 400px;
    }
    
    .preview-pdf {
        height: 400px;
    }
}

@media (max-width: 480px) {
    .admin-preview {
        padding: 0 10px;
    }
    
    .preview-header {
        padding: 15px;
    }
    
    .preview-content,
    .file-metadata {
        padding: 15px;
    }
    
    .preview-container {
        padding: 15px;
    }
    
    .preview-actions {
        flex-direction: column;
    }
    
    .preview-actions .btn {
        width: 100%;
    }
    
    .detail-item,
    .metadata-item {
        flex-direction: column;
        align-items: flex-start;
        gap: 5px;
    }
    
    .detail-item .label,
    .meta-label {
        min-width: auto;
    }
}
</style>

<script>
// 图片控制功能
let currentRotation = 0;

function rotateImage(degrees) {
    const img = document.querySelector('.preview-image');
    if (img) {
        currentRotation += degrees;
        img.style.transform = `rotate(${currentRotation}deg)`;
    }
}

function toggleFullscreen() {
    const img = document.querySelector('.preview-image');
    if (img) {
        if (img.requestFullscreen) {
            img.requestFullscreen();
        } else if (img.webkitRequestFullscreen) {
            img.webkitRequestFullscreen();
        } else if (img.msRequestFullscreen) {
            img.msRequestFullscreen();
        }
    }
}

// 文本控制功能
function copyText() {
    const textContent = document.getElementById('text-content');
    if (textContent) {
        navigator.clipboard.writeText(textContent.textContent).then(() => {
            showNotification('文本已复制到剪贴板', 'success');
        }).catch(() => {
            showNotification('复制失败，请手动复制', 'error');
        });
    }
}

let lineNumbersVisible = false;
let syntaxHighlightVisible = false;

function toggleLineNumbers() {
    const textContent = document.getElementById('text-content');
    if (textContent) {
        const originalText = textContent.dataset.originalText || textContent.textContent;
        
        if (!lineNumbersVisible) {
            // 显示行号
            const lines = originalText.split('\n');
            const numberedText = lines.map((line, index) => `${(index + 1).toString().padStart(4)} ${line}`).join('\n');
            textContent.textContent = numberedText;
            textContent.dataset.originalText = originalText;
            lineNumbersVisible = true;
            
            // 更新按钮状态
            const button = event.target;
            button.style.background = '#28a745';
            button.innerHTML = '<i class="fas fa-list-ol"></i> 隐藏行号';
        } else {
            // 隐藏行号
            textContent.textContent = originalText;
            lineNumbersVisible = false;
            
            // 更新按钮状态
            const button = event.target;
            button.style.background = '#4b6cb7';
            button.innerHTML = '<i class="fas fa-list-ol"></i> 行号';
        }
    }
}

function toggleSyntaxHighlight() {
    const textContent = document.getElementById('text-content');
    if (textContent) {
        const fileType = textContent.dataset.fileType;
        const originalText = textContent.dataset.originalText || textContent.textContent;
        
        if (!syntaxHighlightVisible) {
            // 应用语法高亮
            const highlightedText = applySyntaxHighlight(originalText, fileType);
            textContent.innerHTML = highlightedText;
            textContent.dataset.originalText = originalText;
            syntaxHighlightVisible = true;
            
            // 更新按钮状态
            const button = event.target;
            button.style.background = '#28a745';
            button.innerHTML = '<i class="fas fa-palette"></i> 关闭高亮';
        } else {
            // 关闭语法高亮
            textContent.textContent = originalText;
            syntaxHighlightVisible = false;
            
            // 更新按钮状态
            const button = event.target;
            button.style.background = '#4b6cb7';
            button.innerHTML = '<i class="fas fa-palette"></i> 语法高亮';
        }
    }
}

function applySyntaxHighlight(text, fileType) {
    // 移除HTML标签，然后重新应用高亮
    const cleanText = text.replace(/<[^>]*>/g, '');
    let highlightedText = cleanText;
    
    // 根据文件类型应用不同的高亮规则
    switch(fileType) {
        case 'text/x-php':
            highlightedText = highlightPHP(cleanText);
            break;
        case 'text/x-python':
            highlightedText = highlightPython(cleanText);
            break;
        case 'text/x-javascript':
            highlightedText = highlightJavaScript(cleanText);
            break;
        case 'text/x-html':
            highlightedText = highlightHTML(cleanText);
            break;
        case 'text/x-css':
            highlightedText = highlightCSS(cleanText);
            break;
        case 'text/x-sql':
            highlightedText = highlightSQL(cleanText);
            break;
        case 'text/x-json':
            highlightedText = highlightJSON(cleanText);
            break;
        case 'text/x-markdown':
            highlightedText = highlightMarkdown(cleanText);
            break;
        default:
            highlightedText = highlightGeneric(cleanText);
    }
    
    return highlightedText;
}

function highlightPHP(text) {
    return text
        .replace(/(&lt;\?php|\?&gt;)/g, '<span style="color: #8B4513; font-weight: bold;">$1</span>')
        .replace(/\b(function|class|interface|trait|extends|implements|public|private|protected|static|final|abstract|return|echo|print|if|else|elseif|endif|for|foreach|while|do|switch|case|break|continue|default|try|catch|finally|throw|new|clone|isset|unset|empty|require|include|require_once|include_once|use|namespace|global|const|define|as|and|or|xor|instanceof|insteadof|parent|self|static|var|null|true|false)\b/g, '<span style="color: #0000FF; font-weight: bold;">$1</span>')
        .replace(/\/\/.*$/gm, '<span style="color: #008000;">$&</span>')
        .replace(/\/\*[\s\S]*?\*\//g, '<span style="color: #008000;">$&</span>')
        .replace(/('.*?'|".*?")/g, '<span style="color: #008000;">$1</span>')
        .replace(/\b(\d+)\b/g, '<span style="color: #FF4500;">$1</span>')
        .replace(/(\$[a-zA-Z_][a-zA-Z0-9_]*)\b/g, '<span style="color: #8B008B;">$1</span>');
}

function highlightPython(text) {
    return text
        .replace(/\b(def|class|import|from|as|if|else|elif|for|while|try|except|finally|with|pass|break|continue|return|yield|global|nonlocal|lambda|and|or|not|in|is|True|False|None)\b/g, '<span style="color: #0000FF; font-weight: bold;">$1</span>')
        .replace(/#.*$/gm, '<span style="color: #008000;">$&</span>')
        .replace(/""".*?"""|'''.*?'''/gs, '<span style="color: #008000;">$&</span>')
        .replace(/('.*?'|".*?")/g, '<span style="color: #008000;">$1</span>')
        .replace(/\b(\d+)\b/g, '<span style="color: #FF4500;">$1</span>');
}

function highlightJavaScript(text) {
    return text
        .replace(/\b(function|var|let|const|if|else|for|while|do|switch|case|break|continue|return|try|catch|finally|throw|new|class|extends|super|import|export|default|async|await|typeof|instanceof|in|of|true|false|null|undefined|this|self|window|document|console)\b/g, '<span style="color: #0000FF; font-weight: bold;">$1</span>')
        .replace(/\/\/.*$/gm, '<span style="color: #008000;">$&</span>')
        .replace(/\/\*[\s\S]*?\*\//g, '<span style="color: #008000;">$&</span>')
        .replace(/('.*?'|".*?")/g, '<span style="color: #008000;">$1</span>')
        .replace(/\b(\d+)\b/g, '<span style="color: #FF4500;">$1</span>');
}

function highlightHTML(text) {
    return text
        .replace(/(&lt;[a-zA-Z][^&]*?&gt;)/g, '<span style="color: #0000FF;">$1</span>')
        .replace(/(&lt;\/[a-zA-Z][^&]*?&gt;)/g, '<span style="color: #0000FF;">$1</span>')
        .replace(/(&lt;!--[\s\S]*?--&gt;)/g, '<span style="color: #008000;">$1</span>')
        .replace(/([a-zA-Z-]+)=(".*?"|'.*?')/g, '<span style="color: #8B008B;">$1</span>=<span style="color: #008000;">$2</span>');
}

function highlightCSS(text) {
    return text
        .replace(/([a-zA-Z-]+)\s*:/g, '<span style="color: #8B008B;">$1</span>:')
        .replace(/\/\*[\s\S]*?\*\//g, '<span style="color: #008000;">$&</span>')
        .replace(/('.*?'|".*?")/g, '<span style="color: #008000;">$1</span>')
        .replace(/\b(\d+px|\d+em|\d+%\d+)\b/g, '<span style="color: #FF4500;">$1</span>')
        .replace(/#[a-fA-F0-9]{3,6}/g, '<span style="color: #FF4500;">$&</span>');
}

function highlightSQL(text) {
    return text
        .replace(/\b(SELECT|FROM|WHERE|INSERT|INTO|UPDATE|SET|DELETE|CREATE|TABLE|DROP|ALTER|JOIN|INNER|LEFT|RIGHT|FULL|OUTER|GROUP|BY|ORDER|BY|HAVING|UNION|ALL|DISTINCT|AS|ON|AND|OR|NOT|NULL|IS|BETWEEN|LIKE|IN|EXISTS|COUNT|SUM|AVG|MAX|MIN|GROUP_CONCAT|CONCAT|SUBSTRING|LENGTH|UPPER|LOWER|TRIM|REPLACE|NOW|CURDATE|CURTIME|DATE|TIME|YEAR|MONTH|DAY|HOUR|MINUTE|SECOND)\b/gi, '<span style="color: #0000FF; font-weight: bold;">$1</span>')
        .replace(/(--.*)$/gm, '<span style="color: #008000;">$&</span>')
        .replace(/('.*?'|".*?")/g, '<span style="color: #008000;">$1</span>')
        .replace(/\b(\d+)\b/g, '<span style="color: #FF4500;">$1</span>');
}

function highlightJSON(text) {
    return text
        .replace(/(".*?")\s*:/g, '<span style="color: #8B008B;">$1</span>:')
        .replace(/: (".*?"|'.*?'|\d+|true|false|null)/gi, ': <span style="color: #008000;">$1</span>')
        .replace(/\b(true|false|null)\b/g, '<span style="color: #FF4500;">$1</span>')
        .replace(/\b(\d+)\b/g, '<span style="color: #FF4500;">$1</span>');
}

function highlightMarkdown(text) {
    return text
        .replace(/^#{1,6}\s+(.+)$/gm, '<span style="color: #0000FF; font-weight: bold;">$&</span>')
        .replace(/\*\*(.+?)\*\*/g, '<span style="color: #8B008B; font-weight: bold;">$1</span>')
        .replace(/\*(.+?)\*/g, '<span style="color: #8B008B;">$1</span>')
        .replace(/`(.+?)`/g, '<span style="color: #008000; background: #f0f0f0;">$1</span>')
        .replace(/```[\s\S]*?```/g, '<span style="color: #008000; background: #f0f0f0;">$&</span>')
        .replace(/^>\s+(.+)$/gm, '<span style="color: #666;">$&</span>');
}

function highlightGeneric(text) {
    return text
        .replace(/\b(function|class|def|var|let|const|if|else|for|while|return|true|false|null)\b/g, '<span style="color: #0000FF; font-weight: bold;">$1</span>')
        .replace(/(\/\/.*|#.*|\/\*[\s\S]*?\*\/)/g, '<span style="color: #008000;">$&</span>')
        .replace(/('.*?'|".*?")/g, '<span style="color: #008000;">$1</span>')
        .replace(/\b(\d+)\b/g, '<span style="color: #FF4500;">$1</span>');
}

// 视频控制功能
function togglePictureInPicture() {
    const video = document.getElementById('video-player');
    if (video) {
        if (document.pictureInPictureElement) {
            document.exitPictureInPicture();
        } else if (document.pictureInPictureEnabled) {
            video.requestPictureInPicture();
        } else {
            showNotification('您的浏览器不支持画中画模式', 'error');
        }
    }
}

function toggleVideoFullscreen() {
    const video = document.getElementById('video-player');
    if (video) {
        if (video.requestFullscreen) {
            video.requestFullscreen();
        } else if (video.webkitRequestFullscreen) {
            video.webkitRequestFullscreen();
        } else if (video.msRequestFullscreen) {
            video.msRequestFullscreen();
        }
    }
}

// PDF控制功能
let currentPDFZoom = 1.0;

function zoomPDF(delta) {
    const pdfFrame = document.getElementById('pdf-frame');
    if (pdfFrame) {
        currentPDFZoom += delta;
        currentPDFZoom = Math.max(0.5, Math.min(3.0, currentPDFZoom));
        pdfFrame.style.transform = `scale(${currentPDFZoom})`;
        pdfFrame.style.transformOrigin = 'top left';
    }
}

function togglePDFFullscreen() {
    const pdfFrame = document.getElementById('pdf-frame');
    if (pdfFrame) {
        if (pdfFrame.requestFullscreen) {
            pdfFrame.requestFullscreen();
        } else if (pdfFrame.webkitRequestFullscreen) {
            pdfFrame.webkitRequestFullscreen();
        } else if (pdfFrame.msRequestFullscreen) {
            pdfFrame.msRequestFullscreen();
        }
    }
}

// 通知功能
function showNotification(message, type = 'success') {
    const notification = document.createElement('div');
    notification.className = `notification ${type}`;
    notification.textContent = message;
    notification.style.position = 'fixed';
    notification.style.top = '20px';
    notification.style.right = '20px';
    notification.style.zIndex = '9999';
    notification.style.maxWidth = '300px';
    
    document.body.appendChild(notification);
    
    setTimeout(() => {
        notification.remove();
    }, 3000);
}

// 格式化文件大小函数
function formatFileSize(bytes) {
    if (bytes >= 1073741824) {
        return (bytes / 1073741824).toFixed(2) + ' GB';
    } else if (bytes >= 1048576) {
        return (bytes / 1048576).toFixed(2) + ' MB';
    } else if (bytes >= 1024) {
        return (bytes / 1024).toFixed(2) + ' KB';
    } else {
        return bytes + ' bytes';
    }
}
</script>