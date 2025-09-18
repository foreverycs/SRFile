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
    /* 移动端优化 */
    -webkit-tap-highlight-color: transparent;
    -webkit-touch-callout: none;
    -webkit-user-select: none;
    user-select: none;
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
    /* 移动端优化 */
    -webkit-tap-highlight-color: transparent;
    -webkit-touch-callout: none;
    -webkit-user-select: none;
    user-select: none;
    touch-action: manipulation;
    min-height: 44px;
    display: flex;
    align-items: center;
    justify-content: center;
}

.login-form .btn:hover, .login-form .btn:active {
    transform: translateY(-2px);
    box-shadow: 0 6px 20px rgba(0, 0, 0, 0.3);
}

.login-form .btn:active {
    transform: translateY(0);
}

.login-form .form-group input {
    background: rgba(255, 255, 255, 0.1);
    border: 1px solid rgba(255, 255, 255, 0.3);
    color: white;
    transition: all 0.3s ease;
    /* 移动端优化 */
    font-size: 16px; /* 防止iOS缩放 */
    padding: 12px;
}

.login-form .form-group input:focus {
    outline: none;
    border-color: #fdbb2d;
    background: rgba(255, 255, 255, 0.15);
    box-shadow: 0 0 0 2px rgba(253, 187, 45, 0.2);
}

.login-form .form-group label {
    color: white;
    font-size: 16px; /* 防止iOS缩放 */
}

.login-form .notification.error {
    background: rgba(220, 53, 69, 0.2);
    color: #f8d7da;
    border: 1px solid rgba(220, 53, 69, 0.5);
    font-size: 15px;
    padding: 12px;
    margin-bottom: 15px;
}

/* 移动端适配 */
@media (max-width: 600px) {
    .login-container {
        min-height: 50vh;
        padding: 20px 15px;
    }
    
    .login-form {
        padding: 30px 25px;
        max-width: 100%;
    }
    
    .login-form h2 {
        font-size: 1.5rem;
        margin-bottom: 25px;
    }
    
    .login-form .form-group {
        margin-bottom: 20px;
    }
    
    .login-form .form-group label {
        font-size: 15px;
        margin-bottom: 8px;
    }
    
    .login-form .form-group input {
        padding: 12px;
        font-size: 16px;
    }
    
    .login-form .btn {
        padding: 14px;
        font-size: 16px;
        margin-top: 25px;
    }
    
    .login-form .notification.error {
        font-size: 14px;
        padding: 10px;
    }
}

/* 平板设备适配 */
@media (min-width: 601px) and (max-width: 768px) {
    .login-form {
        padding: 35px 30px;
        max-width: 350px;
    }
    
    .login-form h2 {
        font-size: 1.7rem;
    }
}

/* 大屏手机适配 */
@media (min-width: 769px) and (max-width: 992px) {
    .login-form {
        padding: 38px;
        max-width: 380px;
    }
}

/* 横屏模式适配 */
@media (max-width: 768px) and (orientation: landscape) {
    .login-container {
        min-height: 80vh;
        padding: 15px;
    }
    
    .login-form {
        padding: 25px 20px;
        max-width: 350px;
    }
    
    .login-form h2 {
        font-size: 1.4rem;
        margin-bottom: 20px;
    }
    
    .login-form .form-group {
        margin-bottom: 15px;
    }
    
    .login-form .btn {
        margin-top: 20px;
        padding: 12px;
    }
}
</style>