/**
 * Form validation utilities
 */
class FormValidator {
    constructor(formId) {
        this.form = document.getElementById(formId);
        this.errors = {};
        this.init();
    }
    
    init() {
        if (!this.form) return;
        
        // Real-time validation
        this.form.addEventListener('input', (e) => {
            this.validateField(e.target);
        });
        
        this.form.addEventListener('blur', (e) => {
            this.validateField(e.target);
        }, true);
        
        // Form submission
        this.form.addEventListener('submit', (e) => {
            if (!this.validateForm()) {
                e.preventDefault();
                this.showErrors();
            }
        });
    }
    
    validateField(field) {
        const fieldName = field.name;
        const value = field.value.trim();
        const fieldType = field.type;
        
        // Clear previous error
        this.clearFieldError(field);
        
        // Required validation
        if (field.required && !value) {
            this.addError(fieldName, `${this.getFieldLabel(fieldName)} harus diisi.`);
            return false;
        }
        
        // Type-specific validation
        switch (fieldType) {
            case 'email':
                if (value && !this.isValidEmail(value)) {
                    this.addError(fieldName, 'Format email tidak valid.');
                    return false;
                }
                break;
                
            case 'tel':
                if (value && !this.isValidPhone(value)) {
                    this.addError(fieldName, 'Format nomor telepon tidak valid.');
                    return false;
                }
                break;
                
            case 'number':
                const min = parseFloat(field.min);
                const max = parseFloat(field.max);
                const numValue = parseFloat(value);
                
                if (value && isNaN(numValue)) {
                    this.addError(fieldName, 'Harus berupa angka yang valid.');
                    return false;
                }
                
                if (!isNaN(min) && numValue < min) {
                    this.addError(fieldName, `Nilai minimal adalah ${min}.`);
                    return false;
                }
                
                if (!isNaN(max) && numValue > max) {
                    this.addError(fieldName, `Nilai maksimal adalah ${max}.`);
                    return false;
                }
                break;
                
            case 'password':
                if (value && value.length < 6) {
                    this.addError(fieldName, 'Password minimal 6 karakter.');
                    return false;
                }
                break;
        }
        
        // Custom validation
        if (field.dataset.validation) {
            const rules = field.dataset.validation.split('|');
            for (const rule of rules) {
                if (!this.applyCustomRule(rule, value, fieldName)) {
                    return false;
                }
            }
        }
        
        return true;
    }
    
    applyCustomRule(rule, value, fieldName) {
        const [ruleName, ...params] = rule.split(':');
        
        switch (ruleName) {
            case 'min':
                if (value.length < parseInt(params[0])) {
                    this.addError(fieldName, `Minimal ${params[0]} karakter.`);
                    return false;
                }
                break;
                
            case 'max':
                if (value.length > parseInt(params[0])) {
                    this.addError(fieldName, `Maksimal ${params[0]} karakter.`);
                    return false;
                }
                break;
                
            case 'pattern':
                const pattern = new RegExp(params[0]);
                if (!pattern.test(value)) {
                    this.addError(fieldName, params[1] || 'Format tidak valid.');
                    return false;
                }
                break;
                
            case 'confirm':
                const confirmField = document.querySelector(`[name="${params[0]}"]`);
                if (confirmField && value !== confirmField.value) {
                    this.addError(fieldName, 'Konfirmasi tidak cocok.');
                    return false;
                }
                break;
        }
        
        return true;
    }
    
    validateForm() {
        this.errors = {};
        let isValid = true;
        
        // Validate all fields
        const fields = this.form.querySelectorAll('input, select, textarea');
        for (const field of fields) {
            if (!this.validateField(field)) {
                isValid = false;
            }
        }
        
        return isValid;
    }
    
    addError(fieldName, message) {
        if (!this.errors[fieldName]) {
            this.errors[fieldName] = [];
        }
        this.errors[fieldName].push(message);
    }
    
    clearFieldError(field) {
        const fieldName = field.name;
        delete this.errors[fieldName];
        
        // Remove error display
        const errorElement = field.parentNode.querySelector('.field-error');
        if (errorElement) {
            errorElement.remove();
        }
        
        field.classList.remove('is-invalid');
        field.classList.add('is-valid');
    }
    
    showErrors() {
        for (const [fieldName, messages] of Object.entries(this.errors)) {
            const field = this.form.querySelector(`[name="${fieldName}"]`);
            if (field) {
                field.classList.remove('is-valid');
                field.classList.add('is-invalid');
                
                // Remove existing error message
                const existingError = field.parentNode.querySelector('.field-error');
                if (existingError) {
                    existingError.remove();
                }
                
                // Add error message
                const errorDiv = document.createElement('div');
                errorDiv.className = 'field-error text-danger small mt-1';
                errorDiv.textContent = messages[0];
                field.parentNode.appendChild(errorDiv);
            }
        }
    }
    
    getFieldLabel(fieldName) {
        const label = this.form.querySelector(`label[for="${fieldName}"]`);
        return label ? label.textContent.replace(':', '').trim() : fieldName;
    }
    
    isValidEmail(email) {
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        return emailRegex.test(email);
    }
    
    isValidPhone(phone) {
        const phoneRegex = /^(\+62|62|0)[0-9]{9,13}$/;
        return phoneRegex.test(phone.replace(/[-\s]/g, ''));
    }
}

/**
 * Loading indicator utilities
 */
class LoadingIndicator {
    static show(element, text = 'Loading...') {
        const loader = document.createElement('div');
        loader.className = 'loading-indicator';
        loader.innerHTML = `
            <div class="spinner-border spinner-border-sm me-2" role="status">
                <span class="visually-hidden">Loading...</span>
            </div>
            ${text}
        `;
        
        if (element) {
            element.disabled = true;
            element.dataset.originalText = element.textContent;
            element.textContent = text;
            
            if (element.tagName === 'BUTTON') {
                const spinner = document.createElement('span');
                spinner.className = 'spinner-border spinner-border-sm me-2';
                spinner.setAttribute('role', 'status');
                element.prepend(spinner);
            }
        }
    }
    
    static hide(element) {
        if (element) {
            element.disabled = false;
            element.textContent = element.dataset.originalText || element.textContent;
            
            const spinner = element.querySelector('.spinner-border');
            if (spinner) {
                spinner.remove();
            }
        }
    }
}

/**
 * Alert utilities
 */
class AlertManager {
    static show(message, type = 'info', duration = 5000) {
        const alertId = 'alert-' + Date.now();
        const alertHtml = `
            <div id="${alertId}" class="alert alert-${type} alert-dismissible fade show position-fixed" 
                 style="top: 20px; right: 20px; z-index: 9999; min-width: 300px;" role="alert">
                <div class="d-flex align-items-center">
                    <div class="flex-grow-1">${message}</div>
                    <button type="button" class="btn-close ms-2" data-bs-dismiss="alert"></button>
                </div>
            </div>
        `;
        
        document.body.insertAdjacentHTML('beforeend', alertHtml);
        
        // Auto-dismiss
        if (duration > 0) {
            setTimeout(() => {
                const alertElement = document.getElementById(alertId);
                if (alertElement) {
                    const bsAlert = new bootstrap.Alert(alertElement);
                    bsAlert.close();
                }
            }, duration);
        }
    }
    
    static success(message, duration = 3000) {
        this.show(message, 'success', duration);
    }
    
    static error(message, duration = 5000) {
        this.show(message, 'danger', duration);
    }
    
    static warning(message, duration = 4000) {
        this.show(message, 'warning', duration);
    }
    
    static info(message, duration = 4000) {
        this.show(message, 'info', duration);
    }
}

/**
 * Mobile optimization utilities
 */
class MobileOptimizer {
    static init() {
        this.optimizeTables();
        this.optimizeForms();
        this.addTouchSupport();
    }
    
    static optimizeTables() {
        const tables = document.querySelectorAll('.table-responsive');
        tables.forEach(table => {
            table.classList.add('table-mobile');
        });
    }
    
    static optimizeForms() {
        const forms = document.querySelectorAll('form');
        forms.forEach(form => {
            // Add mobile-friendly input types
            const inputs = form.querySelectorAll('input[type="text"], input[type="email"]');
            inputs.forEach(input => {
                input.setAttribute('autocomplete', 'off');
                input.setAttribute('autocorrect', 'off');
                input.setAttribute('autocapitalize', 'off');
                input.setAttribute('spellcheck', 'false');
            });
        });
    }
    
    static addTouchSupport() {
        // Add touch feedback to buttons
        const buttons = document.querySelectorAll('button, .btn');
        buttons.forEach(button => {
            button.addEventListener('touchstart', function() {
                this.style.transform = 'scale(0.95)';
            });
            
            button.addEventListener('touchend', function() {
                this.style.transform = 'scale(1)';
            });
        });
    }
}

// Initialize when DOM is ready
document.addEventListener('DOMContentLoaded', function() {
    // Initialize mobile optimizations
    MobileOptimizer.init();
    
    // Auto-initialize form validators
    const forms = document.querySelectorAll('form[data-validate="true"]');
    forms.forEach(form => {
        new FormValidator(form.id);
    });
});

// Export classes for use in other files
window.FormValidator = FormValidator;
window.LoadingIndicator = LoadingIndicator;
window.AlertManager = AlertManager;
window.MobileOptimizer = MobileOptimizer;
