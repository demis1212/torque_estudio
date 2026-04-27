<?php
/**
 * Toast Notification Component
 * Include this file and call showToast() function in your JavaScript
 * 
 * Usage:
 *   showToast('message', 'type', duration);
 *   Types: 'success', 'error', 'warning', 'info'
 */
?>
<style>
    .toast-container {
        position: fixed;
        top: 24px;
        right: 24px;
        z-index: 9999;
        display: flex;
        flex-direction: column;
        gap: 12px;
        pointer-events: none;
    }
    
    .toast {
        display: flex;
        align-items: center;
        gap: 14px;
        padding: 16px 20px;
        background: linear-gradient(145deg, rgba(26,29,38,0.95) 0%, rgba(17,19,26,0.98) 100%);
        backdrop-filter: blur(20px);
        border: 1px solid rgba(255,255,255,0.08);
        border-radius: 14px;
        box-shadow: 0 10px 40px rgba(0,0,0,0.4);
        min-width: 320px;
        max-width: 420px;
        pointer-events: all;
        transform: translateX(120%);
        opacity: 0;
        transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
    }
    
    .toast.show {
        transform: translateX(0);
        opacity: 1;
    }
    
    .toast.hiding {
        transform: translateX(120%);
        opacity: 0;
    }
    
    .toast-icon {
        width: 44px;
        height: 44px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 20px;
        flex-shrink: 0;
    }
    
    .toast.success .toast-icon {
        background: rgba(74,222,128,0.15);
        color: #4ade80;
    }
    
    .toast.error .toast-icon {
        background: rgba(248,113,113,0.15);
        color: #f87171;
    }
    
    .toast.warning .toast-icon {
        background: rgba(251,191,36,0.15);
        color: #fbbf24;
    }
    
    .toast.info .toast-icon {
        background: rgba(96,165,250,0.15);
        color: #60a5fa;
    }
    
    .toast-content {
        flex: 1;
        min-width: 0;
    }
    
    .toast-title {
        font-weight: 600;
        font-size: 14px;
        color: #e8eaf2;
        margin-bottom: 4px;
    }
    
    .toast-message {
        font-size: 13px;
        color: #9aa3b2;
        line-height: 1.5;
    }
    
    .toast-close {
        background: none;
        border: none;
        color: #9aa3b2;
        cursor: pointer;
        padding: 4px;
        font-size: 16px;
        transition: all 0.2s;
        border-radius: 6px;
    }
    
    .toast-close:hover {
        color: #e8eaf2;
        background: rgba(255,255,255,0.05);
    }
    
    .toast-progress {
        position: absolute;
        bottom: 0;
        left: 0;
        height: 3px;
        background: currentColor;
        opacity: 0.3;
        border-radius: 0 0 0 14px;
        transition: width linear;
    }
    
    /* Toast positioning variants */
    .toast-container.bottom-right {
        top: auto;
        bottom: 24px;
        right: 24px;
    }
    
    .toast-container.bottom-left {
        top: auto;
        bottom: 24px;
        right: auto;
        left: 24px;
    }
    
    .toast-container.top-left {
        right: auto;
        left: 24px;
    }
    
    .toast-container.center {
        top: 50%;
        left: 50%;
        right: auto;
        transform: translate(-50%, -50%);
    }
</style>

<div class="toast-container" id="toastContainer"></div>

<script>
(function() {
    const container = document.getElementById('toastContainer');
    let toastCounter = 0;
    
    window.showToast = function(message, type = 'info', duration = 5000, title = null) {
        const id = `toast-${++toastCounter}`;
        const titles = {
            success: '¡Éxito!',
            error: 'Error',
            warning: 'Advertencia',
            info: 'Información'
        };
        const icons = {
            success: 'fa-check-circle',
            error: 'fa-times-circle',
            warning: 'fa-exclamation-triangle',
            info: 'fa-info-circle'
        };
        
        const toastTitle = title || titles[type];
        const toastIcon = icons[type];
        
        const toast = document.createElement('div');
        toast.className = `toast ${type}`;
        toast.id = id;
        toast.innerHTML = `
            <div class="toast-icon">
                <i class="fas ${toastIcon}"></i>
            </div>
            <div class="toast-content">
                <div class="toast-title">${toastTitle}</div>
                <div class="toast-message">${message}</div>
            </div>
            <button class="toast-close" onclick="hideToast('${id}')">
                <i class="fas fa-times"></i>
            </button>
            <div class="toast-progress"></div>
        `;
        
        container.appendChild(toast);
        
        // Trigger animation
        requestAnimationFrame(() => {
            toast.classList.add('show');
            const progress = toast.querySelector('.toast-progress');
            progress.style.width = '100%';
            progress.style.transition = `width ${duration}ms linear`;
            requestAnimationFrame(() => {
                progress.style.width = '0%';
            });
        });
        
        // Auto hide
        const timeoutId = setTimeout(() => {
            hideToast(id);
        }, duration);
        
        toast.dataset.timeoutId = timeoutId;
        
        return id;
    };
    
    window.hideToast = function(id) {
        const toast = document.getElementById(id);
        if (!toast) return;
        
        clearTimeout(toast.dataset.timeoutId);
        toast.classList.add('hiding');
        
        setTimeout(() => {
            toast.remove();
        }, 400);
    };
    
    // Show session messages as toasts
    document.addEventListener('DOMContentLoaded', function() {
        <?php if (!empty($_SESSION['success'])): ?>
            showToast(<?= json_encode($_SESSION['success']) ?>, 'success', 5000);
            <?php unset($_SESSION['success']); ?>
        <?php endif; ?>
        
        <?php if (!empty($_SESSION['error'])): ?>
            showToast(<?= json_encode($_SESSION['error']) ?>, 'error', 6000);
            <?php unset($_SESSION['error']); ?>
        <?php endif; ?>
        
        <?php if (!empty($_SESSION['warning'])): ?>
            showToast(<?= json_encode($_SESSION['warning']) ?>, 'warning', 5000);
            <?php unset($_SESSION['warning']); ?>
        <?php endif; ?>
        
        <?php if (!empty($_SESSION['info'])): ?>
            showToast(<?= json_encode($_SESSION['info']) ?>, 'info', 4000);
            <?php unset($_SESSION['info']); ?>
        <?php endif; ?>
    });
})();
</script>
