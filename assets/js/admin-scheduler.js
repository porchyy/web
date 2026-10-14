/**
 * admin-scheduler.js - Main orchestrator for admin class schedule & curriculum
 *
 * Decomposed from a 1,590-line God script into single-responsibility modules:
 *   - modules/scheduler-ui.js       (Toasts & responsive day tabs)
 *   - modules/scheduler-api.js      (AJAX communication & grid DOM sync)
 *   - modules/scheduler-dnd.js      (Desktop HTML5 & mobile touch drag-and-drop)
 *   - modules/scheduler-resize.js   (Slot column resize engine)
 *   - modules/scheduler-periods.js  (Period configuration modal)
 *   - modules/scheduler-modals.js   (Add/edit/duplicate slot modals)
 *   - modules/scheduler-combobox.js (Course search combobox with safe DOM highlight)
 */
(function (global) {
  "use strict";

  const ns = (global.DBTScheduler = global.DBTScheduler || {});

  function boot() {
    const gridEl = document.getElementById("schedulerGrid");

    const csrfInput = document.querySelector(
      'input[name="csrf_token"], input[name="csrf"]'
    );
    const csrfToken = gridEl?.dataset.csrf || csrfInput?.value || "";
    const currentGroup = gridEl?.dataset.group || "ปวช.2/1";
    const apiEndpoint = gridEl?.dataset.endpoint || window.location.pathname;

    let periodsData = [];
    try {
      periodsData = JSON.parse(gridEl?.dataset.periods || "[]");
    } catch (_) {
      periodsData = [];
    }

    const showToast = ns.ui?.showToast
      ? ns.ui.showToast
      : (msg, isErr) => console[isErr ? "error" : "log"](msg);

    const ctx = {
      gridEl,
      csrfToken,
      currentGroup,
      apiEndpoint,
      periodsData,
      showToast,
    };

    // Initialize modules in dependency order
    const modules = [
      ["api", ns.api],
      ["dnd", ns.dnd],
      ["resize", ns.resize],
      ["periods", ns.periods],
      ["modals", ns.modals],
      ["combobox", ns.combobox],
    ];

    modules.forEach(([name, mod]) => {
      if (mod && typeof mod.init === "function") {
        mod.init(ctx);
      } else {
        console.warn(`[DBTScheduler] Module "${name}" is not loaded.`);
      }
    });

    if (ns.ui?.initDayTabs) {
      ns.ui.initDayTabs(ctx);
    }
  }

  if (document.readyState === "loading") {
    document.addEventListener("DOMContentLoaded", boot);
  } else {
    boot();
  }
})(window);
