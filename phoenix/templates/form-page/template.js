/**
 * PHOENIX Form Page Template
 * Data entry forms with validation and auto-save
 */

class PhoenixFormTemplate {
    constructor(options = {}) {
        this.options = {
            enableValidation: true,
            enableAutoSave: false,
            autoSaveInterval: 30,
            confirmNavigation: true,
            ...options
        };

        this.form = null;
        this.isDirty = false;
        this.autoSaveTimer = null;
        this.validationRules = new Map();

        this.init();
    }

    init() {
        this.form = document.getElementById('phoenix-form');
        if (!this.form) return;

        this.bindElements();
        this.bindEvents();
        this.initValidation();
        this.initAutoSave();
        this.initNavigationGuard();

        console.log('📝 PHOENIX Form Template initialized');
    }

    bindElements() {
        this.statusEl = document.getElementById('form-status');
        this.progressEl = document.getElementById('form-progress');
        this.progressPercentEl = document.getElementById('progress-percent');
        this.submitBtn = document.getElementById('btn-submit');
        this.saveDraftBtn = document.getElementById('btn-save-draft');
        this.cancelBtn = document.getElementById('btn-cancel');
        this.backBtn = document.getElementById('btn-back');
    }

    bindEvents() {
        // Form submission
        this.form.addEventListener('submit', (e) => this.handleSubmit(e));

        // Input changes
        this.form.addEventListener('input', () => this.handleInput());
        this.form.addEventListener('change', () => this.handleInput());

        // Buttons
        if (this.saveDraftBtn) {
            this.saveDraftBtn.addEventListener('click', () => this.saveDraft());
        }

        if (this.cancelBtn) {
            this.cancelBtn.addEventListener('click', () => this.cancel());
        }

        if (this.backBtn) {
            this.backBtn.addEventListener('click', () => this.goBack());
        }

        // Keyboard shortcuts
        document.addEventListener('keydown', (e) => {
            // Ctrl/Cmd + S to save
            if ((e.ctrlKey || e.metaKey) && e.key === 's') {
                e.preventDefault();
                this.saveDraft();
            }

            // Ctrl/Cmd + Enter to submit
            if ((e.ctrlKey || e.metaKey) && e.key === 'Enter') {
                e.preventDefault();
                this.form.requestSubmit();
            }
        });
    }

    handleInput() {
        this.isDirty = true;
        this.updateProgress();

        if (this.options.enableValidation) {
            this.validateField(document.activeElement);
        }
    }

    handleSubmit(e) {
        e.preventDefault();

        if (this.options.enableValidation && !this.validateAll()) {
            this.setStatus('Please fix the errors below', 'error');
            return;
        }

        this.submit();
    }

    initValidation() {
        if (!this.options.enableValidation) return;

        // Find all form fields and set up validation
        const fields = this.form.querySelectorAll('[data-validate], [required]');

        fields.forEach(field => {
            const rules = [];

            if (field.required) {
                rules.push({ type: 'required', message: 'This field is required' });
            }

            if (field.type === 'email') {
                rules.push({ type: 'email', message: 'Please enter a valid email' });
            }

            if (field.minLength) {
                rules.push({
                    type: 'minLength',
                    value: field.minLength,
                    message: `Minimum ${field.minLength} characters`
                });
            }

            if (field.maxLength) {
                rules.push({
                    type: 'maxLength',
                    value: field.maxLength,
                    message: `Maximum ${field.maxLength} characters`
                });
            }

            if (field.pattern) {
                rules.push({
                    type: 'pattern',
                    value: new RegExp(field.pattern),
                    message: field.dataset.patternMessage || 'Invalid format'
                });
            }

            if (rules.length > 0) {
                this.validationRules.set(field, rules);
            }

            // Add blur validation
            field.addEventListener('blur', () => this.validateField(field));
        });
    }

    validateField(field) {
        const rules = this.validationRules.get(field);
        if (!rules) return true;

        const group = field.closest('.form-group');
        const errorEl = group?.querySelector('.form-error');

        for (const rule of rules) {
            let isValid = true;
            const value = field.value.trim();

            switch (rule.type) {
                case 'required':
                    isValid = value.length > 0;
                    break;
                case 'email':
                    isValid = !value || /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(value);
                    break;
                case 'minLength':
                    isValid = !value || value.length >= rule.value;
                    break;
                case 'maxLength':
                    isValid = value.length <= rule.value;
                    break;
                case 'pattern':
                    isValid = !value || rule.value.test(value);
                    break;
            }

            if (!isValid) {
                field.classList.add('error');
                if (group) group.classList.add('has-error');
                if (errorEl) errorEl.textContent = rule.message;
                return false;
            }
        }

        field.classList.remove('error');
        if (group) group.classList.remove('has-error');
        if (errorEl) errorEl.textContent = '';
        return true;
    }

    validateAll() {
        let isValid = true;
        let firstError = null;

        this.validationRules.forEach((rules, field) => {
            if (!this.validateField(field)) {
                isValid = false;
                if (!firstError) firstError = field;
            }
        });

        if (firstError) {
            firstError.focus();
            firstError.scrollIntoView({ behavior: 'smooth', block: 'center' });
        }

        return isValid;
    }

    initAutoSave() {
        if (!this.options.enableAutoSave) return;

        this.autoSaveTimer = setInterval(() => {
            if (this.isDirty) {
                this.saveDraft();
            }
        }, this.options.autoSaveInterval * 1000);
    }

    initNavigationGuard() {
        if (!this.options.confirmNavigation) return;

        window.addEventListener('beforeunload', (e) => {
            if (this.isDirty) {
                e.preventDefault();
                e.returnValue = 'You have unsaved changes. Are you sure you want to leave?';
            }
        });
    }

    updateProgress() {
        const fields = this.form.querySelectorAll('input:not([type="hidden"]), textarea, select');
        let filled = 0;

        fields.forEach(field => {
            if (field.value.trim()) filled++;
        });

        const percent = Math.round((filled / fields.length) * 100);

        if (this.progressEl) {
            this.progressEl.style.width = percent + '%';
        }

        if (this.progressPercentEl) {
            this.progressPercentEl.textContent = percent;
        }
    }

    setStatus(message, type = 'info') {
        if (!this.statusEl) return;

        this.statusEl.textContent = message;
        this.statusEl.className = 'form-status ' + type;

        // Clear after 3 seconds for non-error messages
        if (type !== 'error') {
            setTimeout(() => {
                this.statusEl.textContent = '';
                this.statusEl.className = 'form-status';
            }, 3000);
        }
    }

    getFormData() {
        const formData = new FormData(this.form);
        const data = {};

        formData.forEach((value, key) => {
            if (data[key]) {
                if (!Array.isArray(data[key])) {
                    data[key] = [data[key]];
                }
                data[key].push(value);
            } else {
                data[key] = value;
            }
        });

        return data;
    }

    async saveDraft() {
        this.setStatus('Saving...', 'saving');

        try {
            const data = this.getFormData();

            // Store in localStorage as fallback
            localStorage.setItem('phoenix_form_draft_' + window.location.pathname, JSON.stringify(data));

            // Emit event for custom handling
            Phoenix.emit('form:saveDraft', { data });

            this.isDirty = false;
            this.setStatus('Draft saved', 'saved');

        } catch (error) {
            this.setStatus('Failed to save draft', 'error');
            console.error('Save draft error:', error);
        }
    }

    async submit() {
        this.setStatus('Submitting...', 'saving');
        this.submitBtn.disabled = true;

        try {
            const data = this.getFormData();

            // Emit event for custom handling
            Phoenix.emit('form:submit', { data });

            // Default: POST to action URL
            if (this.form.action) {
                const response = await Phoenix.api.post(this.form.action, data);

                if (response.success) {
                    this.isDirty = false;
                    this.setStatus('Submitted successfully', 'saved');

                    // Clear draft
                    localStorage.removeItem('phoenix_form_draft_' + window.location.pathname);

                    Phoenix.emit('form:success', { data, response });
                } else {
                    throw new Error(response.message || 'Submission failed');
                }
            }

        } catch (error) {
            this.setStatus(error.message || 'Submission failed', 'error');
            Phoenix.emit('form:error', { error });

        } finally {
            this.submitBtn.disabled = false;
        }
    }

    cancel() {
        if (this.isDirty) {
            if (!confirm('You have unsaved changes. Are you sure you want to cancel?')) {
                return;
            }
        }

        Phoenix.emit('form:cancel');
        this.goBack();
    }

    goBack() {
        if (document.referrer && document.referrer.includes(window.location.host)) {
            history.back();
        } else {
            window.location.href = '/';
        }
    }

    loadDraft() {
        const draft = localStorage.getItem('phoenix_form_draft_' + window.location.pathname);
        if (!draft) return false;

        try {
            const data = JSON.parse(draft);

            Object.entries(data).forEach(([key, value]) => {
                const field = this.form.querySelector(`[name="${key}"]`);
                if (field) {
                    field.value = value;
                }
            });

            this.updateProgress();
            return true;

        } catch (error) {
            return false;
        }
    }

    reset() {
        this.form.reset();
        this.isDirty = false;
        this.updateProgress();

        // Clear validation errors
        this.form.querySelectorAll('.error').forEach(el => el.classList.remove('error'));
        this.form.querySelectorAll('.has-error').forEach(el => el.classList.remove('has-error'));
    }

    destroy() {
        if (this.autoSaveTimer) {
            clearInterval(this.autoSaveTimer);
        }
    }
}

// Auto-initialize
document.addEventListener('DOMContentLoaded', () => {
    window.phoenixForm = new PhoenixFormTemplate();
});
