/**
 * admin-search-autocomplete.js
 * DBT Admin Reusable Real-Time Search & Autocomplete Controller
 * 
 * Features:
 * - Real-time client-side table row filtering
 * - Dynamic suggestion dropdown matching item titles and meta
 * - Keyword match highlighting with <mark class="sugg-match">
 * - Keyboard navigation: ArrowDown, ArrowUp, Enter to select, Escape to close
 * - Clear button (x) to instantly reset filter and restore table
 * - Clean Code: Guard clauses, early returns, zero external dependencies
 * 
 * Standard: STYLE.md & clean-code
 */

(function (root, factory) {
  if (typeof define === 'function' && define.amd) {
    define([], factory);
  } else if (typeof module === 'object' && module.exports) {
    module.exports = factory();
  } else {
    root.AdminSearchAutocomplete = factory();
  }
})(typeof self !== 'undefined' ? self : this, function () {
  'use strict';

  function renderHighlightedText(container, text, query) {
    container.textContent = '';
    const rawText = text || '';
    if (!query) {
      container.textContent = rawText;
      return;
    }
    const escaped = query.replace(/[.*+?^${}()|[\]\\]/g, '\\$&');
    const regex = new RegExp(`(${escaped})`, 'gi');
    const parts = rawText.split(regex);
    parts.forEach(part => {
      if (!part) return;
      if (part.toLowerCase() === query.toLowerCase()) {
        const mark = document.createElement('mark');
        mark.className = 'sugg-match';
        mark.textContent = part;
        container.appendChild(mark);
      } else {
        container.appendChild(document.createTextNode(part));
      }
    });
  }

  function init(options) {
    const {
      inputSelector,
      clearBtnSelector,
      dropdownSelector,
      rowSelector,
      emptyRowId = 'adminSearchEmptyRow',
      emptyMessage = 'ไม่พบข้อมูลที่ตรงกับคำค้นหา',
      tableSelector = '.admin-table',
      getItemData = (row) => ({
        title: row.dataset.title || row.querySelector('.fw-bold')?.textContent?.trim() || '',
        meta: row.dataset.meta || row.querySelector('.tag')?.textContent?.trim() || '',
        searchContent: row.textContent.toLowerCase()
      })
    } = options;

    const input = document.querySelector(inputSelector);
    if (!input) return null;

    const clearBtn = clearBtnSelector ? document.querySelector(clearBtnSelector) : null;
    const dropdown = dropdownSelector ? document.querySelector(dropdownSelector) : null;
    const table = document.querySelector(tableSelector);
    const tbody = table ? table.querySelector('tbody') : null;

    let activeIndex = -1;
    let currentSuggestions = [];

    function getRows() {
      return Array.from(document.querySelectorAll(rowSelector));
    }

    function closeDropdown() {
      if (!dropdown) return;
      dropdown.hidden = true;
      dropdown.innerHTML = '';
      activeIndex = -1;
      currentSuggestions = [];
      input.setAttribute('aria-expanded', 'false');
      input.removeAttribute('aria-activedescendant');
    }

    function updateActiveOption() {
      if (!dropdown) return;
      const options = dropdown.querySelectorAll('.admin-suggest-item');
      options.forEach((opt, idx) => {
        const isActive = idx === activeIndex;
        opt.classList.toggle('is-active', isActive);
        opt.setAttribute('aria-selected', isActive ? 'true' : 'false');
        if (isActive) {
          input.setAttribute('aria-activedescendant', opt.id);
          opt.scrollIntoView({ block: 'nearest' });
        }
      });
      if (activeIndex === -1) {
        input.removeAttribute('aria-activedescendant');
      }
    }

    function selectSuggestion(item) {
      input.value = item.title;
      closeDropdown();
      applyFilter(item.title);
      updateClearButtonVisibility();
      input.focus();
    }

    function renderDropdown(items, query) {
      if (!dropdown) return;
      currentSuggestions = items;
      activeIndex = -1;
      dropdown.innerHTML = '';

      if (items.length === 0) {
        const emptyLi = document.createElement('li');
        emptyLi.className = 'admin-suggest-empty';
        emptyLi.textContent = emptyMessage;
        dropdown.appendChild(emptyLi);
        dropdown.hidden = false;
        input.setAttribute('aria-expanded', 'true');
        return;
      }

      items.forEach((item, idx) => {
        const li = document.createElement('li');
        li.id = 'admin-sugg-opt-' + idx;
        li.className = 'admin-suggest-item';
        li.setAttribute('role', 'option');
        li.setAttribute('aria-selected', 'false');

        const titleSpan = document.createElement('span');
        titleSpan.className = 'admin-suggest-title';
        renderHighlightedText(titleSpan, item.title, query);

        li.appendChild(titleSpan);

        if (item.meta) {
          const metaSpan = document.createElement('span');
          metaSpan.className = 'admin-suggest-meta';
          metaSpan.textContent = item.meta;
          li.appendChild(metaSpan);
        }

        li.addEventListener('mousedown', function (e) {
          e.preventDefault();
        });

        li.addEventListener('click', function () {
          selectSuggestion(item);
        });

        dropdown.appendChild(li);
      });

      dropdown.hidden = false;
      input.setAttribute('aria-expanded', 'true');
    }

    function updateClearButtonVisibility() {
      if (!clearBtn) return;
      clearBtn.style.display = input.value.trim() !== '' ? 'inline-flex' : 'none';
    }

    function applyFilter(query) {
      const q = query.trim().toLowerCase();
      const rows = getRows();
      let matchCount = 0;
      const matchedItems = [];

      rows.forEach(row => {
        const itemData = getItemData(row);
        const matches = q === '' || itemData.searchContent.includes(q);
        row.style.display = matches ? '' : 'none';

        if (matches) {
          matchCount++;
          if (q !== '' && itemData.title && matchedItems.length < 8) {
            // Deduplicate suggestions by title
            if (!matchedItems.some(i => i.title === itemData.title)) {
              matchedItems.push(itemData);
            }
          }
        }
      });

      // Handle Empty State Row in Table
      let emptyRow = document.getElementById(emptyRowId);
      if (matchCount === 0 && q !== '') {
        if (!emptyRow && tbody) {
          emptyRow = document.createElement('tr');
          emptyRow.id = emptyRowId;
          const colCount = table ? table.querySelectorAll('thead th').length : 6;
          const emptyTd = document.createElement('td');
          emptyTd.colSpan = colCount;
          emptyTd.className = 'text-center py-4 text-muted';
          emptyTd.textContent = emptyMessage;
          emptyRow.appendChild(emptyTd);
          tbody.appendChild(emptyRow);
        }
        if (emptyRow) emptyRow.style.display = '';
      } else if (emptyRow) {
        emptyRow.style.display = 'none';
      }

      return matchedItems;
    }

    // Input Event Handling
    input.addEventListener('input', function () {
      const query = this.value;
      updateClearButtonVisibility();

      if (query.trim() === '') {
        closeDropdown();
        applyFilter('');
        return;
      }

      const matchedItems = applyFilter(query);
      renderDropdown(matchedItems, query.trim());
    });

    // Keyboard Navigation
    input.addEventListener('keydown', function (e) {
      if (dropdown && !dropdown.hidden) {
        if (e.key === 'ArrowDown') {
          e.preventDefault();
          if (currentSuggestions.length > 0) {
            activeIndex = (activeIndex + 1) % currentSuggestions.length;
            updateActiveOption();
          }
          return;
        }

        if (e.key === 'ArrowUp') {
          e.preventDefault();
          if (currentSuggestions.length > 0) {
            activeIndex = (activeIndex - 1 + currentSuggestions.length) % currentSuggestions.length;
            updateActiveOption();
          }
          return;
        }

        if (e.key === 'Enter') {
          if (activeIndex >= 0 && activeIndex < currentSuggestions.length) {
            e.preventDefault();
            selectSuggestion(currentSuggestions[activeIndex]);
            return;
          }
          closeDropdown();
          return;
        }

        if (e.key === 'Escape') {
          e.preventDefault();
          closeDropdown();
          return;
        }
      }

      if (e.key === 'Escape' && input.value.trim() !== '') {
        resetSearch();
      }
    });

    // Outside Click Handling
    document.addEventListener('click', function (e) {
      if (!input.contains(e.target) && (!dropdown || !dropdown.contains(e.target))) {
        closeDropdown();
      }
    });

    // Clear Button Handling
    function resetSearch() {
      input.value = '';
      updateClearButtonVisibility();
      closeDropdown();
      applyFilter('');
      input.focus();
    }

    if (clearBtn) {
      clearBtn.addEventListener('click', resetSearch);
      updateClearButtonVisibility();
    }

    return {
      applyFilter,
      resetSearch,
      closeDropdown
    };
  }

  return { init };
});
