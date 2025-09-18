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
    background: rgba(0, 0, 0, 0.3);
    padding: 20px;
    border-radius: 8px;
    border: 1px solid rgba(255, 255, 255, 0.1);
}

.file-info h2 {
    margin-bottom: 15px;
    color: white;
    text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.5);
}

.file-meta {
    display: flex;
    flex-wrap: wrap;
    gap: 15px;
    font-size: 0.9rem;
    color: rgba(255, 255, 255, 0.7);
}

.file-meta span {
    display: flex;
    align-items: center;
    gap: 5px;
}

.file-preview {
    background: rgba(0, 0, 0, 0.3);
    border-radius: 8px;
    overflow: hidden;
    border: 1px solid rgba(255, 255, 255, 0.1);
}

.image-container {
    position: relative;
    text-align: center;
    padding: 20px;
    /* 移动端优化 */
    -webkit-tap-highlight-color: transparent;
    -webkit-touch-callout: none;
    -webkit-user-select: none;
    user-select: none;
    touch-action: manipulation;
}

.image-container img {
    max-width: 100%;
    max-height: 70vh;
    object-fit: contain;
    /* 移动端优化 */
    -webkit-user-drag: none;
    -khtml-user-drag: none;
    -moz-user-drag: none;
    -o-user-drag: none;
    user-drag: none;
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
    /* 移动端优化 */
    z-index: 10;
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
    /* 移动端优化 */
    -webkit-tap-highlight-color: transparent;
    -webkit-touch-callout: none;
    -webkit-user-select: none;
    user-select: none;
    touch-action: manipulation;
    min-width: 40px;
    min-height: 40px;
    display: flex;
    align-items: center;
    justify-content: center;
}

.control-btn:hover:not(:disabled),
.control-btn:active:not(:disabled) {
    background: rgba(255, 255, 255, 0.2);
}

.control-btn:active {
    transform: scale(0.95);
}

.control-btn:disabled {
    opacity: 0.5;
    cursor: not-allowed;
}

.file-counter {
    font-weight: 500;
    min-width: 40px;
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
    /* 移动端优化 */
    -webkit-tap-highlight-color: transparent;
    -webkit-touch-callout: none;
    -webkit-user-select: none;
    user-select: none;
    touch-action: manipulation;
    z-index: 10;
    min-width: 50px;
    min-height: 40px;
    display: flex;
    align-items: center;
    justify-content: center;
}

.fullscreen-btn:hover,
.fullscreen-btn:active {
    background: rgba(0, 0, 0, 0.9);
}

.fullscreen-btn:active {
    transform: scale(0.95);
}

.video-container {
    position: relative;
    padding: 20px;
    /* 移动端优化 */
    -webkit-tap-highlight-color: transparent;
    -webkit-touch-callout: none;
    -webkit-user-select: none;
    user-select: none;
}

.video-player {
    max-width: 100%;
    max-height: 70vh;
    /* 移动端优化 */
    -webkit-user-drag: none;
    -khtml-user-drag: none;
    -moz-user-drag: none;
    -o-user-drag: none;
    user-drag: none;
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
    /* 移动端优化 */
    z-index: 10;
}

.text-container {
    padding: 20px;
    /* 移动端优化 */
    -webkit-tap-highlight-color: transparent;
    -webkit-touch-callout: none;
    -webkit-user-select: text;
    user-select: text;
}

.text-preview {
    background: rgba(0, 0, 0, 0.2);
    padding: 20px;
    border-radius: 8px;
    max-height: 70vh;
    overflow-y: auto;
    white-space: pre-wrap;
    word-wrap: break-word;
    color: white;
    /* 移动端优化 */
    font-size: 16px; /* 防止iOS缩放 */
    line-height: 1.5;
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
    background: rgba(0, 0, 0, 0.3);
    border-radius: 8px;
    padding: 20px;
    border: 1px solid rgba(255, 255, 255, 0.1);
}

.file-list-container h3 {
    margin-bottom: 15px;
    color: white;
    text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.5);
}

.file-list {
    display: flex;
    flex-wrap: wrap;
    gap: 10px;
}

.file-btn {
    padding: 10px 15px;
    background: rgba(255, 255, 255, 0.1);
    border: 1px solid rgba(255, 255, 255, 0.2);
    border-radius: 6px;
    cursor: pointer;
    transition: all 0.3s ease;
    text-align: left;
    min-width: 200px;
    color: white;
}

.file-btn:hover {
    background: rgba(255, 255, 255, 0.2);
}

.file-btn.active-file {
    background: linear-gradient(90deg, #4b6cb7, #182848);
    color: white;
    border-color: #4b6cb7;
    box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
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
    background: rgba(0, 0, 0, 0.8);
    margin: 5% auto;
    padding: 0;
    width: 90%;
    max-width: 600px;
    border-radius: 8px;
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.3);
    border: 1px solid rgba(255, 255, 255, 0.1);
}

.modal-header {
    padding: 20px;
    border-bottom: 1px solid rgba(255, 255, 255, 0.1);
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.modal-header h3 {
    margin: 0;
    color: white;
    text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.5);
}

.close-btn {
    background: none;
    border: none;
    font-size: 1.5rem;
    cursor: pointer;
    color: rgba(255, 255, 255, 0.7);
}

.close-btn:hover {
    color: white;
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
    color: white;
    text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.5);
}

.link-url {
    display: flex;
    gap: 10px;
}

.link-url input {
    flex: 1;
    padding: 8px;
    border: 1px solid rgba(255, 255, 255, 0.2);
    border-radius: 4px;
    font-family: monospace;
    font-size: 0.9rem;
    background: rgba(255, 255, 255, 0.1);
    color: white;
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

/* 用户下载记录样式 */
.user-download-history {
    background: rgba(0, 0, 0, 0.3);
    border-radius: 8px;
    padding: 20px;
    border: 1px solid rgba(255, 255, 255, 0.1);
    margin-top: 20px;
}

.history-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 15px;
    padding-bottom: 10px;
    border-bottom: 1px solid rgba(255, 255, 255, 0.1);
}

.history-header h3 {
    margin: 0;
    color: white;
    text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.5);
    display: flex;
    align-items: center;
    gap: 8px;
}

.clear-history-btn {
    background: rgba(255, 255, 255, 0.1);
    border: 1px solid rgba(255, 255, 255, 0.2);
    color: rgba(255, 255, 255, 0.7);
    padding: 6px 12px;
    border-radius: 4px;
    cursor: pointer;
    font-size: 0.9rem;
    display: flex;
    align-items: center;
    gap: 5px;
    transition: all 0.3s ease;
}

.clear-history-btn:hover {
    background: rgba(255, 255, 255, 0.2);
    color: white;
}

.history-content {
    max-height: 300px;
    overflow-y: auto;
}

/* 确保历史记录列表始终使用flex布局 */
.download-history-list,
.access-history-list {
    display: flex !important;
    flex-direction: column;
    gap: 10px;
}

/* 历史记录项的统一样式 */
.history-item {
    display: flex !important;
    justify-content: space-between;
    align-items: center;
    padding: 12px;
    background: rgba(255, 255, 255, 0.05);
    border-radius: 6px;
    border: 1px solid rgba(255, 255, 255, 0.1);
    transition: all 0.3s ease;
    cursor: pointer;
}

.history-item:hover {
    background: rgba(255, 255, 255, 0.1);
    transform: translateY(-1px);
    box-shadow: 0 2px 5px rgba(0, 0, 0, 0.2);
}

.history-item-info {
    flex: 1;
}

.history-item-name {
    font-weight: 500;
    color: white;
    margin-bottom: 4px;
    display: flex;
    align-items: center;
    gap: 5px;
}

.history-item-meta {
    display: flex;
    flex-wrap: wrap;
    gap: 10px;
    font-size: 0.8rem;
    color: rgba(255, 255, 255, 0.7);
}

.history-item-meta span {
    display: flex;
    align-items: center;
    gap: 3px;
}

.history-item-code {
    background: #4b6cb7;
    color: white;
    padding: 4px 8px;
    border-radius: 4px;
    font-family: monospace;
    font-size: 0.8rem;
    font-weight: 500;
}

/* 没有记录时的消息样式 */
.no-history-message {
    text-align: center;
    padding: 30px;
    color: rgba(255, 255, 255, 0.7);
    font-style: italic;
    display: none;
}

@media (max-width: 768px) {
    .file-info {
        padding: 15px;
    }
    
    .file-info h2 {
        font-size: 1.3rem;
        margin-bottom: 10px;
    }
    
    .file-meta {
        flex-direction: column;
        gap: 8px;
        font-size: 0.85rem;
    }
    
    .file-preview {
        border-radius: 6px;
    }
    
    .image-container {
        padding: 15px;
    }
    
    .image-container img {
        max-height: 60vh;
    }
    
    .image-controls {
        bottom: 15px;
        padding: 8px 15px;
        gap: 10px;
    }
    
    .control-btn {
        min-width: 36px;
        min-height: 36px;
        font-size: 1rem;
        padding: 4px 8px;
    }
    
    .file-counter {
        font-size: 0.9rem;
    }
    
    .fullscreen-btn {
        top: 15px;
        right: 15px;
        padding: 8px 12px;
        min-width: 45px;
        min-height: 36px;
        font-size: 0.9rem;
    }
    
    .video-container {
        padding: 15px;
    }
    
    .video-player {
        max-height: 60vh;
    }
    
    .video-navigation {
        bottom: 15px;
        padding: 8px 15px;
        gap: 10px;
    }
    
    .text-container {
        padding: 15px;
    }
    
    .text-preview {
        padding: 15px;
        max-height: 60vh;
        font-size: 15px;
        line-height: 1.4;
    }
    
    .no-preview {
        padding: 40px 15px;
    }
    
    .no-preview i {
        font-size: 2.5rem;
    }
    
    .file-list-container {
        padding: 15px;
    }
    
    .file-list-container h3 {
        font-size: 1.1rem;
        margin-bottom: 12px;
    }
    
    .file-btn {
        padding: 12px;
        min-width: 100%;
        margin-bottom: 8px;
    }
    
    .file-actions {
        flex-direction: column;
        gap: 8px;
    }
    
    .file-actions .btn {
        width: 100%;
        padding: 12px;
        font-size: 16px; /* 更大的触摸目标 */
    }
    
    .modal-content {
        margin: 10% auto;
        width: 95%;
        border-radius: 6px;
    }
    
    .modal-header {
        padding: 15px;
    }
    
    .modal-header h3 {
        font-size: 1.1rem;
    }
    
    .close-btn {
        font-size: 1.3rem;
    }
    
    .modal-body {
        padding: 15px;
        max-height: 60vh;
    }
    
    .link-url {
        flex-direction: column;
        gap: 8px;
    }
    
    .link-url input {
        padding: 10px;
        font-size: 14px;
    }
    
    .copy-btn {
        padding: 10px;
        width: 100%;
    }
    
    .user-download-history {
        padding: 15px;
    }
    
    .history-header {
        padding-bottom: 8px;
        margin-bottom: 12px;
    }
    
    .history-header h3 {
        font-size: 1rem;
    }
    
    .clear-history-btn {
        padding: 5px 10px;
        font-size: 0.85rem;
    }
    
    .history-content {
        max-height: 250px;
    }
    
    .history-item {
        padding: 10px;
    }
    
    .history-item-name {
        font-size: 0.9rem;
    }
    
    .history-item-meta {
        font-size: 0.75rem;
        gap: 8px;
    }
    
    .history-item-code {
        padding: 3px 6px;
        font-size: 0.75rem;
    }
}

/* 平板设备适配 */
@media (min-width: 601px) and (max-width: 768px) {
    .file-info h2 {
        font-size: 1.5rem;
    }
    
    .image-container img {
        max-height: 65vh;
    }
    
    .video-player {
        max-height: 65vh;
    }
    
    .text-preview {
        max-height: 65vh;
        font-size: 16px;
    }
    
    .file-btn {
        min-width: 150px;
    }
}

/* 大屏手机适配 */
@media (min-width: 769px) and (max-width: 992px) {
    .file-info h2 {
        font-size: 1.7rem;
    }
    
    .image-container img {
        max-height: 75vh;
    }
    
    .video-player {
        max-height: 75vh;
    }
    
    .text-preview {
        max-height: 75vh;
    }
}

/* 横屏模式适配 */
@media (max-width: 768px) and (orientation: landscape) {
    .file-info {
        padding: 12px;
    }
    
    .file-info h2 {
        font-size: 1.1rem;
        margin-bottom: 8px;
    }
    
    .file-meta {
        font-size: 0.8rem;
        gap: 6px;
    }
    
    .image-container {
        padding: 12px;
    }
    
    .image-container img {
        max-height: 50vh;
    }
    
    .image-controls {
        bottom: 10px;
        padding: 6px 12px;
    }
    
    .fullscreen-btn {
        top: 10px;
        right: 10px;
        padding: 6px 10px;
    }
    
    .video-container {
        padding: 12px;
    }
    
    .video-player {
        max-height: 50vh;
    }
    
    .text-container {
        padding: 12px;
    }
    
    .text-preview {
        padding: 12px;
        max-height: 50vh;
        font-size: 14px;
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

// 用户下载记录功能
document.addEventListener('DOMContentLoaded', function() {
    // 添加当前下载的文件到本地存储
    addCurrentFileToDownloadHistory();
    
    // 显示下载记录
    displayDownloadHistory();
});

// 添加当前下载的文件到本地存储
function addCurrentFileToDownloadHistory() {
    const fileInfo = {
        fileName: '<?= htmlspecialchars($fileInfo->name) ?>',
        code: '<?= htmlspecialchars($fileInfo->pickup_code) ?>',
        timestamp: new Date().toISOString()
    };
    
    // 从localStorage获取现有记录
    let downloadHistory = JSON.parse(localStorage.getItem('fileDownloadHistory') || '[]');
    
    // 检查是否已存在相同记录
    const existingIndex = downloadHistory.findIndex(item => item.fileName === fileInfo.fileName && item.code === fileInfo.code);
    
    if (existingIndex !== -1) {
        // 如果存在，更新时间戳
        downloadHistory[existingIndex].timestamp = fileInfo.timestamp;
    } else {
        // 如果不存在，添加新记录
        downloadHistory.unshift(fileInfo);
    }
    
    // 限制记录数量，最多保存50条
    if (downloadHistory.length > 50) {
        downloadHistory.splice(50);
    }
    
    // 保存到localStorage
    localStorage.setItem('fileDownloadHistory', JSON.stringify(downloadHistory));
}

// 显示下载记录 - 修复版本
function displayDownloadHistory() {
    console.log('Loading download history...');
    const downloadHistory = JSON.parse(localStorage.getItem('fileDownloadHistory') || '[]');
    const historyList = document.getElementById('download-history-list');
    const noHistoryMessage = document.getElementById('no-download-history-message');
    
    console.log('Download list display style:', window.getComputedStyle(historyList).display);
    
    // 清空现有列表
    historyList.innerHTML = '';
    
    if (downloadHistory.length === 0) {
        // 如果没有记录，显示提示信息
        historyList.style.display = 'none';
        noHistoryMessage.style.display = 'block';
    } else {
        // 如果有记录，显示列表
        historyList.style.display = 'flex'; // 确保这里是flex
        noHistoryMessage.style.display = 'none';
        
        // 添加每条记录到列表
        downloadHistory.forEach(item => {
            const historyItem = document.createElement('div');
            historyItem.className = 'history-item';
            historyItem.innerHTML = `
                <div class="history-item-info">
                    <div class="history-item-name">
                        <i class="fas fa-download"></i>
                        ${item.fileName}
                    </div>
                    <div class="history-item-meta">
                        <span><i class="fas fa-key"></i> ${item.code}</span>
                        <span><i class="fas fa-clock"></i> ${formatAccessTime(item.timestamp)}</span>
                    </div>
                </div>
                <div class="history-item-code">${item.code}</div>
            `;
            
            // 添加点击事件，点击记录可以填入取件码到下载表单
            historyItem.addEventListener('click', function() {
                // 返回首页
                window.location.href = '?action=home';
            });
            
            historyList.appendChild(historyItem);
        });
    }
}

// 清除下载记录
function clearDownloadHistory() {
    if (confirm('确定要清除所有下载记录吗？此操作不可撤销。')) {
        localStorage.removeItem('fileDownloadHistory');
        displayDownloadHistory();
    }
}

// 格式化文件大小
function formatSize(bytes) {
    if (bytes === 0) return '0 Bytes';
    
    const k = 1024;
    const sizes = ['Bytes', 'KB', 'MB', 'GB'];
    const i = Math.floor(Math.log(bytes) / Math.log(k));
    
    return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
}

// 格式化访问时间
function formatAccessTime(isoString) {
    const date = new Date(isoString);
    const now = new Date();
    const diffMs = now - date;
    const diffMins = Math.floor(diffMs / 60000);
    const diffHours = Math.floor(diffMs / 3600000);
    const diffDays = Math.floor(diffMs / 86400000);
    
    if (diffMins < 1) {
        return '刚刚';
    } else if (diffMins < 60) {
        return `${diffMins}分钟前`;
    } else if (diffHours < 24) {
        return `${diffHours}小时前`;
    } else if (diffDays < 7) {
        return `${diffDays}天前`;
    } else {
        return date.toLocaleDateString('zh-CN');
    }
}
</script>