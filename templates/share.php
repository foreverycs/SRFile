<?php
// 文件分享页面模板
?>
<div class="share-page">
    <div class="share-container">
        <div class="share-header">
            <div class="share-icon">
                <i class="fas fa-share-alt"></i>
            </div>
            <div class="share-title">
                <h1>文件分享</h1>
                <p>您有 <?= count($files) ?> 个文件等待下载</p>
            </div>
        </div>
        
        <div class="share-info">
            <div class="info-item">
                <i class="fas fa-clock"></i>
                <span>过期时间: <?= date('Y-m-d H:i:s', $share_data['expire_time']) ?></span>
            </div>
            <div class="info-item">
                <i class="fas fa-eye"></i>
                <span>访问次数: <?= $share_data['access_count'] ?>/<?= $share_data['max_access'] ?></span>
            </div>
            <div class="info-item">
                <i class="fas fa-calendar"></i>
                <span>创建时间: <?= date('Y-m-d H:i:s', $share_data['created_time']) ?></span>
            </div>
        </div>
        
        <div class="file-list">
            <h3><i class="fas fa-file"></i> 文件列表</h3>
            <div class="file-grid">
                <?php foreach ($files as $file): ?>
                    <div class="file-card">
                        <div class="file-icon">
                            <?php if (strpos($file->type, 'image/') === 0): ?>
                                <i class="fas fa-image"></i>
                            <?php elseif (strpos($file->type, 'video/') === 0): ?>
                                <i class="fas fa-video"></i>
                            <?php elseif (strpos($file->type, 'audio/') === 0): ?>
                                <i class="fas fa-music"></i>
                            <?php elseif (strpos($file->type, 'text/') === 0): ?>
                                <i class="fas fa-file-alt"></i>
                            <?php elseif ($file->type === 'application/pdf'): ?>
                                <i class="fas fa-file-pdf"></i>
                            <?php else: ?>
                                <i class="fas fa-file"></i>
                            <?php endif; ?>
                        </div>
                        <div class="file-details">
                            <div class="file-name"><?= htmlspecialchars($file->name) ?></div>
                            <div class="file-meta">
                                <span class="file-type"><?= $getFileType($file->type) ?></span>
                                <span class="file-size"><?= $formatSize($file->size) ?></span>
                            </div>
                        </div>
                        <div class="file-actions">
                            <a href="?action=download&id=<?= $file->id ?>" class="btn btn-primary btn-sm">
                                <i class="fas fa-download"></i> 下载
                            </a>
                            <?php if (FilePreview::canPreview($file)): ?>
                                <button class="btn btn-info btn-sm" onclick="previewFile('<?= $file->id ?>')">
                                    <i class="fas fa-eye"></i> 预览
                                </button>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
        
        <div class="share-actions">
            <button class="btn btn-success" onclick="downloadAll()">
                <i class="fas fa-download"></i> 批量下载
            </button>
            <button class="btn btn-secondary" onclick="copyShareLink()">
                <i class="fas fa-copy"></i> 复制链接
            </button>
            <button class="btn btn-info" onclick="generateQRCode()">
                <i class="fas fa-qrcode"></i> 二维码
            </button>
        </div>
    </div>
</div>

<!-- 文件预览模态框 -->
<div id="previewModal" class="modal">
    <div class="modal-content modal-lg">
        <div class="modal-header">
            <h3><i class="fas fa-eye"></i> 文件预览</h3>
            <button class="modal-close" onclick="closeModal('previewModal')">&times;</button>
        </div>
        <div class="modal-body">
            <div id="previewContent">
                <div class="loading-spinner">
                    <i class="fas fa-spinner fa-spin"></i> 加载中...
                </div>
            </div>
        </div>
    </div>
</div>

<!-- 二维码模态框 -->
<div id="qrModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h3><i class="fas fa-qrcode"></i> 分享二维码</h3>
            <button class="modal-close" onclick="closeModal('qrModal')">&times;</button>
        </div>
        <div class="modal-body">
            <div class="qr-container">
                <div id="qrCode"></div>
                <p>扫描二维码即可访问分享链接</p>
                <button class="btn btn-primary" onclick="downloadQRCode()">
                    <i class="fas fa-download"></i> 下载二维码
                </button>
            </div>
        </div>
    </div>
</div>

<style>
.share-page {
    max-width: 1200px;
    margin: 0 auto;
    padding: 20px;
}

.share-container {
    background: white;
    border-radius: 16px;
    box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
    overflow: hidden;
}

.share-header {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    padding: 40px;
    text-align: center;
}

.share-icon {
    font-size: 4rem;
    margin-bottom: 20px;
    animation: pulse 2s infinite;
}

@keyframes pulse {
    0% { transform: scale(1); }
    50% { transform: scale(1.1); }
    100% { transform: scale(1); }
}

.share-title h1 {
    font-size: 2.5rem;
    margin-bottom: 10px;
    font-weight: 600;
}

.share-title p {
    font-size: 1.2rem;
    opacity: 0.9;
}

.share-info {
    display: flex;
    justify-content: space-around;
    padding: 30px;
    background: #f8f9fa;
    border-bottom: 1px solid #e9ecef;
}

.info-item {
    display: flex;
    align-items: center;
    gap: 10px;
    font-weight: 500;
    color: #666;
}

.info-item i {
    color: #667eea;
    font-size: 1.2rem;
}

.file-list {
    padding: 30px;
}

.file-list h3 {
    margin-bottom: 20px;
    color: #333;
    font-size: 1.5rem;
    display: flex;
    align-items: center;
    gap: 10px;
}

.file-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
    gap: 20px;
}

.file-card {
    background: #f8f9fa;
    border-radius: 12px;
    padding: 20px;
    border: 1px solid #e9ecef;
    transition: all 0.3s ease;
    /* 移动端优化 */
    -webkit-tap-highlight-color: transparent;
    -webkit-touch-callout: none;
    -webkit-user-select: none;
    user-select: none;
    touch-action: manipulation;
}

.file-card:hover, .file-card:active {
    transform: translateY(-2px);
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
}

.file-card:active {
    transform: translateY(0);
}

.file-icon {
    font-size: 2rem;
    color: #667eea;
    margin-bottom: 15px;
    text-align: center;
}

.file-details {
    margin-bottom: 15px;
}

.file-name {
    font-weight: 600;
    color: #333;
    margin-bottom: 5px;
    word-break: break-word;
    font-size: 16px; /* 防止iOS缩放 */
}

.file-meta {
    display: flex;
    justify-content: space-between;
    font-size: 0.9rem;
    color: #666;
}

.file-actions {
    display: flex;
    gap: 10px;
    flex-wrap: wrap;
    /* 移动端优化 */
    justify-content: center;
}

.file-actions .btn {
    /* 移动端优化 */
    -webkit-tap-highlight-color: transparent;
    -webkit-touch-callout: none;
    -webkit-user-select: none;
    user-select: none;
    touch-action: manipulation;
    min-height: 40px;
    display: flex;
    align-items: center;
    justify-content: center;
}

.btn-sm {
    padding: 8px 16px;
    font-size: 0.9rem;
    /* 移动端优化 */
    -webkit-tap-highlight-color: transparent;
    -webkit-touch-callout: none;
    -webkit-user-select: none;
    user-select: none;
    touch-action: manipulation;
    min-height: 36px;
    display: flex;
    align-items: center;
    justify-content: center;
}

.share-actions {
    padding: 30px;
    background: #f8f9fa;
    border-top: 1px solid #e9ecef;
    display: flex;
    justify-content: center;
    gap: 15px;
    flex-wrap: wrap;
}

.share-actions .btn {
    /* 移动端优化 */
    -webkit-tap-highlight-color: transparent;
    -webkit-touch-callout: none;
    -webkit-user-select: none;
    user-select: none;
    touch-action: manipulation;
    min-height: 44px; /* 确保足够的触摸区域 */
    display: flex;
    align-items: center;
    justify-content: center;
}

.modal-lg {
    max-width: 900px;
}

.loading-spinner {
    text-align: center;
    padding: 40px;
    color: #666;
}

.loading-spinner i {
    font-size: 2rem;
    margin-bottom: 10px;
}

.qr-container {
    text-align: center;
    padding: 20px;
}

.qr-container img {
    max-width: 300px;
    height: auto;
    border: 1px solid #ddd;
    border-radius: 8px;
    margin-bottom: 20px;
    /* 移动端优化 */
    -webkit-user-drag: none;
    -khtml-user-drag: none;
    -moz-user-drag: none;
    -o-user-drag: none;
    user-drag: none;
}

.qr-container p {
    color: #666;
    margin-bottom: 20px;
    font-size: 16px; /* 防止iOS缩放 */
}

.qr-container .btn {
    /* 移动端优化 */
    min-height: 44px;
    display: flex;
    align-items: center;
    justify-content: center;
}

/* 预览内容样式 */
.preview-container {
    max-width: 100%;
    overflow: hidden;
}

.preview-image img {
    max-width: 100%;
    height: auto;
    border-radius: 8px;
    cursor: pointer;
    /* 移动端优化 */
    -webkit-user-drag: none;
    -khtml-user-drag: none;
    -moz-user-drag: none;
    -o-user-drag: none;
    user-drag: none;
}

.preview-info {
    display: flex;
    justify-content: space-between;
    margin-top: 10px;
    font-size: 0.9rem;
    color: #666;
}

.preview-text {
    background: #f8f9fa;
    padding: 20px;
    border-radius: 8px;
    border: 1px solid #e9ecef;
}

.preview-content {
    max-height: 400px;
    overflow-y: auto;
    white-space: pre-wrap;
    font-family: 'Courier New', monospace;
    font-size: 0.9rem;
    line-height: 1.4;
    /* 移动端优化 */
    font-size: 16px; /* 防止iOS缩放 */
}

.preview-json pre {
    background: #f8f9fa;
    padding: 20px;
    border-radius: 8px;
    border: 1px solid #e9ecef;
    overflow-x: auto;
    font-size: 0.9rem;
    line-height: 1.4;
    /* 移动端优化 */
    font-size: 16px; /* 防止iOS缩放 */
}

/* 响应式设计 */
@media (max-width: 768px) {
    .share-page {
        padding: 10px;
    }
    
    .share-header {
        padding: 30px 20px;
    }
    
    .share-title h1 {
        font-size: 2rem;
    }
    
    .share-title p {
        font-size: 1rem;
    }
    
    .share-info {
        flex-direction: column;
        gap: 15px;
        padding: 20px;
    }
    
    .info-item {
        font-size: 0.9rem;
    }
    
    .info-item i {
        font-size: 1rem;
    }
    
    .file-list {
        padding: 20px;
    }
    
    .file-list h3 {
        font-size: 1.2rem;
    }
    
    .file-grid {
        grid-template-columns: 1fr;
        gap: 15px;
    }
    
    .file-card {
        padding: 15px;
    }
    
    .file-icon {
        font-size: 1.8rem;
        margin-bottom: 12px;
    }
    
    .file-name {
        font-size: 15px;
    }
    
    .file-meta {
        font-size: 0.85rem;
    }
    
    .file-actions {
        flex-direction: column;
        gap: 8px;
    }
    
    .file-actions .btn {
        width: 100%;
    }
    
    .share-actions {
        flex-direction: column;
        padding: 20px;
        gap: 10px;
    }
    
    .share-actions .btn {
        width: 100%;
        padding: 14px;
        font-size: 16px;
    }
    
    .modal-lg {
        width: 95%;
        margin: 5% auto;
    }
    
    .modal-header {
        padding: 15px;
    }
    
    .modal-header h3 {
        font-size: 1.2rem;
    }
    
    .modal-body {
        padding: 15px;
    }
    
    .qr-container {
        padding: 15px;
    }
    
    .qr-container img {
        max-width: 250px;
    }
    
    .qr-container p {
        font-size: 15px;
    }
    
    .preview-content {
        max-height: 300px;
        font-size: 15px;
    }
    
    .preview-json pre {
        font-size: 15px;
    }
}

@media (max-width: 480px) {
    .share-page {
        padding: 5px;
    }
    
    .share-header {
        padding: 20px 15px;
    }
    
    .share-title h1 {
        font-size: 1.5rem;
    }
    
    .share-title p {
        font-size: 0.9rem;
    }
    
    .share-icon {
        font-size: 3rem;
        margin-bottom: 15px;
    }
    
    .share-info {
        padding: 15px;
        gap: 12px;
    }
    
    .info-item {
        font-size: 0.85rem;
    }
    
    .file-list {
        padding: 15px;
    }
    
    .file-list h3 {
        font-size: 1.1rem;
    }
    
    .file-card {
        padding: 12px;
    }
    
    .file-icon {
        font-size: 1.6rem;
        margin-bottom: 10px;
    }
    
    .file-name {
        font-size: 14px;
    }
    
    .file-meta {
        font-size: 0.8rem;
        flex-direction: column;
        gap: 4px;
    }
    
    .file-actions {
        flex-direction: column;
        gap: 6px;
    }
    
    .file-actions .btn {
        width: 100%;
        padding: 12px;
        font-size: 14px;
    }
    
    .share-actions {
        padding: 15px;
        gap: 8px;
    }
    
    .share-actions .btn {
        padding: 12px;
        font-size: 15px;
    }
    
    .modal-content {
        width: 98%;
        margin: 10% auto;
    }
    
    .modal-header {
        padding: 12px;
    }
    
    .modal-header h3 {
        font-size: 1.1rem;
    }
    
    .modal-body {
        padding: 12px;
    }
    
    .qr-container {
        padding: 12px;
    }
    
    .qr-container img {
        max-width: 200px;
    }
    
    .qr-container p {
        font-size: 14px;
    }
    
    .preview-content {
        max-height: 250px;
        font-size: 14px;
    }
    
    .preview-json pre {
        font-size: 14px;
    }
}

/* 平板设备适配 */
@media (min-width: 601px) and (max-width: 768px) {
    .share-title h1 {
        font-size: 2.2rem;
    }
    
    .file-grid {
        grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
    }
}

/* 大屏手机适配 */
@media (min-width: 769px) and (max-width: 992px) {
    .file-grid {
        grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
    }
}

/* 横屏模式适配 */
@media (max-width: 768px) and (orientation: landscape) {
    .share-header {
        padding: 20px 15px;
    }
    
    .share-title h1 {
        font-size: 1.7rem;
    }
    
    .share-icon {
        font-size: 2.5rem;
    }
    
    .share-info {
        padding: 15px;
        flex-direction: row;
        justify-content: space-around;
        gap: 10px;
    }
    
    .file-grid {
        grid-template-columns: repeat(2, 1fr);
        gap: 10px;
    }
    
    .file-card {
        padding: 12px;
    }
    
    .file-actions {
        flex-direction: row;
        gap: 6px;
    }
    
    .file-actions .btn {
        width: auto;
        padding: 8px 12px;
        font-size: 13px;
    }
    
    .share-actions {
        flex-direction: row;
        padding: 15px;
        gap: 10px;
    }
    
    .share-actions .btn {
        width: auto;
        padding: 10px 15px;
    }
}
</style>

<script>
function previewFile(fileId) {
    document.getElementById('previewModal').style.display = 'block';
    document.getElementById('previewContent').innerHTML = '<div class="loading-spinner"><i class="fas fa-spinner fa-spin"></i> 加载中...</div>';
    
    fetch('?action=preview&id=' + fileId)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                document.getElementById('previewContent').innerHTML = data.template;
            } else {
                document.getElementById('previewContent').innerHTML = '<div class="alert alert-danger">' + data.message + '</div>';
            }
        })
        .catch(error => {
            document.getElementById('previewContent').innerHTML = '<div class="alert alert-danger">预览失败</div>';
        });
}

function downloadAll() {
    const fileIds = <?= json_encode(array_map(function($file) { return $file->id; }, $files)) ?>;
    
    fetch('?action=create_share', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: 'csrf_token=<?= $csrf_token ?>&file_ids=' + fileIds.join(',') + '&expire_hours=1'
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // 创建批量下载
            fetch('?action=create_batch', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: 'csrf_token=<?= $csrf_token ?>&file_ids=' + fileIds.join(',')
            })
            .then(response => response.json())
            .then(batchData => {
                if (batchData.success) {
                    window.location.href = batchData.download_url;
                } else {
                    alert('创建批量下载失败');
                }
            });
        } else {
            alert('创建分享失败');
        }
    });
}

function copyShareLink() {
    const shareUrl = window.location.href;
    navigator.clipboard.writeText(shareUrl).then(() => {
        showNotification('分享链接已复制到剪贴板');
    }).catch(() => {
        showNotification('复制失败，请手动复制');
    });
}

function generateQRCode() {
    const shareUrl = window.location.href;
    const qrSize = 300;
    const qrCodeUrl = 'https://api.qrserver.com/v1/create-qr-code/?size=' + qrSize + 'x' + qrSize + '&data=' + encodeURIComponent(shareUrl);
    
    document.getElementById('qrCode').innerHTML = '<img src="' + qrCodeUrl + '" alt="分享二维码">';
    document.getElementById('qrModal').style.display = 'block';
}

function downloadQRCode() {
    const qrImage = document.querySelector('#qrCode img');
    if (qrImage) {
        const link = document.createElement('a');
        link.download = 'share_qrcode.png';
        link.href = qrImage.src;
        link.click();
    }
}

function closeModal(modalId) {
    document.getElementById(modalId).style.display = 'none';
}

function showNotification(message) {
    const notification = document.createElement('div');
    notification.className = 'notification-toast';
    notification.textContent = message;
    notification.style.cssText = `
        position: fixed;
        top: 20px;
        right: 20px;
        background: #28a745;
        color: white;
        padding: 15px 20px;
        border-radius: 8px;
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
        z-index: 1001;
        animation: slideInRight 0.3s ease;
    `;
    
    document.body.appendChild(notification);
    
    setTimeout(() => {
        notification.style.animation = 'slideOutRight 0.3s ease';
        setTimeout(() => {
            document.body.removeChild(notification);
        }, 300);
    }, 3000);
}

// 点击模态框外部关闭
window.onclick = function(event) {
    const modals = document.querySelectorAll('.modal');
    modals.forEach(modal => {
        if (event.target === modal) {
            modal.style.display = 'none';
        }
    });
}

// 添加动画样式
const style = document.createElement('style');
style.textContent = `
    @keyframes slideInRight {
        from {
            transform: translateX(100%);
            opacity: 0;
        }
        to {
            transform: translateX(0);
            opacity: 1;
        }
    }
    
    @keyframes slideOutRight {
        from {
            transform: translateX(0);
            opacity: 1;
        }
        to {
            transform: translateX(100%);
            opacity: 0;
        }
    }
`;
document.head.appendChild(style);
</script>