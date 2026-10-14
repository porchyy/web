/**
 * admin-image-upload.js - Modern Interactive Image Dropzone & Live Preview Component
 * 
 * Strict Vanilla JS (Ponytail) - Zero external dependencies.
 * Safe DOM manipulation, robust drag & drop, real-time image preview with revert support.
 */

(function () {
  'use strict';

  function initImageUpload(wrapperEl) {
    if (!wrapperEl) return;

    const inputEl = wrapperEl.querySelector('.image-upload-input');
    const dropzoneEl = wrapperEl.querySelector('.image-upload-dropzone');
    const previewContainerEl = wrapperEl.querySelector('.image-upload-preview');
    const fileSelectedBoxEl = wrapperEl.querySelector('.dropzone-file-selected');
    const fileNameEl = wrapperEl.querySelector('.dropzone-file-selected .file-name');
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
        fileNameEl.textContent = 'เลือกแล้ว: ' + file.name + ' (' + sizeStr + ')';
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
    }

    function revertToInitial() {
      inputEl.value = '';
      if (fileSelectedBoxEl) {
        fileSelectedBoxEl.classList.add('d-none');
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
              <span>ยังไม่มีรูปถ่าย</span>
            </div>
          `;
        }
      }
    }
  }

  document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('[data-image-upload]').forEach(initImageUpload);
  });
})();
