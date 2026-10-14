/**
 * scheduler-resize.js - Edge drag-to-resize with wrap-relative column snapping preview
 *
 * Grabbing a card's left/right handle dynamically snaps to period columns inside
 * .timetable-admin-wrap. Shows live duration feedback in navyish institutional tones
 * and obstacle clamping against adjacent classes.
 * Releasing commits the new range through DBTScheduler.api.debouncedSlotUpdate().
 */
(function (global) {
  "use strict";

  const ns = (global.DBTScheduler = global.DBTScheduler || {});

  let ctx = null;
  let gridEl = null;
  let resizingState = null;

  const toHm = (time) => String(time).substring(0, 5);
  const formatRange = (start, end, span) => `${start} – ${end} (${span} คาบ)`;

  function findStartPeriodIndex(cardEl, start) {
    const periods = ctx.periodsData;
    const periodNum = parseInt(cardEl.getAttribute("data-period-num"), 10);

    let idx = periods.findIndex((p) => parseInt(p.num, 10) === periodNum);
    if (idx === -1) {
      idx = periods.findIndex((p) => toHm(p.start) === start);
    }
    if (idx === -1) {
      idx = periods.findIndex((p) => start >= toHm(p.start) && start < toHm(p.end));
    }
    return idx === -1 ? 0 : idx;
  }

  function getWrapPreviewEl() {
    const wrapEl = gridEl.closest(".timetable-admin-wrap");
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

  function findColumnIndexAt(clientX, headerCells) {
    for (let i = 0; i < headerCells.length; i++) {
      const r = headerCells[i].getBoundingClientRect();
      if (clientX >= r.left && clientX <= r.right) return i;
    }
    if (clientX < headerCells[0].getBoundingClientRect().left) return 0;
    if (clientX > headerCells[headerCells.length - 1].getBoundingClientRect().right) {
      return headerCells.length - 1;
    }
    return 0;
  }

  function onPointerMove(moveEvent) {
    if (!resizingState) return;

    const x = moveEvent.clientX;
    const colIdx = findColumnIndexAt(x, resizingState.headerCells);

    if (resizingState.side === "left") {
      resizingState.currentStartPIdx = Math.max(
        resizingState.minAllowedStartIdx,
        Math.min(resizingState.endPIdx, colIdx),
      );
    } else {
      resizingState.currentEndPIdx = Math.max(
        resizingState.startPIdx,
        Math.min(resizingState.maxAllowedEndIdx, colIdx),
      );
    }

    const startTh = resizingState.headerCells[resizingState.currentStartPIdx];
    const endTh = resizingState.headerCells[resizingState.currentEndPIdx];
    if (startTh && endTh) {
      const left = startTh.offsetLeft;
      const width = endTh.offsetLeft + endTh.offsetWidth - left;
      resizingState.previewEl.style.left = left + "px";
      resizingState.previewEl.style.width = width + "px";
    }

    const curSpan =
      resizingState.currentEndPIdx - resizingState.currentStartPIdx + 1;
    const snapLabel = formatRange(
      ctx.periodsData[resizingState.currentStartPIdx].start,
      ctx.periodsData[resizingState.currentEndPIdx].end,
      curSpan,
    );
    if (resizingState.previewBadge) {
      resizingState.previewBadge.textContent = snapLabel;
    }
  }

  function onPointerUp(upEvent) {
    window.removeEventListener("pointermove", onPointerMove);
    window.removeEventListener("pointerup", onPointerUp);

    if (!resizingState) return;

    // Resizing finished: suppress synthesized click event to prevent opening edit modal
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

    const state = resizingState;
    state.cardEl.classList.remove("is-resizing");

    if (state.previewEl) state.previewEl.classList.add("d-none");

    try {
      state.handleEl.releasePointerCapture(upEvent.pointerId);
    } catch (_) {}

    const finalStart = ctx.periodsData[state.currentStartPIdx].start;
    const finalEnd = ctx.periodsData[state.currentEndPIdx].end;
    const changed =
      toHm(state.initialStart) !== toHm(finalStart) ||
      toHm(state.initialEnd) !== toHm(finalEnd);

    if (changed) {
      ns.api.debouncedSlotUpdate({
        slotId: state.slotId,
        day: state.day,
        timeStart: finalStart,
        timeEnd: finalEnd,
      });
    }

    resizingState = null;
  }

  function onPointerDown(e) {
    const handleEl = e.target.closest(".slot-resize-handle, .resize-handle");
    if (!handleEl) return;

    e.preventDefault();
    e.stopPropagation();

    const cardEl = handleEl.closest(".admin-slot-card");
    if (!cardEl) return;

    const start = cardEl.getAttribute("data-time-start");
    const initialEnd = cardEl.getAttribute("data-time-end");
    const span = parseInt(cardEl.getAttribute("data-span"), 10) || 1;

    const startPIdx = findStartPeriodIndex(cardEl, start);
    let endPIdx = ctx.periodsData.findIndex(
      (p) => toHm(p.end) === toHm(initialEnd),
    );
    if (endPIdx === -1) {
      endPIdx = Math.min(ctx.periodsData.length - 1, startPIdx + span - 1);
    }

    const rowEl = cardEl.closest("tr");
    if (!rowEl) return;

    const headerCells = Array.from(
      gridEl.querySelectorAll("thead th:not(.day-col)"),
    );
    if (headerCells.length === 0 || !headerCells[startPIdx] || !headerCells[endPIdx]) {
      return;
    }

    // Boundary limits from adjacent obstacle cards in the same day row
    let minAllowedStartIdx = 0;
    let maxAllowedEndIdx = ctx.periodsData.length - 1;

    rowEl.querySelectorAll(".admin-slot-card").forEach((otherCard) => {
      if (otherCard === cardEl) return;
      const otherStart = otherCard.getAttribute("data-time-start");
      const otherEnd = otherCard.getAttribute("data-time-end");
      if (!otherStart || !otherEnd) return;

      const otherStartIdx = findStartPeriodIndex(otherCard, otherStart);
      const otherSpan = parseInt(otherCard.getAttribute("data-span"), 10) || 1;
      const otherEndIdx = Math.min(
        ctx.periodsData.length - 1,
        otherStartIdx + otherSpan - 1,
      );

      // Card to our left
      if (otherEndIdx < startPIdx) {
        minAllowedStartIdx = Math.max(minAllowedStartIdx, otherEndIdx + 1);
      }
      // Card to our right
      if (otherStartIdx > endPIdx) {
        maxAllowedEndIdx = Math.min(maxAllowedEndIdx, otherStartIdx - 1);
      }
    });

    const previewEl = getWrapPreviewEl();
    if (!previewEl) return;

    const startTh = headerCells[startPIdx];
    const endTh = headerCells[endPIdx];
    const left = startTh.offsetLeft;
    const width = endTh.offsetLeft + endTh.offsetWidth - left;
    const top = rowEl.offsetTop;
    const height = rowEl.offsetHeight;

    previewEl.style.left = left + "px";
    previewEl.style.top = top + 2 + "px";
    previewEl.style.width = width + "px";
    previewEl.style.height = height - 4 + "px";
    previewEl.classList.remove("d-none", "is-self", "is-conflict");
    previewEl.classList.add("is-resize");

    const label = formatRange(start, initialEnd, span);
    const previewBadge = previewEl.querySelector(".scheduler-live-preview-badge");
    if (previewBadge) previewBadge.textContent = label;

    cardEl.classList.add("is-resizing");

    resizingState = {
      cardEl,
      handleEl,
      side: handleEl.getAttribute("data-side") || "right",
      slotId: cardEl.getAttribute("data-slot-id"),
      day: cardEl.getAttribute("data-day"),
      initialStart: start,
      initialEnd,
      startPIdx,
      endPIdx,
      minAllowedStartIdx,
      maxAllowedEndIdx,
      currentStartPIdx: startPIdx,
      currentEndPIdx: endPIdx,
      headerCells,
      previewEl,
      previewBadge,
    };

    try {
      handleEl.setPointerCapture(e.pointerId);
    } catch (_) {}

    window.addEventListener("pointermove", onPointerMove);
    window.addEventListener("pointerup", onPointerUp);
  }

  function init(sharedCtx) {
    ctx = sharedCtx;
    gridEl = ctx.gridEl;
    if (!gridEl) return;

    gridEl.addEventListener("pointerdown", onPointerDown);
  }

  ns.resize = { init };
})(window);
