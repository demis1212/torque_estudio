<?php
/**
 * Modal Component
 * Reusable modal dialog with customizable content
 * 
 * Usage:
 *   include 'components/modal.php';
 *   Then use JavaScript: openModal('title', 'content HTML', callback);
 */
?>
<style>
    .modal-overlay {
        position: fixed;
        inset: 0;
        background: rgba(0, 0, 0, 0.7);
        backdrop-filter: blur(8px);
        display: flex;
        align-items: center;
        justify-content: center;
        z-index: 10000;
        opacity: 0;
        visibility: hidden;
        transition: all 0.3s ease;
    }
    
    .modal-overlay.active {
        opacity: 1;
        visibility: visible;
    }
    
    .modal-container {
        background: linear-gradient(145deg, rgba(26,29,38,0.98) 0%, rgba(17,19,26,0.99) 100%);
        border: 1px solid rgba(255,255,255,0.08);
        border-radius: 20px;
        width: 90%;
        max-width: 520px;
        max-height: 90vh;
        overflow: hidden;
        transform: scale(0.9) translateY(20px);
        transition: all 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275);
        box-shadow: 0 25px 80px rgba(0,0,0,0.5);
    }
    
    .modal-overlay.active .modal-container {
        transform: scale(1) translateY(0);
    }
    
    .modal-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 20px 24px;
        border-bottom: 1px solid rgba(255,255,255,0.05);
    }
    
    .modal-title {
        font-family: 'Space Grotesk', sans-serif;
        font-size: 20px;
        font-weight: 600;
        color: #e8eaf2;
        margin: 0;
        display: flex;
        align-items: center;
        gap: 12px;
    }
    
    .modal-title-icon {
        width: 40px;
        height: 40px;
        background: rgba(138,180,248,0.15);
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        color: #8ab4f8;
        font-size: 18px;
    }
    
    .modal-close {
        width: 36px;
        height: 36px;
        background: rgba(255,255,255,0.05);
        border: none;
        border-radius: 10px;
        color: #9aa3b2;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
        transition: all 0.2s;
    }
    
    .modal-close:hover {
        background: rgba(255,255,255,0.1);
        color: #e8eaf2;
    }
    
    .modal-body {
        padding: 24px;
        overflow-y: auto;
        max-height: 60vh;
    }
    
    .modal-footer {
        display: flex;
        justify-content: flex-end;
        gap: 12px;
        padding: 20px 24px;
        border-top: 1px solid rgba(255,255,255,0.05);
        background: rgba(0,0,0,0.2);
    }
    
    .modal-btn {
        padding: 12px 20px;
        border-radius: 10px;
        font-size: 14px;
        font-weight: 500;
        cursor: pointer;
        transition: all 0.2s;
        border: none;
        display: flex;
        align-items: center;
        gap: 8px;
    }
    
    .modal-btn-secondary {
        background: rgba(255,255,255,0.05);
        color: #9aa3b2;
        border: 1px solid rgba(255,255,255,0.1);
    }
    
    .modal-btn-secondary:hover {
        background: rgba(255,255,255,0.1);
        color: #e8eaf2;
    }
    
    .modal-btn-primary {
        background: linear-gradient(135deg, #4d8eff 0%, #3b7de8 100%);
        color: #fff;
    }
    
    .modal-btn-primary:hover {
        transform: translateY(-1px);
        box-shadow: 0 8px 25px rgba(77,142,255,0.4);
    }
    
    .modal-btn-danger {
        background: linear-gradient(135deg, rgba(248,113,113,0.2) 0%, rgba(248,113,113,0.1) 100%);
        color: #f87171;
        border: 1px solid rgba(248,113,113,0.3);
    }
    
    .modal-btn-danger:hover {
        background: linear-gradient(135deg, rgba(248,113,113,0.3) 0%, rgba(248,113,113,0.2) 100%);
    }
</style>

<div class="modal-overlay" id="modalOverlay" onclick="closeModalOnOverlay(event)">
    <div class="modal-container" onclick="event.stopPropagation()">
        <div class="modal-header">
            <h3 class="modal-title" id="modalTitle">
                <span class="modal-title-icon"><i class="fas fa-info-circle"></i></span>
                <span id="modalTitleText">Título</span>
            </h3>
            <button class="modal-close" onclick="closeModal()">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <div class="modal-body" id="modalBody">
            <!-- Content goes here -->
        </div>
        <div class="modal-footer" id="modalFooter">
            <button class="modal-btn modal-btn-secondary" onclick="closeModal()">Cancelar</button>
            <button class="modal-btn modal-btn-primary" id="modalConfirmBtn" onclick="confirmModal()">Confirmar</button>
        </div>
    </div>
</div>

<script>
let modalCallback = null;

function openModal(title, content, options = {}) {
    const overlay = document.getElementById('modalOverlay');
    const titleText = document.getElementById('modalTitleText');
    const body = document.getElementById('modalBody');
    const footer = document.getElementById('modalFooter');
    const confirmBtn = document.getElementById('modalConfirmBtn');
    
    titleText.textContent = title;
    body.innerHTML = content;
    
    // Configure buttons
    if (options.hideCancel) {
        footer.querySelector('.modal-btn-secondary').style.display = 'none';
    } else {
        footer.querySelector('.modal-btn-secondary').style.display = 'flex';
    }
    
    if (options.confirmText) {
        confirmBtn.innerHTML = options.confirmText;
    } else {
        confirmBtn.innerHTML = '<i class="fas fa-check"></i> Confirmar';
    }
    
    if (options.danger) {
        confirmBtn.className = 'modal-btn modal-btn-danger';
    } else {
        confirmBtn.className = 'modal-btn modal-btn-primary';
    }
    
    modalCallback = options.onConfirm || null;
    
    overlay.classList.add('active');
    document.body.style.overflow = 'hidden';
}

function closeModal() {
    const overlay = document.getElementById('modalOverlay');
    overlay.classList.remove('active');
    document.body.style.overflow = '';
    modalCallback = null;
}

function confirmModal() {
    if (modalCallback) {
        modalCallback();
    }
    closeModal();
}

function closeModalOnOverlay(event) {
    if (event.target === event.currentTarget) {
        closeModal();
    }
}

// Close on Escape key
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        closeModal();
    }
});
</script>
