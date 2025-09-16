<div class="error-page">
    <div class="error-container">
        <div class="error-icon">
            <i class="fas fa-exclamation-triangle"></i>
        </div>
        <h2 class="error-title">错误 <?= $code ?></h2>
        <p class="error-message"><?= htmlspecialchars($message) ?></p>
        <a href="?action=home" class="btn">
            <i class="fas fa-home"></i> 返回首页
        </a>
    </div>
</div>

<style>
.error-page {
    display: flex;
    justify-content: center;
    align-items: center;
    min-height: 70vh;
    padding: 20px;
}

.error-container {
    text-align: center;
    padding: 40px;
    background: rgba(0, 0, 0, 0.3);
    border-radius: 15px;
    border: 1px solid rgba(255, 255, 255, 0.1);
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.3);
    max-width: 500px;
    width: 100%;
}

.error-icon {
    font-size: 4rem;
    color: #dc3545;
    margin-bottom: 20px;
}

.error-title {
    color: #dc3545;
    margin-bottom: 10px;
    font-size: 2rem;
    text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.5);
}

.error-message {
    font-size: 1.2rem;
    margin-bottom: 30px;
    color: rgba(255, 255, 255, 0.8);
}

.btn {
    display: inline-block;
    padding: 10px 20px;
    background: linear-gradient(90deg, #4b6cb7, #182848);
    color: white;
    text-decoration: none;
    border-radius: 8px;
    font-weight: 500;
    transition: all 0.3s ease;
    box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
}

.btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 20px rgba(0, 0, 0, 0.3);
}

.btn i {
    margin-right: 8px;
}
</style>