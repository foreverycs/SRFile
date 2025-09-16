<div class="file-viewer">
    <div class="file-info">
        <h2><?= htmlspecialchars($fileInfo->name) ?></h2>
        <div class="file-meta">
            <span><i class="fas fa-file"></i> <?= getFileType($fileInfo->type) ?></span>
            <span><i class="fas fa-weight"></i> <?= formatSize($fileInfo->size) ?></span>
            <span><i class="fas fa-clock"></i> <?= date('Y-m-d H:i', $fileInfo->upload_time) ?></span>
            <span><i class="fas fa-hourglass"></i> <?= date('Y-m-d H:i', $fileInfo->expire_time) ?> 过期</span>
            <span><i class="fas fa-key"></i> 取件码: <?= htmlspecialchars($fileInfo->pickup_code) ?></span>
        </div>
    </div>
    
    <div class="file-preview">
        <?php if (strpos($fileInfo->type, 'image/') === 0): ?>
            <!-- 图片预览 -->
            <div class="image-container">
                <img src="?action=download&id=<?= $fileInfo->id ?>" 
                     alt="<?= htmlspecialchars($fileInfo->name) ?>"
                     style="<?= $fileInfo->orientation === 'vertical' ? 'max-height: 80vh;' : 'max-width: 100%;' ?>">
                
                <?php if (count($allFiles) > 1): ?>
                    <div class="image-controls">
                        <button class="control-btn" id="prev-btn" <?= $currentIndex === 0 ? 'disabled' : '' ?>>
                            <i class="fas fa-chevron-left"></i>
                        </button>
                        <div class="file-counter">
                            <?= ($currentIndex + 1) ?>/<?= count($allFiles) ?>
                        </div>
                        <button class="control-btn" id="next-btn" <?= $currentIndex === count($allFiles) - 1 ? 'disabled' : '' ?>>
                            <i class="fas fa-chevron-right"></i>
                        </button>
                    </div>
                <?php endif; ?>
                
                <button class="fullscreen-btn" id="fullscreen-btn">
                    <i class="fas fa-expand"></i> 全屏
                </button>
            </div>
            
        <?php elseif (strpos($fileInfo->type, 'video/') === 0): ?>
            <!-- 视频预览 -->
            <div class="video-container" id="video-container">
                <video class="video-player" id="preview-video"
                       style="<?= $fileInfo->orientation === 'vertical' ? 'max-height: 80vh;' : 'width: 100%;' ?>"
                       controls>
                    <source src="?action=download&id=<?= $fileInfo->id ?>" type="<?= $fileInfo->type ?>">
                    您的浏览器不支持视频播放
                </video>
                
                <?php if (count($allFiles) > 1): ?>
                    <div class="video-navigation">
                        <button class="control-btn" id="prev-btn" <?= $currentIndex === 0 ? 'disabled' : '' ?>>
                            <i class="fas fa-chevron-left"></i>
                        </button>
                        <div class="file-counter">
                            <?= ($currentIndex + 1) ?>/<?= count($allFiles) ?>
                        </div>
                        <button class="control-btn" id="next-btn" <?= $currentIndex === count($allFiles) - 1 ? 'disabled' : '' ?>>
                            <i class="fas fa-chevron-right"></i>
                        </button>
                    </div>
                <?php endif; ?>
            </div>
            
        <?php elseif (strpos($fileInfo->type, 'text/') === 0): ?>
            <!-- 文本预览 -->
            <div class="text-container">
                <pre class="text-preview"><?= htmlspecialchars(file_get_contents($fileInfo->path)) ?></pre>
            </div>
            
        <?php else: ?>
            <!-- 其他文件类型 -->
            <div class="no-preview">
                <i class="fas fa-file"></i>
                <p>此文件类型不支持预览</p>
                <p>请下载文件到本地查看</p>
            </div>
        <?php endif; ?>
    </div>
    
    <?php if (count($allFiles) > 1): ?>
        <div class="file-list-container">
            <h3>文件列表</h3>
            <div class="file-list">
                <?php foreach ($allFiles as $index => $file): ?>
                    <button class="file-btn <?= $index === $currentIndex ? 'active-file' : '' ?>" 
                            onclick="window.location.href='?action=view&id=<?= $file->id ?>'">
                        <i class="fas fa-file"></i>
                        <?= htmlspecialchars($file->name) ?>
                    </button>
                <?php endforeach; ?>
            </div>
        </div>
    <?php endif; ?>
    
    <div class="file-actions">
        <a href="?action=download&id=<?= $fileInfo->id ?>" class="btn btn-primary">
            <i class="fas fa-download"></i> 下载文件
        </a>
        
        <?php if (count($allFiles) > 1): ?>
            <button class="btn" onclick="showAllLinks()">
                <i class="fas fa-link"></i> 全部直链
            </button>
        <?php else: ?>
            <button class="btn" onclick="copyToClipboard('<?= getFileLink($fileInfo->id) ?>')">
                <i class="fas fa-link"></i> 复制直链
            </button>
        <?php endif; ?>
        
        <button class="btn" onclick="window.location.href='?action=home'">
            <i class="fas fa-home"></i> 返回首页
        </button>
    </div>
    
    <!-- 全部链接模态框 -->
    <div id="all-links-modal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3>全部下载链接</h3>
                <button class="close-btn" onclick="closeModal()">&times;</button>
            </div>
            <div class="modal-body">
                <?php foreach ($allFiles as $file): ?>
                    <div class="link-item">
                        <div class="link-name"><?= htmlspecialchars($file->name) ?></div>
                        <div class="link-url">
                            <input type="text" value="<?= getFileLink($file->id) ?>" readonly>
                            <button class="copy-btn" onclick="copyToClipboard('<?= getFileLink($file->id) ?>')">
                                <i class="fas fa-copy"></i>
                            </button>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</div>

<style>
.file-viewer {
    display: flex;
    flex-direction: column;
    gap: 20px;
}

.file-info {
    background: #f8f9fa;
    padding: 20px;
    border-radius: 8px;
}

.file-info h2 {
    margin-bottom: 15px;
    color: #333;
}

.file-meta {
    display: flex;
    flex-wrap: wrap;
    gap: 15px;
    font-size: 0.9rem;
    color: #666;
}

.file-meta span {
    display: flex;
    align-items: center;
    gap: 5px;
}

.file-preview {
    background: white;
    border-radius: 8px;
    overflow: hidden;
    box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
}

.image-container {
    position: relative;
    text-align: center;
    padding: 20px;
}

.image-container img {
    max-width: 100%;
    max-height: 70vh;
    object-fit: contain;
}

.image-controls {
    position: absolute;
    bottom: 20px;
    left: 50%;
    transform: translateX(-50%);
    display: flex;
    align-items: center;
    gap: 15px;
    background: rgba(0, 0, 0, 0.7);
    padding: 10px 20px;
    border-radius: 25px;
    color: white;
}

.control-btn {
    background: none;
    border: none;
    color: white;
    font-size: 1.2rem;
    cursor: pointer;
    padding: 5px 10px;
    border-radius: 4px;
    transition: background 0.3s ease;
}

.control-btn:hover:not(:disabled) {
    background: rgba(255, 255, 255, 0.2);
}

.control-btn:disabled {
    opacity: 0.5;
    cursor: not-allowed;
}

.file-counter {
    font-weight: 500;
}

.fullscreen-btn {
    position: absolute;
    top: 20px;
    right: 20px;
    background: rgba(0, 0, 0, 0.7);
    color: white;
    border: none;
    padding: 10px 15px;
    border-radius: 6px;
    cursor: pointer;
    transition: background 0.3s ease;
}

.fullscreen-btn:hover {
    background: rgba(0, 0, 0, 0.9);
}

.video-container {
    position: relative;
    padding: 20px;
}

.video-player {
    max-width: 100%;
    max-height: 70vh;
}

.video-navigation {
    position: absolute;
    bottom: 20px;
    left: 50%;
    transform: translateX(-50%);
    display: flex;
    align-items: center;
    gap: 15px;
    background: rgba(0, 0, 0, 0.7);
    padding: 10px 20px;
    border-radius: 25px;
    color: white;
}

.text-container {
    padding: 20px;
}

.text-preview {
    background: #f8f9fa;
    padding: 20px;
    border-radius: 8px;
    max-height: 70vh;
    overflow-y: auto;
    white-space: pre-wrap;
    word-wrap: break-word;
}

.no-preview {
    text-align: center;
    padding: 60px 20px;
    color: #666;
}

.no-preview i {
    font-size: 3rem;
    margin-bottom: 20px;
    color: #ccc;
}

.file-list-container {
    background: white;
    border-radius: 8px;
    padding: 20px;
    box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
}

.file-list-container h3 {
    margin-bottom: 15px;
    color: #333;
}

.file-list {
    display: flex;
    flex-wrap: wrap;
    gap: 10px;
}

.file-btn {
    padding: 10px 15px;
    background: #f8f9fa;
    border: 1px solid #e9ecef;
    border-radius: 6px;
    cursor: pointer;
    transition: all 0.3s ease;
    text-align: left;
    min-width: 200px;
}

.file-btn:hover {
    background: #e9ecef;
}

.file-btn.active-file {
    background: #4b6cb7;
    color: white;
    border-color: #4b6cb7;
}

.file-actions {
    display: flex;
    gap: 10px;
    justify-content: center;
    flex-wrap: wrap;
}

.modal {
    display: none;
    position: fixed;
    z-index: 1000;
    left: 0;
    top: 0;
    width: 100%;
    height: 100%;
    background: rgba(0, 0, 0, 0.5);
}

.modal-content {
    position: relative;
    background: white;
    margin: 5% auto;
    padding: 0;
    width: 90%;
    max-width: 600px;
    border-radius: 8px;
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.3);
}

.modal-header {
    padding: 20px;
    border-bottom: 1px solid #e9ecef;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.modal-header h3 {
    margin: 0;
    color: #333;
}

.close-btn {
    background: none;
    border: none;
    font-size: 1.5rem;
    cursor: pointer;
    color: #666;
}

.close-btn:hover {
    color: #333;
}

.modal-body {
    padding: 20px;
    max-height: 70vh;
    overflow-y: auto;
}

.link-item {
    margin-bottom: 15px;
}

.link-name {
    font-weight: 500;
    margin-bottom: 8px;
    color: #333;
}

.link-url {
    display: flex;
    gap: 10px;
}

.link-url input {
    flex: 1;
    padding: 8px;
    border: 1px solid #e9ecef;
    border-radius: 4px;
    font-family: monospace;
    font-size: 0.9rem;
}

.copy-btn {
    background: #4b6cb7;
    color: white;
    border: none;
    padding: 8px 12px;
    border-radius: 4px;
    cursor: pointer;
    transition: background 0.3s ease;
}

.copy-btn:hover {
    background: #3a5ca0;
}

@media (max-width: 768px) {
    .file-meta {
        flex-direction: column;
        gap: 8px;
    }
    
    .file-actions {
        flex-direction: column;
    }
    
    .file-actions .btn {
        width: 100%;
    }
    
    .modal-content {
        margin: 10% auto;
        width: 95%;
    }
    
    .link-url {
        flex-direction: column;
    }
}
</style>

<script>
// 图片导航功能
document.addEventListener('DOMContentLoaded', function() {
    const prevBtn = document.getElementById('prev-btn');
    const nextBtn = document.getElementById('next-btn');
    const fullscreenBtn = document.getElementById('fullscreen-btn');
    
    if (prevBtn && nextBtn) {
        prevBtn.addEventListener('click', function() {
            const currentIndex = parseInt(document.querySelector('.file-counter').textContent.split('/')[0]) - 1;
            if (currentIndex > 0) {
                const allFiles = <?= json_encode(array_map(function($f) { return $f->id; }, $allFiles)) ?>;
                window.location.href = '?action=view&id=' + allFiles[currentIndex - 1];
            }
        });
        
        nextBtn.addEventListener('click', function() {
            const currentIndex = parseInt(document.querySelector('.file-counter').textContent.split('/')[0]) - 1;
            const allFiles = <?= json_encode(array_map(function($f) { return $f->id; }, $allFiles)) ?>;
            if (currentIndex < allFiles.length - 1) {
                window.location.href = '?action=view&id=' + allFiles[currentIndex + 1];
            }
        });
    }
    
    // 全屏功能
    if (fullscreenBtn) {
        fullscreenBtn.addEventListener('click', function() {
            const img = document.querySelector('.image-container img');
            if (img) {
                if (img.requestFullscreen) {
                    img.requestFullscreen();
                } else if (img.webkitRequestFullscreen) {
                    img.webkitRequestFullscreen();
                } else if (img.msRequestFullscreen) {
                    img.msRequestFullscreen();
                }
            }
        });
    }
});

// 模态框功能
function showAllLinks() {
    document.getElementById('all-links-modal').style.display = 'block';
}

function closeModal() {
    document.getElementById('all-links-modal').style.display = 'none';
}

// 点击模态框外部关闭
window.onclick = function(event) {
    const modal = document.getElementById('all-links-modal');
    if (event.target === modal) {
        modal.style.display = 'none';
    }
}
</script>