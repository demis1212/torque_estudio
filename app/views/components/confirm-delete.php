<?php
/**
 * Confirm Delete Component
 * Usage:
 *   include 'components/confirm-delete.php';
 *   Then: confirmDelete('Item Name', 'delete-url');
 */
?>
<style>
    .confirm-delete-overlay {
        position: fixed;
        inset: 0;
        background: rgba(0, 0, 0, 0.8);
        backdrop-filter: blur(10px);
        display: flex;
        align-items: center;
        justify-content: center;
        z-index: 10001;
        opacity: 0;
        visibility: hidden;
        transition: all 0.3s ease;
    }
    
    .confirm-delete-overlay.active {
        opacity: 1;
        visibility: visible;
    }
    
    .confirm-delete-container {
        background: linear-gradient(145deg, rgba(26,29,38,0.98) 0%, rgba(17,19,26,0.99) 100%);
        border: 1px solid rgba(248,113,113,0.2);
        border-radius: 24px;
        width: 90%;
        max-width: 420px;
        padding: 32px;
        text-align: center;
        transform: scale(0.9);
        transition: all 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275);
    }
    
    .confirm-delete-overlay.active .confirm-delete-container {
        transform: scale(1);
    }
    
    .confirm-delete-icon {
        width: 80px;
        height: 80px;
        background: rgba(248,113,113,0.15);
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto 24px;
        font-size: 36px;
        color: #f87171;
        animation: shakeIcon 0.5s ease;
    }
    
    @keyframes shakeIcon {
        0%, 100% { transform: rotate(0); }
        25% { transform: rotate(-10deg); }
        75% { transform: rotate(10deg); }
    }
    
    .confirm-delete-title {
        font-family: 'Space Grotesk', sans-serif;
        font-size: 24px;
        font-weight: 600;
        color: #e8eaf2;
        margin-bottom: 12px;
    }
    
    .confirm-delete-message {
        color: #9aa3b2;
        font-size: 15px;
        line-height: 1.6;
        margin-bottom: 8px;
    }
    
    .confirm-delete-item {
        display: inline-block;
        background: rgba(248,113,113,0.1);
        border: 1px dashed rgba(248,113,113,0.3);
        border-radius: 8px;
        padding: 8px 16px;
        margin: 16px 0;
        color: #f87171;
        font-weight: 600;
        font-size: 14px;
    }
    
    .confirm-delete-warning {
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        background: rgba(251,191,36,0.1);
        border: 1px solid rgba(251,191,36,0.2);
        border-radius: 8px;
        padding: 12px;
        margin: 20px 0;
        color: #fbbf24;
        font-size: 13px;
    }
    
    .confirm-delete-actions {
        display: flex;
        gap: 12px;
        margin-top: 24px;
    }
    
    .confirm-delete-btn {
        flex: 1;
        padding: 14px 20px;
        border-radius: 12px;
        font-size: 14px;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.2s;
        border: none;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
    }
    
    .confirm-delete-btn-cancel {
        background: rgba(255,255,255,0.05);
        color: #9aa3b2;
        border: 1px solid rgba(255,255,255,0.1);
    }
    
    .confirm-delete-btn-cancel:hover {
        background: rgba(255,255,255,0.1);
        color: #e8eaf2;
    }
    
    .confirm-delete-btn-confirm {
        background: linear-gradient(135deg, #f87171 0%, #ef4444 100%);
        color: #fff;
        box-shadow: 0 4px 15px rgba(248,113,113,0.3);
    }
    
    .confirm-delete-btn-confirm:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 25px rgba(248,113,113,0.4);
    }
</style>

<div class="confirm-delete-overlay" id="confirmDeleteOverlay">
    <div class="confirm-delete-container">
        <div class="confirm-delete-icon">
            <i class="fas fa-trash-alt"></i>
        </div>
        <h3 class="confirm-delete-title">¿Eliminar elemento?</h3>
        <p class="confirm-delete-message">Estás a punto de eliminar:</p>
        <div class="confirm-delete-item" id="confirmDeleteItemName">Item Name</div>
        <div class="confirm-delete-warning">
            <i class="fas fa-exclamation-triangle"></i>
            <span>Esta acción no se puede deshacer</span>
        </div>
        <div class="confirm-delete-actions">
            <button class="confirm-delete-btn confirm-delete-btn-cancel" onclick="closeConfirmDelete()">
                <i class="fas fa-times"></i> Cancelar
            </button>
            <button class="confirm-delete-btn confirm-delete-btn-confirm" onclick="executeDelete()">
                <i class="fas fa-trash-alt"></i> Sí, Eliminar
            </button>
        </div>
    </div>
</div>

<form id="deleteForm" method="POST" style="display: none;">
    <input type="hidden" name="csrf_token" value="">
</form>

<script>
let deleteUrl = '';
let deleteForm = null;

document.addEventListener('DOMContentLoaded', function() {
    deleteForm = document.getElementById('deleteForm');
});

function confirmDelete(itemName, url, options = {}) {
    deleteUrl = url;
    document.getElementById('confirmDeleteItemName').textContent = itemName;
    
    // Update CSRF token
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content || '';
    deleteForm.querySelector('input[name="csrf_token"]').value = csrfToken;
    
    const overlay = document.getElementById('confirmDeleteOverlay');
    overlay.classList.add('active');
    document.body.style.overflow = 'hidden';
}

function closeConfirmDelete() {
    const overlay = document.getElementById('confirmDeleteOverlay');
    overlay.classList.remove('active');
    document.body.style.overflow = '';
    deleteUrl = '';
}

function executeDelete() {
    if (deleteUrl) {
        deleteForm.action = deleteUrl;
        deleteForm.submit();
    }
    closeConfirmDelete();
}

// Close on overlay click
document.getElementById('confirmDeleteOverlay').addEventListener('click', function(e) {
    if (e.target === this) {
        closeConfirmDelete();
    }
});

// Close on Escape key
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        closeConfirmDelete();
    }
});
</script>
