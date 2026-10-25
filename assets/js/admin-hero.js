/**
 * admin-hero.js - Hero Management Client Controller
 * Standard: STYLE.md & RULES.md
 */

(function () {
  'use strict';



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
      const render = window.renderSafeHtml || (window.DBTQuillParser && window.DBTQuillParser.renderSafe);

      if (modalTitle && inputTitle) {
        if (typeof render === 'function') {
          render(modalTitle, inputTitle.value, { isTitle: true, fallback: 'พาดหัวหลัก' });
        } else {
          while (modalTitle.firstChild) modalTitle.removeChild(modalTitle.firstChild);
          const parts = (inputTitle.value || 'พาดหัวหลัก').split(/<br\s*\/?>/i);
          parts.forEach((part, idx) => {
            if (idx > 0) modalTitle.appendChild(document.createElement('br'));
            modalTitle.appendChild(document.createTextNode(part));
          });
        }
      }

      if (modalIntro && inputIntro) {
        if (typeof render === 'function') {
          render(modalIntro, inputIntro.value, { isTitle: false, fallback: '' });
        } else {
          modalIntro.textContent = inputIntro.value || '';
        }
      }

      if (modalCaption && inputCaption) {
        modalCaption.textContent = inputCaption.value || 'ภาพถ่ายพื้นที่เรียนรู้และห้องปฏิบัติการคอมพิวเตอร์';
      }

      // Sync modal hero image from live preview if available
      const modalHeroImg = document.getElementById('modalHeroImg');
      if (modalHeroImg) {
        const livePreviewImg = document.querySelector('.image-upload-preview .preview-img');
        if (livePreviewImg && livePreviewImg.src) {
          modalHeroImg.src = livePreviewImg.src;
        }
      }
    });
  }

  document.addEventListener('DOMContentLoaded', () => {
    initFullPreviewModal();
  });
})();
