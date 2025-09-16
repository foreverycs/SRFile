
<?php if ($uploadSuccess): ?>
    <div class="notification success">
        文件上传成功！取件码: <strong><?= htmlspecialchars($pickupCode) ?></strong>
    </div>
<?php endif; ?>

<?php if ($pickupError): ?>
    <div class="notification error">
        <?= htmlspecialchars($errorMessage) ?>
    </div>
<?php endif; ?>

<div class="tabs">
    <div class="tab active" data-tab="send">发送文件</div>
    <div class="tab" data-tab="receive">提取文件</div>
</div>

<div class="content">
    <div class="tab-content active" id="send-tab">
        <h2>发送文件</h2>
        <p>上传您的文件并生成取件码</p>
        
        <form id="upload-form" enctype="multipart/form-data">
            <input type="hidden" name="csrf_token" value="<?= $csrf_token ?? '' ?>">
            
            <div class="upload-area" id="upload-area">
                <div class="upload-icon">
                    <i class="fas fa-cloud-upload-alt"></i>
                </div>
                <div class="upload-text">
                    <h3>拖拽文件到此处或点击选择</h3>
                    <p>支持多文件同时上传，最大 <?= htmlspecialchars($config['max_upload_size']) ?> MB</p>
                </div>
                <input type="file" id="file-input" name="file[]" multiple style="display: none;">
            </div>
            
            <div class="allowed-types">
                <h4>允许的文件类型:</h4>
                <div class="types-list">
                    <?php 
                    $typeNames = [
                        'image/jpeg' => 'JPEG图片',
                        'image/png' => 'PNG图片',
                        'image/gif' => 'GIF图片',
                        'image/webp' => 'WebP图片',
                        'video/mp4' => 'MP4视频',
                        'video/quicktime' => 'MOV视频',
                        'video/x-msvideo' => 'AVI视频',
                        'video/x-matroska' => 'MKV视频',
                        'audio/mpeg' => 'MP3音频',
                        'audio/wav' => 'WAV音频',
                        'audio/ogg' => 'OGG音频',
                        'text/plain' => '文本文件',
                        'application/pdf' => 'PDF文档',
                        'application/msword' => 'Word文档',
                        'application/vnd.openxmlformats-officedocument.wordprocessingml.document' => 'Word文档(.docx)',
                        'application/vnd.ms-excel' => 'Excel表格',
                        'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' => 'Excel表格(.xlsx)',
                        'application/vnd.ms-powerpoint' => 'PowerPoint演示文稿',
                        'application/vnd.openxmlformats-officedocument.presentationml.presentation' => 'PowerPoint演示文稿(.pptx)',
                        'application/zip' => 'ZIP压缩包',
                        'application/x-rar-compressed' => 'RAR压缩包',
                        'application/x-7z-compressed' => '7Z压缩包',
                        'application/x-tar' => 'TAR压缩包',
                        // 代码类型
                        'text/x-php' => 'PHP代码',
                        'text/x-c' => 'C代码',
                        'text/x-c++' => 'C++代码',
                        'text/x-python' => 'Python代码',
                        'text/x-java' => 'Java代码',
                        'text/x-javascript' => 'JavaScript代码',
                        'text/x-html' => 'HTML代码',
                        'text/x-css' => 'CSS代码',
                        'text/x-sql' => 'SQL代码',
                        'text/x-shellscript' => 'Shell脚本',
                        'text/x-perl' => 'Perl脚本',
                        'text/x-ruby' => 'Ruby代码',
                        'text/x-go' => 'Go代码',
                        'text/x-rust' => 'Rust代码',
                        'text/x-swift' => 'Swift代码',
                        'text/x-kotlin' => 'Kotlin代码',
                        'text/x-typescript' => 'TypeScript代码',
                        'text/x-markdown' => 'Markdown文档',
                        'text/x-yaml' => 'YAML配置',
                        'text/x-json' => 'JSON配置',
                        'text/x-xml' => 'XML配置'
                    ];
                    
                    $allowedTypes = $config['allowed_types'] ?? [];
                    $displayTypes = array_slice($allowedTypes, 0, 8); // 只显示前8个类型
                    $remainingCount = count($allowedTypes) - count($displayTypes);
                    
                    foreach ($displayTypes as $type):
                        if (isset($typeNames[$type])):
                    ?>
                        <span class="type-tag"><?= htmlspecialchars($typeNames[$type]) ?></span>
                    <?php 
                        endif;
                    endforeach;
                    
                    if ($remainingCount > 0):
                    ?>
                        <span class="type-tag more">+<?= $remainingCount ?> 更多</span>
                    <?php endif; ?>
                </div>
            </div>
            
            <div class="file-list-container" id="file-list-container">
                <div class="file-list-title">
                    <i class="fas fa-list"></i> 已选择的文件
                </div>
                <div id="file-list"></div>
            </div>
            
            <div class="form-group">
                <label for="expire-days">过期时间:</label>
                <select id="expire-days" name="expire_days">
                    <option value="1">1天</option>
                    <option value="3">3天</option>
                    <option value="7" selected>7天</option>
                    <option value="30">30天</option>
                </select>
            </div>
            
            <div class="progress-container" id="progress-container">
                <div class="progress-bar" id="progress-bar">0%</div>
            </div>
            
            <button type="submit" class="btn" id="upload-btn">
                <i class="fas fa-upload"></i> 开始上传
            </button>
        </form>
    </div>
    
    <div class="tab-content" id="receive-tab">
        <h2>提取文件</h2>
        <p>输入取件码提取文件</p>
        
        <form id="pickup-form" method="post" action="?action=pickup">
            <div class="form-group">
                <label for="pickup-code">取件码:</label>
                <input type="text" id="pickup-code" name="pickup_code" 
                       placeholder="请输入5位取件码" maxlength="5" required>
            </div>
            
            <button type="submit" class="btn">
                <i class="fas fa-download"></i> 提取文件
            </button>
        </form>
        
        <?php if (isset($_SESSION['downloaded_files']) && !empty($_SESSION['downloaded_files'])): ?>
            <div class="recent-downloads">
                <h3>最近下载</h3>
                <div class="download-list">
                    <?php foreach (array_slice(array_reverse($_SESSION['downloaded_files']), 0, 5) as $download): ?>
                        <div class="download-item">
                            <div class="download-info">
                                <div class="download-name"><?= htmlspecialchars($download['name']) ?></div>
                                <div class="download-time">
                                    <?= date('Y-m-d H:i', $download['download_time']) ?>
                                </div>
                            </div>
                            <div class="download-code">
                                取件码: <?= htmlspecialchars($download['pickup_code']) ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>

<style>
.tabs {
    display: flex;
    background: rgba(0, 0, 0, 0.3);
    border-radius: 10px 10px 0 0;
    overflow: hidden;
    border: 1px solid rgba(255, 255, 255, 0.1);
    border-bottom: none;
}

.tab {
    flex: 1;
    padding: 15px;
    text-align: center;
    cursor: pointer;
    transition: all 0.3s ease;
    font-weight: 500;
    color: rgba(255, 255, 255, 0.7);
}

.tab:hover {
    background: rgba(255, 255, 255, 0.1);
}

.tab.active {
    background: linear-gradient(90deg, #4b6cb7, #182848);
    color: white;
    box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
}

.tab-content {
    display: none;
    background: rgba(0, 0, 0, 0.3);
    border-radius: 0 0 10px 10px;
    border: 1px solid rgba(255, 255, 255, 0.1);
    border-top: none;
    padding: 20px;
}

.tab-content.active {
    display: block;
}

.upload-area {
    border: 2px dashed rgba(255, 255, 255, 0.3);
    border-radius: 10px;
    padding: 40px;
    text-align: center;
    cursor: pointer;
    transition: all 0.3s ease;
    margin: 20px 0;
    background: rgba(255, 255, 255, 0.05);
}

.upload-area:hover {
    border-color: #fdbb2d;
    background: rgba(255, 255, 255, 0.1);
}

.allowed-types {
    background: rgba(0, 0, 0, 0.3);
    border-radius: 10px;
    padding: 15px;
    margin: 15px 0;
    border: 1px solid rgba(255, 255, 255, 0.1);
}

.allowed-types h4 {
    margin: 0 0 10px 0;
    color: white;
    font-size: 14px;
    font-weight: 600;
}

.types-list {
    display: flex;
    flex-wrap: wrap;
    gap: 8px;
}

.type-tag {
    background: rgba(255, 255, 255, 0.1);
    color: rgba(255, 255, 255, 0.7);
    padding: 4px 8px;
    border-radius: 4px;
    font-size: 12px;
    font-weight: 500;
}

.type-tag.more {
    background: linear-gradient(90deg, #4b6cb7, #182848);
    color: white;
    box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
}

.notification {
    padding: 12px 16px;
    margin: 10px 0;
    border-radius: 6px;
    font-weight: 500;
    z-index: 1000;
    animation: fadeIn 0.5s;
}

.notification.success {
    background: rgba(40, 167, 69, 0.2);
    color: #d4edda;
    border: 1px solid rgba(40, 167, 69, 0.5);
}

.notification.error {
    background: rgba(220, 53, 69, 0.2);
    color: #f8d7da;
    border: 1px solid rgba(220, 53, 69, 0.5);
}

.notification.info {
    background: rgba(23, 162, 184, 0.2);
    color: #d1ecf1;
    border: 1px solid rgba(23, 162, 184, 0.5);
}

@keyframes fadeIn {
    from { opacity: 0; }
    to { opacity: 1; }
}

.upload-area.dragover {
    border-color: #fdbb2d;
    background: rgba(255, 255, 255, 0.15);
}

.upload-icon {
    font-size: 3rem;
    color: #fdbb2d;
    margin-bottom: 20px;
    text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.5);
}

.upload-text h3 {
    margin-bottom: 10px;
    color: white;
    text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.5);
}

.upload-text p {
    color: rgba(255, 255, 255, 0.7);
}

.file-list-container {
    margin-top: 20px;
    max-height: 300px;
    overflow-y: auto;
    background: rgba(0, 0, 0, 0.2);
    border-radius: 10px;
    padding: 15px;
    border: 1px solid rgba(255, 255, 255, 0.1);
}

.file-list-title {
    font-size: 1.1rem;
    font-weight: 600;
    margin-bottom: 10px;
    color: white;
    display: flex;
    align-items: center;
    gap: 8px;
}

.file-item {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 10px 15px;
    background: rgba(255, 255, 255, 0.05);
    border-radius: 8px;
    margin-bottom: 8px;
    transition: all 0.3s ease;
}

.file-item:hover {
    background: rgba(255, 255, 255, 0.1);
}

.file-info {
    display: flex;
    align-items: center;
    gap: 10px;
}

.file-icon {
    font-size: 1.2rem;
    color: #fdbb2d;
}

.file-name {
    font-weight: 500;
    color: white;
}

.file-size {
    color: rgba(255, 255, 255, 0.7);
    font-size: 0.9rem;
}

.file-remove {
    background: linear-gradient(90deg, #dc3545, #a71e2a);
    color: white;
    border: none;
    border-radius: 4px;
    padding: 5px 10px;
    cursor: pointer;
    font-size: 0.8rem;
    box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
    transition: all 0.3s ease;
}

.file-remove:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 20px rgba(0, 0, 0, 0.3);
}

.recent-downloads {
    margin-top: 30px;
    padding: 20px;
    background: rgba(0, 0, 0, 0.3);
    border-radius: 10px;
    border: 1px solid rgba(255, 255, 255, 0.1);
}

.recent-downloads h3 {
    margin-bottom: 15px;
    color: white;
    text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.5);
}

.download-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 10px 0;
    border-bottom: 1px solid rgba(255, 255, 255, 0.1);
}

.download-item:last-child {
    border-bottom: none;
}

.download-name {
    font-weight: 500;
    color: white;
}

.download-time {
    font-size: 0.9rem;
    color: rgba(255, 255, 255, 0.7);
}

.download-code {
    font-size: 0.9rem;
    color: #fdbb2d;
    font-weight: 500;
}

.progress-container {
    width: 100%;
    background: rgba(255, 255, 255, 0.1);
    border-radius: 10px;
    margin: 15px 0;
    display: none;
}

.progress-bar {
    height: 20px;
    border-radius: 10px;
    background: linear-gradient(90deg, #4b6cb7, #182848);
    width: 0%;
    transition: width 0.3s;
    text-align: center;
    color: white;
    font-size: 12px;
    line-height: 20px;
}
</style>

<script>
// 配置信息
const config = {
    maxUploadSize: <?= $config['max_upload_size'] ?>,
    allowedTypes: <?= json_encode($config['allowed_types'] ?? []) ?>
};

// 标签页切换
document.querySelectorAll('.tab').forEach(tab => {
    tab.addEventListener('click', () => {
        // 移除所有活动状态
        document.querySelectorAll('.tab').forEach(t => t.classList.remove('active'));
        document.querySelectorAll('.tab-content').forEach(c => c.classList.remove('active'));
        
        // 添加活动状态
        tab.classList.add('active');
        document.getElementById(tab.dataset.tab + '-tab').classList.add('active');
    });
});

// 文件上传处理
const uploadArea = document.getElementById('upload-area');
const fileInput = document.getElementById('file-input');
const fileList = document.getElementById('file-list');
const fileListContainer = document.getElementById('file-list-container');
const uploadForm = document.getElementById('upload-form');
const uploadBtn = document.getElementById('upload-btn');
const progressContainer = document.getElementById('progress-container');
const progressBar = document.getElementById('progress-bar');

let selectedFiles = [];

// 点击上传区域
uploadArea.addEventListener('click', () => {
    fileInput.click();
});

// 文件选择
fileInput.addEventListener('change', (e) => {
    handleFiles(e.target.files);
});

// 拖拽上传
uploadArea.addEventListener('dragover', (e) => {
    e.preventDefault();
    uploadArea.classList.add('dragover');
});

uploadArea.addEventListener('dragleave', () => {
    uploadArea.classList.remove('dragover');
});

uploadArea.addEventListener('drop', (e) => {
    e.preventDefault();
    uploadArea.classList.remove('dragover');
    handleFiles(e.dataTransfer.files);
});

function handleFiles(files) {
    for (let file of files) {
        // 验证文件大小
        if (file.size > config.maxUploadSize * 1024 * 1024) {
            showNotification(`文件 ${file.name} 超过大小限制 (${config.maxUploadSize}MB)`, 'error');
            continue;
        }
        
        // 验证文件类型
        const extension = file.name.split('.').pop().toLowerCase();
        
        // 对压缩文件类型特殊处理
        if (extension === 'zip') {
            fileMimeType = 'application/zip';
            isAllowed = config.allowedTypes.includes('application/zip');
        } else if (extension === 'rar') {
            fileMimeType = 'application/x-rar-compressed';
            isAllowed = config.allowedTypes.includes('application/x-rar-compressed');
        } else if (extension === '7z') {
            fileMimeType = 'application/x-7z-compressed';
            isAllowed = config.allowedTypes.includes('application/x-7z-compressed');
        } else if (extension === 'tar') {
            fileMimeType = 'application/x-tar';
            isAllowed = config.allowedTypes.includes('application/x-tar');
        } else {
            // 其他文件类型使用浏览器检测的MIME类型
            fileMimeType = file.type;
            
            // 如果浏览器无法识别MIME类型或返回通用二进制类型，基于扩展名推断
            if (!fileMimeType || fileMimeType === 'application/octet-stream' || fileMimeType === 'application/x-download') {
                const extensionToMime = {
                    'c': 'text/x-c',
                    'cpp': 'text/x-c++',
                    'h': 'text/x-c',
                    'hpp': 'text/x-c++',
                    'py': 'text/x-python',
                    'java': 'text/x-java',
                    'js': 'text/x-javascript',
                    'html': 'text/x-html',
                    'css': 'text/x-css',
                    'sql': 'text/x-sql',
                    'sh': 'text/x-shellscript',
                    'rb': 'text/x-ruby',
                    'go': 'text/x-go',
                    'rs': 'text/x-rust',
                    'swift': 'text/x-swift',
                    'kt': 'text/x-kotlin',
                    'ts': 'text/x-typescript',
                    'md': 'text/x-markdown',
                    'yaml': 'text/x-yaml',
                    'yml': 'text/x-yaml',
                    'json': 'text/x-json',
                    'xml': 'text/x-xml',
                    'php': 'text/x-php',
                    'txt': 'text/plain'
                };
                fileMimeType = extensionToMime[extension] || 'text/plain';
            }
            isAllowed = config.allowedTypes.includes(fileMimeType);
        }
        
        // 记录调试信息到控制台
        console.log('文件验证信息:', {
            fileName: file.name,
            extension: extension,
            originalType: file.type,
            detectedType: fileMimeType,
            isAllowed: isAllowed
        });
        
        if (!isAllowed) {
            showNotification(`文件 ${file.name} 的类型不被支持`, 'error');
            continue;
        }
        
        selectedFiles.push(file);
    }
    updateFileList();
}

function updateFileList() {
    if (selectedFiles.length > 0) {
        fileListContainer.style.display = 'block';
        fileList.innerHTML = '';
        
        selectedFiles.forEach((file, index) => {
            const fileItem = document.createElement('div');
            fileItem.className = 'file-item';
            fileItem.innerHTML = `
                <div class="file-info">
                    <div class="file-icon">
                        <i class="fas fa-file"></i>
                    </div>
                    <div>
                        <div class="file-name">${file.name}</div>
                        <div class="file-size">${formatFileSize(file.size)}</div>
                    </div>
                </div>
                <button type="button" class="file-remove" onclick="removeFile(${index})">
                    <i class="fas fa-times"></i>
                </button>
            `;
            fileList.appendChild(fileItem);
        });
    } else {
        fileListContainer.style.display = 'none';
    }
}

function removeFile(index) {
    selectedFiles.splice(index, 1);
    updateFileList();
}

// 表单提交
uploadForm.addEventListener('submit', (e) => {
    e.preventDefault();
    
    if (selectedFiles.length === 0) {
        showNotification('请选择要上传的文件', 'error');
        return;
    }
    
    const formData = new FormData();
    formData.append('csrf_token', document.querySelector('input[name="csrf_token"]').value);
    formData.append('expire_days', document.getElementById('expire-days').value);
    
    // 添加文件
    selectedFiles.forEach(file => {
        formData.append('file[]', file);
    });
    
    // 显示进度条
    progressContainer.style.display = 'block';
    uploadBtn.disabled = true;
    uploadBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> 上传中...';
    
    const xhr = new XMLHttpRequest();
    
    xhr.upload.addEventListener('progress', (e) => {
        if (e.lengthComputable) {
            const percentComplete = (e.loaded / e.total) * 100;
            progressBar.style.width = percentComplete + '%';
            progressBar.textContent = Math.round(percentComplete) + '%';
        }
    });
    
    xhr.addEventListener('load', () => {
        try {
            const response = JSON.parse(xhr.responseText);
            if (response.success) {
                showNotification('文件上传成功！取件码：' + response.pickup_code);
                // 重置表单
                selectedFiles = [];
                updateFileList();
                fileInput.value = '';
                progressContainer.style.display = 'none';
                progressBar.style.width = '0%';
                progressBar.textContent = '0%';
            } else {
                showNotification(response.message || '上传失败', 'error');
            }
        } catch (e) {
            showNotification('上传失败', 'error');
        }
        
        uploadBtn.disabled = false;
        uploadBtn.innerHTML = '<i class="fas fa-upload"></i> 开始上传';
    });
    
    xhr.addEventListener('error', () => {
        showNotification('上传失败', 'error');
        uploadBtn.disabled = false;
        uploadBtn.innerHTML = '<i class="fas fa-upload"></i> 开始上传';
    });
    
    xhr.open('POST', '?action=upload', true);
    xhr.send(formData);
});

// 通知函数
function showNotification(message, type = 'info') {
    const notification = document.createElement('div');
    notification.className = `notification ${type}`;
    notification.textContent = message;
    
    // 插入到页面顶部
    const container = document.querySelector('.content') || document.body;
    container.insertBefore(notification, container.firstChild);
    
    // 3秒后自动移除
    setTimeout(() => {
        if (notification.parentNode) {
            notification.parentNode.removeChild(notification);
        }
    }, 3000);
}

// 文件大小格式化
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
