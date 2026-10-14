/**
 * admin-hero.js - Hero Management Client Controller
 * Standard: STYLE.md & RULES.md
 */

(function () {
  'use strict';

  function initStatModeToggle() {
    const dynamicRadioEl = document.getElementById('modeDynamic');
    const manualRadioEl = document.getElementById('modeManual');
    const manualInputsEl = document.getElementById('stat-manual-inputs');

    if (!dynamicRadioEl || !manualRadioEl || !manualInputsEl) return;

    function updateVisibility() {
      if (manualRadioEl.checked) {
        manualInputsEl.style.display = 'flex';
        manualInputsEl.classList.remove('d-none');
      } else {
        manualInputsEl.style.display = 'none';
        manualInputsEl.classList.add('d-none');
      }
    }

    dynamicRadioEl.addEventListener('change', updateVisibility);
    manualRadioEl.addEventListener('change', updateVisibility);
    updateVisibility();
  }

  function initFileDropzone() {
    const dropzoneEl = document.querySelector('.image-upload-dropzone');
    const fileInputEl = document.getElementById('hero_image');
    const previewImgEl = document.getElementById('heroPreview');
    const modalImgEl = document.getElementById('modalHeroImg');
    const selectedBadgeEl = document.querySelector('.dropzone-file-selected');
    const selectedNameEl = selectedBadgeEl?.querySelector('.file-name');
    const clearBtn = selectedBadgeEl?.querySelector('.btn-clear-file');

    if (!fileInputEl) return;

    if (dropzoneEl) {
      dropzoneEl.addEventListener('click', (e) => {
        if (e.target !== fileInputEl && e.target !== clearBtn) {
          fileInputEl.click();
        }
      });

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
        const files = e.dataTransfer.files;
        if (files && files.length > 0) {
          fileInputEl.files = files;
          handleFile(files[0]);
        }
      });
    }

    fileInputEl.addEventListener('change', () => {
      if (fileInputEl.files && fileInputEl.files.length > 0) {
        handleFile(fileInputEl.files[0]);
      }
    });

    if (clearBtn) {
      clearBtn.addEventListener('click', (e) => {
        e.stopPropagation();
        fileInputEl.value = '';
        if (selectedBadgeEl) selectedBadgeEl.classList.add('d-none');
      });
    }

    function handleFile(file) {
      if (selectedBadgeEl && selectedNameEl) {
        selectedNameEl.textContent = file.name;
        selectedBadgeEl.classList.remove('d-none');
      }

      if (file.type.startsWith('image/')) {
        const reader = new FileReader();
        reader.onload = (e) => {
          if (previewImgEl) previewImgEl.src = e.target.result;
          if (modalImgEl) modalImgEl.src = e.target.result;
        };
        reader.readAsDataURL(file);
      }
    }
  }

  function initFullPreviewModal() {
    const modalEl = document.getElementById('heroPreviewModal');
    if (!modalEl) return;

    const frameEl = document.getElementById('heroPreviewFrame');
    const btnDesktop = document.getElementById('btnPreviewDesktop');
    const btnMobile = document.getElementById('btnPreviewMobile');

    const inputTitle = document.getElementById('hero_title');
    const inputIntro = document.getElementById('hero_intro');
    const inputCaption = document.getElementById('hero_caption');

    const modalTitle = document.getElementById('modalHeroTitle');
    const modalIntro = document.getElementById('modalHeroIntro');
    const modalCaption = document.getElementById('modalHeroCaption');

    // Viewport Toggle
    if (btnDesktop && btnMobile && frameEl) {
      btnDesktop.addEventListener('click', () => {
        btnDesktop.classList.add('is-active');
        btnMobile.classList.remove('is-active');
        frameEl.classList.remove('is-mobile');
        frameEl.classList.add('is-desktop');
      });

      btnMobile.addEventListener('click', () => {
        btnMobile.classList.add('is-active');
        btnDesktop.classList.remove('is-active');
        frameEl.classList.remove('is-desktop');
        frameEl.classList.add('is-mobile');
      });
    }

    // Sync content on modal show
    modalEl.addEventListener('show.bs.modal', () => {
      if (modalTitle && inputTitle) {
        modalTitle.innerHTML = inputTitle.value || 'พาดหัวหลัก';
      }
      if (modalIntro && inputIntro) {
        const introVal = inputIntro.value || '';
        modalIntro.innerHTML = introVal;
      }
      if (modalCaption && inputCaption) {
        modalCaption.textContent = inputCaption.value || 'ภาพถ่ายพื้นที่เรียนรู้และห้องปฏิบัติการคอมพิวเตอร์';
      }
    });
  }

  document.addEventListener('DOMContentLoaded', () => {
    initStatModeToggle();
    initFileDropzone();
    initFullPreviewModal();
  });
})();
