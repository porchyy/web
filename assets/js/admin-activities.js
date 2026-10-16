/**
 * admin-activities.js - Admin Activities Management & Filter Controller
 * Complies with RULES.md (Rule 4.1 & 4.2: External JS & Safe DOM Construction)
 */

(function () {
  'use strict';

  document.addEventListener('DOMContentLoaded', () => {
    initActivityModal();
    initActivitySearchFilter();
  });

  /**
   * 1. Activity Modal Add / Edit Controller
   */
  function initActivityModal() {
    const modalEl = document.getElementById('activityModal');
    if (!modalEl || typeof bootstrap === 'undefined') return;
    const bsModal = new bootstrap.Modal(modalEl);

    const modalTitle = document.getElementById('activityModalLabel');
    const inputId = document.getElementById('act_modal_id');
    const inputTitle = document.getElementById('act_title');
    const inputEventDate = document.getElementById('act_event_date');
    const inputEventDateEnd = document.getElementById('act_event_date_end');
    const inputTimeStart = document.getElementById('act_time_start');
    const inputTimeEnd = document.getElementById('act_time_end');
    const inputLocation = document.getElementById('act_location');
    const inputActionUrl = document.getElementById('act_action_url');
    const inputDesc = document.getElementById('act_description');

    // Open for New
    const btnAdd = document.getElementById('btnOpenAddActivityModal');
    if (btnAdd) {
      btnAdd.addEventListener('click', () => {
        if (modalTitle) modalTitle.textContent = 'เพิ่มกิจกรรมใหม่';
        if (inputId) inputId.value = '';
        if (inputTitle) inputTitle.value = '';
        if (inputEventDate) {
          const todayStr = btnAdd.dataset.today || new Date().toLocaleDateString('en-CA');
          inputEventDate.value = todayStr;
        }
        if (inputEventDateEnd) inputEventDateEnd.value = '';
        if (inputTimeStart) inputTimeStart.value = '09:00';
        if (inputTimeEnd) inputTimeEnd.value = '16:00';
        if (inputLocation) inputLocation.value = '';
        if (inputActionUrl) inputActionUrl.value = '';
        if (inputDesc) inputDesc.value = '';
        bsModal.show();
      });
    }

    // Open for Edit
    document.querySelectorAll('.btn-edit-activity').forEach(btn => {
      btn.addEventListener('click', () => {
        if (modalTitle) modalTitle.textContent = 'แก้ไขกิจกรรม #' + (btn.dataset.id || '');
        if (inputId) inputId.value = btn.dataset.id || '';
        if (inputTitle) inputTitle.value = btn.dataset.title || '';
        if (inputEventDate) inputEventDate.value = btn.dataset.eventDate || '';
        if (inputEventDateEnd) inputEventDateEnd.value = btn.dataset.eventDateEnd || '';
        if (inputTimeStart) inputTimeStart.value = btn.dataset.timeStart || '';
        if (inputTimeEnd) inputTimeEnd.value = btn.dataset.timeEnd || '';
        if (inputLocation) inputLocation.value = btn.dataset.location || '';
        if (inputActionUrl) inputActionUrl.value = btn.dataset.actionUrl || '';
        if (inputDesc) inputDesc.value = btn.dataset.description || '';
        bsModal.show();
      });
    });
  }

  /**
   * 2. Activities List Real-time Search Filter with Autocomplete
   */
  function initActivitySearchFilter() {
    if (typeof AdminSearchAutocomplete !== 'undefined') {
      AdminSearchAutocomplete.init({
        inputSelector: '#actSearchInput',
        clearBtnSelector: '#btnActClearSearch',
        dropdownSelector: '#actAutocompleteList',
        rowSelector: '.activity-list-row',
        emptyRowId: 'actSearchEmptyRow',
        emptyMessage: 'ไม่พบกิจกรรมที่ตรงกับคำค้นหา',
        getItemData: (row) => ({
          title: row.dataset.title || '',
          meta: row.dataset.meta || '',
          searchContent: row.textContent.toLowerCase()
        })
      });
      return;
    }

    // Fallback: Real-time search without autocomplete if module not loaded
    const actSearch = document.getElementById('actSearchInput');
    const rows = document.querySelectorAll('.activity-list-row');
    if (!actSearch || !rows.length) return;

    actSearch.addEventListener('input', function () {
      const q = this.value.trim().toLowerCase();
      let matchCount = 0;
      rows.forEach(tr => {
        const text = tr.textContent.toLowerCase();
        const matches = text.includes(q);
        tr.style.display = matches ? '' : 'none';
        if (matches) matchCount++;
      });

      let emptyRow = document.getElementById('actSearchEmptyRow');
      if (matchCount === 0) {
        if (!emptyRow) {
          emptyRow = document.createElement('tr');
          emptyRow.id = 'actSearchEmptyRow';
          const td = document.createElement('td');
          td.colSpan = 6;
          td.className = 'text-center py-4 text-muted';
          td.textContent = 'ไม่พบกิจกรรมที่ตรงกับคำค้นหา';
          emptyRow.appendChild(td);

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
