/**
 * scheduler-periods.js - Period settings modal (dynamic row management)
 *
 * Builds rows with native DOM APIs only (zero innerHTML injection).  The form
 * itself is submitted normally by the browser (sub_action=save_periods).
 */
(function (global) {
  "use strict";

  const ns = (global.DBTScheduler = global.DBTScheduler || {});

  const DEFAULT_FIRST_START = "08:30";
  const DEFAULT_FIRST_END = "09:30";
  const PERIOD_TYPES = [
    { value: "class", label: "คาบเรียนปกติ" },
    { value: "lunch", label: "พักกลางวัน" },
    { value: "flag", label: "หน้าเสาธง/โฮมรูม" },
  ];

  let ctx = null;
  let tbodyEl = null;

  function createCell(childEl, className = "") {
    const tdEl = document.createElement("td");
    if (className) tdEl.className = className;
    tdEl.appendChild(childEl);
    return tdEl;
  }

  function createInput(type, name, className, value = "") {
    const inputEl = document.createElement("input");
    inputEl.type = type;
    inputEl.name = name;
    inputEl.className = className;
    inputEl.value = value;
    return inputEl;
  }

  function createRequiredInput(type, name, className, value) {
    const inputEl = createInput(type, name, className, value);
    inputEl.required = true;
    return inputEl;
  }

  function createTypeSelect() {
    const selectEl = document.createElement("select");
    selectEl.name = "p_type[]";
    selectEl.className = "form-select form-select-sm";
    PERIOD_TYPES.forEach(({ value, label }) => {
      const optionEl = document.createElement("option");
      optionEl.value = value;
      optionEl.textContent = label;
      selectEl.appendChild(optionEl);
    });
    return selectEl;
  }

  function createDeleteButton() {
    const delBtn = document.createElement("button");
    delBtn.type = "button";
    delBtn.className = "btn btn-sm btn-danger-line p-0 btn-del-period";
    delBtn.style.cssText =
      "width: 30px; height: 30px; font-size: 13px; line-height: 28px;";
    delBtn.title = "ลบคาบนี้";
    delBtn.textContent = "✕";
    return delBtn;
  }

  function createPeriodRow(nextNum, nextStart, nextEnd) {
    const monoClass = "form-control form-control-sm mono";
    const nameInput = createInput("text", "p_name[]", "form-control form-control-sm");
    nameInput.placeholder = `เช่น คาบที่ ${nextNum}`;

    const trEl = document.createElement("tr");
    trEl.appendChild(createCell(createRequiredInput("number", "p_num[]", monoClass, nextNum)));
    trEl.appendChild(createCell(createRequiredInput("time", "p_start[]", monoClass, nextStart)));
    trEl.appendChild(createCell(createRequiredInput("time", "p_end[]", monoClass, nextEnd)));
    trEl.appendChild(createCell(createTypeSelect()));
    trEl.appendChild(createCell(nameInput));
    trEl.appendChild(createCell(createDeleteButton(), "text-center"));
    return trEl;
  }

  function computeNextNumber() {
    const existingNums = Array.from(
      tbodyEl.querySelectorAll('input[name="p_num[]"]'),
    ).map((inp) => parseInt(inp.value, 10) || 0);
    return existingNums.length ? Math.max(...existingNums) + 1 : 1;
  }

  // New row starts where the last one ended and runs for one hour (capped at 23:xx).
  function computeNextTimes() {
    const lastEndInp = tbodyEl.querySelector('tr:last-child input[name="p_end[]"]');
    if (!lastEndInp || !lastEndInp.value) {
      return { start: DEFAULT_FIRST_START, end: DEFAULT_FIRST_END };
    }

    const start = lastEndInp.value;
    const parts = start.split(":").map(Number);
    const endH = String(Math.min(23, (parts[0] || 0) + 1)).padStart(2, "0");
    const endM = String(parts[1] || 0).padStart(2, "0");
    return { start, end: `${endH}:${endM}` };
  }

  function addPeriodRow() {
    const { start, end } = computeNextTimes();
    tbodyEl.appendChild(createPeriodRow(computeNextNumber(), start, end));
  }

  function onTableClick(e) {
    const delBtn = e.target.closest(".btn-del-period");
    if (!delBtn) return;

    if (tbodyEl.querySelectorAll("tr").length <= 1) {
      ctx.showToast("จำเป็นต้องมีคาบเวลาอย่างน้อย 1 คาบ", true);
      return;
    }
    delBtn.closest("tr").remove();
  }

  function init(sharedCtx) {
    ctx = sharedCtx;
    const addBtnEl = document.getElementById("btnAddPeriod");
    tbodyEl = document.getElementById("periodsTbody");
    if (!addBtnEl || !tbodyEl) return;

    addBtnEl.addEventListener("click", addPeriodRow);
    tbodyEl.addEventListener("click", onTableClick);
  }

  ns.periods = { init };
})(window);
