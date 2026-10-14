/**
 * admin-image-upload.js - Modern Interactive Image Dropzone & Live Preview Component
 * 
 * Strict Vanilla JS (Ponytail) - Zero external dependencies.
 * Safe DOM manipulation, robust drag & drop, real-time image preview with revert support.
 */

(function () {
  'use strict';

  function formatShortFileName(name, maxLen = 32) {
    if (typeof name !== 'string' || name.length <= maxLen) return name;
    const extIndex = name.lastIndexOf('.');
    const ext = extIndex !== -1 ? name.slice(extIndex) : '';
    const base = extIndex !== -1 ? name.slice(0, extIndex) : name;
    const availBase = maxLen - ext.length - 3;
    if (availBase <= 4) {
      return name.slice(0, Math.max(1, maxLen - 3)) + '...';
    }
    const front = Math.ceil(availBase / 2);
    const back = Math.floor(availBase / 2);
    return base.slice(0, front) + '...' + base.slice(-back) + ext;
  }

  function initImageUpload(wrapperEl) {
    if (!wrapperEl) return;

    const inputEl = wrapperEl.querySelector('.image-upload-input');
    const dropzoneEl = wrapperEl.querySelector('.image-upload-dropzone');
    const previewContainerEl = wrapperEl.querySelector('.image-upload-preview');
    const fileSelectedBoxEl = wrapperEl.querySelector('.dropzone-file-selected');
    const fileNameEl = wrapperEl.querySelector('.dropzone-file-selected .file-name');
    const fileSizeEl = wrapperEl.querySelector('.dropzone-file-selected .file-size');
    const clearBtnEl = wrapperEl.querySelector('.btn-clear-file');

    if (!inputEl || !dropzoneEl) return;

    // Cache initial state
    const initialSrc = wrapperEl.dataset.currentSrc || '';
    const initialHasImage = Boolean(initialSrc);

    // Click to open file dialog
    dropzoneEl.addEventListener('click', (e) => {
      if (e.target !== inputEl && !e.target.closest('.btn-clear-file')) {
        inputEl.click();
      }
    });

    // Keyboard accessibility
    dropzoneEl.addEventListener('keydown', (e) => {
      if (e.key === 'Enter' || e.key === ' ') {
        e.preventDefault();
        inputEl.click();
      }
    });

    // Drag & Drop
    ['dragenter', 'dragover'].forEach((eventName) => {
      dropzoneEl.addEventListener(eventName, (e) => {
        e.preventDefault();
        e.stopPropagation();
        dropzoneEl.classList.add('is-dragover');
      });
    });

    ['dragleave', 'drop'].forEach((eventName) => {
      dropzoneEl.addEventListener(eventName, (e) => {
        e.preventDefault();
        e.stopPropagation();
        dropzoneEl.classList.remove('is-dragover');
      });
    });

    dropzoneEl.addEventListener('drop', (e) => {
      const files = e.dataTransfer?.files;
      if (files && files.length > 0) {
        processFile(files[0]);
      }
    });

    inputEl.addEventListener('change', () => {
      if (inputEl.files && inputEl.files.length > 0) {
        processFile(inputEl.files[0]);
      }
    });

    if (clearBtnEl) {
      clearBtnEl.addEventListener('click', (e) => {
        e.preventDefault();
        e.stopPropagation();
        revertToInitial();
      });
    }

    function processFile(file) {
      // Validate file type
      const validTypes = ['image/jpeg', 'image/png', 'image/webp'];
      if (!validTypes.includes(file.type)) {
        alert('กรุณาเลือกไฟล์รูปภาพประเภท JPG, PNG หรือ WebP เท่านั้น');
        return;
      }

      // Validate file size (5MB max)
      if (file.size > 5 * 1024 * 1024) {
        alert('ไฟล์มีขนาดใหญ่เกิน 5 MB กรุณาเลือกไฟล์ที่มีขนาดเล็กลง');
        return;
      }

      // Assign to input files if from drag & drop
      if (inputEl.files[0] !== file) {
        try {
          const dt = new DataTransfer();
          dt.items.add(file);
          inputEl.files = dt.files;
        } catch {
          // Fallback for older browsers
        }
      }

      // Show file info chip
      if (fileSelectedBoxEl && fileNameEl) {
        const sizeKb = Math.round(file.size / 1024);
        const sizeStr = sizeKb > 1024 ? (sizeKb / 1024).toFixed(1) + ' MB' : sizeKb + ' KB';
        const shortName = formatShortFileName(file.name, 28);

        fileNameEl.textContent = shortName;
        fileNameEl.title = `${file.name} (${sizeStr})`;

        if (fileSizeEl) {
          fileSizeEl.textContent = `(${sizeStr})`;
        } else {
          fileNameEl.textContent = 'เลือกแล้ว: ' + shortName + ' (' + sizeStr + ')';
        }

        fileSelectedBoxEl.classList.remove('d-none');
      }

      // Live image preview
      const reader = new FileReader();
      reader.onload = (e) => {
        updatePreview(e.target.result, true);
      };
      reader.readAsDataURL(file);
    }

    function updatePreview(src, isNew) {
      if (!previewContainerEl) return;

      let imgEl = previewContainerEl.querySelector('.preview-img');
      let badgeEl = previewContainerEl.querySelector('.preview-badge');
      let placeholderEl = previewContainerEl.querySelector('.preview-placeholder');

      if (placeholderEl) {
        placeholderEl.remove();
      }

      if (!imgEl) {
        imgEl = document.createElement('img');
        imgEl.className = 'preview-img';
        imgEl.alt = 'รูปถ่ายตัวอย่าง';
        if (inputEl.id === 'input-news-image') imgEl.id = 'newsPreviewImg';
        if (inputEl.id === 'hero_image') imgEl.id = 'heroPreview';
        previewContainerEl.appendChild(imgEl);
      }
      imgEl.src = src;

      if (!badgeEl) {
        badgeEl = document.createElement('span');
        badgeEl.className = 'preview-badge';
        previewContainerEl.appendChild(badgeEl);
      }

      if (isNew) {
        badgeEl.className = 'preview-badge badge-new';
        badgeEl.textContent = 'รูปที่เลือกใหม่';
      } else {
        badgeEl.className = 'preview-badge badge-current';
        badgeEl.textContent = 'รูปปัจจุบัน';
      }

      // Update linked modal preview images immediately before saving
      const modalHeroImg = document.getElementById('modalHeroImg');
      if (modalHeroImg && (inputEl.id === 'hero_image' || wrapperEl.closest('#form-hero-settings'))) {
        modalHeroImg.src = src;
      }
      const modalNewsImg = document.getElementById('modalNewsImg');
      if (modalNewsImg && (inputEl.id === 'input-news-image' || wrapperEl.closest('form'))) {
        modalNewsImg.src = src;
        const modalFigWrap = document.getElementById('modalNewsFigWrap');
        if (modalFigWrap) modalFigWrap.style.display = 'block';
      }

      wrapperEl.dispatchEvent(new CustomEvent('image:updated', { detail: { src: src, isNew: isNew } }));
    }

    function revertToInitial() {
      inputEl.value = '';
      if (fileSelectedBoxEl) {
        fileSelectedBoxEl.classList.add('d-none');
      }
      if (fileNameEl) {
        fileNameEl.textContent = '';
        fileNameEl.removeAttribute('title');
      }
      if (fileSizeEl) {
        fileSizeEl.textContent = '';
      }

      if (initialHasImage) {
        updatePreview(initialSrc, false);
      } else {
        // Reset to placeholder
        if (previewContainerEl) {
          previewContainerEl.innerHTML = `
            <div class="preview-placeholder">
              <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                <circle cx="12" cy="8" r="4"/><path d="M4 21a8 8 0 0 1 16 0"/>
              </svg>
              <span>ยังไม่มีรูปภาพ</span>
            </div>
          `;
        }
      }

      // Revert linked modal preview images
      const modalHeroImg = document.getElementById('modalHeroImg');
      if (modalHeroImg && (inputEl.id === 'hero_image' || wrapperEl.closest('#form-hero-settings'))) {
        modalHeroImg.src = initialSrc;
      }
      const modalNewsImg = document.getElementById('modalNewsImg');
      if (modalNewsImg && (inputEl.id === 'input-news-image' || wrapperEl.closest('form'))) {
        modalNewsImg.src = initialSrc;
        const modalFigWrap = document.getElementById('modalNewsFigWrap');
        if (modalFigWrap) modalFigWrap.style.display = initialHasImage ? 'block' : 'none';
      }

      wrapperEl.dispatchEvent(new CustomEvent('image:reverted', { detail: { initialSrc: initialSrc } }));
    }
  }

  document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('[data-image-upload]').forEach(initImageUpload);
  });
})();
