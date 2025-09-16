<div class="admin-config">
    <form method="post" action="?action=save_config">
        <div class="config-section">
            <h3>基本设置</h3>
            
            <div class="form-group">
                <label for="site_name">网站名称:</label>
                <input type="text" id="site_name" name="site_name" 
                       value="<?= htmlspecialchars($config['site_name']) ?>" required>
            </div>
            
            <div class="form-group">
                <label for="max_upload_size">最大上传大小 (MB):</label>
                <input type="number" id="max_upload_size" name="max_upload_size" 
                       value="<?= htmlspecialchars($config['max_upload_size']) ?>" min="1" max="500" required>
            </div>
            
            <div class="form-group">
                <label class="checkbox-label">
                    <input type="checkbox" name="moderation_enabled" 
                           <?= isset($config['moderation_enabled']) && $config['moderation_enabled'] ? 'checked' : '' ?>>
                    启用文件审核
                </label>
            </div>
        </div>
        
        <div class="config-section">
            <h3>允许的文件类型</h3>
            
            <div class="file-types-container">
                <div class="file-types-grid">
                    <label class="file-type-item">
                        <input type="checkbox" name="allowed_types[]" value="image/jpeg" 
                               <?= in_array('image/jpeg', $config['allowed_types']) ? 'checked' : '' ?>>
                        <span>JPEG 图片</span>
                    </label>
                    
                    <label class="file-type-item">
                        <input type="checkbox" name="allowed_types[]" value="image/png" 
                               <?= in_array('image/png', $config['allowed_types']) ? 'checked' : '' ?>>
                        <span>PNG 图片</span>
                    </label>
                    
                    <label class="file-type-item">
                        <input type="checkbox" name="allowed_types[]" value="image/gif" 
                               <?= in_array('image/gif', $config['allowed_types']) ? 'checked' : '' ?>>
                        <span>GIF 图片</span>
                    </label>
                    
                    <label class="file-type-item">
                        <input type="checkbox" name="allowed_types[]" value="image/webp" 
                               <?= in_array('image/webp', $config['allowed_types']) ? 'checked' : '' ?>>
                        <span>WebP 图片</span>
                    </label>
                    
                    <label class="file-type-item">
                        <input type="checkbox" name="allowed_types[]" value="video/mp4" 
                               <?= in_array('video/mp4', $config['allowed_types']) ? 'checked' : '' ?>>
                        <span>MP4 视频</span>
                    </label>
                    
                    <label class="file-type-item">
                        <input type="checkbox" name="allowed_types[]" value="video/quicktime" 
                               <?= in_array('video/quicktime', $config['allowed_types']) ? 'checked' : '' ?>>
                        <span>MOV 视频</span>
                    </label>
                    
                    <label class="file-type-item">
                        <input type="checkbox" name="allowed_types[]" value="video/x-msvideo" 
                               <?= in_array('video/x-msvideo', $config['allowed_types']) ? 'checked' : '' ?>>
                        <span>AVI 视频</span>
                    </label>
                    
                    <label class="file-type-item">
                        <input type="checkbox" name="allowed_types[]" value="video/x-matroska" 
                               <?= in_array('video/x-matroska', $config['allowed_types']) ? 'checked' : '' ?>>
                        <span>MKV 视频</span>
                    </label>
                    
                    <label class="file-type-item">
                        <input type="checkbox" name="allowed_types[]" value="audio/mpeg" 
                               <?= in_array('audio/mpeg', $config['allowed_types']) ? 'checked' : '' ?>>
                        <span>MP3 音频</span>
                    </label>
                    
                    <label class="file-type-item">
                        <input type="checkbox" name="allowed_types[]" value="audio/wav" 
                               <?= in_array('audio/wav', $config['allowed_types']) ? 'checked' : '' ?>>
                        <span>WAV 音频</span>
                    </label>
                    
                    <label class="file-type-item">
                        <input type="checkbox" name="allowed_types[]" value="audio/ogg" 
                               <?= in_array('audio/ogg', $config['allowed_types']) ? 'checked' : '' ?>>
                        <span>OGG 音频</span>
                    </label>
                    
                    <label class="file-type-item">
                        <input type="checkbox" name="allowed_types[]" value="text/plain" 
                               <?= in_array('text/plain', $config['allowed_types']) ? 'checked' : '' ?>>
                        <span>文本文件</span>
                    </label>
                    
                    <label class="file-type-item">
                        <input type="checkbox" name="allowed_types[]" value="application/pdf" 
                               <?= in_array('application/pdf', $config['allowed_types']) ? 'checked' : '' ?>>
                        <span>PDF 文档</span>
                    </label>
                    
                    <label class="file-type-item">
                        <input type="checkbox" name="allowed_types[]" value="application/msword" 
                               <?= in_array('application/msword', $config['allowed_types']) ? 'checked' : '' ?>>
                        <span>Word 文档</span>
                    </label>
                    
                    <label class="file-type-item">
                        <input type="checkbox" name="allowed_types[]" value="application/vnd.openxmlformats-officedocument.wordprocessingml.document" 
                               <?= in_array('application/vnd.openxmlformats-officedocument.wordprocessingml.document', $config['allowed_types']) ? 'checked' : '' ?>>
                        <span>Word 文档 (.docx)</span>
                    </label>
                    
                    <label class="file-type-item">
                        <input type="checkbox" name="allowed_types[]" value="application/vnd.ms-excel" 
                               <?= in_array('application/vnd.ms-excel', $config['allowed_types']) ? 'checked' : '' ?>>
                        <span>Excel 表格</span>
                    </label>
                    
                    <label class="file-type-item">
                        <input type="checkbox" name="allowed_types[]" value="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet" 
                               <?= in_array('application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', $config['allowed_types']) ? 'checked' : '' ?>>
                        <span>Excel 表格 (.xlsx)</span>
                    </label>
                    
                    <label class="file-type-item">
                        <input type="checkbox" name="allowed_types[]" value="application/vnd.ms-powerpoint" 
                               <?= in_array('application/vnd.ms-powerpoint', $config['allowed_types']) ? 'checked' : '' ?>>
                        <span>PowerPoint 演示文稿</span>
                    </label>
                    
                    <label class="file-type-item">
                        <input type="checkbox" name="allowed_types[]" value="application/vnd.openxmlformats-officedocument.presentationml.presentation" 
                               <?= in_array('application/vnd.openxmlformats-officedocument.presentationml.presentation', $config['allowed_types']) ? 'checked' : '' ?>>
                        <span>PowerPoint 演示文稿 (.pptx)</span>
                    </label>
                    
                    <label class="file-type-item">
                        <input type="checkbox" name="allowed_types[]" value="application/zip" 
                               <?= in_array('application/zip', $config['allowed_types']) ? 'checked' : '' ?>>
                        <span>ZIP 压缩包</span>
                    </label>
                    
                    <label class="file-type-item">
                        <input type="checkbox" name="allowed_types[]" value="application/x-rar-compressed" 
                               <?= in_array('application/x-rar-compressed', $config['allowed_types']) ? 'checked' : '' ?>>
                        <span>RAR 压缩包</span>
                    </label>
                    
                    <label class="file-type-item">
                        <input type="checkbox" name="allowed_types[]" value="application/x-7z-compressed" 
                               <?= in_array('application/x-7z-compressed', $config['allowed_types']) ? 'checked' : '' ?>>
                        <span>7Z 压缩包</span>
                    </label>
                    
                    <label class="file-type-item">
                        <input type="checkbox" name="allowed_types[]" value="application/x-tar" 
                               <?= in_array('application/x-tar', $config['allowed_types']) ? 'checked' : '' ?>>
                        <span>TAR 压缩包</span>
                    </label>
                    
                    <label class="file-type-item">
                        <input type="checkbox" name="allowed_types[]" value="text/x-php" 
                               <?= in_array('text/x-php', $config['allowed_types']) ? 'checked' : '' ?>>
                        <span>PHP 代码</span>
                    </label>
                    
                    <label class="file-type-item">
                        <input type="checkbox" name="allowed_types[]" value="text/x-c" 
                               <?= in_array('text/x-c', $config['allowed_types']) ? 'checked' : '' ?>>
                        <span>C 代码</span>
                    </label>
                    
                    <label class="file-type-item">
                        <input type="checkbox" name="allowed_types[]" value="text/x-c++" 
                               <?= in_array('text/x-c++', $config['allowed_types']) ? 'checked' : '' ?>>
                        <span>C++ 代码</span>
                    </label>
                    
                    <label class="file-type-item">
                        <input type="checkbox" name="allowed_types[]" value="text/x-python" 
                               <?= in_array('text/x-python', $config['allowed_types']) ? 'checked' : '' ?>>
                        <span>Python 代码</span>
                    </label>
                    
                    <label class="file-type-item">
                        <input type="checkbox" name="allowed_types[]" value="text/x-java" 
                               <?= in_array('text/x-java', $config['allowed_types']) ? 'checked' : '' ?>>
                        <span>Java 代码</span>
                    </label>
                    
                    <label class="file-type-item">
                        <input type="checkbox" name="allowed_types[]" value="text/x-javascript" 
                               <?= in_array('text/x-javascript', $config['allowed_types']) ? 'checked' : '' ?>>
                        <span>JavaScript 代码</span>
                    </label>
                    
                    <label class="file-type-item">
                        <input type="checkbox" name="allowed_types[]" value="text/x-html" 
                               <?= in_array('text/x-html', $config['allowed_types']) ? 'checked' : '' ?>>
                        <span>HTML 代码</span>
                    </label>
                    
                    <label class="file-type-item">
                        <input type="checkbox" name="allowed_types[]" value="text/x-css" 
                               <?= in_array('text/x-css', $config['allowed_types']) ? 'checked' : '' ?>>
                        <span>CSS 代码</span>
                    </label>
                    
                    <label class="file-type-item">
                        <input type="checkbox" name="allowed_types[]" value="text/x-sql" 
                               <?= in_array('text/x-sql', $config['allowed_types']) ? 'checked' : '' ?>>
                        <span>SQL 代码</span>
                    </label>
                    
                    <label class="file-type-item">
                        <input type="checkbox" name="allowed_types[]" value="text/x-shellscript" 
                               <?= in_array('text/x-shellscript', $config['allowed_types']) ? 'checked' : '' ?>>
                        <span>Shell 脚本</span>
                    </label>
                    
                    <label class="file-type-item">
                        <input type="checkbox" name="allowed_types[]" value="text/x-perl" 
                               <?= in_array('text/x-perl', $config['allowed_types']) ? 'checked' : '' ?>>
                        <span>Perl 脚本</span>
                    </label>
                    
                    <label class="file-type-item">
                        <input type="checkbox" name="allowed_types[]" value="text/x-ruby" 
                               <?= in_array('text/x-ruby', $config['allowed_types']) ? 'checked' : '' ?>>
                        <span>Ruby 代码</span>
                    </label>
                    
                    <label class="file-type-item">
                        <input type="checkbox" name="allowed_types[]" value="text/x-go" 
                               <?= in_array('text/x-go', $config['allowed_types']) ? 'checked' : '' ?>>
                        <span>Go 代码</span>
                    </label>
                    
                    <label class="file-type-item">
                        <input type="checkbox" name="allowed_types[]" value="text/x-rust" 
                               <?= in_array('text/x-rust', $config['allowed_types']) ? 'checked' : '' ?>>
                        <span>Rust 代码</span>
                    </label>
                    
                    <label class="file-type-item">
                        <input type="checkbox" name="allowed_types[]" value="text/x-swift" 
                               <?= in_array('text/x-swift', $config['allowed_types']) ? 'checked' : '' ?>>
                        <span>Swift 代码</span>
                    </label>
                    
                    <label class="file-type-item">
                        <input type="checkbox" name="allowed_types[]" value="text/x-kotlin" 
                               <?= in_array('text/x-kotlin', $config['allowed_types']) ? 'checked' : '' ?>>
                        <span>Kotlin 代码</span>
                    </label>
                    
                    <label class="file-type-item">
                        <input type="checkbox" name="allowed_types[]" value="text/x-typescript" 
                               <?= in_array('text/x-typescript', $config['allowed_types']) ? 'checked' : '' ?>>
                        <span>TypeScript 代码</span>
                    </label>
                    
                    <label class="file-type-item">
                        <input type="checkbox" name="allowed_types[]" value="text/x-markdown" 
                               <?= in_array('text/x-markdown', $config['allowed_types']) ? 'checked' : '' ?>>
                        <span>Markdown 文档</span>
                    </label>
                    
                    <label class="file-type-item">
                        <input type="checkbox" name="allowed_types[]" value="text/x-yaml" 
                               <?= in_array('text/x-yaml', $config['allowed_types']) ? 'checked' : '' ?>>
                        <span>YAML 配置</span>
                    </label>
                    
                    <label class="file-type-item">
                        <input type="checkbox" name="allowed_types[]" value="text/x-json" 
                               <?= in_array('text/x-json', $config['allowed_types']) ? 'checked' : '' ?>>
                        <span>JSON 配置</span>
                    </label>
                    
                    <label class="file-type-item">
                        <input type="checkbox" name="allowed_types[]" value="text/x-xml" 
                               <?= in_array('text/x-xml', $config['allowed_types']) ? 'checked' : '' ?>>
                        <span>XML 配置</span>
                    </label>
                </div>
                
                <div class="file-types-actions">
                    <button type="button" class="btn btn-sm" onclick="selectAllTypes()">
                        <i class="fas fa-check-square"></i> 全选
                    </button>
                    <button type="button" class="btn btn-sm" onclick="deselectAllTypes()">
                        <i class="fas fa-square"></i> 全不选
                    </button>
                    <button type="button" class="btn btn-sm" onclick="selectCommonTypes()">
                        <i class="fas fa-star"></i> 常用类型
                    </button>
                </div>
            </div>
        </div>
        
        <div class="config-section">
            <h3>存储设置</h3>
            
            <div class="storage-info">
                <div class="storage-item">
                    <div class="storage-label">上传目录:</div>
                    <div class="storage-value"><?= UPLOAD_DIR ?></div>
                </div>
                <div class="storage-item">
                    <div class="storage-label">数据目录:</div>
                    <div class="storage-value"><?= DATA_DIR ?></div>
                </div>
                <div class="storage-item">
                    <div class="storage-label">配置文件:</div>
                    <div class="storage-value"><?= CONFIG_FILE ?></div>
                </div>
            </div>
        </div>
        
        <div class="form-actions">
            <button type="submit" class="btn btn-success">
                <i class="fas fa-save"></i> 保存配置
            </button>
            <button type="reset" class="btn">
                <i class="fas fa-undo"></i> 重置
            </button>
        </div>
    </form>
</div>

<script>
function selectAllTypes() {
    const checkboxes = document.querySelectorAll('input[name="allowed_types[]"]');
    checkboxes.forEach(checkbox => checkbox.checked = true);
}

function deselectAllTypes() {
    const checkboxes = document.querySelectorAll('input[name="allowed_types[]"]');
    checkboxes.forEach(checkbox => checkbox.checked = false);
}

function selectCommonTypes() {
    const checkboxes = document.querySelectorAll('input[name="allowed_types[]"]');
    const commonTypes = [
        'image/jpeg', 'image/png', 'image/gif',
        'video/mp4', 'audio/mpeg', 'text/plain',
        'application/pdf', 'application/msword',
        'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        'application/vnd.ms-excel',
        'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        'application/zip',
        'text/x-php', 'text/x-python', 'text/x-javascript',
        'text/x-html', 'text/x-css', 'text/x-sql',
        'text/x-markdown', 'text/x-json', 'text/x-yaml'
    ];
    
    checkboxes.forEach(checkbox => {
        checkbox.checked = commonTypes.includes(checkbox.value);
    });
}
</script>

<style>
.config-section {
    margin-bottom: 30px;
    padding: 20px;
    background: #f8f9fa;
    border-radius: 8px;
}

.config-section h3 {
    margin-bottom: 20px;
    color: #333;
}

.checkbox-label {
    display: flex;
    align-items: center;
    gap: 8px;
    cursor: pointer;
    font-weight: 500;
    min-height: 38px;
    line-height: 38px;
    padding: 8px 0;
}

.checkbox-label input[type="checkbox"] {
    margin: 0;
    vertical-align: middle;
    width: 18px;
    height: 18px;
    accent-color: #4b6cb7;
}

.file-types-container {
    display: flex;
    flex-direction: column;
    gap: 15px;
}

.file-types-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 10px;
}

.file-types-actions {
    display: flex;
    gap: 10px;
    justify-content: flex-start;
    flex-wrap: wrap;
}

.file-type-item {
    display: flex;
    align-items: center;
    gap: 8px;
    padding: 10px;
    background: white;
    border-radius: 6px;
    cursor: pointer;
    transition: all 0.3s ease;
}

.file-type-item:hover {
    background: #e9ecef;
}

.file-type-item input[type="checkbox"] {
    margin: 0;
}

.storage-info {
    display: flex;
    flex-direction: column;
    gap: 10px;
}

.storage-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 10px;
    background: white;
    border-radius: 6px;
}

.storage-label {
    font-weight: 500;
    color: #666;
}

.storage-value {
    font-family: monospace;
    color: #333;
    background: #f8f9fa;
    padding: 4px 8px;
    border-radius: 4px;
}

.form-actions {
    display: flex;
    gap: 10px;
    justify-content: flex-end;
    margin-top: 30px;
}

@media (max-width: 768px) {
    .file-types-grid {
        grid-template-columns: 1fr;
    }
    
    .storage-item {
        flex-direction: column;
        align-items: flex-start;
        gap: 8px;
    }
    
    .form-actions {
        flex-direction: column;
    }
    
    .form-actions .btn {
        width: 100%;
    }
}
</style>