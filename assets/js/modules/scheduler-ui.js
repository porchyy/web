/**
 * scheduler-ui.js - Toast notifications & mobile day tabs
 *
 * Pure presentation helpers for the admin scheduler. No server calls.
 * Safe native DOM construction only (zero raw innerHTML injection).
 */
(function (global) {
  "use strict";

  const ns = (global.DBTScheduler = global.DBTScheduler || {});

  const TOAST_SUCCESS_MS = 3500;
  const TOAST_ERROR_MS = 5000;
  const TOAST_FADE_MS = 300;
  const ROW_FLASH_MS = 600;

  function buildToastElement(message, isError) {
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
    return toastEl;
  }

  function showToast(message, isError = false) {
    const existingToastEl = document.querySelector(".toast-msg");
    if (existingToastEl) {
      existingToastEl.remove();
    }

    const toastEl = buildToastElement(message, isError);
    document.body.appendChild(toastEl);

    const duration = isError ? TOAST_ERROR_MS : TOAST_SUCCESS_MS;
    setTimeout(() => {
      if (!document.body.contains(toastEl)) return;
      toastEl.style.opacity = "0";
      toastEl.style.transition = "opacity 0.3s ease";
      setTimeout(() => toastEl.remove(), TOAST_FADE_MS);
    }, duration);
  }

  function flashRow(rowEl) {
    rowEl.scrollIntoView({ behavior: "smooth", block: "nearest" });
    rowEl.style.transition = "background-color 0.4s ease";
    rowEl.style.backgroundColor = "rgba(233, 154, 74, 0.12)";
    setTimeout(() => {
      rowEl.style.backgroundColor = "";
    }, ROW_FLASH_MS);
  }

  /** Mobile / Tablet day selector tabs (< 1024px) */
  function initDayTabs(ctx) {
    const tabsContainerEl = document.getElementById("adminSchedulerDayTabs");
    if (!tabsContainerEl || !ctx.gridEl) return;

    const tabs = tabsContainerEl.querySelectorAll(".admin-scheduler-day-tab");
    tabs.forEach((tab) => {
      tab.addEventListener("click", () => {
        tabs.forEach((t) => t.classList.remove("is-active"));
        tab.classList.add("is-active");

        const targetDay = tab.getAttribute("data-target-day");
        const targetRow = ctx.gridEl.querySelector(
          `tbody tr[data-day="${targetDay}"]`,
        );
        if (targetRow) flashRow(targetRow);
      });
    });
  }

  ns.ui = { showToast, initDayTabs };
})(window);
