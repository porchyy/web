/**
 * scheduler-modals.js - Slot & master-course modal handling
 *
 * - Opens the slot modal in add / duplicate / edit mode (event delegation)
 * - Opens the master-course modal (edit / new)
 * - Auto-fills slot fields when a master course is selected
 * - Submits the slot form via AJAX (zero page reload on the grid view)
 */
(function (global) {
  "use strict";

  const ns = (global.DBTScheduler = global.DBTScheduler || {});

  const FORM_RELOAD_DELAY_MS = 400;

  // Slot modal field keys -> element ids
  const SLOT_FIELD_IDS = {
    slotId: "modalSlotId",
    classId: "modalClassId",
    courseText: "modalCourseSearch",
    group: "modalClassGroup",
    day: "modalDayOfWeek",
    start: "modalTimeStart",
    end: "modalTimeEnd",
    code: "modalSubjectCode",
    name: "modalSubjectName",
    teacher: "modalTeacherName",
    room: "modalRoom",
  };

  // Master-course modal element ids -> [data attribute, default value]
  const COURSE_FIELD_SOURCES = {
    courseModalId: ["data-id", ""],
    courseModalCode: ["data-code", ""],
    courseModalName: ["data-name", ""],
    courseModalType: ["data-type", "professional"],
    courseModalCredit: ["data-credit", "3"],
    courseModalTeacher: ["data-teacher", ""],
    courseModalRoom: ["data-room", ""],
  };

  let ctx = null;

  // --------------------------------------------------------------------------
  // Small DOM helpers
  // --------------------------------------------------------------------------
  const getSlotModalEl = () =>
    document.getElementById("modal-slot-editor") ||
    document.getElementById("slotModal");

  function setValue(id, value) {
    const el = document.getElementById(id);
    if (el) el.value = value;
  }

  function setText(id, text) {
    const el = document.getElementById(id);
    if (el) el.textContent = text;
  }

  function hideCourseMenu() {
    const menuEl = document.getElementById("courseComboboxMenu");
    if (menuEl) menuEl.style.display = "none";
  }

  function showModal(modalEl) {
    bootstrap.Modal.getOrCreateInstance(modalEl).show();
  }

  function hideModal(modalEl) {
    const instance = modalEl ? bootstrap.Modal.getInstance(modalEl) : null;
    if (instance) instance.hide();
  }

  const toHm = (time) => String(time).substring(0, 5);
  const courseLabel = (code, name) => `${code} ${name}`.trim();

  // Reads an attribute from the button first, then falls back to the card.
  function pickAttr(btnEl, cardEl, name, fallback = "") {
    return (
      (btnEl ? btnEl.getAttribute(name) : "") ||
      (cardEl ? cardEl.getAttribute(name) : "") ||
      fallback
    );
  }

  /**
   * Fill the slot modal and show it.  Keys left undefined are not touched,
   * which preserves whatever the user had previously in those inputs.
   */
  function openSlotModal(title, values) {
    const modalEl = getSlotModalEl();
    if (!modalEl) return;

    setText("slotModalTitle", title);
    Object.entries(SLOT_FIELD_IDS).forEach(([key, id]) => {
      if (values[key] !== undefined) setValue(id, values[key]);
    });
    hideCourseMenu();
    showModal(modalEl);
  }

  // --------------------------------------------------------------------------
  // Slot modal: add / duplicate / edit
  // --------------------------------------------------------------------------
  function openAddSlotModal(addBtn) {
    const day = addBtn.getAttribute("data-day");
    const start = addBtn.getAttribute("data-time-start");
    const end = addBtn.getAttribute("data-time-end");

    openSlotModal("เพิ่มคาบเรียนใหม่", {
      slotId: "",
      classId: "",
      courseText: "",
      group: addBtn.getAttribute("data-group") || ctx.currentGroup,
      day: day || undefined,
      start: start ? toHm(start) : undefined,
      end: end ? toHm(end) : undefined,
      code: "",
      name: "",
      teacher: "",
      room: "",
    });
  }

  function openDuplicateSlotModal(dupBtn) {
    const code = dupBtn.getAttribute("data-subject-code") || "";
    const name = dupBtn.getAttribute("data-subject-name") || "";

    openSlotModal("ทำซ้ำคาบเรียน (Duplicate Slot)", {
      slotId: "", // empty id creates a new slot
      classId: dupBtn.getAttribute("data-class-id") || "",
      courseText: courseLabel(code, name),
      group: dupBtn.getAttribute("data-group") || ctx.currentGroup,
      day: dupBtn.getAttribute("data-day") || "1",
      start: toHm(dupBtn.getAttribute("data-time-start") || "08:30"),
      end: toHm(dupBtn.getAttribute("data-time-end") || "10:30"),
      code,
      name,
      teacher: dupBtn.getAttribute("data-teacher-name") || "",
      room: dupBtn.getAttribute("data-room") || "",
    });
  }

  function openEditSlotModal(editBtn, slotCard) {
    const card = slotCard || editBtn.closest(".admin-slot-card");
    const btn = editBtn || (card ? card.querySelector(".btn-edit-slot-modal") : null);

    const id =
      (btn ? btn.getAttribute("data-id") : "") ||
      (card ? card.getAttribute("data-slot-id") : "") ||
      "";
    const code = pickAttr(btn, card, "data-subject-code");
    const name = pickAttr(btn, card, "data-subject-name");

    openSlotModal(id ? `แก้ไขคาบเรียน #${id}` : "แก้ไขข้อมูลคาบเรียน", {
      slotId: id,
      classId: pickAttr(btn, card, "data-class-id"),
      courseText: courseLabel(code, name),
      group: pickAttr(btn, card, "data-group", ctx.currentGroup),
      day: pickAttr(btn, card, "data-day", "1"),
      start: toHm(pickAttr(btn, card, "data-time-start", "08:30")),
      end: toHm(pickAttr(btn, card, "data-time-end", "10:30")),
      code,
      name,
      teacher: pickAttr(btn, card, "data-teacher-name"),
      room: pickAttr(btn, card, "data-room"),
    });
  }

  // --------------------------------------------------------------------------
  // Master course modal
  // --------------------------------------------------------------------------
  function openEditCourseModal(editCourseBtn) {
    const modalEl = document.getElementById("courseModal");
    if (!modalEl) return;

    setText("courseModalTitle", "แก้ไขข้อมูลรายวิชา");
    Object.entries(COURSE_FIELD_SOURCES).forEach(([id, [attrName, fallback]]) => {
      setValue(id, editCourseBtn.getAttribute(attrName) || fallback);
    });
    showModal(modalEl);
  }

  // The modal itself opens via data-bs-toggle; only reset its fields here.
  function resetNewCourseModal() {
    const formEl = document.getElementById("formCourseModal");
    if (formEl) formEl.reset();
    setText("courseModalTitle", "เพิ่มรายวิชาใหม่ในหลักสูตร");
    setValue("courseModalId", "");
    setValue("courseModalCredit", "3");
  }

  // --------------------------------------------------------------------------
  // Delegated click routing
  // --------------------------------------------------------------------------
  function onDocumentClick(e) {
    // Prevent opening modal if user just finished dragging or resizing
    if (ns.lastDragEndTime && Date.now() - ns.lastDragEndTime < 350) {
      e.preventDefault();
      e.stopPropagation();
      return;
    }

    const addBtn = e.target.closest(".btn-open-slot-modal");
    if (addBtn) {
      e.preventDefault();
      openAddSlotModal(addBtn);
      return;
    }

    const dupBtn = e.target.closest(".btn-duplicate-slot-modal");
    if (dupBtn) {
      e.preventDefault();
      e.stopPropagation();
      openDuplicateSlotModal(dupBtn);
      return;
    }

    const editBtn = e.target.closest(".btn-edit-slot-modal");
    const slotCard = e.target.closest(".admin-slot-card");
    const isSpecialAction = e.target.closest(
      '.slot-drag-handle, .slot-resize-handle, .btn-duplicate-slot-modal, form, button[type="submit"]',
    );
    if ((editBtn || slotCard) && !isSpecialAction) {
      e.preventDefault();
      openEditSlotModal(editBtn, slotCard);
      return;
    }

    const editCourseBtn = e.target.closest(".btn-edit-course");
    if (editCourseBtn) {
      e.preventDefault();
      openEditCourseModal(editCourseBtn);
      return;
    }

    if (e.target.closest("#btnOpenNewCourseModal")) {
      resetNewCourseModal();
    }
  }

  // --------------------------------------------------------------------------
  // Master course dropdown auto-fill & custom course toggle
  // --------------------------------------------------------------------------
  function setIfEmpty(id, value) {
    const el = document.getElementById(id);
    if (el && (!el.value || el.value.trim() === "")) el.value = value;
  }

  function onMasterCourseChange(classSelectEl) {
    const selectedOpt = classSelectEl.options[classSelectEl.selectedIndex];
    if (!selectedOpt || !selectedOpt.value) return;

    setValue("modalSubjectCode", selectedOpt.getAttribute("data-code") || "");
    setValue("modalSubjectName", selectedOpt.getAttribute("data-name") || "");
    setIfEmpty("modalTeacherName", selectedOpt.getAttribute("data-teacher") || "");
    setIfEmpty("modalRoom", selectedOpt.getAttribute("data-room") || "");
  }

  function onToggleCustomCourse() {
    setValue("modalClassId", "");
    setValue("modalCourseSearch", "");
    hideCourseMenu();

    const codeInput = document.getElementById("modalSubjectCode");
    if (codeInput) {
      codeInput.focus();
      codeInput.select();
    }
  }

  // --------------------------------------------------------------------------
  // AJAX slot form submission (zero page reload)
  // --------------------------------------------------------------------------
  async function onSlotFormSubmit(e, formEl) {
    e.preventDefault();
    const submitBtn = document.getElementById("modalSubmitBtn");
    if (submitBtn) submitBtn.disabled = true;

    const formData = new FormData(formEl);
    formData.append("ajax", "1");

    try {
      const data = await ns.api.postForm(
        formData,
        "เกิดข้อผิดพลาดในการบันทึกข้อมูล",
      );
      ctx.showToast(data.message || "บันทึกข้อมูลเรียบร้อยแล้ว");
      hideModal(getSlotModalEl());

      if (ctx.gridEl) {
        ns.api.applyGridUpdate(data);
      } else {
        // List view or other view: reload to display updated records
        setTimeout(() => window.location.reload(), FORM_RELOAD_DELAY_MS);
      }
    } catch (err) {
      ctx.showToast(err.message, true);
    } finally {
      if (submitBtn) submitBtn.disabled = false;
    }
  }

  function init(sharedCtx) {
    ctx = sharedCtx;

    document.addEventListener("click", onDocumentClick);

    const classSelectEl = document.getElementById("modalClassId");
    if (classSelectEl) {
      classSelectEl.addEventListener("change", () =>
        onMasterCourseChange(classSelectEl),
      );
    }

    const toggleCustomBtn = document.getElementById("btnToggleNewCourseInline");
    if (toggleCustomBtn) {
      toggleCustomBtn.addEventListener("click", onToggleCustomCourse);
    }

    const slotFormEl =
      document.getElementById("form-slot-modal") ||
      document.getElementById("slotModalForm");
    if (slotFormEl) {
      slotFormEl.addEventListener("submit", (e) => onSlotFormSubmit(e, slotFormEl));
    }
  }

  ns.modals = { init };
})(window);
