/**
 * admin-news.js - DBT Admin News Management & Live Preview Controller
 * Handles:
 * - Quick Category creation modal via AJAX
 * - Live device preview modal viewport switcher (Desktop / Mobile)
 * - Real-time sync of Quill editor content & uploaded image to preview modal
 * - Client-side table search & filter
 * Standard: STYLE.md & RULES.md
 */

(function () {
  'use strict';

  document.addEventListener('DOMContentLoaded', () => {
    initQuickCategoryModal();
    initNewsPreviewModal();
    initNewsSearchFilter();
  });

  /**
   * 1. Quick Category Creation Modal
   */
  function initQuickCategoryModal() {
    const btnQuickAddCat = document.getElementById('btnQuickAddCat');
    const catSelect = document.getElementById('input-news-category');

    if (!btnQuickAddCat || !catSelect) return;

    btnQuickAddCat.addEventListener('click', () => {
      if (typeof Modal === 'undefined' || typeof Modal.open !== 'function') {
        return;
      }

      Modal.open({
        title: 'เพิ่มหมวดหมู่ข่าวสารใหม่',
        bodyHtml: `
          <div class="mb-3">
            <label class="form-label" for="quick_cat_name">ชื่อหมวดหมู่ใหม่ <span class="req">*</span></label>
            <input type="text" id="quick_cat_name" class="form-control" placeholder="เช่น กิจกรรมนักศึกษา, ประกาศผล" required>
          </div>
        `,
        footerHtml: `
          <button type="button" class="btn btn-sm btn-line" data-bs-dismiss="modal">ยกเลิก</button>
          <button type="button" class="btn btn-sm btn-navy" id="btnSaveQuickCat">บันทึกหมวดหมู่</button>
        `,
        onShown: (modalEl) => {
          const input = modalEl.querySelector('#quick_cat_name');
          const saveBtn = modalEl.querySelector('#btnSaveQuickCat');
          if (!input || !saveBtn) return;

          input.focus();

          saveBtn.addEventListener('click', async () => {
            const val = input.value.trim();
            if (!val) {
              input.classList.add('is-invalid');
              return;
            }

            const csrfEl = document.querySelector('input[name="csrf"]');
            const csrfToken = csrfEl ? csrfEl.value : '';
            const apiUrl = btnQuickAddCat.dataset.apiUrl || 'api/admin/news-category';

            const formData = new FormData();
            formData.append('csrf', csrfToken);
            formData.append('sub_action', 'ajax_add_category');
            formData.append('name', val);

            try {
              const res = await fetch(apiUrl, {
                method: 'POST',
                body: formData
              });
              const data = await res.json();
              if (data.success) {
                const opt = document.createElement('option');
                opt.value = data.id;
                opt.textContent = data.name;
                opt.selected = true;
                catSelect.appendChild(opt);

                if (window.bootstrap && bootstrap.Modal) {
                  const bsModal = bootstrap.Modal.getInstance(modalEl);
                  if (bsModal) bsModal.hide();
                }
                if (typeof window.showToast === 'function') {
                  window.showToast('เพิ่มหมวดหมู่ "' + data.name + '" สำเร็จแล้ว');
                }
              } else {
                alert(data.error || 'เกิดข้อผิดพลาดในการบันทึกหมวดหมู่');
              }
            } catch (err) {
              alert('ไม่สามารถติดต่อเซิร์ฟเวอร์ได้');
            }
          });
        }
      });
    });
  }

  /**
   * 2. Full Preview Modal Viewport Switcher & Content Sync
   */
  function initNewsPreviewModal() {
    const newsModalEl = document.getElementById('newsPreviewModal');
    if (!newsModalEl) return;

    const frameEl = document.getElementById('newsPreviewFrame');
    const btnDesk = document.getElementById('btnNewsPreviewDesktop');
    const btnMob = document.getElementById('btnNewsPreviewMobile');

    const inTitle = document.getElementById('input-news-title');
    const catSelect = document.getElementById('input-news-category');
    const inSummary = document.getElementById('input-news-summary');
    const inContent = document.getElementById('input-news-content');

    const modalTitle = document.getElementById('modalNewsTitle');
    const modalCat = document.getElementById('modalNewsCat');
    const modalSummary = document.getElementById('modalNewsSummary');
    const modalContent = document.getElementById('modalNewsContent');
    const modalImg = document.getElementById('modalNewsImg');
    const modalFigWrap = document.getElementById('modalNewsFigWrap');

    if (btnDesk && btnMob && frameEl) {
      btnDesk.addEventListener('click', () => {
        btnDesk.classList.add('is-active');
        btnMob.classList.remove('is-active');
        frameEl.classList.remove('is-mobile');
        frameEl.classList.add('is-desktop');
      });

      btnMob.addEventListener('click', () => {
        btnMob.classList.add('is-active');
        btnDesk.classList.remove('is-active');
        frameEl.classList.remove('is-desktop');
        frameEl.classList.add('is-mobile');
      });
    }

    newsModalEl.addEventListener('show.bs.modal', () => {
      if (modalTitle && inTitle) {
        modalTitle.textContent = inTitle.value || 'หัวข้อข่าว';
      }
      if (modalCat && catSelect) {
        const selOpt = catSelect.options[catSelect.selectedIndex];
        const catVal = catSelect.value;
        modalCat.textContent = (catVal && selOpt) ? selOpt.textContent : 'ข่าวสารทั่วไป';
      }
      if (modalSummary && inSummary) {
        modalSummary.textContent = inSummary.value || '';
      }

      // Sync real-time content directly from active Quill editor instance
      const quillEditorEl = document.getElementById('quill-news-editor');
      let contentHtml = '';
      if (quillEditorEl && quillEditorEl.__quillInstance) {
        const q = quillEditorEl.__quillInstance;
        if (q.getText().trim()) {
          contentHtml = q.root.innerHTML;
        }
      } else if (inContent) {
        contentHtml = inContent.value;
      }
      if (modalContent) {
        const render = window.renderSafeHtml || (window.DBTQuillParser && window.DBTQuillParser.renderSafe);
        if (typeof render === 'function') {
          render(modalContent, contentHtml, {
            isTitle: false,
            fallback: 'ไม่มีเนื้อหาข่าว'
          });
        } else {
          modalContent.textContent = contentHtml || 'ไม่มีเนื้อหาข่าว';
        }
      }

      // Dynamically query the uploaded preview image at modal trigger time
      if (modalImg && modalFigWrap) {
        const livePreviewImg = document.querySelector('.image-upload-preview .preview-img');
        const currentSrc = livePreviewImg ? livePreviewImg.getAttribute('src') : '';
        if (currentSrc && currentSrc !== '') {
          modalImg.src = currentSrc;
          modalFigWrap.style.display = 'block';
        } else {
          modalFigWrap.style.display = 'none';
        }
      }
    });
  }

  /**
   * 3. News List Client-Side Search Filter
   */
  function initNewsSearchFilter() {
    const newsSearch = document.getElementById('newsSearchInput');
    const rows = document.querySelectorAll('.news-list-row');
    if (!newsSearch || rows.length === 0) return;

    newsSearch.addEventListener('input', function () {
      const q = this.value.trim().toLowerCase();
      let matchCount = 0;
      rows.forEach(tr => {
        const text = tr.textContent.toLowerCase();
        const matches = text.includes(q);
        tr.style.display = matches ? '' : 'none';
        if (matches) matchCount++;
      });
      let emptyRow = document.getElementById('newsSearchEmptyRow');
      if (matchCount === 0) {
        if (!emptyRow) {
          emptyRow = document.createElement('tr');
          emptyRow.id = 'newsSearchEmptyRow';
          emptyRow.innerHTML = '<td colspan="6" class="text-center py-4 text-muted">ไม่พบข่าวสารที่ตรงกับคำค้นหา</td>';
          const tbody = document.querySelector('.admin-table tbody');
          if (tbody) tbody.appendChild(emptyRow);
        }
        emptyRow.style.display = '';
      } else if (emptyRow) {
        emptyRow.style.display = 'none';
      }
    });
  }
})();
