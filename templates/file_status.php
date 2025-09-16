<?php
// 文件状态页面模板
?>
<div class="file-status-page">
    <div class="status-container">
        <div class="status-notice status-<?= $status ?>">
            <div class="status-icon">
                <?php if ($status === 'pending'): ?>
                    <i class="fas fa-hourglass-half"></i>
                <?php elseif ($status === 'blocked'): ?>
                    <i class="fas fa-ban"></i>
                <?php elseif ($status === 'expired'): ?>
                    <i class="fas fa-clock"></i>
                <?php endif; ?>
            </div>
            
            <div class="status-content">
                <h2><?= $title ?></h2>
                <p class="status-message">
                    <?php if ($status === 'pending'): ?>
                        文件正在等待管理员审核
                    <?php elseif ($status === 'blocked'): ?>
                        此文件因违反使用政策已被封禁
                    <?php elseif ($status === 'expired'): ?>
                        此文件已超过有效期
                    <?php endif; ?>
                </p>
                <p class="status-details">
                    <?php if ($status === 'pending'): ?>
                        您的文件已成功上传，正在等待管理员审核。审核通过后即可访问。
                    <?php elseif ($status === 'blocked'): ?>
                        此文件因违反使用政策已被管理员封禁，无法访问。
                    <?php elseif ($status === 'expired'): ?>
                        此文件已超过有效期，无法访问。请联系文件分享者重新上传。
                    <?php endif; ?>
                </p>
            </div>
        </div>
        
        <div class="file-details-card">
            <h3><i class="fas fa-info-circle"></i> 文件信息</h3>
            <div class="file-info-grid">
                <div class="info-item">
                    <span class="info-label">文件名:</span>
                    <span class="info-value"><?= htmlspecialchars($fileInfo->name) ?></span>
                </div>
                <div class="info-item">
                    <span class="info-label">文件类型:</span>
                    <span class="info-value"><?= getFileType($fileInfo->type) ?></span>
                </div>
                <div class="info-item">
                    <span class="info-label">文件大小:</span>
                    <span class="info-value"><?= formatSize($fileInfo->size) ?></span>
                </div>
                <div class="info-item">
                    <span class="info-label">上传时间:</span>
                    <span class="info-value"><?= date('Y-m-d H:i:s', $fileInfo->upload_time) ?></span>
                </div>
                <div class="info-item">
                    <span class="info-label">取件码:</span>
                    <span class="info-value"><code><?= htmlspecialchars($fileInfo->pickup_code) ?></code></span>
                </div>
                <div class="info-item">
                    <span class="info-label">状态:</span>
                    <span class="info-value">
                        <span class="status-badge status-<?= $status ?>">
                            <?php if ($status === 'pending'): ?>
                                待审核
                            <?php elseif ($status === 'blocked'): ?>
                                已封禁
                            <?php elseif ($status === 'expired'): ?>
                                已过期
                            <?php endif; ?>
                        </span>
                    </span>
                </div>
            </div>
        </div>
        
        <div class="status-actions">
            <a href="?action=home" class="btn btn-primary">
                <i class="fas fa-arrow-left"></i> 返回首页
            </a>
            
            <?php if ($status === 'pending'): ?>
                <button class="btn btn-secondary" onclick="refreshStatus()">
                    <i class="fas fa-sync"></i> 刷新状态
                </button>
                <button class="btn btn-info" onclick="sharePickupCode()">
                    <i class="fas fa-share"></i> 分享取件码
                </button>
            <?php elseif ($status === 'blocked'): ?>
                <button class="btn btn-warning" onclick="showContactInfo()">
                    <i class="fas fa-question-circle"></i> 联系客服
                </button>
            <?php elseif ($status === 'expired'): ?>
                <button class="btn btn-info" onclick="showExpiredInfo()">
                    <i class="fas fa-info-circle"></i> 了解更多
                </button>
            <?php endif; ?>
        </div>
    </div>
    
    <!-- 分享取件码模态框 -->
    <?php if ($status === 'pending'): ?>
    <div id="shareModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3><i class="fas fa-share"></i> 分享取件码</h3>
                <button class="modal-close" onclick="closeModal('shareModal')">&times;</button>
            </div>
            <div class="modal-body">
                <div class="share-info">
                    <div class="pickup-code-display">
                        <span class="code-label">取件码:</span>
                        <span class="code-value"><?= htmlspecialchars($fileInfo->pickup_code) ?></span>
                        <button class="copy-btn" onclick="copyPickupCode()">
                            <i class="fas fa-copy"></i>
                        </button>
                    </div>
                    <div class="share-links">
                        <p>分享链接:</p>
                        <div class="link-input">
                            <input type="text" value="<?= (isset($_SERVER['HTTPS']) ? "https" : "http") . "://$_SERVER[HTTP_HOST]$_SERVER[SCRIPT_NAME]?action=view&id=$fileInfo->id" ?>" readonly>
                            <button class="copy-btn" onclick="copyShareLink()">
                                <i class="fas fa-copy"></i>
                            </button>
                        </div>
                    </div>
                    <div class="share-tips">
                        <h4><i class="fas fa-lightbulb"></i> 分享提示</h4>
                        <ul>
                            <li>取件码是获取文件的唯一凭证，请妥善保管</li>
                            <li>文件审核通过后即可正常访问</li>
                            <li>文件将在 <?= date('Y-m-d H:i', $fileInfo->expire_time) ?> 过期</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>
    
    <!-- 联系客服模态框 -->
    <?php if ($status === 'blocked'): ?>
    <div id="contactModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3><i class="fas fa-question-circle"></i> 联系客服</h3>
                <button class="modal-close" onclick="closeModal('contactModal')">&times;</button>
            </div>
            <div class="modal-body">
                <div class="contact-info">
                    <div class="contact-item">
                        <i class="fas fa-envelope"></i>
                        <div>
                            <strong>邮箱客服</strong>
                            <p>support@example.com</p>
                        </div>
                    </div>
                    <div class="contact-item">
                        <i class="fab fa-qq"></i>
                        <div>
                            <strong>QQ客服</strong>
                            <p>2277680934</p>
                        </div>
                    </div>
                    <div class="contact-item">
                        <i class="fas fa-clock"></i>
                        <div>
                            <strong>服务时间</strong>
                            <p>周一至周五 9:00-18:00</p>
                        </div>
                    </div>
                    <div class="appeal-info">
                        <h4><i class="fas fa-gavel"></i> 申诉流程</h4>
                        <p>如果您认为文件被错误封禁，请联系客服并提供以下信息：</p>
                        <ul>
                            <li>文件取件码: <?= htmlspecialchars($fileInfo->pickup_code) ?></li>
                            <li>文件名称: <?= htmlspecialchars($fileInfo->name) ?></li>
                            <li>申诉理由</li>
                            <li>联系方式</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>
    
    <!-- 过期信息模态框 -->
    <?php if ($status === 'expired'): ?>
    <div id="expiredModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3><i class="fas fa-info-circle"></i> 文件过期说明</h3>
                <button class="modal-close" onclick="closeModal('expiredModal')">&times;</button>
            </div>
            <div class="modal-body">
                <div class="expired-info">
                    <div class="info-section">
                        <h4><i class="fas fa-clock"></i> 为什么文件会过期？</h4>
                        <p>为了保护用户隐私和节省存储空间，所有文件都有有效期限制。您的文件已超过设定的有效期。</p>
                    </div>
                    <div class="info-section">
                        <h4><i class="fas fa-redo"></i> 如何重新获取文件？</h4>
                        <p>请联系文件分享者，请他们重新上传文件并分享新的取件码。</p>
                    </div>
                    <div class="info-section">
                        <h4><i class="fas fa-shield-alt"></i> 安全提醒</h4>
                        <ul>
                            <li>过期的文件已被系统自动删除，无法恢复</li>
                            <li>请勿从非官方渠道获取所谓的"恢复链接"</li>
                            <li>建议及时下载重要文件，避免过期</li>
                        </ul>
                    </div>
                    <div class="file-summary">
                        <h4><i class="fas fa-file"></i> 文件信息</h4>
                        <p><strong>文件名:</strong> <?= htmlspecialchars($fileInfo->name) ?></p>
                        <p><strong>过期时间:</strong> <?= date('Y-m-d H:i:s', $fileInfo->expire_time) ?></p>
                        <p><strong>文件大小:</strong> <?= formatSize($fileInfo->size) ?></p>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>
</div>

<style>
/* 模态框样式 */
.modal {
    display: none;
    position: fixed;
    z-index: 1000;
    left: 0;
    top: 0;
    width: 100%;
    height: 100%;
    background-color: rgba(0, 0, 0, 0.5);
    backdrop-filter: blur(5px);
}

.modal-content {
    background-color: white;
    margin: 5% auto;
    padding: 0;
    border-radius: 15px;
    width: 90%;
    max-width: 600px;
    box-shadow: 0 10px 40px rgba(0, 0, 0, 0.3);
    animation: modalSlideIn 0.3s ease;
}

@keyframes modalSlideIn {
    from {
        transform: translateY(-50px);
        opacity: 0;
    }
    to {
        transform: translateY(0);
        opacity: 1;
    }
}

.modal-header {
    padding: 20px 25px;
    border-bottom: 1px solid #eee;
    display: flex;
    justify-content: space-between;
    align-items: center;
    background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
    border-radius: 15px 15px 0 0;
}

.modal-header h3 {
    margin: 0;
    color: #333;
    font-size: 1.3rem;
    display: flex;
    align-items: center;
    gap: 10px;
}

.modal-close {
    background: none;
    border: none;
    font-size: 1.5rem;
    cursor: pointer;
    color: #666;
    padding: 0;
    width: 30px;
    height: 30px;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 50%;
    transition: all 0.3s ease;
}

.modal-close:hover {
    background: rgba(0, 0, 0, 0.1);
    color: #333;
}

.modal-body {
    padding: 25px;
    max-height: 70vh;
    overflow-y: auto;
}

/* 分享信息样式 */
.share-info {
    display: flex;
    flex-direction: column;
    gap: 20px;
}

.pickup-code-display {
    display: flex;
    align-items: center;
    gap: 15px;
    padding: 15px;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    border-radius: 10px;
    color: white;
}

.code-label {
    font-weight: 600;
}

.code-value {
    font-family: 'Courier New', monospace;
    font-size: 1.5rem;
    font-weight: bold;
    flex: 1;
    text-align: center;
}

.copy-btn {
    background: rgba(255, 255, 255, 0.2);
    border: none;
    color: white;
    padding: 8px 12px;
    border-radius: 6px;
    cursor: pointer;
    transition: all 0.3s ease;
}

.copy-btn:hover {
    background: rgba(255, 255, 255, 0.3);
}

.share-links {
    display: flex;
    flex-direction: column;
    gap: 10px;
}

.share-links p {
    margin: 0;
    font-weight: 600;
    color: #333;
}

.link-input {
    display: flex;
    gap: 10px;
}

.link-input input {
    flex: 1;
    padding: 10px;
    border: 2px solid #ddd;
    border-radius: 6px;
    font-size: 0.9rem;
    background: #f8f9fa;
}

.share-tips {
    background: #f8f9ff;
    padding: 15px;
    border-radius: 8px;
    border-left: 4px solid #667eea;
}

.share-tips h4 {
    margin: 0 0 10px 0;
    color: #333;
    font-size: 1.1rem;
}

.share-tips ul {
    margin: 0;
    padding-left: 20px;
}

.share-tips li {
    margin-bottom: 5px;
    color: #666;
}

/* 联系信息样式 */
.contact-info {
    display: flex;
    flex-direction: column;
    gap: 20px;
}

.contact-item {
    display: flex;
    align-items: center;
    gap: 15px;
    padding: 15px;
    background: #f8f9fa;
    border-radius: 8px;
}

.contact-item i {
    font-size: 1.5rem;
    color: #667eea;
    width: 30px;
    text-align: center;
}

.contact-item div {
    flex: 1;
}

.contact-item strong {
    color: #333;
    margin-bottom: 5px;
    display: block;
}

.contact-item p {
    margin: 0;
    color: #666;
}

.appeal-info {
    background: #fff3cd;
    padding: 15px;
    border-radius: 8px;
    border-left: 4px solid #ffc107;
}

.appeal-info h4 {
    margin: 0 0 10px 0;
    color: #856404;
}

.appeal-info p {
    margin: 0 0 10px 0;
    color: #856404;
}

.appeal-info ul {
    margin: 0;
    padding-left: 20px;
}

.appeal-info li {
    margin-bottom: 5px;
    color: #856404;
}

/* 过期信息样式 */
.expired-info {
    display: flex;
    flex-direction: column;
    gap: 20px;
}

.info-section {
    background: #f8f9fa;
    padding: 15px;
    border-radius: 8px;
}

.info-section h4 {
    margin: 0 0 10px 0;
    color: #333;
    display: flex;
    align-items: center;
    gap: 8px;
}

.info-section p {
    margin: 0;
    color: #666;
    line-height: 1.6;
}

.info-section ul {
    margin: 10px 0 0 0;
    padding-left: 20px;
}

.info-section li {
    margin-bottom: 5px;
    color: #666;
}

.file-summary {
    background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
    padding: 15px;
    border-radius: 8px;
}

.file-summary h4 {
    margin: 0 0 10px 0;
    color: #333;
}

.file-summary p {
    margin: 5px 0;
    color: #666;
}

/* 按钮样式增强 */
.btn-info {
    background: #17a2b8;
    color: white;
}

.btn-info:hover {
    background: #138496;
    transform: translateY(-2px);
}

.btn-warning {
    background: #ffc107;
    color: #212529;
}

.btn-warning:hover {
    background: #e0a800;
    transform: translateY(-2px);
}

/* 响应式设计 */
@media (max-width: 768px) {
    .modal-content {
        width: 95%;
        margin: 10% auto;
    }
    
    .modal-header {
        padding: 15px 20px;
    }
    
    .modal-header h3 {
        font-size: 1.1rem;
    }
    
    .modal-body {
        padding: 20px;
    }
    
    .pickup-code-display {
        flex-direction: column;
        text-align: center;
        gap: 10px;
    }
    
    .code-value {
        font-size: 1.2rem;
    }
    
    .link-input {
        flex-direction: column;
    }
    
    .contact-item {
        flex-direction: column;
        text-align: center;
        gap: 10px;
    }
    
    .contact-item i {
        font-size: 2rem;
    }
}

@media (max-width: 480px) {
    .modal-content {
        width: 98%;
        margin: 15% auto;
    }
    
    .modal-header {
        padding: 12px 15px;
    }
    
    .modal-header h3 {
        font-size: 1rem;
    }
    
    .modal-body {
        padding: 15px;
    }
    
    .status-actions {
        flex-direction: column;
    }
    
    .btn {
        width: 100%;
        margin-bottom: 10px;
    }
}
</style>

<script>
function refreshStatus() {
    location.reload();
}

function sharePickupCode() {
    document.getElementById('shareModal').style.display = 'block';
}

function showContactInfo() {
    document.getElementById('contactModal').style.display = 'block';
}

function showExpiredInfo() {
    document.getElementById('expiredModal').style.display = 'block';
}

function closeModal(modalId) {
    document.getElementById(modalId).style.display = 'none';
}

function copyPickupCode() {
    const pickupCode = '<?= htmlspecialchars($fileInfo->pickup_code) ?>';
    navigator.clipboard.writeText(pickupCode).then(() => {
        showNotification('取件码已复制到剪贴板');
    }).catch(() => {
        showNotification('复制失败，请手动复制');
    });
}

function copyShareLink() {
    const shareLink = '<?= (isset($_SERVER['HTTPS']) ? "https" : "http") . "://$_SERVER[HTTP_HOST]$_SERVER[SCRIPT_NAME]?action=view&id=$fileInfo->id" ?>';
    navigator.clipboard.writeText(shareLink).then(() => {
        showNotification('分享链接已复制到剪贴板');
    }).catch(() => {
        showNotification('复制失败，请手动复制');
    });
}

function showNotification(message) {
    // 创建通知元素
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
    
    // 3秒后自动移除
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

<style>
.file-status-page {
    max-width: 800px;
    margin: 0 auto;
    padding: 20px;
}

.status-container {
    background: white;
    border-radius: 16px;
    box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
    overflow: hidden;
    margin-bottom: 30px;
}

.status-notice {
    padding: 40px;
    text-align: center;
    color: white;
}

.status-pending {
    background: linear-gradient(135deg, #f39c12 0%, #e67e22 100%);
}

.status-blocked {
    background: linear-gradient(135deg, #e74c3c 0%, #c0392b 100%);
}

.status-expired {
    background: linear-gradient(135deg, #95a5a6 0%, #7f8c8d 100%);
}

.status-icon {
    font-size: 4rem;
    margin-bottom: 20px;
    opacity: 0.9;
}

.status-content h2 {
    font-size: 2rem;
    margin-bottom: 15px;
    font-weight: 600;
}

.status-message {
    font-size: 1.2rem;
    margin-bottom: 15px;
    opacity: 0.9;
}

.status-details {
    font-size: 1rem;
    opacity: 0.8;
    line-height: 1.6;
}

.file-details-card {
    background: white;
    border-radius: 12px;
    padding: 30px;
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
    margin-bottom: 30px;
}

.file-details-card h3 {
    margin-bottom: 20px;
    color: #333;
    display: flex;
    align-items: center;
    gap: 10px;
}

.file-info-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 15px;
}

.info-item {
    display: flex;
    align-items: center;
    gap: 10px;
}

.info-label {
    font-weight: 600;
    color: #666;
    min-width: 80px;
}

.info-value {
    color: #333;
    font-family: 'Courier New', monospace;
    font-size: 0.9rem;
}

.status-actions {
    display: flex;
    gap: 15px;
    justify-content: center;
    flex-wrap: wrap;
}

.btn {
    padding: 12px 24px;
    border: none;
    border-radius: 8px;
    font-size: 1rem;
    font-weight: 500;
    text-decoration: none;
    cursor: pointer;
    transition: all 0.3s ease;
    display: inline-flex;
    align-items: center;
    gap: 8px;
}

.btn-primary {
    background: #4b6cb7;
    color: white;
}

.btn-primary:hover {
    background: #3a5ca0;
    transform: translateY(-2px);
}

.btn-secondary {
    background: #6c757d;
    color: white;
}

.btn-secondary:hover {
    background: #5a6268;
    transform: translateY(-2px);
}

.status-badge {
    padding: 4px 12px;
    border-radius: 20px;
    font-size: 0.8rem;
    font-weight: 500;
}

.status-pending .status-badge {
    background: rgba(255, 255, 255, 0.2);
    color: white;
}

.status-blocked .status-badge {
    background: rgba(255, 255, 255, 0.2);
    color: white;
}

.status-expired .status-badge {
    background: rgba(255, 255, 255, 0.2);
    color: white;
}

@media (max-width: 768px) {
    .file-status-page {
        padding: 10px;
    }
    
    .status-notice {
        padding: 30px 20px;
    }
    
    .status-icon {
        font-size: 3rem;
    }
    
    .status-content h2 {
        font-size: 1.6rem;
    }
    
    .status-message {
        font-size: 1rem;
    }
    
    .file-details-card {
        padding: 20px;
    }
    
    .file-info-grid {
        grid-template-columns: 1fr;
    }
    
    .info-item {
        flex-direction: column;
        align-items: flex-start;
        gap: 5px;
    }
    
    .info-label {
        min-width: auto;
    }
    
    .status-actions {
        flex-direction: column;
        align-items: center;
    }
    
    .btn {
        width: 100%;
        max-width: 300px;
        justify-content: center;
    }
}

@media (max-width: 480px) {
    .status-container {
        border-radius: 12px;
    }
    
    .status-notice {
        padding: 25px 15px;
    }
    
    .status-icon {
        font-size: 2.5rem;
    }
    
    .status-content h2 {
        font-size: 1.4rem;
    }
    
    .file-details-card {
        padding: 15px;
    }
    
    .btn {
        padding: 10px 20px;
        font-size: 0.9rem;
    }
}
</style>

<script>
function refreshStatus() {
    location.reload();
}

// 自动刷新功能（仅对待审核状态）
<?php if ($status === 'pending'): ?>
setTimeout(function() {
    location.reload();
}, 30000); // 30秒后自动刷新
<?php endif; ?>
</script>