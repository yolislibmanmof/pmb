// ==========================================
// ADMIN DASHBOARD - ULTIMATE MULTIMATE
// ==========================================

document.addEventListener('DOMContentLoaded', () => {
    initThemeToggle();
    initSidebar();
    initTableSearch();
    initTableSort();
    initFormValidation();
    initModals();
});

// ==========================================
// 1. THEME TOGGLE (Dark Mode)
// ==========================================
function initThemeToggle() {
    const themeToggle = document.getElementById('admin-theme-toggle');
    const currentTheme = localStorage.getItem('theme') || 'light';
    document.documentElement.setAttribute('data-theme', currentTheme);
    
    if (themeToggle) {
        themeToggle.addEventListener('click', () => {
            const newTheme = document.documentElement.getAttribute('data-theme') === 'light' ? 'dark' : 'light';
            document.documentElement.setAttribute('data-theme', newTheme);
            localStorage.setItem('theme', newTheme);
            
            // Sync dengan public side
            if (window.opener && window.opener.document) {
                window.opener.document.documentElement.setAttribute('data-theme', newTheme);
            }
            
            showToast('Tema Berubah', `Mode ${newTheme === 'dark' ? 'Gelap' : 'Terang'} aktif`, 'success');
        });
    }
}

// ==========================================
// 2. SIDEBAR & MOBILE MENU
// ==========================================
function initSidebar() {
    const mobileToggle = document.querySelector('.mobile-toggle');
    const sidebar = document.querySelector('.sidebar');
    const overlay = document.querySelector('.sidebar-overlay');
    
    if (mobileToggle && sidebar) {
        mobileToggle.addEventListener('click', (e) => {
            e.stopPropagation();
            sidebar.classList.toggle('open');
            if (overlay) overlay.classList.toggle('active');
        });
        
        // Close sidebar when clicking overlay
        if (overlay) {
            overlay.addEventListener('click', () => {
                sidebar.classList.remove('open');
                overlay.classList.remove('active');
            });
        }
        
        // Close sidebar when clicking outside on mobile
        document.addEventListener('click', (e) => {
            if (window.innerWidth <= 1024 && 
                !sidebar.contains(e.target) && 
                !mobileToggle.contains(e.target)) {
                sidebar.classList.remove('open');
                if (overlay) overlay.classList.remove('active');
            }
        });
    }
}

// ==========================================
// 3. TABLE SEARCH (Real-time Filter)
// ==========================================
function initTableSearch() {
    const searchInputs = document.querySelectorAll('[data-table-search]');
    
    searchInputs.forEach(input => {
        const tableId = input.getAttribute('data-table-search');
        const table = document.getElementById(tableId);
        
        if (!table) return;
        
        input.addEventListener('input', function() {
            const value = this.value.toLowerCase();
            const rows = table.querySelectorAll('tbody tr');
            let visibleCount = 0;
            
            rows.forEach(row => {
                const text = row.textContent.toLowerCase();
                const match = text.includes(value);
                row.style.display = match ? '' : 'none';
                if (match) visibleCount++;
            });
            
            // Show/hide empty state
            const emptyState = table.parentElement.querySelector('.table-empty-state');
            if (emptyState) {
                emptyState.style.display = visibleCount === 0 ? 'block' : 'none';
            }
        });
    });
}

// ==========================================
// 4. TABLE SORT (Column Sorting)
// ==========================================
function initTableSort() {
    const tables = document.querySelectorAll('.admin-table[data-sortable]');
    
    tables.forEach(table => {
        const headers = table.querySelectorAll('th[data-sort]');
        
        headers.forEach(header => {
            header.addEventListener('click', () => {
                const column = header.getAttribute('data-sort');
                const type = header.getAttribute('data-sort-type') || 'string';
                const tbody = table.querySelector('tbody');
                const rows = Array.from(tbody.querySelectorAll('tr'));
                
                // Determine sort direction
                const isAsc = header.classList.contains('sorted-asc');
                const direction = isAsc ? -1 : 1;
                
                // Remove sort classes from all headers
                headers.forEach(h => {
                    h.classList.remove('sorted', 'sorted-asc', 'sorted-desc');
                });
                
                // Add sort class to clicked header
                header.classList.add('sorted', isAsc ? 'sorted-desc' : 'sorted-asc');
                
                // Sort rows
                rows.sort((a, b) => {
                    const aCell = a.querySelector(`[data-column="${column}"]`);
                    const bCell = b.querySelector(`[data-column="${column}"]`);
                    
                    if (!aCell || !bCell) return 0;
                    
                    let aVal = aCell.textContent.trim();
                    let bVal = bCell.textContent.trim();
                    
                    if (type === 'number') {
                        aVal = parseFloat(aVal) || 0;
                        bVal = parseFloat(bVal) || 0;
                        return (aVal - bVal) * direction;
                    } else if (type === 'date') {
                        aVal = new Date(aVal);
                        bVal = new Date(bVal);
                        return (aVal - bVal) * direction;
                    } else {
                        return aVal.localeCompare(bVal) * direction;
                    }
                });
                
                // Re-append sorted rows
                rows.forEach(row => tbody.appendChild(row));
            });
        });
    });
}

// ==========================================
// 5. FORM VALIDATION (Real-time)
// ==========================================
function initFormValidation() {
    const forms = document.querySelectorAll('form[data-validate]');
    
    forms.forEach(form => {
        const inputs = form.querySelectorAll('[required], [data-validate]');
        
        inputs.forEach(input => {
            input.addEventListener('blur', () => validateField(input));
            input.addEventListener('input', () => {
                if (input.classList.contains('error')) {
                    validateField(input);
                }
            });
        });
        
        form.addEventListener('submit', (e) => {
            let isValid = true;
            
            inputs.forEach(input => {
                if (!validateField(input)) {
                    isValid = false;
                }
            });
            
            if (!isValid) {
                e.preventDefault();
                showToast('Validasi Gagal', 'Mohon periksa kembali field yang ditandai merah', 'error');
                
                // Scroll to first error
                const firstError = form.querySelector('.error');
                if (firstError) {
                    firstError.scrollIntoView({ behavior: 'smooth', block: 'center' });
                }
            }
        });
    });
}

function validateField(field) {
    const value = field.value.trim();
    const errorEl = field.parentElement.querySelector('.form-error');
    let isValid = true;
    let errorMessage = '';
    
    // Required validation
    if (field.hasAttribute('required') && !value) {
        isValid = false;
        errorMessage = 'Field ini wajib diisi';
    }
    
    // Email validation
    if (field.type === 'email' && value) {
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        if (!emailRegex.test(value)) {
            isValid = false;
            errorMessage = 'Format email tidak valid';
        }
    }
    
    // Min length validation
    const minLength = field.getAttribute('minlength');
    if (minLength && value.length < parseInt(minLength)) {
        isValid = false;
        errorMessage = `Minimal ${minLength} karakter`;
    }
    
    // Update UI
    if (isValid) {
        field.classList.remove('error');
        if (errorEl) errorEl.style.display = 'none';
    } else {
        field.classList.add('error');
        if (errorEl) {
            errorEl.textContent = errorMessage;
            errorEl.style.display = 'flex';
        }
    }
    
    return isValid;
}

// ==========================================
// 6. MODAL SYSTEM
// ==========================================
function initModals() {
    // Open modal
    document.querySelectorAll('[data-modal-open]').forEach(trigger => {
        trigger.addEventListener('click', (e) => {
            e.preventDefault();
            const modalId = trigger.getAttribute('data-modal-open');
            openModal(modalId);
        });
    });
    
    // Close modal
    document.querySelectorAll('[data-modal-close]').forEach(closeBtn => {
        closeBtn.addEventListener('click', () => {
            const modal = closeBtn.closest('.modal-overlay');
            closeModal(modal);
        });
    });
    
    // Close modal on overlay click
    document.querySelectorAll('.modal-overlay').forEach(overlay => {
        overlay.addEventListener('click', (e) => {
            if (e.target === overlay) {
                closeModal(overlay);
            }
        });
    });
    
    // Close modal on ESC key
    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape') {
            document.querySelectorAll('.modal-overlay.active').forEach(modal => {
                closeModal(modal);
            });
        }
    });
}

function openModal(modalId) {
    const modal = document.getElementById(modalId);
    if (modal) {
        modal.classList.add('active');
        document.body.style.overflow = 'hidden';
    }
}

function closeModal(modal) {
    modal.classList.remove('active');
    document.body.style.overflow = '';
}

// ==========================================
// 7. TOAST NOTIFICATION SYSTEM
// ==========================================
function showToast(title, message, type = 'info', duration = 4000) {
    let container = document.querySelector('.toast-container');
    
    if (!container) {
        container = document.createElement('div');
        container.className = 'toast-container';
        document.body.appendChild(container);
    }
    
    const toast = document.createElement('div');
    toast.className = `toast ${type}`;
    
    const iconSvg = getToastIcon(type);
    
    toast.innerHTML = `
        <div class="toast-icon">${iconSvg}</div>
        <div class="toast-content">
            <div class="toast-title">${title}</div>
            <div class="toast-message">${message}</div>
        </div>
        <button class="toast-close" aria-label="Close">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/>
            </svg>
        </button>
    `;
    
    container.appendChild(toast);
    
    // Trigger animation
    setTimeout(() => toast.classList.add('show'), 10);
    
    // Close button
    toast.querySelector('.toast-close').addEventListener('click', () => {
        removeToast(toast);
    });
    
    // Auto remove
    setTimeout(() => {
        removeToast(toast);
    }, duration);
}

function removeToast(toast) {
    toast.classList.remove('show');
    setTimeout(() => {
        if (toast.parentElement) {
            toast.parentElement.removeChild(toast);
        }
    }, 400);
}

function getToastIcon(type) {
    const icons = {
        success: '<svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>',
        error: '<svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="15" y1="9" x2="9" y2="15"/><line x1="9" y1="9" x2="15" y2="15"/></svg>',
        warning: '<svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>',
        info: '<svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="16" x2="12" y2="12"/><line x1="12" y1="8" x2="12.01" y2="8"/></svg>'
    };
    
    return icons[type] || icons.info;
}

// ==========================================
// 8. CONFIRM DELETE (Modal-based)
// ==========================================
function confirmDelete(message, callback) {
    const modalHtml = `
        <div class="modal-overlay" id="confirm-modal">
            <div class="modal">
                <div class="modal-header">
                    <h3>Konfirmasi Hapus</h3>
                    <button class="modal-close" data-modal-close>
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/>
                        </svg>
                    </button>
                </div>
                <div class="modal-body">
                    <div style="display: flex; gap: 1rem; align-items: flex-start;">
                        <div style="width: 48px; height: 48px; background: rgba(239, 68, 68, 0.1); border-radius: 50%; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#ef4444" stroke-width="2">
                                <path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/>
                                <line x1="12" y1="9" x2="12" y2="13"/>
                                <line x1="12" y1="17" x2="12.01" y2="17"/>
                            </svg>
                        </div>
                        <div>
                            <p style="font-weight: 600; margin-bottom: 0.5rem;">Apakah Anda yakin?</p>
                            <p style="color: var(--text-muted); font-size: 0.9rem;">${message || 'Data yang dihapus tidak dapat dikembalikan.'}</p>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button class="btn btn-secondary" data-modal-close>Batal</button>
                    <button class="btn btn-danger" id="confirm-delete-btn">Ya, Hapus</button>
                </div>
            </div>
        </div>
    `;
    
    // Remove existing modal if any
    const existingModal = document.getElementById('confirm-modal');
    if (existingModal) {
        existingModal.remove();
    }
    
    // Add modal to DOM
    document.body.insertAdjacentHTML('beforeend', modalHtml);
    
    // Open modal
    const modal = document.getElementById('confirm-modal');
    setTimeout(() => modal.classList.add('active'), 10);
    
    // Handle confirm
    document.getElementById('confirm-delete-btn').addEventListener('click', () => {
        closeModal(modal);
        if (callback) callback();
    });
    
    // Handle close
    modal.querySelectorAll('[data-modal-close]').forEach(btn => {
        btn.addEventListener('click', () => closeModal(modal));
    });
}

// ==========================================
// 9. COUNTER ANIMATION (For Stats)
// ==========================================
function animateCounter(element, target, duration = 1000) {
    const start = 0;
    const increment = target / (duration / 16);
    let current = start;
    
    const timer = setInterval(() => {
        current += increment;
        if (current >= target) {
            element.textContent = target.toLocaleString();
            clearInterval(timer);
        } else {
            element.textContent = Math.floor(current).toLocaleString();
        }
    }, 16);
}

// Auto-animate counters on page load
document.addEventListener('DOMContentLoaded', () => {
    const counters = document.querySelectorAll('[data-counter]');
    counters.forEach(counter => {
        const target = parseInt(counter.getAttribute('data-counter'));
        animateCounter(counter, target);
    });
});

// ==========================================
// 10. UTILITY FUNCTIONS
// ==========================================

// Format currency
function formatCurrency(amount) {
    return new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR',
        minimumFractionDigits: 0
    }).format(amount);
}

// Format date
function formatDate(dateString) {
    const options = { year: 'numeric', month: 'long', day: 'numeric' };
    return new Date(dateString).toLocaleDateString('id-ID', options);
}

// Debounce function
function debounce(func, wait) {
    let timeout;
    return function executedFunction(...args) {
        const later = () => {
            clearTimeout(timeout);
            func(...args);
        };
        clearTimeout(timeout);
        timeout = setTimeout(later, wait);
    };
}

// Export for global use
window.showToast = showToast;
window.confirmDelete = confirmDelete;
window.openModal = openModal;
window.closeModal = closeModal;
window.formatCurrency = formatCurrency;
window.formatDate = formatDate;