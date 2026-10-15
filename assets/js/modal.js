/**
 * Digital Business Technology - Accessible Editorial Modal & Dialog System
 * Standard: STYLE.md & RULES.md
 * Replaces native window.alert() and window.confirm() with accessible, styled editorial modals.
 */

(function (window, document) {
  'use strict';

  const Modal = {
    /**
     * Show an accessible styled confirmation dialog
     * @param {Object|string} options - Configuration object or message string
     * @returns {Promise<boolean>}
     */
    confirm: function (options) {
      if (typeof options === 'string') {
        options = { message: options };
      }

      const {
        title = 'ยืนยันการดำเนินการ',
        message = 'คุณต้องการดำเนินการต่อหรือไม่?',
        confirmText = 'ยืนยัน',
        cancelText = 'ยกเลิก',
        confirmVariant = 'btn-navy',
      } = options || {};

      return new Promise((resolve) => {
        const modalId = 'modal-confirm-' + Date.now();
        const html = `
          <div class="modal fade" id="${modalId}" tabindex="-1" aria-labelledby="${modalId}-label" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered" style="max-width: 440px;">
              <div class="modal-content">
                <div class="modal-header">
                  <h5 class="modal-title" id="${modalId}-label">${escapeHtml(title)}</h5>
                  <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="ปิด"></button>
                </div>
                <div class="modal-body">
                  <p class="m-0">${escapeHtml(message)}</p>
                </div>
                <div class="modal-footer">
                  <button type="button" class="btn btn-sm btn-line" data-bs-dismiss="modal">${escapeHtml(cancelText)}</button>
                  <button type="button" class="btn btn-sm ${confirmVariant} btn-confirm-action">${escapeHtml(confirmText)}</button>
                </div>
              </div>
            </div>
          </div>
        `;

        document.body.insertAdjacentHTML('beforeend', html);
        const modalEl = document.getElementById(modalId);
        const bsModal = new bootstrap.Modal(modalEl, { backdrop: 'static', keyboard: true });

        let confirmed = false;
        const confirmBtn = modalEl.querySelector('.btn-confirm-action');

        confirmBtn.addEventListener('click', () => {
          confirmed = true;
          bsModal.hide();
        });

        modalEl.addEventListener('hidden.bs.modal', () => {
          modalEl.remove();
          resolve(confirmed);
        });

        bsModal.show();
      });
    },

    /**
     * Show an accessible alert dialog
     * @param {Object|string} options
     * @returns {Promise<void>}
     */
    alert: function (options) {
      if (typeof options === 'string') {
        options = { message: options };
      }

      const {
        title = 'แจ้งเตือน',
        message = '',
        okText = 'ตกลง',
      } = options || {};

      return new Promise((resolve) => {
        const modalId = 'modal-alert-' + Date.now();
        const html = `
          <div class="modal fade" id="${modalId}" tabindex="-1" aria-labelledby="${modalId}-label" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered" style="max-width: 440px;">
              <div class="modal-content">
                <div class="modal-header">
                  <h5 class="modal-title" id="${modalId}-label">${escapeHtml(title)}</h5>
                  <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="ปิด"></button>
                </div>
                <div class="modal-body">
                  <p class="m-0">${escapeHtml(message)}</p>
                </div>
                <div class="modal-footer">
                  <button type="button" class="btn btn-sm btn-navy" data-bs-dismiss="modal">${escapeHtml(okText)}</button>
                </div>
              </div>
            </div>
          </div>
        `;

        document.body.insertAdjacentHTML('beforeend', html);
        const modalEl = document.getElementById(modalId);
        const bsModal = new bootstrap.Modal(modalEl);

        modalEl.addEventListener('hidden.bs.modal', () => {
          modalEl.remove();
          resolve();
        });

        bsModal.show();
      });
    },

    /**
     * Open a custom modal dialog dynamically
     */
    open: function (options) {
      const {
        title = '',
        body = null,
        bodyHtml = '',
        footer = null,
        footerHtml = '',
        size = '', // 'modal-lg', 'modal-xl', etc.
        onShown = null,
        onHidden = null,
      } = options || {};

      const modalId = 'modal-dynamic-' + Date.now();
      const modalEl = document.createElement('div');
      modalEl.className = 'modal fade';
      modalEl.id = modalId;
      modalEl.tabIndex = -1;
      modalEl.setAttribute('aria-hidden', 'true');

      const dialogEl = document.createElement('div');
      dialogEl.className = 'modal-dialog modal-dialog-centered' + (size ? ' ' + size : '');

      const contentEl = document.createElement('div');
      contentEl.className = 'modal-content';

      if (title) {
        const headerEl = document.createElement('div');
        headerEl.className = 'modal-header';

        const titleEl = document.createElement('h5');
        titleEl.className = 'modal-title';
        titleEl.textContent = title;

        const closeBtn = document.createElement('button');
        closeBtn.type = 'button';
        closeBtn.className = 'btn-close';
        closeBtn.setAttribute('data-bs-dismiss', 'modal');
        closeBtn.setAttribute('aria-label', 'ปิด');

        headerEl.appendChild(titleEl);
        headerEl.appendChild(closeBtn);
        contentEl.appendChild(headerEl);
      }

      const bodyEl = document.createElement('div');
      bodyEl.className = 'modal-body';
      if (body instanceof Node) {
        bodyEl.appendChild(body);
      } else if (bodyHtml) {
        bodyEl.innerHTML = bodyHtml;
      }
      contentEl.appendChild(bodyEl);

      if (footer instanceof Node || footerHtml) {
        const footerEl = document.createElement('div');
        footerEl.className = 'modal-footer';
        if (footer instanceof Node) {
          footerEl.appendChild(footer);
        } else if (footerHtml) {
          footerEl.innerHTML = footerHtml;
        }
        contentEl.appendChild(footerEl);
      }

      dialogEl.appendChild(contentEl);
      modalEl.appendChild(dialogEl);
      document.body.appendChild(modalEl);

      const bsModal = new bootstrap.Modal(modalEl);

      if (typeof onShown === 'function') {
        modalEl.addEventListener('shown.bs.modal', () => onShown(modalEl));
      }

      modalEl.addEventListener('hidden.bs.modal', () => {
        if (typeof onHidden === 'function') onHidden(modalEl);
        modalEl.remove();
      });

      bsModal.show();
      return { modalEl, bsModal };
    }
  };

  /**
   * Global Toast Notification Function
   */
  window.showToast = function (message, type = 'success', duration = 3200) {
    let toast = document.querySelector('.admin-toast');
    if (!toast) {
      toast = document.createElement('div');
      toast.className = 'admin-toast';
      document.body.appendChild(toast);
    }

    toast.className = 'admin-toast';
    if (type === 'error') {
      toast.classList.add('is-error');
    } else {
      toast.classList.add('is-success');
    }

    toast.textContent = message;
    toast.classList.add('is-active');

    if (toast._timer) clearTimeout(toast._timer);
    toast._timer = setTimeout(() => {
      toast.classList.remove('is-active');
    }, duration);
  };

  function escapeHtml(str) {
    if (!str) return '';
    return String(str)
      .replace(/&/g, '&amp;')
      .replace(/</g, '&lt;')
      .replace(/>/g, '&gt;')
      .replace(/"/g, '&quot;')
      .replace(/'/g, '&#039;');
  }

  // Intercept all [data-confirm] clicks to use styled Modal.confirm
  document.addEventListener('click', (e) => {
    const trigger = e.target.closest('[data-confirm]');
    if (!trigger) return;

    // If already confirmed through modal bypass
    if (trigger.dataset.confirmed === 'true') {
      trigger.dataset.confirmed = 'false';
      return;
    }

    e.preventDefault();
    e.stopPropagation();

    const message = trigger.getAttribute('data-confirm') || 'ยืนยันการดำเนินการ?';
    const isDanger = trigger.classList.contains('btn-danger') || trigger.classList.contains('btn-action-icon--danger') || trigger.classList.contains('btn-danger-line');

    Modal.confirm({
      title: 'ยืนยันการดำเนินการ',
      message: message,
      confirmText: 'ยืนยัน',
      confirmVariant: isDanger ? 'btn-danger' : 'btn-navy',
    }).then((ok) => {
      if (ok) {
        trigger.dataset.confirmed = 'true';
        if (trigger.tagName === 'A' && trigger.href) {
          window.location.href = trigger.href;
        } else if (trigger.type === 'submit' || trigger.closest('form')) {
          const form = trigger.closest('form');
          if (form) {
            if (typeof form.requestSubmit === 'function') {
              form.requestSubmit(trigger);
            } else {
              form.submit();
            }
          }
        } else {
          trigger.click();
        }
      }
    });
  }, true);

  // Expose Modal globally
  window.Modal = Modal;

})(window, document);
