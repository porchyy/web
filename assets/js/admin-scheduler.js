/**
 * DBT Admin Visual Scheduler - Client-side Interactive Module
 *
 * Features:
 * - HTML5 Drag & Drop with multi-slot shadow projection
 * - Real-time Edge Resize with column-snapping preview
 * - Safe Native DOM element creation (Zero raw innerHTML injection)
 * - Zero page reload background sync via JSON / FormData APIs
 *
 * Complies with RULES.md and STYLE.md standards.
 */

document.addEventListener("DOMContentLoaded", () => {
  const gridEl = document.getElementById("schedulerGrid");

  const csrfToken =
    (gridEl ? gridEl.getAttribute("data-csrf") : "") ||
    document.querySelector('input[name="csrf_token"]')?.value ||
    "";
  const currentGroup =
    (gridEl ? gridEl.getAttribute("data-group") : "") || "ปวช.2/1";
  const apiEndpoint =
    (gridEl ? gridEl.getAttribute("data-endpoint") : "") ||
    window.location.pathname;

  let periodsData = [];
  if (gridEl) {
    try {
      const rawPeriods = gridEl.getAttribute("data-periods");
      periodsData = rawPeriods ? JSON.parse(rawPeriods) : [];
    } catch (e) {
      periodsData = [];
    }
  }

  // --------------------------------------------------------------------------
  // Safe Toast Notification (Native DOM Construction - Zero innerHTML injection)
  // --------------------------------------------------------------------------
  function showToast(message, isError = false) {
    const existingToastEl = document.querySelector(".toast-msg");
    if (existingToastEl) {
      existingToastEl.remove();
    }

    const toastEl = document.createElement("div");
    toastEl.className = "toast-msg " + (isError ? "is-error" : "is-success");

    const iconEl = document.createElement("span");
    iconEl.className = "toast-icon";
    iconEl.textContent = isError ? "⚠️" : "✓";

    const textEl = document.createElement("span");
    textEl.className = "toast-text";
    textEl.textContent = message;

    const closeBtnEl = document.createElement("button");
    closeBtnEl.type = "button";
    closeBtnEl.className = "toast-close";
    closeBtnEl.setAttribute("aria-label", "ปิด");
    closeBtnEl.style.cssText =
      "background:none;border:none;color:inherit;font-size:16px;line-height:1;cursor:pointer;padding:0 0 0 10px;opacity:0.75;";
    closeBtnEl.textContent = "×";
    closeBtnEl.addEventListener("click", () => toastEl.remove());

    toastEl.appendChild(iconEl);
    toastEl.appendChild(textEl);
    toastEl.appendChild(closeBtnEl);
    document.body.appendChild(toastEl);

    const duration = isError ? 5000 : 3500;
    setTimeout(() => {
      if (document.body.contains(toastEl)) {
        toastEl.style.opacity = "0";
        toastEl.style.transition = "opacity 0.3s ease";
        setTimeout(() => toastEl.remove(), 300);
      }
    }, duration);
  }

  // --------------------------------------------------------------------------
  // Debounced API Synchronization
  // --------------------------------------------------------------------------
  let isUpdating = false;
  let updateDebounceTimer = null;

  function debouncedSlotUpdate(updateData, delay = 80) {
    clearTimeout(updateDebounceTimer);
    updateDebounceTimer = setTimeout(() => {
      sendSlotUpdate(updateData);
    }, delay);
  }

  async function sendSlotUpdate(updateData) {
    if (isUpdating) return;
    isUpdating = true;

    const formData = new FormData();
    formData.append("csrf_token", csrfToken);
    formData.append("class_group", currentGroup);
    formData.append("sub_action", "ajax_update_slot");
    formData.append("slot_id", updateData.slotId);
    formData.append("day_of_week", updateData.day);
    formData.append("time_start", updateData.timeStart);
    formData.append("time_end", updateData.timeEnd);

    try {
      const resp = await fetch(apiEndpoint, {
        method: "POST",
        headers: { "X-Requested-With": "XMLHttpRequest" },
        body: formData,
      });
      const data = await resp.json();
      if (!resp.ok || !data.success) {
        throw new Error(data.error || "เกิดข้อผิดพลาดในการย้ายคาบเรียน");
      }
      showToast(data.message || "อัปเดตตำแหน่งสำเร็จ");

      // Update periodsData array cache if returned from server
      if (data.periods && Array.isArray(data.periods)) {
        periodsData = data.periods;
      }

      // In-place dynamic DOM update (Zero page reload)
      const tbodyEl = gridEl.querySelector("tbody");
      if (data.tbody_html && tbodyEl) {
        tbodyEl.innerHTML = data.tbody_html;
      }
    } catch (err) {
      showToast(err.message, true);
    } finally {
      isUpdating = false;
    }
  }

  if (gridEl) {
    // --------------------------------------------------------------------------
    // Unified Drag & Drop Engine (HTML5 Desktop + Pointer/Touch Mobile)
    // --------------------------------------------------------------------------
    let draggedCardEl = null;
    let dragShadowEl = null;
    let pendingDropUpdate = null;
    let touchGhostEl = null;
    let touchDragState = null;

    // Clear hover preview without terminating the active drag session
    function clearHoverPreview() {
      pendingDropUpdate = null;
      if (dragShadowEl) {
        dragShadowEl.remove();
        dragShadowEl = null;
      }
      document
        .querySelectorAll(
          ".admin-slot-empty.drag-over-origin, .admin-slot-empty.drag-over-span, .admin-slot-empty.drag-over-invalid",
        )
        .forEach((el) => {
          el.classList.remove(
            "drag-over-origin",
            "drag-over-span",
            "drag-over-invalid",
          );
        });
    }

    // Cleanup all drag state and elements when drag finishes or is cancelled
    function cleanupAllDrag() {
      gridEl.classList.remove("is-dragging-active");
      clearHoverPreview();
      if (touchGhostEl) {
        touchGhostEl.remove();
        touchGhostEl = null;
      }
      if (draggedCardEl) {
        draggedCardEl.style.pointerEvents = "";
        draggedCardEl.classList.remove("is-dragging");
        draggedCardEl = null;
      }
      touchDragState = null;
    }

    function handleDragComplete() {
      if (pendingDropUpdate) {
        const update = pendingDropUpdate;
        pendingDropUpdate = null;
        debouncedSlotUpdate(update);
      }
      cleanupAllDrag();
    }

    function updateDragHoverState(clientX, clientY) {
      if (!draggedCardEl) return;

      // 1. Detect target row (day) via elementFromPoint or vertical position
      let trEl = null;
      const elUnderPoint = document.elementFromPoint(clientX, clientY);

      if (elUnderPoint) {
        const tdEl = elUnderPoint.closest("#schedulerGrid tbody td");
        if (tdEl) {
          trEl = tdEl.closest("tr");
        }
      }

      // Fallback: detect row by vertical position (with generous tolerance for touch fingers)
      if (!trEl) {
        const trs = Array.from(gridEl.querySelectorAll("tbody tr"));
        for (const tr of trs) {
          const r = tr.getBoundingClientRect();
          if (clientY >= r.top - 16 && clientY <= r.bottom + 16) {
            trEl = tr;
            break;
          }
        }
      }

      if (!trEl) {
        clearHoverPreview();
        return;
      }

      // 2. Direct column hit test: calculate period column index strictly from clientX
      // against thead th column coordinates (avoids locking onto multi-colspan card's start period)
      const ths = Array.from(gridEl.querySelectorAll("thead th:not(.day-col)"));
      if (ths.length === 0) return;

      let targetPeriodIndex = -1;
      for (let i = 0; i < ths.length; i++) {
        const rect = ths[i].getBoundingClientRect();
        if (clientX >= rect.left && clientX <= rect.right) {
          targetPeriodIndex = i;
          break;
        }
      }
      if (targetPeriodIndex === -1) {
        if (clientX < ths[0].getBoundingClientRect().left)
          targetPeriodIndex = 0;
        else if (clientX > ths[ths.length - 1].getBoundingClientRect().right)
          targetPeriodIndex = ths.length - 1;
      }

      if (targetPeriodIndex < 0 || targetPeriodIndex >= periodsData.length) {
        clearHoverPreview();
        return;
      }

      const startPIdx = targetPeriodIndex;
      const span = parseInt(draggedCardEl.getAttribute("data-span"), 10) || 1;
      const endPIdx = Math.min(periodsData.length - 1, startPIdx + span - 1);
      const targetStart = periodsData[startPIdx].start;
      const targetEnd = periodsData[endPIdx].end;
      const draggedSlotId = draggedCardEl.getAttribute("data-slot-id");
      const targetDay =
        trEl.getAttribute("data-day") ||
        draggedCardEl.getAttribute("data-day") ||
        "1";

      // Conflict check on target row
      let hasConflict = false;
      let conflictName = "";
      trEl.querySelectorAll(".admin-slot-card").forEach((c) => {
        if (c.getAttribute("data-slot-id") === draggedSlotId) {
          return; // Allow overlap with self
        }
        const cStart = c.getAttribute("data-time-start");
        const cEnd = c.getAttribute("data-time-end");
        if (cStart && cEnd) {
          if (cStart < targetEnd && cEnd > targetStart) {
            hasConflict = true;
            const titleEl = c.querySelector(".subject-name");
            conflictName = titleEl
              ? titleEl.textContent.trim()
              : "คาบเรียนอื่น";
          }
        }
      });

      const oldDay = draggedCardEl.getAttribute("data-day");
      const oldStart = draggedCardEl.getAttribute("data-time-start");
      const oldEnd = draggedCardEl.getAttribute("data-time-end");

      if (
        !hasConflict &&
        (targetDay !== oldDay ||
          targetStart !== oldStart ||
          targetEnd !== oldEnd)
      ) {
        pendingDropUpdate = {
          slotId: draggedSlotId,
          day: targetDay,
          timeStart: targetStart,
          timeEnd: targetEnd,
        };
      } else {
        pendingDropUpdate = null;
      }

      // Render multi-slot shadow box
      if (ths[startPIdx] && ths[endPIdx]) {
        const leftColRect = ths[startPIdx].getBoundingClientRect();
        const rightColRect = ths[endPIdx].getBoundingClientRect();
        const trRect = trEl.getBoundingClientRect();

        if (!dragShadowEl) {
          dragShadowEl = document.createElement("div");
          dragShadowEl.className = "drag-drop-preview";
          const badgeEl = document.createElement("span");
          badgeEl.className = "drag-drop-preview-badge";
          dragShadowEl.appendChild(badgeEl);
          document.body.appendChild(dragShadowEl);
        }

        dragShadowEl.style.left = leftColRect.left + "px";
        dragShadowEl.style.width =
          Math.max(20, rightColRect.right - leftColRect.left) + "px";
        dragShadowEl.style.top = trRect.top + 3 + "px";
        dragShadowEl.style.height = trRect.height - 6 + "px";

        const curSpan = endPIdx - startPIdx + 1;
        const badgeEl = dragShadowEl.querySelector(".drag-drop-preview-badge");
        if (hasConflict) {
          dragShadowEl.classList.add("is-conflict");
          if (badgeEl) badgeEl.textContent = `⚠️ ซ้อนทับกับ ${conflictName}`;
        } else {
          dragShadowEl.classList.remove("is-conflict");
          if (badgeEl)
            badgeEl.textContent = `${targetStart} – ${targetEnd} (${curSpan} คาบ)`;
        }
      }

      // Highlight slots in active row
      document
        .querySelectorAll(
          ".admin-slot-empty.drag-over-origin, .admin-slot-empty.drag-over-span, .admin-slot-empty.drag-over-invalid",
        )
        .forEach((cell) => {
          if (cell.closest("tr") !== trEl) {
            cell.classList.remove(
              "drag-over-origin",
              "drag-over-span",
              "drag-over-invalid",
            );
          }
        });

      trEl.querySelectorAll(".admin-slot-empty").forEach((cell) => {
        const pNum = parseInt(cell.getAttribute("data-period-num"), 10);
        const pIdx = periodsData.findIndex((p) => parseInt(p.num, 10) === pNum);
        if (pIdx === startPIdx) {
          cell.classList.toggle("drag-over-origin", !hasConflict);
          cell.classList.toggle("drag-over-invalid", hasConflict);
          cell.classList.remove("drag-over-span");
        } else if (pIdx > startPIdx && pIdx <= endPIdx) {
          cell.classList.toggle("drag-over-span", !hasConflict);
          cell.classList.toggle("drag-over-invalid", hasConflict);
          cell.classList.remove("drag-over-origin");
        } else {
          cell.classList.remove(
            "drag-over-origin",
            "drag-over-span",
            "drag-over-invalid",
          );
        }
      });
    }

    // Edge auto-scrolling helper on mobile/scrollable viewports
    function checkEdgeAutoScroll(clientX) {
      const wrapEl = gridEl.closest(".timetable-admin-wrap");
      if (!wrapEl) return;
      const wrapRect = wrapEl.getBoundingClientRect();
      const edgeZone = 60;
      if (clientX < wrapRect.left + edgeZone) {
        const factor = (wrapRect.left + edgeZone - clientX) / edgeZone;
        wrapEl.scrollLeft -= Math.max(6, Math.round(factor * 18));
      } else if (clientX > wrapRect.right - edgeZone) {
        const factor = (clientX - (wrapRect.right - edgeZone)) / edgeZone;
        wrapEl.scrollLeft += Math.max(6, Math.round(factor * 18));
      }
    }

    // --------------------------------------------------------------------------
    // Desktop HTML5 Drag & Drop
    // --------------------------------------------------------------------------
    gridEl.addEventListener("dragstart", (e) => {
      // Prevent drag if clicking resize handles or action buttons
      if (
        e.target.closest(".slot-resize-handle") ||
        e.target.closest(".card-actions") ||
        e.target.closest(".btn-overlay-action") ||
        e.target.closest("button")
      ) {
        e.preventDefault();
        return;
      }
      const cardEl = e.target.closest(".admin-slot-card");
      if (!cardEl) return;

      draggedCardEl = cardEl;
      pendingDropUpdate = null;
      gridEl.classList.add("is-dragging-active");
      cardEl.classList.add("is-dragging");

      setTimeout(() => {
        if (draggedCardEl) {
          draggedCardEl.style.pointerEvents = "none";
        }
      }, 0);

      e.dataTransfer.effectAllowed = "move";
      e.dataTransfer.setData(
        "text/plain",
        cardEl.getAttribute("data-slot-id") ||
          cardEl.getAttribute("data-period-num") ||
          "",
      );
    });

    gridEl.addEventListener("dragover", (e) => {
      if (!draggedCardEl) return;
      e.preventDefault();
      e.dataTransfer.dropEffect = "move";
      updateDragHoverState(e.clientX, e.clientY);
      checkEdgeAutoScroll(e.clientX);
    });

    gridEl.addEventListener("dragleave", (e) => {
      const related = e.relatedTarget;
      if (!related || !gridEl.contains(related)) {
        clearHoverPreview();
      }
    });

    gridEl.addEventListener("drop", (e) => {
      e.preventDefault();
      handleDragComplete();
    });

    gridEl.addEventListener("dragend", handleDragComplete);
    window.addEventListener("dragend", handleDragComplete);

    window.addEventListener("keydown", (e) => {
      if (e.key === "Escape") {
        cleanupAllDrag();
      }
    });

    // --------------------------------------------------------------------------
    // Mobile / Touch Drag & Drop (Touch Events with Full iOS/Android Support)
    // --------------------------------------------------------------------------
    gridEl.addEventListener(
      "touchstart",
      (e) => {
        if (e.touches.length > 1) return;
        // Ignore resize handles and action buttons
        if (
          e.target.closest(".slot-resize-handle") ||
          e.target.closest(".btn-overlay-action") ||
          e.target.closest("button") ||
          e.target.closest("form")
        ) {
          return;
        }

        const cardEl = e.target.closest(".admin-slot-card");
        if (!cardEl) {
          document
            .querySelectorAll(".admin-slot-card.is-active-touch")
            .forEach((c) => c.classList.remove("is-active-touch"));
          return;
        }

        // Only initiate touch drag when touching the dedicated drag handle so body scrolling remains smooth
        const isDragHandle = Boolean(e.target.closest(".slot-drag-handle"));
        if (!isDragHandle) {
          return;
        }

        const touch = e.touches[0];
        touchDragState = {
          cardEl: cardEl,
          startX: touch.clientX,
          startY: touch.clientY,
          lastX: touch.clientX,
          lastY: touch.clientY,
          isDragging: false,
        };
      },
      { passive: true },
    );

    window.addEventListener(
      "touchmove",
      (e) => {
        if (!touchDragState) return;
        const touch = e.touches[0];
        touchDragState.lastX = touch.clientX;
        touchDragState.lastY = touch.clientY;
        const dx = touch.clientX - touchDragState.startX;
        const dy = touch.clientY - touchDragState.startY;

        if (!touchDragState.isDragging) {
          if (Math.hypot(dx, dy) > 7) {
            touchDragState.isDragging = true;
            draggedCardEl = touchDragState.cardEl;
            pendingDropUpdate = null;
            gridEl.classList.add("is-dragging-active");
            draggedCardEl.classList.add("is-dragging");

            // Create floating ghost clone
            const cardRect = draggedCardEl.getBoundingClientRect();
            touchGhostEl = draggedCardEl.cloneNode(true);
            touchGhostEl.className = "touch-drag-ghost";
            touchGhostEl
              .querySelectorAll(".slot-resize-handle, .slot-hover-overlay")
              .forEach((el) => el.remove());
            touchGhostEl.style.width = cardRect.width + "px";
            touchGhostEl.style.height = cardRect.height + "px";
            touchGhostEl.style.left = touch.clientX - cardRect.width / 2 + "px";
            touchGhostEl.style.top = touch.clientY - 45 + "px";
            document.body.appendChild(touchGhostEl);

            document
              .querySelectorAll(".admin-slot-card.is-active-touch")
              .forEach((c) => c.classList.remove("is-active-touch"));
          }
        }

        if (touchDragState.isDragging) {
          e.preventDefault(); // Stop native page scroll during card drag
          if (touchGhostEl) {
            const ghostWidth = parseFloat(touchGhostEl.style.width) || 120;
            touchGhostEl.style.left = touch.clientX - ghostWidth / 2 + "px";
            touchGhostEl.style.top = touch.clientY - 45 + "px";
          }
          updateDragHoverState(touch.clientX, touch.clientY);
          checkEdgeAutoScroll(touch.clientX);
        }
      },
      { passive: false },
    );

    window.addEventListener("touchend", (e) => {
      if (!touchDragState) return;

      if (touchDragState.isDragging) {
        e.preventDefault();
        handleDragComplete();
        return;
      }

      // Tap (< 7px movement): toggle blunt overlay for mobile actions
      const targetCard = touchDragState.cardEl;
      const wasActive = targetCard.classList.contains("is-active-touch");
      document
        .querySelectorAll(".admin-slot-card.is-active-touch")
        .forEach((c) => c.classList.remove("is-active-touch"));
      if (!wasActive) {
        targetCard.classList.add("is-active-touch");
      }
      cleanupAllDrag();
    });

    window.addEventListener("touchcancel", () => {
      cleanupAllDrag();
    });

    // --------------------------------------------------------------------------
    // Edge Drag-to-Resize with Column Snapping Preview
    // --------------------------------------------------------------------------
    let resizingState = null;

    gridEl.addEventListener("pointerdown", (e) => {
      const handleEl = e.target.closest(".slot-resize-handle");
      if (!handleEl) return;

      e.preventDefault();
      e.stopPropagation();

      const cardEl = handleEl.closest(".admin-slot-card");
      if (!cardEl) return;

      const side = handleEl.getAttribute("data-side") || "right";
      const slotId = cardEl.getAttribute("data-slot-id");
      const day = cardEl.getAttribute("data-day");
      const start = cardEl.getAttribute("data-time-start");
      const initialEnd = cardEl.getAttribute("data-time-end");
      const span = parseInt(cardEl.getAttribute("data-span"), 10) || 1;

      const periodNum = parseInt(cardEl.getAttribute("data-period-num"), 10);
      let startPIdx = periodsData.findIndex(
        (p) => parseInt(p.num, 10) === periodNum,
      );
      if (startPIdx === -1) {
        startPIdx = periodsData.findIndex(
          (p) => p.start.substring(0, 5) === start,
        );
      }
      if (startPIdx === -1) {
        startPIdx = periodsData.findIndex(
          (p) =>
            start >= p.start.substring(0, 5) && start < p.end.substring(0, 5),
        );
      }
      if (startPIdx === -1) {
        startPIdx = 0;
      }
      const endPIdx = Math.min(periodsData.length - 1, startPIdx + span - 1);

      const trEl = cardEl.closest("tr");
      if (!trEl) return;

      const ths = Array.from(gridEl.querySelectorAll("thead th:not(.day-col)"));
      const colBounds = ths.map((th, idx) => {
        const rect = th.getBoundingClientRect();
        return {
          index: idx,
          left: rect.left,
          right: rect.right,
          width: rect.width,
        };
      });
      if (
        colBounds.length === 0 ||
        !colBounds[startPIdx] ||
        !colBounds[endPIdx]
      )
        return;

      const trRect = trEl.getBoundingClientRect();
      const previewEl = document.createElement("div");
      previewEl.className = "resize-live-preview";
      previewEl.style.top = trRect.top + 2 + "px";
      previewEl.style.height = trRect.height - 4 + "px";
      previewEl.style.left = colBounds[startPIdx].left + "px";
      previewEl.style.width =
        colBounds[endPIdx].right - colBounds[startPIdx].left + "px";

      const previewBadge = document.createElement("span");
      previewBadge.className = "resize-live-preview-badge";
      previewBadge.textContent = `${start} – ${initialEnd} (${span} คาบ)`;
      previewEl.appendChild(previewBadge);
      document.body.appendChild(previewEl);

      cardEl.classList.add("is-resizing");
      cardEl.setAttribute("draggable", "false");

      const tooltipEl = document.createElement("div");
      tooltipEl.className = "resize-tooltip";
      tooltipEl.textContent = `${start} – ${initialEnd} (${span} คาบ)`;
      tooltipEl.style.left = e.clientX + "px";
      tooltipEl.style.top = e.clientY - 24 + "px";
      document.body.appendChild(tooltipEl);

      resizingState = {
        cardEl,
        handleEl,
        side,
        slotId,
        day,
        initialStart: start,
        initialEnd: initialEnd,
        startPIdx,
        endPIdx,
        currentStartPIdx: startPIdx,
        currentEndPIdx: endPIdx,
        colBounds,
        previewEl,
        previewBadge,
        tooltipEl,
      };

      try {
        handleEl.setPointerCapture(e.pointerId);
      } catch (err) {}

      function onPointerMove(moveEvent) {
        if (!resizingState) return;

        const x = moveEvent.clientX;
        resizingState.tooltipEl.style.left = x + "px";
        resizingState.tooltipEl.style.top = moveEvent.clientY - 24 + "px";

        let colIdx = 0;
        for (let i = 0; i < resizingState.colBounds.length; i++) {
          const col = resizingState.colBounds[i];
          if (x >= col.left && x <= col.right) {
            colIdx = i;
            break;
          } else if (x > col.right) {
            colIdx = i;
          }
        }

        if (resizingState.side === "left") {
          const candidateStartIdx = Math.max(
            0,
            Math.min(resizingState.endPIdx, colIdx),
          );
          resizingState.currentStartPIdx = candidateStartIdx;
        } else {
          const candidateEndIdx = Math.max(
            resizingState.startPIdx,
            Math.min(periodsData.length - 1, colIdx),
          );
          resizingState.currentEndPIdx = candidateEndIdx;
        }

        const leftPx =
          resizingState.colBounds[resizingState.currentStartPIdx].left;
        const rightPx =
          resizingState.colBounds[resizingState.currentEndPIdx].right;
        resizingState.previewEl.style.left = leftPx + "px";
        resizingState.previewEl.style.width =
          Math.max(10, rightPx - leftPx) + "px";

        const curSpan =
          resizingState.currentEndPIdx - resizingState.currentStartPIdx + 1;
        const curStartStr = periodsData[resizingState.currentStartPIdx].start;
        const curEndStr = periodsData[resizingState.currentEndPIdx].end;
        const snapLabel = `${curStartStr} – ${curEndStr} (${curSpan} คาบ)`;

        resizingState.previewBadge.textContent = snapLabel;
        resizingState.tooltipEl.textContent = snapLabel;
      }

      function onPointerUp(upEvent) {
        window.removeEventListener("pointermove", onPointerMove);
        window.removeEventListener("pointerup", onPointerUp);

        if (!resizingState) return;

        resizingState.cardEl.classList.remove("is-resizing");
        resizingState.cardEl.setAttribute("draggable", "true");
        if (resizingState.tooltipEl) resizingState.tooltipEl.remove();
        if (resizingState.previewEl) resizingState.previewEl.remove();

        try {
          handleEl.releasePointerCapture(upEvent.pointerId);
        } catch (err) {}

        const finalStart = periodsData[resizingState.currentStartPIdx].start;
        const finalEnd = periodsData[resizingState.currentEndPIdx].end;

        const initStartNorm = resizingState.initialStart.substring(0, 5);
        const finalStartNorm = finalStart.substring(0, 5);
        const initEndNorm = resizingState.initialEnd.substring(0, 5);
        const finalEndNorm = finalEnd.substring(0, 5);

        if (initStartNorm !== finalStartNorm || initEndNorm !== finalEndNorm) {
          debouncedSlotUpdate({
            slotId: resizingState.slotId,
            day: resizingState.day,
            timeStart: finalStart,
            timeEnd: finalEnd,
          });
        }

        resizingState = null;
      }

      window.addEventListener("pointermove", onPointerMove);
      window.addEventListener("pointerup", onPointerUp);
    });
  } // end if (gridEl)

  // --------------------------------------------------------------------------
  // Period Settings Modal: Safe Dynamic Row Creation (Zero innerHTML injection)
  // --------------------------------------------------------------------------
  const btnAddPeriodEl = document.getElementById("btnAddPeriod");
  const periodsTbodyEl = document.getElementById("periodsTbody");

  function createPeriodRow(nextNum, nextStart, nextEnd) {
    const trEl = document.createElement("tr");

    // 1. Period Number
    const tdNum = document.createElement("td");
    const inputNum = document.createElement("input");
    inputNum.type = "number";
    inputNum.name = "p_num[]";
    inputNum.className = "form-control form-control-sm mono";
    inputNum.value = nextNum;
    inputNum.required = true;
    tdNum.appendChild(inputNum);
    trEl.appendChild(tdNum);

    // 2. Start Time
    const tdStart = document.createElement("td");
    const inputStart = document.createElement("input");
    inputStart.type = "time";
    inputStart.name = "p_start[]";
    inputStart.className = "form-control form-control-sm mono";
    inputStart.value = nextStart;
    inputStart.required = true;
    tdStart.appendChild(inputStart);
    trEl.appendChild(tdStart);

    // 3. End Time
    const tdEnd = document.createElement("td");
    const inputEnd = document.createElement("input");
    inputEnd.type = "time";
    inputEnd.name = "p_end[]";
    inputEnd.className = "form-control form-control-sm mono";
    inputEnd.value = nextEnd;
    inputEnd.required = true;
    tdEnd.appendChild(inputEnd);
    trEl.appendChild(tdEnd);

    // 4. Period Type Select
    const tdType = document.createElement("td");
    const selectType = document.createElement("select");
    selectType.name = "p_type[]";
    selectType.className = "form-select form-select-sm";

    const optClass = document.createElement("option");
    optClass.value = "class";
    optClass.textContent = "คาบเรียนปกติ";
    const optLunch = document.createElement("option");
    optLunch.value = "lunch";
    optLunch.textContent = "พักกลางวัน";
    const optFlag = document.createElement("option");
    optFlag.value = "flag";
    optFlag.textContent = "หน้าเสาธง/โฮมรูม";

    selectType.appendChild(optClass);
    selectType.appendChild(optLunch);
    selectType.appendChild(optFlag);
    tdType.appendChild(selectType);
    trEl.appendChild(tdType);

    // 5. Period Name
    const tdName = document.createElement("td");
    const inputName = document.createElement("input");
    inputName.type = "text";
    inputName.name = "p_name[]";
    inputName.className = "form-control form-control-sm";
    inputName.placeholder = `เช่น คาบที่ ${nextNum}`;
    tdName.appendChild(inputName);
    trEl.appendChild(tdName);

    // 6. Delete Action Button
    const tdAction = document.createElement("td");
    tdAction.className = "text-center";
    const delBtn = document.createElement("button");
    delBtn.type = "button";
    delBtn.className = "btn btn-sm btn-danger-line p-0 btn-del-period";
    delBtn.style.cssText =
      "width: 30px; height: 30px; font-size: 13px; line-height: 28px;";
    delBtn.title = "ลบคาบนี้";
    delBtn.textContent = "✕";
    tdAction.appendChild(delBtn);
    trEl.appendChild(tdAction);

    return trEl;
  }

  if (btnAddPeriodEl && periodsTbodyEl) {
    btnAddPeriodEl.addEventListener("click", () => {
      const existingNums = Array.from(
        periodsTbodyEl.querySelectorAll('input[name="p_num[]"]'),
      ).map((inp) => parseInt(inp.value, 10) || 0);
      const nextNum = existingNums.length ? Math.max(...existingNums) + 1 : 1;

      let nextStart = "08:30";
      let nextEnd = "09:30";
      const lastEndInp = periodsTbodyEl.querySelector(
        'tr:last-child input[name="p_end[]"]',
      );
      if (lastEndInp && lastEndInp.value) {
        nextStart = lastEndInp.value;
        const parts = nextStart.split(":").map(Number);
        const endH = String(Math.min(23, (parts[0] || 0) + 1)).padStart(2, "0");
        const endM = String(parts[1] || 0).padStart(2, "0");
        nextEnd = `${endH}:${endM}`;
      }

      const newRowEl = createPeriodRow(nextNum, nextStart, nextEnd);
      periodsTbodyEl.appendChild(newRowEl);
    });

    periodsTbodyEl.addEventListener("click", (e) => {
      const delBtn = e.target.closest(".btn-del-period");
      if (!delBtn) return;
      const rows = periodsTbodyEl.querySelectorAll("tr");
      if (rows.length <= 1) {
        showToast("จำเป็นต้องมีคาบเวลาอย่างน้อย 1 คาบ", true);
        return;
      }
      delBtn.closest("tr").remove();
    });
  }

  // --------------------------------------------------------------------------
  // Add / Edit / Duplicate Slot Modal Handling
  // --------------------------------------------------------------------------
  document.addEventListener("click", (e) => {
    // Quick Add button
    const addBtn = e.target.closest(".btn-open-slot-modal");
    if (addBtn) {
      e.preventDefault();
      const modalEl =
        document.getElementById("modal-slot-editor") ||
        document.getElementById("slotModal");
      if (!modalEl) return;

      const titleEl = document.getElementById("slotModalTitle");
      if (titleEl) titleEl.textContent = "เพิ่มคาบเรียนใหม่";

      const idInput = document.getElementById("modalSlotId");
      if (idInput) idInput.value = "";

      const classSelect = document.getElementById("modalClassId");
      if (classSelect) classSelect.value = "";

      const courseSearch = document.getElementById("modalCourseSearch");
      if (courseSearch) courseSearch.value = "";
      const courseMenu = document.getElementById("courseComboboxMenu");
      if (courseMenu) courseMenu.style.display = "none";

      const groupInput = document.getElementById("modalClassGroup");
      if (groupInput)
        groupInput.value = addBtn.getAttribute("data-group") || currentGroup;

      const dayInput = document.getElementById("modalDayOfWeek");
      const day = addBtn.getAttribute("data-day");
      if (dayInput && day) dayInput.value = day;

      const startInput = document.getElementById("modalTimeStart");
      const start = addBtn.getAttribute("data-time-start");
      if (startInput && start) startInput.value = start.substring(0, 5);

      const endInput = document.getElementById("modalTimeEnd");
      const end = addBtn.getAttribute("data-time-end");
      if (endInput && end) endInput.value = end.substring(0, 5);

      const codeInput = document.getElementById("modalSubjectCode");
      if (codeInput) codeInput.value = "";

      const nameInput = document.getElementById("modalSubjectName");
      if (nameInput) nameInput.value = "";

      const teacherInput = document.getElementById("modalTeacherName");
      if (teacherInput) teacherInput.value = "";

      const roomInput = document.getElementById("modalRoom");
      if (roomInput) roomInput.value = "";

      bootstrap.Modal.getOrCreateInstance(modalEl).show();
      return;
    }

    // Duplicate Slot button
    const dupBtn = e.target.closest(".btn-duplicate-slot-modal");
    if (dupBtn) {
      e.preventDefault();
      e.stopPropagation();
      const modalEl =
        document.getElementById("modal-slot-editor") ||
        document.getElementById("slotModal");
      if (!modalEl) return;

      const titleEl = document.getElementById("slotModalTitle");
      if (titleEl) titleEl.textContent = "ทำซ้ำคาบเรียน (Duplicate Slot)";

      const idInput = document.getElementById("modalSlotId");
      if (idInput) idInput.value = ""; // empty id creates new slot

      const classId = dupBtn.getAttribute("data-class-id") || "";
      const classSelect = document.getElementById("modalClassId");
      if (classSelect) classSelect.value = classId;

      const courseSearch = document.getElementById("modalCourseSearch");
      if (courseSearch) {
        const cCode = dupBtn.getAttribute("data-subject-code") || "";
        const cName = dupBtn.getAttribute("data-subject-name") || "";
        courseSearch.value = `${cCode} ${cName}`.trim();
      }
      const courseMenu = document.getElementById("courseComboboxMenu");
      if (courseMenu) courseMenu.style.display = "none";

      const groupInput = document.getElementById("modalClassGroup");
      if (groupInput)
        groupInput.value = dupBtn.getAttribute("data-group") || currentGroup;

      const dayInput = document.getElementById("modalDayOfWeek");
      if (dayInput) dayInput.value = dupBtn.getAttribute("data-day") || "1";

      const startInput = document.getElementById("modalTimeStart");
      const rawStart = dupBtn.getAttribute("data-time-start") || "08:30";
      if (startInput) startInput.value = rawStart.substring(0, 5);

      const endInput = document.getElementById("modalTimeEnd");
      const rawEnd = dupBtn.getAttribute("data-time-end") || "10:30";
      if (endInput) endInput.value = rawEnd.substring(0, 5);

      const codeInput = document.getElementById("modalSubjectCode");
      if (codeInput)
        codeInput.value = dupBtn.getAttribute("data-subject-code") || "";

      const nameInput = document.getElementById("modalSubjectName");
      if (nameInput)
        nameInput.value = dupBtn.getAttribute("data-subject-name") || "";

      const teacherInput = document.getElementById("modalTeacherName");
      if (teacherInput)
        teacherInput.value = dupBtn.getAttribute("data-teacher-name") || "";

      const roomInput = document.getElementById("modalRoom");
      if (roomInput) roomInput.value = dupBtn.getAttribute("data-room") || "";

      bootstrap.Modal.getOrCreateInstance(modalEl).show();
      return;
    }

    // Edit Slot button or Card click
    const editBtn = e.target.closest(".btn-edit-slot-modal");
    const slotCard = e.target.closest(".admin-slot-card");
    const isSpecialAction = e.target.closest(
      '.slot-drag-handle, .slot-resize-handle, .btn-duplicate-slot-modal, form, button[type="submit"]',
    );

    if ((editBtn || slotCard) && !isSpecialAction) {
      e.preventDefault();
      const modalEl =
        document.getElementById("modal-slot-editor") ||
        document.getElementById("slotModal");
      if (!modalEl) return;

      const card = slotCard || editBtn.closest(".admin-slot-card");
      const btn = editBtn || card?.querySelector(".btn-edit-slot-modal");

      const id =
        (btn ? btn.getAttribute("data-id") : "") ||
        (card ? card.getAttribute("data-slot-id") : "");
      const titleEl = document.getElementById("slotModalTitle");
      if (titleEl)
        titleEl.textContent = id
          ? `แก้ไขคาบเรียน #${id}`
          : "แก้ไขข้อมูลคาบเรียน";

      const idInput = document.getElementById("modalSlotId");
      if (idInput) idInput.value = id;

      const classId =
        (btn ? btn.getAttribute("data-class-id") : "") ||
        (card ? card.getAttribute("data-class-id") : "");
      const classSelect = document.getElementById("modalClassId");
      if (classSelect) {
        classSelect.value = classId || "";
      }

      const courseSearch = document.getElementById("modalCourseSearch");
      if (courseSearch) {
        const cCode =
          (btn ? btn.getAttribute("data-subject-code") : "") ||
          (card ? card.getAttribute("data-subject-code") : "");
        const cName =
          (btn ? btn.getAttribute("data-subject-name") : "") ||
          (card ? card.getAttribute("data-subject-name") : "");
        courseSearch.value = `${cCode} ${cName}`.trim();
      }
      const courseMenu = document.getElementById("courseComboboxMenu");
      if (courseMenu) courseMenu.style.display = "none";

      const groupInput = document.getElementById("modalClassGroup");
      if (groupInput)
        groupInput.value =
          (btn ? btn.getAttribute("data-group") : "") ||
          (card ? card.getAttribute("data-group") : "") ||
          currentGroup;

      const dayInput = document.getElementById("modalDayOfWeek");
      if (dayInput)
        dayInput.value =
          (btn ? btn.getAttribute("data-day") : "") ||
          (card ? card.getAttribute("data-day") : "1");

      const startInput = document.getElementById("modalTimeStart");
      const rawStart =
        (btn ? btn.getAttribute("data-time-start") : "") ||
        (card ? card.getAttribute("data-time-start") : "08:30");
      if (startInput) startInput.value = rawStart.substring(0, 5);

      const endInput = document.getElementById("modalTimeEnd");
      const rawEnd =
        (btn ? btn.getAttribute("data-time-end") : "") ||
        (card ? card.getAttribute("data-time-end") : "10:30");
      if (endInput) endInput.value = rawEnd.substring(0, 5);

      const codeInput = document.getElementById("modalSubjectCode");
      if (codeInput)
        codeInput.value =
          (btn ? btn.getAttribute("data-subject-code") : "") ||
          (card ? card.getAttribute("data-subject-code") : "");

      const nameInput = document.getElementById("modalSubjectName");
      if (nameInput)
        nameInput.value =
          (btn ? btn.getAttribute("data-subject-name") : "") ||
          (card ? card.getAttribute("data-subject-name") : "");

      const teacherInput = document.getElementById("modalTeacherName");
      if (teacherInput)
        teacherInput.value =
          (btn ? btn.getAttribute("data-teacher-name") : "") ||
          (card ? card.getAttribute("data-teacher-name") : "");

      const roomInput = document.getElementById("modalRoom");
      if (roomInput)
        roomInput.value =
          (btn ? btn.getAttribute("data-room") : "") ||
          (card ? card.getAttribute("data-room") : "");

      bootstrap.Modal.getOrCreateInstance(modalEl).show();
      return;
    }

    // Edit Master Course Button
    const editCourseBtn = e.target.closest(".btn-edit-course");
    if (editCourseBtn) {
      e.preventDefault();
      const modalEl = document.getElementById("courseModal");
      if (!modalEl) return;

      const titleEl = document.getElementById("courseModalTitle");
      if (titleEl) titleEl.textContent = "แก้ไขข้อมูลรายวิชา";

      const idInput = document.getElementById("courseModalId");
      if (idInput) idInput.value = editCourseBtn.getAttribute("data-id") || "";

      const codeInput = document.getElementById("courseModalCode");
      if (codeInput)
        codeInput.value = editCourseBtn.getAttribute("data-code") || "";

      const nameInput = document.getElementById("courseModalName");
      if (nameInput)
        nameInput.value = editCourseBtn.getAttribute("data-name") || "";

      const typeSelect = document.getElementById("courseModalType");
      if (typeSelect)
        typeSelect.value =
          editCourseBtn.getAttribute("data-type") || "professional";

      const creditInput = document.getElementById("courseModalCredit");
      if (creditInput)
        creditInput.value = editCourseBtn.getAttribute("data-credit") || "3";

      const teacherInput = document.getElementById("courseModalTeacher");
      if (teacherInput)
        teacherInput.value = editCourseBtn.getAttribute("data-teacher") || "";

      const roomInput = document.getElementById("courseModalRoom");
      if (roomInput)
        roomInput.value = editCourseBtn.getAttribute("data-room") || "";

      bootstrap.Modal.getOrCreateInstance(modalEl).show();
      return;
    }

    // New Master Course Button
    const newCourseBtn = e.target.closest("#btnOpenNewCourseModal");
    if (newCourseBtn) {
      const form = document.getElementById("formCourseModal");
      if (form) form.reset();
      const titleEl = document.getElementById("courseModalTitle");
      if (titleEl) titleEl.textContent = "เพิ่มรายวิชาใหม่ในหลักสูตร";
      const idInput = document.getElementById("courseModalId");
      if (idInput) idInput.value = "";
      const creditInput = document.getElementById("courseModalCredit");
      if (creditInput) creditInput.value = "3";
    }
  });

  // Master Course Dropdown Auto-Fill in Slot Modal
  const classSelect = document.getElementById("modalClassId");
  if (classSelect) {
    classSelect.addEventListener("change", () => {
      const selectedOpt = classSelect.options[classSelect.selectedIndex];
      if (selectedOpt && selectedOpt.value) {
        const code = selectedOpt.getAttribute("data-code") || "";
        const name = selectedOpt.getAttribute("data-name") || "";
        const teacher = selectedOpt.getAttribute("data-teacher") || "";
        const room = selectedOpt.getAttribute("data-room") || "";

        const codeInput = document.getElementById("modalSubjectCode");
        if (codeInput) codeInput.value = code;

        const nameInput = document.getElementById("modalSubjectName");
        if (nameInput) nameInput.value = name;

        const teacherInput = document.getElementById("modalTeacherName");
        if (
          teacherInput &&
          (!teacherInput.value || teacherInput.value.trim() === "")
        ) {
          teacherInput.value = teacher;
        }

        const roomInput = document.getElementById("modalRoom");
        if (roomInput && (!roomInput.value || roomInput.value.trim() === "")) {
          roomInput.value = room;
        }
      }
    });
  }

  // Toggle Custom Course Input Inline
  const toggleCustomBtn = document.getElementById("btnToggleNewCourseInline");
  if (toggleCustomBtn) {
    toggleCustomBtn.addEventListener("click", () => {
      const classSelect = document.getElementById("modalClassId");
      if (classSelect) classSelect.value = "";
      const courseSearch = document.getElementById("modalCourseSearch");
      if (courseSearch) courseSearch.value = "";
      const courseMenu = document.getElementById("courseComboboxMenu");
      if (courseMenu) courseMenu.style.display = "none";
      const codeInput = document.getElementById("modalSubjectCode");
      if (codeInput) {
        codeInput.focus();
        codeInput.select();
      }
    });
  }

  // --------------------------------------------------------------------------
  // --------------------------------------------------------------------------
  // Searchable Course Combobox in Slot Modal
  // --------------------------------------------------------------------------
  const courseSearchInput = document.getElementById("modalCourseSearch");
  const courseComboboxMenu = document.getElementById("courseComboboxMenu");
  const btnToggleCombobox = document.getElementById("btnToggleCourseCombobox");
  const btnClearCombobox = document.getElementById("btnClearCourseSearch");

  if (courseSearchInput && courseComboboxMenu) {
    let highlightedIndex = -1;

    function escapeHtml(str) {
      return String(str)
        .replace(/&/g, "&amp;")
        .replace(/</g, "&lt;")
        .replace(/>/g, "&gt;")
        .replace(/"/g, "&quot;");
    }

    function highlightText(el, query) {
      const text = el.dataset.originalText || el.textContent;
      if (!query || !query.trim()) {
        el.textContent = text;
        return;
      }
      const escaped = query.trim().replace(/[.*+?^${}()|[\]\\]/g, "\\$&");
      const regex = new RegExp(`(${escaped})`, "gi");
      el.innerHTML = text.replace(
        regex,
        '<mark class="bg-warning text-dark rounded-0 px-0 mx-0">$1</mark>',
      );
    }

    function getVisibleItems() {
      return Array.from(
        courseComboboxMenu.querySelectorAll(".course-combobox-item"),
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
      const items = courseComboboxMenu.querySelectorAll(
        ".course-combobox-item",
      );
      const sectionHeaders = courseComboboxMenu.querySelectorAll(
        ".course-combobox-group-header.section-header",
      );
      const searchHeader = document.getElementById(
        "courseComboboxSearchHeader",
      );
      const noResult = document.getElementById("courseComboboxNoResult");

      if (btnClearCombobox) {
        btnClearCombobox.classList.toggle("d-none", query.length === 0);
      }

      if (tokens.length === 0) {
        // IF HAS NO SEARCH = SHOW ALL LIST
        items.forEach((item) => {
          item.style.display = "flex";
          const titleEl = item.querySelector(".course-title");
          if (titleEl && titleEl.dataset.originalText) {
            titleEl.textContent = titleEl.dataset.originalText;
          }
        });

        // Show section category headers
        sectionHeaders.forEach((header) => {
          header.style.display = "";
        });

        // Hide search result header
        if (searchHeader) {
          searchHeader.classList.add("d-none");
        }

        // Hide no-result message
        if (noResult) {
          noResult.classList.add("d-none");
        }

        setHighlighted(-1);
        return;
      }

      // HAS SEARCH = REPLACE LIST WITH SEARCH RESULTS
      // Hide all category section headers so the list is replaced with search results
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

      // Show and update search result count header
      if (searchHeader) {
        searchHeader.classList.remove("d-none");
        searchHeader.textContent = `ผลการค้นหา "${query}" (${matchCount} รายวิชา)`;
      }

      // Show no-result message if zero matches
      if (noResult) {
        noResult.classList.toggle("d-none", matchCount > 0);
        if (matchCount === 0) {
          noResult.innerHTML = `ไม่พบรายวิชาที่ตรงกับ "<strong>${escapeHtml(query)}</strong>" — สามารถคลิก "+ กำหนดรหัส/ชื่อวิชาเอง" เพื่อกรอกโดยตรง`;
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

      // Mark selected in list
      courseComboboxMenu
        .querySelectorAll(".course-combobox-item")
        .forEach((it) => {
          it.classList.toggle("is-selected", it === item);
        });

      hideCombobox();
    }

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
            const prevIdx =
              (highlightedIndex - 1 + items.length) % items.length;
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

    // Prevent input blur when clicking inside the combobox menu
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

  // --------------------------------------------------------------------------
  // AJAX Slot Form Submission (Zero page reload)
  // --------------------------------------------------------------------------
  const slotModalForm =
    document.getElementById("form-slot-modal") ||
    document.getElementById("slotModalForm");
  if (slotModalForm) {
    slotModalForm.addEventListener("submit", async (e) => {
      e.preventDefault();
      const submitBtn = document.getElementById("modalSubmitBtn");
      if (submitBtn) submitBtn.disabled = true;

      const formData = new FormData(slotModalForm);
      formData.append("ajax", "1");

      try {
        const resp = await fetch(apiEndpoint, {
          method: "POST",
          headers: { "X-Requested-With": "XMLHttpRequest" },
          body: formData,
        });
        const data = await resp.json();
        if (!resp.ok || !data.success) {
          throw new Error(data.error || "เกิดข้อผิดพลาดในการบันทึกข้อมูล");
        }

        showToast(data.message || "บันทึกข้อมูลเรียบร้อยแล้ว");

        const modalEl =
          document.getElementById("modal-slot-editor") ||
          document.getElementById("slotModal");
        const modalInstance = bootstrap.Modal.getInstance(modalEl);
        if (modalInstance) modalInstance.hide();

        if (gridEl) {
          const tbodyEl = gridEl.querySelector("tbody");
          if (data.tbody_html && tbodyEl) {
            tbodyEl.innerHTML = data.tbody_html;
          }
        } else {
          // If in list view or other view, reload to display updated records
          setTimeout(() => window.location.reload(), 400);
        }
      } catch (err) {
        showToast(err.message, true);
      } finally {
        if (submitBtn) submitBtn.disabled = false;
      }
    });
  }

  // Mobile / Tablet Day Selector Tabs (< 1024px)
  const dayTabsContainer = document.getElementById("adminSchedulerDayTabs");
  if (dayTabsContainer && gridEl) {
    const tabs = dayTabsContainer.querySelectorAll(".admin-scheduler-day-tab");
    tabs.forEach((tab) => {
      tab.addEventListener("click", () => {
        tabs.forEach((t) => t.classList.remove("is-active"));
        tab.classList.add("is-active");

        const targetDay = tab.getAttribute("data-target-day");
        const targetRow = gridEl.querySelector(
          `tbody tr[data-day="${targetDay}"]`,
        );
        if (targetRow) {
          targetRow.scrollIntoView({ behavior: "smooth", block: "nearest" });
          targetRow.style.transition = "background-color 0.4s ease";
          targetRow.style.backgroundColor = "rgba(233, 154, 74, 0.12)";
          setTimeout(() => {
            targetRow.style.backgroundColor = "";
          }, 600);
        }
      });
    });
  }
});
