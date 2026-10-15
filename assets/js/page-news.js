/**
 * page-news.js - Public News Page Client Controller
 * Handles autocomplete search suggestions, keyboard navigation, and dropdown state
 */
(function () {
  'use strict';

  document.addEventListener('DOMContentLoaded', () => {
    const form = document.getElementById('news-search-form');
    const input = document.getElementById('news-search-input');
    const list = document.getElementById('news-autocomplete-list');
    const catInput = document.getElementById('news-search-cat');
    if (!form || !input || !list) return;

    const endpoint = form.dataset.endpoint || '/api/news-search.php';
    let activeIndex = -1;
    let debounceTimer = null;
    let abortCtrl = null;
    let currentItems = [];

    function renderHighlightedText(container, text, query) {
      container.textContent = '';
      const rawText = text || '';
      if (!query) {
        container.textContent = rawText;
        return;
      }
      const escaped = query.replace(/[.*+?^${}()|[\]\\]/g, '\\$&');
      const parts = rawText.split(new RegExp(`(${escaped})`, 'gi'));
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

    function closeDropdown() {
      list.hidden = true;
      list.innerHTML = '';
      activeIndex = -1;
      currentItems = [];
      input.setAttribute('aria-expanded', 'false');
      input.removeAttribute('aria-activedescendant');
    }

    function openDropdown() {
      if (currentItems.length > 0) {
        list.hidden = false;
        input.setAttribute('aria-expanded', 'true');
      }
    }

    function updateActiveOption() {
      const options = list.querySelectorAll('.news-suggest-item');
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

    function selectItem(title) {
      input.value = title;
      closeDropdown();
      form.submit();
    }

    function renderSuggestions(items, query) {
      currentItems = items;
      activeIndex = -1;
      list.innerHTML = '';

      if (!items || items.length === 0) {
        const empty = document.createElement('li');
        empty.className = 'news-suggest-empty';
        empty.textContent = 'ไม่พบข่าวที่ตรงกับคำค้นหา';
        list.appendChild(empty);
        list.hidden = false;
        input.setAttribute('aria-expanded', 'true');
        return;
      }

      items.forEach((item, idx) => {
        const li = document.createElement('li');
        li.id = 'sugg-opt-' + idx;
        li.className = 'news-suggest-item';
        li.setAttribute('role', 'option');
        li.setAttribute('aria-selected', 'false');
        li.dataset.title = item.title;

        const titleSpan = document.createElement('span');
        titleSpan.className = 'news-suggest-title';
        renderHighlightedText(titleSpan, item.title, query);

        const catSpan = document.createElement('span');
        catSpan.className = 'news-suggest-cat';
        catSpan.textContent = item.category_name;

        li.appendChild(titleSpan);
        li.appendChild(catSpan);

        li.addEventListener('mousedown', function (e) {
          e.preventDefault();
        });

        li.addEventListener('click', function () {
          selectItem(item.title);
        });

        list.appendChild(li);
      });

      list.hidden = false;
      input.setAttribute('aria-expanded', 'true');
    }

    function fetchSuggestions(query) {
      if (abortCtrl) {
        abortCtrl.abort();
      }
      abortCtrl = new AbortController();

      const cat = catInput ? catInput.value : '';
      const url = new URL(endpoint, window.location.origin);
      url.searchParams.set('q', query);
      if (cat) {
        url.searchParams.set('cat', cat);
      }

      fetch(url.toString(), {
        signal: abortCtrl.signal,
        headers: { 'Accept': 'application/json' }
      })
        .then(res => res.json())
        .then(data => {
          if (data && data.success) {
            renderSuggestions(data.items, query);
          } else {
            closeDropdown();
          }
        })
        .catch(err => {
          if (err.name !== 'AbortError') {
            closeDropdown();
          }
        });
    }

    input.addEventListener('input', function () {
      const val = this.value.trim();
      clearTimeout(debounceTimer);

      if (val.length < 1) {
        closeDropdown();
        return;
      }

      debounceTimer = setTimeout(() => {
        fetchSuggestions(val);
      }, 200);
    });

    input.addEventListener('keydown', function (e) {
      const options = list.querySelectorAll('.news-suggest-item');
      if (!options.length || list.hidden) {
        if (e.key === 'ArrowDown' && input.value.trim().length >= 1) {
          fetchSuggestions(input.value.trim());
        }
        return;
      }

      if (e.key === 'ArrowDown') {
        e.preventDefault();
        activeIndex = (activeIndex + 1) % options.length;
        updateActiveOption();
      } else if (e.key === 'ArrowUp') {
        e.preventDefault();
        activeIndex = activeIndex <= 0 ? options.length - 1 : activeIndex - 1;
        updateActiveOption();
      } else if (e.key === 'Enter') {
        if (activeIndex >= 0 && options[activeIndex]) {
          e.preventDefault();
          selectItem(options[activeIndex].dataset.title);
        }
      } else if (e.key === 'Escape') {
        closeDropdown();
      }
    });

    input.addEventListener('focus', function () {
      if (currentItems.length > 0) {
        openDropdown();
      }
    });

    document.addEventListener('click', function (e) {
      if (!form.contains(e.target)) {
        closeDropdown();
      }
    });
  });
})();
