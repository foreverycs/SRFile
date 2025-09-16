<?php
/**
 * 文件预览类
 * 提供安全的文件预览功能
 */
class FilePreview {
    /**
     * 检查文件是否可以预览
     */
    public static function canPreview($fileInfo) {
        $previewableTypes = [
            'image/jpeg', 'image/png', 'image/gif', 'image/webp',
            'text/plain', 'text/html', 'text/css', 'text/javascript',
            'application/pdf', 'application/json',
            'video/mp4', 'video/webm', 'audio/mpeg', 'audio/wav'
        ];
        
        return in_array($fileInfo->type, $previewableTypes);
    }
    
    /**
     * 生成文件预览
     */
    public static function generatePreview($fileInfo) {
        if (!self::canPreview($fileInfo)) {
            return null;
        }
        
        $mimeType = $fileInfo->type;
        
        if (strpos($mimeType, 'image/') === 0) {
            return self::previewImage($fileInfo);
        } elseif (strpos($mimeType, 'text/') === 0) {
            return self::previewText($fileInfo);
        } elseif ($mimeType === 'application/pdf') {
            return self::previewPdf($fileInfo);
        } elseif (strpos($mimeType, 'video/') === 0) {
            return self::previewVideo($fileInfo);
        } elseif (strpos($mimeType, 'audio/') === 0) {
            return self::previewAudio($fileInfo);
        } elseif ($mimeType === 'application/json') {
            return self::previewJson($fileInfo);
        }
        
        return null;
    }
    
    /**
     * 图片预览
     */
    private static function previewImage($fileInfo) {
        if (!file_exists($fileInfo->path)) {
            return null;
        }
        
        // 生成缩略图
        $thumbnailPath = self::generateThumbnail($fileInfo->path);
        
        return [
            'type' => 'image',
            'thumbnail' => $thumbnailPath,
            'original' => $fileInfo->path,
            'size' => self::getImageSize($fileInfo->path)
        ];
    }
    
    /**
     * 文本预览
     */
    private static function previewText($fileInfo) {
        if (!file_exists($fileInfo->path)) {
            return null;
        }
        
        $content = file_get_contents($fileInfo->path);
        $content = substr($content, 0, 5000); // 限制预览长度
        
        // 检查是否包含敏感内容
        if (self::containsSensitiveContent($content)) {
            return [
                'type' => 'text',
                'content' => '文件内容可能包含敏感信息，无法预览',
                'lines' => 0,
                'size' => 0
            ];
        }
        
        return [
            'type' => 'text',
            'content' => $content,
            'lines' => substr_count($content, "\n") + 1,
            'size' => strlen($content)
        ];
    }
    
    /**
     * PDF预览
     */
    private static function previewPdf($fileInfo) {
        if (!file_exists($fileInfo->path)) {
            return null;
        }
        
        return [
            'type' => 'pdf',
            'path' => $fileInfo->path,
            'size' => filesize($fileInfo->path)
        ];
    }
    
    /**
     * 视频预览
     */
    private static function previewVideo($fileInfo) {
        if (!file_exists($fileInfo->path)) {
            return null;
        }
        
        return [
            'type' => 'video',
            'path' => $fileInfo->path,
            'size' => filesize($fileInfo->path),
            'duration' => self::getVideoDuration($fileInfo->path)
        ];
    }
    
    /**
     * 音频预览
     */
    private static function previewAudio($fileInfo) {
        if (!file_exists($fileInfo->path)) {
            return null;
        }
        
        return [
            'type' => 'audio',
            'path' => $fileInfo->path,
            'size' => filesize($fileInfo->path),
            'duration' => self::getAudioDuration($fileInfo->path)
        ];
    }
    
    /**
     * JSON预览
     */
    private static function previewJson($fileInfo) {
        if (!file_exists($fileInfo->path)) {
            return null;
        }
        
        $content = file_get_contents($fileInfo->path);
        $data = json_decode($content, true);
        
        if ($data === null) {
            return [
                'type' => 'json',
                'content' => '无效的JSON格式',
                'error' => true
            ];
        }
        
        return [
            'type' => 'json',
            'content' => $data,
            'size' => strlen($content)
        ];
    }
    
    /**
     * 生成缩略图
     */
    private static function generateThumbnail($imagePath) {
        $thumbnailPath = UPLOAD_DIR . 'thumbnails/' . basename($imagePath);
        
        if (file_exists($thumbnailPath)) {
            return $thumbnailPath;
        }
        
        // 确保缩略图目录存在
        if (!file_exists(UPLOAD_DIR . 'thumbnails/')) {
            mkdir(UPLOAD_DIR . 'thumbnails/', 0777, true);
        }
        
        try {
            $imageInfo = getimagesize($imagePath);
            if ($imageInfo === false) {
                return null;
            }
            
            $width = $imageInfo[0];
            $height = $imageInfo[1];
            $type = $imageInfo[2];
            
            // 计算缩略图尺寸
            $maxSize = 300;
            if ($width > $height) {
                $newWidth = $maxSize;
                $newHeight = (int)($height * $maxSize / $width);
            } else {
                $newHeight = $maxSize;
                $newWidth = (int)($width * $maxSize / $height);
            }
            
            // 创建缩略图
            $thumbnail = imagecreatetruecolor($newWidth, $newHeight);
            
            // 根据图片类型创建源图像
            switch ($type) {
                case IMAGETYPE_JPEG:
                    $source = imagecreatefromjpeg($imagePath);
                    break;
                case IMAGETYPE_PNG:
                    $source = imagecreatefrompng($imagePath);
                    break;
                case IMAGETYPE_GIF:
                    $source = imagecreatefromgif($imagePath);
                    break;
                default:
                    return null;
            }
            
            if ($source === false) {
                return null;
            }
            
            // 调整图片大小
            imagecopyresampled($thumbnail, $source, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);
            
            // 保存缩略图
            imagejpeg($thumbnail, $thumbnailPath, 85);
            
            // 释放内存
            imagedestroy($thumbnail);
            imagedestroy($source);
            
            return $thumbnailPath;
        } catch (Exception $e) {
            ErrorHandler::logError('Thumbnail generation failed: ' . $e->getMessage());
            return null;
        }
    }
    
    /**
     * 获取图片尺寸
     */
    private static function getImageSize($imagePath) {
        $imageInfo = getimagesize($imagePath);
        if ($imageInfo === false) {
            return null;
        }
        
        return [
            'width' => $imageInfo[0],
            'height' => $imageInfo[1],
            'type' => image_type_to_extension($imageInfo[2], false)
        ];
    }
    
    /**
     * 获取视频时长
     */
    private static function getVideoDuration($videoPath) {
        // 简化的视频时长获取，实际项目中可以使用FFmpeg
        return null;
    }
    
    /**
     * 获取音频时长
     */
    private static function getAudioDuration($audioPath) {
        // 简化的音频时长获取，实际项目中可以使用音频处理库
        return null;
    }
    
    /**
     * 检查是否包含敏感内容
     */
    private static function containsSensitiveContent($content) {
        $sensitivePatterns = [
            '/password/i',
            '/secret/i',
            '/token/i',
            '/key/i',
            '/private/i',
            '/confidential/i'
        ];
        
        foreach ($sensitivePatterns as $pattern) {
            if (preg_match($pattern, $content)) {
                return true;
            }
        }
        
        return false;
    }
    
    /**
     * 清理预览缓存
     */
    public static function cleanupPreviewCache($days = 7) {
        $thumbnailDir = UPLOAD_DIR . 'thumbnails/';
        
        if (!file_exists($thumbnailDir)) {
            return;
        }
        
        $cutoffTime = time() - ($days * 24 * 3600);
        
        foreach (glob($thumbnailDir . '*') as $file) {
            if (filemtime($file) < $cutoffTime) {
                unlink($file);
            }
        }
    }
    
    /**
     * 获取预览模板
     */
    public static function getPreviewTemplate($preview) {
        if (!$preview) {
            return '';
        }
        
        switch ($preview['type']) {
            case 'image':
                return self::getImagePreviewTemplate($preview);
            case 'text':
                return self::getTextPreviewTemplate($preview);
            case 'pdf':
                return self::getPdfPreviewTemplate($preview);
            case 'video':
                return self::getVideoPreviewTemplate($preview);
            case 'audio':
                return self::getAudioPreviewTemplate($preview);
            case 'json':
                return self::getJsonPreviewTemplate($preview);
            default:
                return '';
        }
    }
    
    /**
     * 图片预览模板
     */
    private static function getImagePreviewTemplate($preview) {
        $thumbnailUrl = str_replace(UPLOAD_DIR, 'uploads/', $preview['thumbnail']);
        $originalUrl = str_replace(UPLOAD_DIR, 'uploads/', $preview['original']);
        
        return '
        <div class="preview-container">
            <div class="preview-image">
                <img src="' . htmlspecialchars($thumbnailUrl) . '" alt="图片预览" onclick="openImageModal(\'' . htmlspecialchars($originalUrl) . '\')">
                <div class="preview-info">
                    <span class="preview-size">' . ($preview['size']['width'] ?? '?') . ' × ' . ($preview['size']['height'] ?? '?') . '</span>
                    <span class="preview-type">' . ($preview['size']['type'] ?? 'image') . '</span>
                </div>
            </div>
        </div>';
    }
    
    /**
     * 文本预览模板
     */
    private static function getTextPreviewTemplate($preview) {
        $content = nl2br(htmlspecialchars($preview['content']));
        
        return '
        <div class="preview-container">
            <div class="preview-text">
                <div class="preview-content">' . $content . '</div>
                <div class="preview-info">
                    <span class="preview-lines">' . $preview['lines'] . ' 行</span>
                    <span class="preview-size">' . formatSize($preview['size']) . '</span>
                </div>
            </div>
        </div>';
    }
    
    /**
     * PDF预览模板
     */
    private static function getPdfPreviewTemplate($preview) {
        $pdfUrl = str_replace(UPLOAD_DIR, 'uploads/', $preview['path']);
        
        return '
        <div class="preview-container">
            <div class="preview-pdf">
                <iframe src="' . htmlspecialchars($pdfUrl) . '" width="100%" height="500px"></iframe>
                <div class="preview-info">
                    <span class="preview-size">' . formatSize($preview['size']) . '</span>
                </div>
            </div>
        </div>';
    }
    
    /**
     * 视频预览模板
     */
    private static function getVideoPreviewTemplate($preview) {
        $videoUrl = str_replace(UPLOAD_DIR, 'uploads/', $preview['path']);
        
        return '
        <div class="preview-container">
            <div class="preview-video">
                <video controls width="100%" height="400px">
                    <source src="' . htmlspecialchars($videoUrl) . '" type="video/mp4">
                    您的浏览器不支持视频播放
                </video>
                <div class="preview-info">
                    <span class="preview-size">' . formatSize($preview['size']) . '</span>
                </div>
            </div>
        </div>';
    }
    
    /**
     * 音频预览模板
     */
    private static function getAudioPreviewTemplate($preview) {
        $audioUrl = str_replace(UPLOAD_DIR, 'uploads/', $preview['path']);
        
        return '
        <div class="preview-container">
            <div class="preview-audio">
                <audio controls width="100%">
                    <source src="' . htmlspecialchars($audioUrl) . '" type="audio/mpeg">
                    您的浏览器不支持音频播放
                </audio>
                <div class="preview-info">
                    <span class="preview-size">' . formatSize($preview['size']) . '</span>
                </div>
            </div>
        </div>';
    }
    
    /**
     * JSON预览模板
     */
    private static function getJsonPreviewTemplate($preview) {
        if ($preview['error']) {
            return '<div class="preview-error">' . htmlspecialchars($preview['content']) . '</div>';
        }
        
        $jsonContent = json_encode($preview['content'], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        $content = htmlspecialchars($jsonContent);
        
        return '
        <div class="preview-container">
            <div class="preview-json">
                <pre class="json-content">' . $content . '</pre>
                <div class="preview-info">
                    <span class="preview-size">' . formatSize($preview['size']) . '</span>
                </div>
            </div>
        </div>';
    }
}
?>