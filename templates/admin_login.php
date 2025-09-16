<div class="login-container">
    <div class="login-form">
        <h2><i class="fas fa-lock"></i> 管理员登录</h2>
        
        <?php if (!empty($error)): ?>
            <div class="notification error">
                <?= htmlspecialchars($error) ?>
            </div>
        <?php endif; ?>
        
        <form method="post" action="">
            <div class="form-group">
                <label for="username">用户名:</label>
                <input type="text" id="username" name="username" required>
            </div>
            
            <div class="form-group">
                <label for="password">密码:</label>
                <input type="password" id="password" name="password" required>
            </div>
            
            <button type="submit" class="btn">
                <i class="fas fa-sign-in-alt"></i> 登录
            </button>
        </form>
    </div>
</div>

<style>
.login-container {
    display: flex;
    justify-content: center;
    align-items: center;
    min-height: 60vh;
}

.login-form {
    background: white;
    padding: 40px;
    border-radius: 12px;
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
    width: 100%;
    max-width: 400px;
}

.login-form h2 {
    text-align: center;
    margin-bottom: 30px;
    color: #333;
}

.login-form .btn {
    width: 100%;
    margin-top: 20px;
}
</style>