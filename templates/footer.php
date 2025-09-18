      </div>
    </div>
    
    <script>
        // 公共JavaScript函数
        function showNotification(message, type = 'success') {
            const notification = document.createElement('div');
            notification.className = `notification ${type}`;
            notification.textContent = message;
            notification.style.position = 'fixed';
            notification.style.top = '20px';
            notification.style.right = '20px';
            notification.style.zIndex = '9999';
            notification.style.maxWidth = '300px';
            notification.style.animation = 'fadeIn 0.5s';
            
            document.body.appendChild(notification);
            
            setTimeout(() => {
                notification.style.animation = 'fadeOut 0.5s';
                setTimeout(() => {
                    notification.remove();
                }, 500);
            }, 3000);
        }
        
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
        
        function copyToClipboard(text) {
            navigator.clipboard.writeText(text).then(() => {
                showNotification('链接已复制到剪贴板');
            }).catch(() => {
                showNotification('复制失败，请手动复制', 'error');
            });
        }
        
        // 移动端触摸手势支持
        document.addEventListener('DOMContentLoaded', function() {
            // 触摸手势元素
            const touchGesture = document.getElementById('touchGesture');
            const swipeBackIndicator = document.getElementById('swipeBackIndicator');
            const doubleTapIndicator = document.getElementById('doubleTapIndicator');
            const longPressMenu = document.getElementById('longPressMenu');
            const refreshItem = document.getElementById('refreshItem');
            const backItem = document.getElementById('backItem');
            const homeItem = document.getElementById('homeItem');
            
            // 触摸开始位置
            let touchStartX = 0;
            let touchStartY = 0;
            let touchEndX = 0;
            let touchEndY = 0;
            
            // 长按定时器
            let longPressTimer = null;
            
            // 双击检测
            let lastTapTime = 0;
            let tapTimeout = null;
            
            // 检测是否为移动设备
            const isMobile = /Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent);
            
            if (isMobile) {
                // 添加全局触摸事件监听
                document.addEventListener('touchstart', handleTouchStart, { passive: true });
                document.addEventListener('touchmove', handleTouchMove, { passive: true });
                document.addEventListener('touchend', handleTouchEnd, { passive: true });
                
                // 长按菜单项点击事件
                if (refreshItem) {
                    refreshItem.addEventListener('click', function() {
                        location.reload();
                    });
                }
                
                if (backItem) {
                    backItem.addEventListener('click', function() {
                        if (window.history.length > 1) {
                            window.history.back();
                        } else {
                            window.location.href = '?action=home';
                        }
                    });
                }
                
                if (homeItem) {
                    homeItem.addEventListener('click', function() {
                        window.location.href = '?action=home';
                    });
                }
                
                // 点击其他地方关闭长按菜单
                document.addEventListener('click', function() {
                    if (longPressMenu) {
                        longPressMenu.classList.remove('show');
                    }
                });
            }
            
            // 处理触摸开始事件
            function handleTouchStart(event) {
                // 记录触摸开始位置
                touchStartX = event.touches[0].clientX;
                touchStartY = event.touches[0].clientY;
                
                // 设置长按定时器
                clearTimeout(longPressTimer);
                longPressTimer = setTimeout(function() {
                    handleLongPress(event);
                }, 500); // 500ms后触发长按
            }
            
            // 处理触摸移动事件
            function handleTouchMove(event) {
                // 如果移动距离超过阈值，取消长按
                const touchX = event.touches[0].clientX;
                const touchY = event.touches[0].clientY;
                const moveX = Math.abs(touchX - touchStartX);
                const moveY = Math.abs(touchY - touchStartY);
                
                if (moveX > 10 || moveY > 10) {
                    clearTimeout(longPressTimer);
                }
            }
            
            // 处理触摸结束事件
            function handleTouchEnd(event) {
                // 记录触摸结束位置
                touchEndX = event.changedTouches[0].clientX;
                touchEndY = event.changedTouches[0].clientY;
                
                // 取消长按定时器
                clearTimeout(longPressTimer);
                
                // 计算滑动距离
                const swipeDistanceX = touchEndX - touchStartX;
                const swipeDistanceY = touchEndY - touchStartY;
                
                // 处理滑动返回
                if (Math.abs(swipeDistanceX) > 100 && Math.abs(swipeDistanceY) < 50) {
                    if (swipeDistanceX > 0) {
                        // 向右滑动
                        handleSwipeRight();
                    } else {
                        // 向左滑动
                        handleSwipeLeft();
                    }
                }
                
                // 处理双击
                const currentTime = new Date().getTime();
                const tapLength = currentTime - lastTapTime;
                
                if (tapLength < 300 && tapLength > 0) {
                    // 双击
                    clearTimeout(tapTimeout);
                    handleDoubleTap(event);
                } else {
                    // 单击
                    lastTapTime = currentTime;
                    tapTimeout = setTimeout(function() {
                        // 处理单击
                    }, 300);
                }
            }
            
            // 处理长按
            function handleLongPress(event) {
                // 显示长按菜单
                if (longPressMenu) {
                    longPressMenu.style.left = event.changedTouches[0].clientX + 'px';
                    longPressMenu.style.top = event.changedTouches[0].clientY + 'px';
                    longPressMenu.classList.add('show');
                }
                
                // 显示触摸手势提示
                if (touchGesture) {
                    touchGesture.classList.add('show');
                    touchGesture.innerHTML = '<i class="fas fa-hand-rock"></i>';
                    
                    setTimeout(() => {
                        touchGesture.classList.remove('show');
                    }, 1000);
                }
            }
            
            // 处理向右滑动
            function handleSwipeRight() {
                // 显示滑动返回提示
                if (swipeBackIndicator) {
                    swipeBackIndicator.classList.add('active');
                    
                    setTimeout(() => {
                        swipeBackIndicator.classList.remove('active');
                    }, 1000);
                }
                
                // 显示触摸手势提示
                if (touchGesture) {
                    touchGesture.classList.add('show');
                    touchGesture.innerHTML = '<i class="fas fa-arrow-right"></i>';
                    
                    setTimeout(() => {
                        touchGesture.classList.remove('show');
                    }, 1000);
                }
                
                // 返回上一页
                if (window.history.length > 1) {
                    setTimeout(() => {
                        window.history.back();
                    }, 300);
                }
            }
            
            // 处理向左滑动
            function handleSwipeLeft() {
                // 显示触摸手势提示
                if (touchGesture) {
                    touchGesture.classList.add('show');
                    touchGesture.innerHTML = '<i class="fas fa-arrow-left"></i>';
                    
                    setTimeout(() => {
                        touchGesture.classList.remove('show');
                    }, 1000);
                }
            }
            
            // 处理双击
            function handleDoubleTap(event) {
                // 显示双击缩放提示
                if (doubleTapIndicator) {
                    doubleTapIndicator.classList.add('show');
                    
                    setTimeout(() => {
                        doubleTapIndicator.classList.remove('show');
                    }, 1000);
                }
                
                // 显示触摸手势提示
                if (touchGesture) {
                    touchGesture.classList.add('show');
                    touchGesture.innerHTML = '<i class="fas fa-expand-arrows-alt"></i>';
                    
                    setTimeout(() => {
                        touchGesture.classList.remove('show');
                    }, 1000);
                }
                
                // 检查是否双击图片
                const target = event.target;
                if (target.tagName === 'IMG') {
                    // 如果是图片，则全屏显示
                    if (target.requestFullscreen) {
                        target.requestFullscreen();
                    } else if (target.webkitRequestFullscreen) {
                        target.webkitRequestFullscreen();
                    } else if (target.msRequestFullscreen) {
                        target.msRequestFullscreen();
                    }
                }
            }
        });
    </script>
    
    <style>
        @keyframes fadeOut {
            from { opacity: 1; }
            to { opacity: 0; }
        }
    </style>
    <?= AssetManager::renderScripts() ?>
</body>
</html>