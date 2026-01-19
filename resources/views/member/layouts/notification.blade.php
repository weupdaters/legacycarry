<style>
/* Base Notification Styles */
.notification {
    position: fixed;
    top: 20px;
    right: 20px;
    z-index: 11111;
    padding: 15px;
    display: flex;
    align-items: center;
    width: 320px;
    border-radius: 6px;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
    opacity: 0;
    transform: translateX(100%);
    transition: all 0.3s ease;
}

.notification.show {
    opacity: 1;
    transform: translateX(0);
}

.notification .fas {
    font-size: 24px;
    margin-right: 15px;
}

.notification-content h4 {
    margin: 0 0 5px 0;
    font-weight: 600;
    font-size: 16px;
}

.notification-content p {
    margin: 0;
    font-size: 14px;
    line-height: 1.5;
}

/* Success Notification */
.notification-success {
    background-color: #f0f9eb;
    border-left: 4px solid #67c23a;
    color: #67c23a;
}

.notification-success .fas {
    color: #67c23a;
}

/* Danger Notification */
.notification-danger {
    background-color: #fef0f0;
    border-left: 4px solid #f56c6c;
    color: #f56c6c;
}

.notification-danger .fas {
    color: #f56c6c;
}

/* Warning Notification */
.notification-warning {
    background-color: #fdf6ec;
    border-left: 4px solid #e6a23c;
    color: #e6a23c;
}

.notification-warning .fas {
    color: #e6a23c;
}

/* Animation for hiding */
@keyframes slideOut {
    to {
        transform: translateX(100%);
        opacity: 0;
    }
}

.notification.hide {
    animation: slideOut 0.3s ease forwards;
}
</style>

@if(session('success'))
    <div class="notification notification-success show" id="notification">
        <i class="fas fa-check-circle"></i>
        <div class="notification-content">
            <h4>Success</h4>
            <p>{{ session('success') }}</p>
        </div>
    </div>
@elseif(session('danger'))
    <div class="notification notification-danger show" id="notification">
        <i class="fas fa-times-circle"></i>
        <div class="notification-content text-warning">
            <h4>Danger</h4>
            <p>{{ session('danger') }}</p>
        </div>
    </div>
@elseif(session('error'))
<div class="notification notification-danger show" id="notification">
    <i class="fas fa-times-circle"></i>
    <div class="notification-content text-dark">
        <h4>Danger</h4>
        <p>{{ session('error') }}</p>
    </div>
</div>
@elseif(session('warning'))
    <div class="notification notification-warning show" id="notification">
        <i class="fas fa-exclamation-circle"></i>
        <div class="notification-content text-warning">
            <h4>Warning</h4>
            <p>{{ session('warning') }}</p>
        </div>
    </div>
@endif

{{-- ✅ Show Global Validation Errors --}}
@if ($errors->any())
    <div class="notification notification-danger show" id="notification">
        <i class="fas fa-exclamation-circle"></i>
        <div class="notification-content">
            <h4>Validation Error</h4>
            <ul style="margin:0; padding-left:18px;">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    </div>
@endif

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const notification = document.getElementById('notification');
        
        if (notification) {
            notification.classList.add('show');
            
            setTimeout(() => {
                notification.classList.remove('show');
                notification.classList.add('hide');
                
                // Remove element after animation completes
                setTimeout(() => {
                    notification.remove();
                }, 300);
            }, 3000);
        }
    });
</script>