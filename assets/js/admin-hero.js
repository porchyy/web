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
        modalTitle.textContent = inputTitle.value || 'พาดหัวหลัก';
      }
      if (modalIntro && inputIntro) {
        modalIntro.textContent = inputIntro.value || '';
      }
      if (modalCaption && inputCaption) {
        modalCaption.textContent = inputCaption.value || 'ภาพถ่ายพื้นที่เรียนรู้และห้องปฏิบัติการคอมพิวเตอร์';
      }
    });
  }

  document.addEventListener('DOMContentLoaded', () => {
    initStatModeToggle();
    initFullPreviewModal();
  });
})();
