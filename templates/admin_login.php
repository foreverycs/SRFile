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
    background: rgba(0, 0, 0, 0.7);
    padding: 40px;
    border-radius: 15px;
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.5);
    width: 100%;
    max-width: 400px;
    backdrop-filter: blur(5px);
    border: 1px solid rgba(255, 255, 255, 0.1);
}

.login-form h2 {
    text-align: center;
    margin-bottom: 30px;
    color: white;
    text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.5);
}

.login-form .btn {
    width: 100%;
    margin-top: 20px;
    background: linear-gradient(90deg, #4b6cb7, #182848);
    box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
}

.login-form .btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 20px rgba(0, 0, 0, 0.3);
}

.login-form .form-group input {
    background: rgba(255, 255, 255, 0.1);
    border: 1px solid rgba(255, 255, 255, 0.3);
    color: white;
    transition: all 0.3s ease;
}

.login-form .form-group input:focus {
    outline: none;
    border-color: #fdbb2d;
    background: rgba(255, 255, 255, 0.15);
    box-shadow: 0 0 0 2px rgba(253, 187, 45, 0.2);
}

.login-form .form-group label {
    color: white;
}

.login-form .notification.error {
    background: rgba(220, 53, 69, 0.2);
    color: #f8d7da;
    border: 1px solid rgba(220, 53, 69, 0.5);
}
</style>