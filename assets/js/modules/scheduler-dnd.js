/**
 * scheduler-dnd.js - Unified Pointer Events Drag & Drop Engine for Admin Scheduler
 *
 * Replaces native HTML5 Drag and Drop with a robust Pointer Events architecture:
 * - Desktop: Full card surface drag (with 5px click-protection threshold)
 * - Mobile: Dedicated .slot-drag-handle drag (keeps page scrolling smooth)
 * - Span Preservation: Multi-period courses never truncate to 1 hour on table boundaries
 * - Self & Tail Overlap: Fully permitted; distinct blue badge for origin
 * - Collision Feedback: Crimson preview badge and toast warning on invalid drop
 * - Commit: Dispatches DBTScheduler.api.debouncedSlotUpdate() with zero page reload
 */
(function (global) {
  "use strict";

  const ns = (global.DBTScheduler = global.DBTScheduler || {});

  const ROW_HIT_TOLERANCE_PX = 16;
  const DRAG_THRESHOLD_PX = 5;
  const EDGE_SCROLL_ZONE_PX = 60;
  const NON_DRAG_TARGETS =
    ".slot-resize-handle, .resize-handle, .card-actions, .btn-overlay-action, button, form, a, input, select, textarea";

  const HOVER_CLASSES = [
    "drag-over-origin",
    "drag-over-span",
    "drag-over-invalid",
  ];
  const HOVER_SELECTOR =
    ".admin-slot-empty.drag-over-origin, .admin-slot-empty.drag-over-span, .admin-slot-empty.drag-over-invalid";

  let ctx = null;
  let gridEl = null;
  let pendingPointer = null;
  let activeDrag = null;

  // --------------------------------------------------------------------------
  // Overlay & Hover Highlight Utilities
  // --------------------------------------------------------------------------
  function clearHoverClasses(exceptRowEl = null) {
    document.querySelectorAll(HOVER_SELECTOR).forEach((cell) => {
      if (exceptRowEl && cell.closest("tr") === exceptRowEl) return;
      cell.classList.remove(...HOVER_CLASSES);
    });
  }

  function getWrapPreviewEl() {
    const wrapEl = gridEl ? gridEl.closest(".timetable-admin-wrap") : null;
    if (!wrapEl) return null;

    let previewEl = wrapEl.querySelector("#schedulerLivePreview");
    if (!previewEl) {
      previewEl = document.createElement("div");
      previewEl.id = "schedulerLivePreview";
      previewEl.className = "scheduler-live-preview";
      const badge = document.createElement("div");
      badge.className = "scheduler-live-preview-badge";
      previewEl.appendChild(badge);
      wrapEl.appendChild(previewEl);
    }
    return previewEl;
  }

  function clearHoverPreview() {
    const previewEl = getWrapPreviewEl();
    if (previewEl) {
      previewEl.classList.add("d-none");
    }
    clearHoverClasses();
  }

  // --------------------------------------------------------------------------
  // Drop Target & Collision Hit Tests
  // --------------------------------------------------------------------------
  function findTargetRow(clientX, clientY) {
    const elUnderPoint = document.elementFromPoint(clientX, clientY);
    if (elUnderPoint) {
      const tdEl = elUnderPoint.closest("#schedulerGrid tbody td");
      if (tdEl) return tdEl.closest("tr");
    }

    const rows = Array.from(gridEl.querySelectorAll("tbody tr"));
    for (const row of rows) {
      const rect = row.getBoundingClientRect();
      if (
        clientY >= rect.top - ROW_HIT_TOLERANCE_PX &&
        clientY <= rect.bottom + ROW_HIT_TOLERANCE_PX
      ) {
        return row;
      }
    }
    return null;
  }

  function findTargetPeriodIndex(headerCells, clientX) {
    for (let i = 0; i < headerCells.length; i++) {
      const rect = headerCells[i].getBoundingClientRect();
      if (clientX >= rect.left && clientX <= rect.right) return i;
    }
    if (clientX < headerCells[0].getBoundingClientRect().left) return 0;
    if (clientX > headerCells[headerCells.length - 1].getBoundingClientRect().right) {
      return headerCells.length - 1;
    }
    return -1;
  }

  function findConflict(rowEl, draggedSlotId, targetStart, targetEnd) {
    let hasConflict = false;
    let conflictName = "";
    rowEl.querySelectorAll(".admin-slot-card").forEach((card) => {
      // Exclude self and tail slots of the currently dragged card
      if (card.getAttribute("data-slot-id") === draggedSlotId) {
        return;
      }
      const cardStart = card.getAttribute("data-time-start");
      const cardEnd = card.getAttribute("data-time-end");
      if (cardStart && cardEnd && cardStart < targetEnd && cardEnd > targetStart) {
        hasConflict = true;
        const titleEl = card.querySelector(".subject-name");
        conflictName = titleEl ? titleEl.textContent.trim() : "คาบเรียนอื่น";
      }
    });
    return { hasConflict, conflictName };
  }

  function renderDragShadow(headerCells, rowEl, range, conflict, isSelf) {
    const previewEl = getWrapPreviewEl();
    if (!previewEl) return;

    const startTh = headerCells[range.startIdx];
    const endTh = headerCells[range.endIdx];
    if (!startTh || !endTh) return;

    const left = startTh.offsetLeft;
    const width = endTh.offsetLeft + endTh.offsetWidth - left;
    const top = rowEl.offsetTop;
    const height = rowEl.offsetHeight;

    previewEl.style.left = left + "px";
    previewEl.style.top = top + 2 + "px";
    previewEl.style.width = width + "px";
    previewEl.style.height = height - 4 + "px";
    previewEl.classList.remove("d-none", "is-self", "is-conflict", "is-resize");

    const span = range.endIdx - range.startIdx + 1;
    const badgeEl = previewEl.querySelector(".scheduler-live-preview-badge");

    if (conflict.hasConflict) {
      previewEl.classList.add("is-conflict");
      if (badgeEl) badgeEl.textContent = `⚠️ ซ้อนทับกับ ${conflict.conflictName}`;
    } else if (isSelf) {
      previewEl.classList.add("is-self");
      if (badgeEl) badgeEl.textContent = `📍 ตำแหน่งเดิม (${span} คาบ)`;
    } else {
      if (badgeEl) badgeEl.textContent = `${range.start} – ${range.end} (${span} คาบ)`;
    }
  }

  function highlightRowCells(rowEl, range, hasConflict) {
    clearHoverClasses(rowEl);

    rowEl.querySelectorAll(".admin-slot-empty").forEach((cell) => {
      const periodNum = parseInt(cell.getAttribute("data-period-num"), 10);
      const pIdx = ctx.periodsData.findIndex(
        (p) => parseInt(p.num, 10) === periodNum,
      );

      if (pIdx === range.startIdx) {
        cell.classList.toggle("drag-over-origin", !hasConflict);
        cell.classList.toggle("drag-over-invalid", hasConflict);
        cell.classList.remove("drag-over-span");
      } else if (pIdx > range.startIdx && pIdx <= range.endIdx) {
        cell.classList.toggle("drag-over-span", !hasConflict);
        cell.classList.toggle("drag-over-invalid", hasConflict);
        cell.classList.remove("drag-over-origin");
      } else {
        cell.classList.remove(...HOVER_CLASSES);
      }
    });
  }

  function checkEdgeAutoScroll(clientX) {
    const wrapEl = gridEl.closest(".timetable-admin-wrap");
    if (!wrapEl) return;
    const wrapRect = wrapEl.getBoundingClientRect();
    if (clientX < wrapRect.left + EDGE_SCROLL_ZONE_PX) {
      const factor =
        (wrapRect.left + EDGE_SCROLL_ZONE_PX - clientX) / EDGE_SCROLL_ZONE_PX;
      wrapEl.scrollLeft -= Math.max(6, Math.round(factor * 18));
    } else if (clientX > wrapRect.right - EDGE_SCROLL_ZONE_PX) {
      const factor =
        (clientX - (wrapRect.right - EDGE_SCROLL_ZONE_PX)) / EDGE_SCROLL_ZONE_PX;
      wrapEl.scrollLeft -= Math.max(6, Math.round(factor * 18));
    }
  }

  // --------------------------------------------------------------------------
  // Floating Drag Ghost Management
  // --------------------------------------------------------------------------
  function createFloatingGhost(cardEl, pointerX, pointerY) {
    const rect = cardEl.getBoundingClientRect();
    const ghost = document.createElement("div");
    ghost.className = "pointer-drag-ghost";

    const subCode = cardEl.querySelector(".subject-code")?.textContent.trim() || "";
    const subName = cardEl.querySelector(".subject-name")?.textContent.trim() || "";
    const timeStart = cardEl.getAttribute("data-time-start") || "";
    const timeEnd = cardEl.getAttribute("data-time-end") || "";
    const span = cardEl.getAttribute("data-span") || "1";

    const wrap = document.createElement("div");
    wrap.style.cssText = "padding: 6px 10px; font-family: var(--font-body, sans-serif); line-height: 1.3;";

    const head = document.createElement("div");
    head.style.cssText = "display: flex; justify-content: space-between; align-items: center; margin-bottom: 2px;";

    const codeEl = document.createElement("strong");
    codeEl.style.cssText = "font-size: 11.5px; color: var(--c-navy, #17324d);";
    codeEl.textContent = subCode;

    const spanEl = document.createElement("span");
    spanEl.style.cssText = "font-size: 10.5px; font-family: var(--font-mono, monospace); color: var(--c-navy, #17324d); font-weight: 600;";
    spanEl.textContent = `(${span} คาบ)`;

    head.appendChild(codeEl);
    head.appendChild(spanEl);

    const nameEl = document.createElement("div");
    nameEl.style.cssText = "font-size: 12px; font-weight: 600; color: #1e293b; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; max-width: 200px;";
    nameEl.textContent = subName;

    const timeEl = document.createElement("div");
    timeEl.style.cssText = "font-size: 10.5px; color: #64748b; font-family: var(--font-mono, monospace); margin-top: 2px;";
    timeEl.textContent = `${timeStart} – ${timeEnd}`;

    wrap.appendChild(head);
    wrap.appendChild(nameEl);
    wrap.appendChild(timeEl);
    ghost.appendChild(wrap);

    ghost.style.width = Math.max(160, Math.min(240, rect.width)) + "px";
    ghost.style.left = pointerX + "px";
    ghost.style.top = pointerY + "px";

    document.body.appendChild(ghost);
    return ghost;
  }

  // --------------------------------------------------------------------------
  // Unified Pointer Event Handlers
  // --------------------------------------------------------------------------
  function onPointerDown(e) {
    if (e.button !== 0) return; // Primary button only

    // Prevent drag if clicking buttons, forms, links, or resize handles
    if (e.target.closest(NON_DRAG_TARGETS)) return;

    const cardEl = e.target.closest(".admin-slot-card");
    if (!cardEl) return;

    const isTouch =
      e.pointerType === "touch" ||
      window.matchMedia("(pointer: coarse)").matches;

    // Mobile requirement: Only dedicated .slot-drag-handle initiates dragging (preserves page scrolling)
    if (isTouch && !e.target.closest(".slot-drag-handle")) {
      return;
    }

    pendingPointer = {
      cardEl,
      startX: e.clientX,
      startY: e.clientY,
      pointerId: e.pointerId,
      isTouch,
    };

    window.addEventListener("pointermove", onPointerMove);
    window.addEventListener("pointerup", onPointerUp);
    window.addEventListener("pointercancel", onPointerCancel);
  }

  function onPointerMove(e) {
    if (!pendingPointer) return;

    // Threshold check before activating drag mode
    if (!activeDrag) {
      const dist = Math.hypot(
        e.clientX - pendingPointer.startX,
        e.clientY - pendingPointer.startY,
      );
      if (dist < DRAG_THRESHOLD_PX) return;

      const cardEl = pendingPointer.cardEl;
      const ghostEl = createFloatingGhost(cardEl, e.clientX, e.clientY);

      cardEl.classList.add("is-card-ghost");
      gridEl.classList.add("is-dragging-active");

      activeDrag = {
        cardEl,
        slotId: cardEl.getAttribute("data-slot-id"),
        span: parseInt(cardEl.getAttribute("data-span"), 10) || 1,
        ghostEl,
        pointerId: pendingPointer.pointerId,
        lastHover: null,
      };

      try {
        cardEl.setPointerCapture(pendingPointer.pointerId);
      } catch (_) {}
    }

    // Active drag tracking
    if (activeDrag.ghostEl) {
      activeDrag.ghostEl.style.left = e.clientX + "px";
      activeDrag.ghostEl.style.top = e.clientY + "px";
    }

    checkEdgeAutoScroll(e.clientX);

    const rowEl = findTargetRow(e.clientX, e.clientY);
    if (!rowEl) {
      activeDrag.lastHover = null;
      clearHoverPreview();
      return;
    }

    const headerCells = Array.from(
      gridEl.querySelectorAll("thead th:not(.day-col)"),
    );
    if (headerCells.length === 0) return;

    const rawStartIdx = findTargetPeriodIndex(headerCells, e.clientX);
    if (rawStartIdx < 0 || rawStartIdx >= ctx.periodsData.length) {
      activeDrag.lastHover = null;
      clearHoverPreview();
      return;
    }

    const span = activeDrag.span;

    // CRITICAL: Span preservation clamp — dragging to right boundary NEVER shrinks course span!
    let startIdx = rawStartIdx;
    if (startIdx + span > ctx.periodsData.length) {
      startIdx = Math.max(0, ctx.periodsData.length - span);
    }
    const endIdx = startIdx + span - 1;

    if (endIdx >= ctx.periodsData.length) {
      activeDrag.lastHover = null;
      clearHoverPreview();
      return;
    }

    const range = {
      startIdx,
      endIdx,
      start: ctx.periodsData[startIdx].start,
      end: ctx.periodsData[endIdx].end,
    };

    const targetDay =
      rowEl.getAttribute("data-day") ||
      activeDrag.cardEl.getAttribute("data-day") ||
      "1";

    const conflict = findConflict(
      rowEl,
      activeDrag.slotId,
      range.start,
      range.end,
    );

    const isSelf =
      targetDay === activeDrag.cardEl.getAttribute("data-day") &&
      range.start === activeDrag.cardEl.getAttribute("data-time-start") &&
      range.end === activeDrag.cardEl.getAttribute("data-time-end");

    activeDrag.lastHover = {
      rowEl,
      range,
      targetDay,
      conflict,
      isSelf,
    };

    renderDragShadow(headerCells, rowEl, range, conflict, isSelf);
    highlightRowCells(rowEl, range, conflict.hasConflict);
  }

  function cleanupActiveDrag() {
    if (activeDrag) {
      if (activeDrag.ghostEl) {
        activeDrag.ghostEl.remove();
      }
      activeDrag.cardEl.classList.remove("is-card-ghost", "is-dragging");
      try {
        activeDrag.cardEl.releasePointerCapture(activeDrag.pointerId);
      } catch (_) {}
    }
    if (gridEl) {
      gridEl.classList.remove("is-dragging-active");
    }
    clearHoverPreview();
    activeDrag = null;
    pendingPointer = null;

    window.removeEventListener("pointermove", onPointerMove);
    window.removeEventListener("pointerup", onPointerUp);
    window.removeEventListener("pointercancel", onPointerCancel);
  }

  function onPointerUp(e) {
    if (!pendingPointer && !activeDrag) return;

    // Simple click without drag
    if (!activeDrag) {
      cleanupActiveDrag();
      return;
    }

    // Active drag finished: suppress synthesized click event to prevent opening edit modal
    ns.lastDragEndTime = Date.now();
    const suppressClick = (clickEvent) => {
      clickEvent.preventDefault();
      clickEvent.stopPropagation();
      clickEvent.stopImmediatePropagation();
      window.removeEventListener("click", suppressClick, true);
    };
    window.addEventListener("click", suppressClick, true);
    setTimeout(() => {
      window.removeEventListener("click", suppressClick, true);
    }, 350);

    const hover = activeDrag.lastHover;
    const cardEl = activeDrag.cardEl;
    const slotId = activeDrag.slotId;

    cleanupActiveDrag();

    if (!hover) return;

    if (hover.conflict.hasConflict) {
      // Collision with another class: shake feedback & notification
      if (cardEl) {
        cardEl.classList.add("shake-invalid");
        setTimeout(() => cardEl.classList.remove("shake-invalid"), 600);
      }
      if (ns.ui && typeof ns.ui.showToast === "function") {
        ns.ui.showToast("ไม่สามารถวางซ้อนทับคาบเรียนอื่นได้", "warning");
      }
      return;
    }

    if (hover.isSelf) {
      // Dropped back onto origin: smooth reset without network spam
      return;
    }

    // Valid drop onto a new slot or day: commit update
    ns.api.debouncedSlotUpdate({
      slotId,
      day: hover.targetDay,
      timeStart: hover.range.start,
      timeEnd: hover.range.end,
    });
  }

  function onPointerCancel() {
    cleanupActiveDrag();
  }

  // --------------------------------------------------------------------------
  // Module Initialization
  // --------------------------------------------------------------------------
  function init(sharedCtx) {
    ctx = sharedCtx;
    gridEl = ctx.gridEl;
    if (!gridEl) return;

    gridEl.addEventListener("pointerdown", onPointerDown);

    window.addEventListener("keydown", (e) => {
      if (e.key === "Escape" && activeDrag) {
        cleanupActiveDrag();
      }
    });
  }

  ns.dnd = { init };
})(window);
