<div class="error-page">
    <div style="text-align: center; padding: 40px;">
        <i class="fas fa-exclamation-triangle" style="font-size: 4rem; color: #dc3545; margin-bottom: 20px;"></i>
        <h2 style="color: #dc3545; margin-bottom: 10px;">错误 <?= $code ?></h2>
        <p style="font-size: 1.2rem; margin-bottom: 30px;"><?= htmlspecialchars($message) ?></p>
        <a href="?action=home" class="btn">
            <i class="fas fa-home"></i> 返回首页
        </a>
    </div>
</div>