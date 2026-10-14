/**
 * scheduler-api.js - Server synchronisation for the admin scheduler
 *
 * Owns every POST to the scheduler JSON micro-endpoint so the DnD, resize and
 * modal modules never talk to fetch() directly.
 */
(function (global) {
  "use strict";

  const ns = (global.DBTScheduler = global.DBTScheduler || {});

  const DEBOUNCE_MS = 80;

  let ctx = null;
  let isUpdating = false;
  let updateDebounceTimer = null;

  function init(sharedCtx) {
    ctx = sharedCtx;
  }

  /**
   * POST a FormData payload and return the parsed JSON.
   * Throws an Error carrying the server message when the call fails.
   */
  async function postForm(formData, fallbackError) {
    const resp = await fetch(ctx.apiEndpoint, {
      method: "POST",
      headers: { "X-Requested-With": "XMLHttpRequest" },
      body: formData,
    });
    const data = await resp.json();
    if (!resp.ok || !data.success) {
      throw new Error(data.error || fallbackError);
    }
    return data;
  }

  /** Apply server-rendered grid HTML and refreshed periods (no page reload). */
  function applyGridUpdate(data) {
    if (data.periods && Array.isArray(data.periods)) {
      ctx.periodsData = data.periods;
    }
    const tbodyEl = ctx.gridEl ? ctx.gridEl.querySelector("tbody") : null;
    if (data.tbody_html && tbodyEl) {
      tbodyEl.innerHTML = data.tbody_html;
    }
  }

  function buildSlotUpdatePayload(updateData) {
    const formData = new FormData();
    formData.append("csrf_token", ctx.csrfToken);
    formData.append("class_group", ctx.currentGroup);
    formData.append("sub_action", "ajax_update_slot");
    formData.append("slot_id", updateData.slotId);
    formData.append("day_of_week", updateData.day);
    formData.append("time_start", updateData.timeStart);
    formData.append("time_end", updateData.timeEnd);
    return formData;
  }

  async function sendSlotUpdate(updateData) {
    if (isUpdating) return;
    isUpdating = true;

    try {
      const data = await postForm(
        buildSlotUpdatePayload(updateData),
        "เกิดข้อผิดพลาดในการย้ายคาบเรียน",
      );
      ctx.showToast(data.message || "อัปเดตตำแหน่งสำเร็จ");
      applyGridUpdate(data);
    } catch (err) {
      ctx.showToast(err.message, true);
    } finally {
      isUpdating = false;
    }
  }

  function debouncedSlotUpdate(updateData, delay = DEBOUNCE_MS) {
    clearTimeout(updateDebounceTimer);
    updateDebounceTimer = setTimeout(() => {
      sendSlotUpdate(updateData);
    }, delay);
  }

  ns.api = { init, postForm, applyGridUpdate, debouncedSlotUpdate };
})(window);
