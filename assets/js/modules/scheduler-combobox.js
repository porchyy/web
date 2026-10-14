/**
 * scheduler-combobox.js - Searchable Course Combobox in Slot Modal
 *
 * Provides instant filtering, keyboard navigation (ArrowUp/Down/Enter/Escape),
 * search query highlighting, and field auto-fill for the course picker dropdown.
 */
(function (global) {
  "use strict";

  const ns = (global.DBTScheduler = global.DBTScheduler || {});

  function escapeRegex(str) {
    return str.replace(/[.*+?^${}()|[\]\\]/g, "\\$&");
  }

  /**
   * Highlights occurrences of query within el using safe DOM nodes (no innerHTML injection).
   */
  function highlightText(el, query) {
    const text = el.dataset.originalText || el.textContent;
    if (!query || !query.trim()) {
      el.textContent = text;
      return;
    }
    const escaped = escapeRegex(query.trim());
    let regex;
    try {
      regex = new RegExp(escaped, "gi");
    } catch (_) {
      el.textContent = text;
      return;
    }

    el.textContent = "";
    let lastIndex = 0;
    let match;
    while ((match = regex.exec(text)) !== null) {
      if (match.index > lastIndex) {
        el.appendChild(document.createTextNode(text.substring(lastIndex, match.index)));
      }
      const mark = document.createElement("mark");
      mark.className = "bg-warning text-dark rounded-0 px-0 mx-0";
      mark.textContent = match[0];
      el.appendChild(mark);
      lastIndex = regex.lastIndex;
      if (lastIndex >= text.length) break;
    }
    if (lastIndex < text.length) {
      el.appendChild(document.createTextNode(text.substring(lastIndex)));
    }
  }

  function init(_ctx) {
    const courseSearchInput = document.getElementById("modalCourseSearch");
    const courseComboboxMenu = document.getElementById("courseComboboxMenu");
    const btnToggleCombobox = document.getElementById("btnToggleCourseCombobox");
    const btnClearCombobox = document.getElementById("btnClearCourseSearch");

    if (!courseSearchInput || !courseComboboxMenu) {
      return;
    }

    let highlightedIndex = -1;

    function getVisibleItems() {
      return Array.from(
        courseComboboxMenu.querySelectorAll(".course-combobox-item")
      ).filter((item) => item.style.display !== "none");
    }

    function setHighlighted(index) {
      const items = getVisibleItems();
      items.forEach((it) => it.classList.remove("is-highlighted"));
      if (index >= 0 && index < items.length) {
        highlightedIndex = index;
        items[index].classList.add("is-highlighted");
        items[index].scrollIntoView({ block: "nearest" });
      } else {
        highlightedIndex = -1;
      }
    }

    function showCombobox() {
      courseComboboxMenu.style.display = "block";
    }

    function hideCombobox() {
      courseComboboxMenu.style.display = "none";
      setHighlighted(-1);
    }

    function filterCombobox(q) {
      const query = (q || "").trim();
      const tokens = query.toLowerCase().split(/\s+/).filter(Boolean);
      const items = courseComboboxMenu.querySelectorAll(".course-combobox-item");
      const sectionHeaders = courseComboboxMenu.querySelectorAll(
        ".course-combobox-group-header.section-header"
      );
      const searchHeader = document.getElementById("courseComboboxSearchHeader");
      const noResult = document.getElementById("courseComboboxNoResult");

      if (btnClearCombobox) {
        btnClearCombobox.classList.toggle("d-none", query.length === 0);
      }

      // No query: restore full categorized list
      if (tokens.length === 0) {
        items.forEach((item) => {
          item.style.display = "flex";
          const titleEl = item.querySelector(".course-title");
          if (titleEl && titleEl.dataset.originalText) {
            titleEl.textContent = titleEl.dataset.originalText;
          }
        });

        sectionHeaders.forEach((header) => {
          header.style.display = "";
        });

        if (searchHeader) {
          searchHeader.classList.add("d-none");
        }
        if (noResult) {
          noResult.classList.add("d-none");
        }
        setHighlighted(-1);
        return;
      }

      // Active query: hide section headers and filter matching items
      sectionHeaders.forEach((header) => {
        header.style.display = "none";
      });

      let matchCount = 0;
      items.forEach((item) => {
        const searchTarget = (
          item.getAttribute("data-search") || item.textContent
        ).toLowerCase();
        const matches = tokens.every((tok) => searchTarget.includes(tok));
        item.style.display = matches ? "flex" : "none";
        if (matches) {
          matchCount++;
          const titleEl = item.querySelector(".course-title");
          if (titleEl) {
            if (!titleEl.dataset.originalText) {
              titleEl.dataset.originalText = titleEl.textContent;
            }
            highlightText(titleEl, query);
          }
        }
      });

      if (searchHeader) {
        searchHeader.classList.remove("d-none");
        searchHeader.textContent = `ผลการค้นหา "${query}" (${matchCount} รายวิชา)`;
      }

      if (noResult) {
        noResult.classList.toggle("d-none", matchCount > 0);
        if (matchCount === 0) {
          noResult.textContent = "";
          noResult.appendChild(document.createTextNode('ไม่พบรายวิชาที่ตรงกับ "'));
          const strong = document.createElement("strong");
          strong.textContent = query;
          noResult.appendChild(strong);
          noResult.appendChild(
            document.createTextNode(
              '" — สามารถคลิก "+ กำหนดรหัส/ชื่อวิชาเอง" เพื่อกรอกโดยตรง'
            )
          );
        }
      }

      setHighlighted(matchCount > 0 ? 0 : -1);
    }

    function selectCourseItem(item) {
      if (!item) return;
      const id = item.getAttribute("data-id") || "";
      const code = item.getAttribute("data-code") || "";
      const name = item.getAttribute("data-name") || "";
      const teacher = item.getAttribute("data-teacher") || "";
      const room = item.getAttribute("data-room") || "";

      const clSelect = document.getElementById("modalClassId");
      if (clSelect) {
        clSelect.value = id;
        clSelect.dispatchEvent(new Event("change"));
      }

      courseSearchInput.value = `${code} ${name}`.trim();

      const codeInput = document.getElementById("modalSubjectCode");
      if (codeInput) codeInput.value = code;

      const nameInput = document.getElementById("modalSubjectName");
      if (nameInput) nameInput.value = name;

      const teacherInput = document.getElementById("modalTeacherName");
      if (teacherInput && teacher) {
        teacherInput.value = teacher;
      }

      const roomInput = document.getElementById("modalRoom");
      if (roomInput && room) {
        roomInput.value = room;
      }

      courseComboboxMenu
        .querySelectorAll(".course-combobox-item")
        .forEach((it) => {
          it.classList.toggle("is-selected", it === item);
        });

      hideCombobox();
    }

    // Event Listeners
    courseSearchInput.addEventListener("focus", () => {
      showCombobox();
      filterCombobox(courseSearchInput.value);
      courseSearchInput.select();
    });

    courseSearchInput.addEventListener("input", () => {
      showCombobox();
      filterCombobox(courseSearchInput.value);
    });

    courseSearchInput.addEventListener("keydown", (e) => {
      const isMenuVisible = courseComboboxMenu.style.display === "block";

      if (e.key === "ArrowDown") {
        e.preventDefault();
        if (!isMenuVisible) {
          showCombobox();
          filterCombobox(courseSearchInput.value);
        } else {
          const items = getVisibleItems();
          if (items.length > 0) {
            const nextIdx = (highlightedIndex + 1) % items.length;
            setHighlighted(nextIdx);
          }
        }
      } else if (e.key === "ArrowUp") {
        e.preventDefault();
        if (isMenuVisible) {
          const items = getVisibleItems();
          if (items.length > 0) {
            const prevIdx = (highlightedIndex - 1 + items.length) % items.length;
            setHighlighted(prevIdx);
          }
        }
      } else if (e.key === "Enter") {
        if (isMenuVisible) {
          e.preventDefault();
          const items = getVisibleItems();
          if (highlightedIndex >= 0 && highlightedIndex < items.length) {
            selectCourseItem(items[highlightedIndex]);
          } else if (items.length === 1) {
            selectCourseItem(items[0]);
          }
        }
      } else if (e.key === "Escape") {
        if (isMenuVisible) {
          e.preventDefault();
          hideCombobox();
        }
      }
    });

    if (btnToggleCombobox) {
      btnToggleCombobox.addEventListener("click", (e) => {
        e.preventDefault();
        e.stopPropagation();
        if (courseComboboxMenu.style.display === "block") {
          hideCombobox();
        } else {
          showCombobox();
          filterCombobox(courseSearchInput.value);
          courseSearchInput.focus();
        }
      });
    }

    if (btnClearCombobox) {
      btnClearCombobox.addEventListener("click", (e) => {
        e.preventDefault();
        e.stopPropagation();
        courseSearchInput.value = "";
        courseSearchInput.focus();
        showCombobox();
        filterCombobox("");
      });
    }

    courseComboboxMenu.addEventListener("mousedown", (e) => {
      e.preventDefault();
    });

    courseComboboxMenu.addEventListener("click", (e) => {
      const item = e.target.closest(".course-combobox-item");
      if (item) {
        selectCourseItem(item);
      }
    });

    document.addEventListener("click", (e) => {
      if (!e.target.closest("#courseComboboxWrap")) {
        hideCombobox();
      }
    });
  }

  ns.combobox = { init };
})(window);
