/**
 * DBT Portal - Schedule Page Client Script
 * Handles:
 * - Mobile day tab switching
 * - Course slot interactive detail modal
 * - High-parity Print / PDF / PNG export via html2canvas
 *
 * Rules: Zero unescaped innerHTML string injection, strict DOM nodes for security.
 */

(function (window, document) {
  "use strict";

  function getExportConfig() {
    const actionWrap = document.querySelector(".scc-action-buttons");
    return {
      tab: actionWrap?.dataset.tab || "class",
      fileName: actionWrap?.dataset.filename || "schedule",
      groupName: actionWrap?.dataset.group || "",
    };
  }

  // 1. Mobile Tab Switching & Interactive Course Details
  document.addEventListener("DOMContentLoaded", () => {
    // Mobile Day Tab Switching
    const tabs = document.querySelectorAll(".day-tab");
    const slotGroups = document.querySelectorAll(".slot-group");

    tabs.forEach((tab) => {
      tab.addEventListener("click", () => {
        const day = tab.getAttribute("data-day");
        tabs.forEach((t) => {
          t.classList.remove("is-active");
          t.setAttribute("aria-selected", "false");
        });
        tab.classList.add("is-active");
        tab.setAttribute("aria-selected", "true");

        slotGroups.forEach((grp) => {
          if (grp.getAttribute("data-day") === day) {
            grp.removeAttribute("hidden");
          } else {
            grp.setAttribute("hidden", "");
          }
        });
      });
    });

    // Course Slot Click -> Open Details Modal
    const modalEl = document.getElementById("courseDetailModal");
    if (modalEl && typeof bootstrap !== "undefined") {
      const detailModal = bootstrap.Modal.getOrCreateInstance(modalEl);

      document.addEventListener("click", (e) => {
        const card = e.target.closest(".js-slot-card");
        if (!card) return;

        const code = card.getAttribute("data-code") || "";
        const name = card.getAttribute("data-name") || "";
        const type = card.getAttribute("data-type") || "regular";
        const credit = card.getAttribute("data-credit") || "3";
        const teacher = card.getAttribute("data-teacher") || "-";
        const room = card.getAttribute("data-room") || "-";
        const time = card.getAttribute("data-time") || "";
        const day = card.getAttribute("data-day") || "";
        const group = card.getAttribute("data-group") || "";

        const codeEl = document.getElementById("detailSubjectCode");
        const nameEl = document.getElementById("detailSubjectName");
        const creditEl = document.getElementById("detailCredit");
        const timeEl = document.getElementById("detailTime");
        const teacherEl = document.getElementById("detailTeacher");
        const roomEl = document.getElementById("detailRoom");
        const groupEl = document.getElementById("detailGroup");
        const typeBadge = document.getElementById("detailCourseType");

        if (codeEl) codeEl.textContent = code;
        if (nameEl) nameEl.textContent = name;
        if (creditEl) creditEl.textContent = `${credit} หน่วยกิต`;
        if (timeEl) timeEl.textContent = `${day} ${time}`;
        if (teacherEl) teacherEl.textContent = teacher;
        if (roomEl) roomEl.textContent = room;
        if (groupEl) groupEl.textContent = group;

        if (typeBadge) {
          if (type === "professional") {
            typeBadge.className = "badge bg-warning text-dark";
            typeBadge.textContent = "วิชาชีพเฉพาะ";
          } else {
            typeBadge.className = "badge bg-light text-muted border";
            typeBadge.textContent = "วิชาสามัญ / พื้นฐาน";
          }
        }

        detailModal.show();
      });
    }

    // Bind export button listeners
    const btnPrint = document.getElementById("btn-export-print");
    if (btnPrint) {
      btnPrint.addEventListener("click", exportSchedulePrint);
    }
    const btnPdf = document.getElementById("btn-export-pdf");
    if (btnPdf) {
      btnPdf.addEventListener("click", exportSchedulePDF);
    }
    const btnPng = document.getElementById("btn-export-png");
    if (btnPng) {
      btnPng.addEventListener("click", exportSchedulePNG);
    }
  });

  // 2. PDF & Print Export
  function exportSchedulePrint() {
    window.print();
  }

  function exportSchedulePDF() {
    const config = getExportConfig();
    const originalTitle = document.title;
    document.title = config.fileName;
    window.print();
    const restoreTitle = () => {
      document.title = originalTitle;
      window.removeEventListener("afterprint", restoreTitle);
    };
    window.addEventListener("afterprint", restoreTitle);
    setTimeout(restoreTitle, 2000);
  }

  // 3. PNG Export via html2canvas (Exact Print/PDF Parity)
  function loadHtml2Canvas() {
    if (typeof html2canvas !== "undefined") {
      return Promise.resolve();
    }
    return new Promise((resolve, reject) => {
      const s = document.createElement("script");
      s.src =
        "https://cdn.jsdelivr.net/npm/html2canvas@1.4.1/dist/html2canvas.min.js";
      s.onload = resolve;
      s.onerror = () =>
        reject(new Error("ไม่สามารถโหลดไลบรารีสำหรับสร้างรูปภาพได้"));
      document.head.appendChild(s);
    });
  }

  async function exportSchedulePNG() {
    const btn = document.getElementById("btn-export-png");
    const originalHtml = btn ? btn.innerHTML : "";
    if (btn) {
      btn.disabled = true;
      btn.textContent = "กำลังสร้างภาพ...";
    }

    try {
      await loadHtml2Canvas();

      const config = getExportConfig();
      const isClassTab = config.tab === "class";
      const targetFileName = config.fileName;
      const groupName = config.groupName;
      let sourceEl;
      let cleanup = null;

      if (document.fonts && document.fonts.ready) {
        await document.fonts.ready;
      }

      if (isClassTab) {
        const originalWrap = document.querySelector(".timetable-wrap-user");
        if (!originalWrap) throw new Error("ไม่พบข้อมูลตารางเรียน");

        // Create capture wrapper styled with schedule-print-view (matches @media print / PDF)
        const cloneContainer = document.createElement("div");
        cloneContainer.className = "schedule-print-view";
        cloneContainer.style.position = "fixed";
        cloneContainer.style.left = "0";
        cloneContainer.style.top = "0";
        cloneContainer.style.opacity = "1";
        cloneContainer.style.pointerEvents = "none";
        cloneContainer.style.zIndex = "-99999";
        cloneContainer.style.width = "1280px";

        // Explicit inline styles to guarantee print layout parity
        const captureStyle = document.createElement("style");
        captureStyle.textContent = `
          .schedule-print-view { background: #ffffff !important; color: #0e1e2e !important; font-family: 'Prompt', -apple-system, BlinkMacSystemFont, sans-serif !important; padding: 24px !important; box-sizing: border-box !important; }
          .schedule-print-view .page-head-print { font-size: 1.25rem !important; font-weight: 700 !important; margin: 0 0 8px 0 !important; padding-bottom: 8px !important; color: #17324d !important; border-bottom: 1.5px solid #17324d !important; font-family: 'Prompt', sans-serif !important; }
          .schedule-print-view .print-metadata-header { display: flex !important; justify-content: space-between !important; align-items: center !important; font-family: 'IBM Plex Mono', Menlo, Consolas, monospace !important; font-size: 11px !important; color: #57606a !important; margin-bottom: 8px !important; padding-bottom: 4px !important; border-bottom: 1px dashed #cbd5e1 !important; }
          .schedule-print-view .timetable-wrap-user { overflow: visible !important; width: 100% !important; margin: 0 !important; padding: 0 !important; border: none !important; background: #ffffff !important; box-shadow: none !important; }
          .schedule-print-view .week-table { width: 100% !important; table-layout: fixed !important; border-collapse: collapse !important; border: 1.5px solid #17324d !important; font-size: 11px !important; line-height: 1.35 !important; }
          .schedule-print-view .week-table tbody tr, .schedule-print-view .week-table tbody td { height: auto !important; min-height: 0 !important; }
          .schedule-print-view .week-table th, .schedule-print-view .week-table td { padding: 6px 8px !important; border: 1px solid #94a3b8 !important; background: #ffffff !important; color: #0e1e2e !important; box-sizing: border-box !important; }
          .schedule-print-view .week-table th { background: #f1f5f9 !important; color: #17324d !important; font-weight: 700 !important; text-align: center !important; }
          .schedule-print-view .period-head-inner .period-name { font-weight: 700 !important; font-size: 11px !important; color: #17324d !important; }
          .schedule-print-view .period-head-inner .period-time { font-family: 'IBM Plex Mono', Menlo, Consolas, monospace !important; font-size: 10px !important; color: #57606a !important; }
          .schedule-print-view .week-table td.col-day { background: #f8fafc !important; font-weight: 700 !important; color: #17324d !important; width: 85px !important; text-align: center !important; border-left: 1px solid #94a3b8 !important; font-size: 11.5px !important; }
          .schedule-print-view .week-table td.slot-class { padding: 0 !important; vertical-align: top !important; background: #ffffff !important; }
          .schedule-print-view .week-table td.slot-class .cell-slot { padding: 6px 8px !important; box-sizing: border-box !important; height: 100% !important; min-height: 68px !important; display: flex !important; flex-direction: column !important; justify-content: space-between !important; background: #ffffff !important; box-shadow: none !important; transform: none !important; border-left: 0 !important; border-radius: 0 !important; }
          .schedule-print-view .cell-slot .slot-head { margin-bottom: 2px !important; }
          .schedule-print-view .cell-slot .slot-code { font-weight: 700 !important; color: #17324d !important; font-size: 11px !important; font-family: 'IBM Plex Mono', Menlo, Consolas, monospace !important; }
          .schedule-print-view .cell-slot .badge { background: transparent !important; border: none !important; color: #57606a !important; font-weight: normal !important; font-size: 9.5px !important; padding: 0 !important; }
          .schedule-print-view .cell-slot .s, .schedule-print-view .cell-slot .slot-name { display: block !important; -webkit-line-clamp: unset !important; -webkit-box-orient: unset !important; overflow: visible !important; white-space: normal !important; word-break: break-word !important; font-family: 'Prompt', -apple-system, BlinkMacSystemFont, sans-serif !important; font-size: 11px !important; font-weight: 600 !important; line-height: 1.35 !important; margin: 2px 0 4px 0 !important; color: #0e1e2e !important; }
          .schedule-print-view .cell-slot .r { display: flex !important; align-items: center !important; justify-content: space-between !important; gap: 4px !important; margin-top: auto !important; padding-top: 4px !important; border-top: 1px dashed #cbd5e1 !important; }
          .schedule-print-view .cell-slot .r .teacher { display: inline-flex !important; align-items: center !important; gap: 3px !important; white-space: normal !important; overflow: visible !important; text-overflow: clip !important; max-width: none !important; font-family: 'Prompt', -apple-system, BlinkMacSystemFont, sans-serif !important; font-size: 10px !important; color: #57606a !important; line-height: 1.25 !important; }
          .schedule-print-view .cell-slot .r .room { flex-shrink: 0 !important; font-size: 10px !important; font-weight: 600 !important; color: #57606a !important; background: transparent !important; border: none !important; padding: 0 !important; border-radius: 0 !important; font-family: 'IBM Plex Mono', Menlo, Consolas, monospace !important; }
          .schedule-print-view .slot-flag, .schedule-print-view .slot-lunch, .schedule-print-view .slot-empty { background: #f8fafc !important; }
          .schedule-print-view .slot-flag .special-slot-inner, .schedule-print-view .slot-lunch .special-slot-inner, .schedule-print-view .slot-empty .empty-slot-inner { padding: 6px 8px !important; text-align: center !important; font-size: 11px !important; color: #57606a !important; }
          .schedule-print-view .special-title { font-weight: 600 !important; color: #17324d !important; font-size: 11px !important; }
          .schedule-print-view .badge-today-pill, .schedule-print-view .is-current-class, .schedule-print-view .is-next-class { border-left: 0 !important; box-shadow: none !important; transform: none !important; background: transparent !important; }
          .schedule-print-view .week-table tbody tr.is-today td, .schedule-print-view .week-table tbody tr:hover td { background: transparent !important; }
        `;
        cloneContainer.appendChild(captureStyle);

        // Header mirroring Print/PDF structure with pure DOM nodes (Zero innerHTML)
        const header = document.createElement("div");
        const headerTitle = document.createElement("h1");
        headerTitle.className = "page-head-print";
        headerTitle.textContent = "ตารางเรียนและตารางกิจกรรม";

        const metaHeader = document.createElement("div");
        metaHeader.className = "print-metadata-header";

        const metaGroup = document.createElement("span");
        metaGroup.textContent = `สาขาวิชาเทคโนโลยีธุรกิจดิจิทัล — ตารางเรียนระดับชั้น: ${groupName}`;

        const metaDate = document.createElement("span");
        metaDate.textContent = `พิมพ์เมื่อ: ${new Date().toLocaleDateString("th-TH")} ${new Date().toLocaleTimeString("th-TH", { hour: "2-digit", minute: "2-digit" })} น.`;

        metaHeader.appendChild(metaGroup);
        metaHeader.appendChild(metaDate);
        header.appendChild(headerTitle);
        header.appendChild(metaHeader);
        cloneContainer.appendChild(header);

        const clonedTable = originalWrap.cloneNode(true);
        clonedTable.classList.remove("d-none", "d-md-block");
        clonedTable.style.display = "block";

        // Clean up interactive classes and badges to match print/PDF
        clonedTable
          .querySelectorAll(".teacher")
          .forEach((el) => el.classList.remove("text-truncate"));
        clonedTable
          .querySelectorAll(".is-current-class, .is-next-class, .is-today")
          .forEach((el) => {
            el.classList.remove(
              "is-current-class",
              "is-next-class",
              "is-today",
            );
          });
        clonedTable
          .querySelectorAll(".badge-today-pill")
          .forEach((el) => el.remove());
        clonedTable.querySelectorAll(".badge").forEach((b) => {
          const text = b.textContent.trim();
          b.textContent = text ? "(" + text + ")" : "";
        });

        cloneContainer.appendChild(clonedTable);

        document.body.appendChild(cloneContainer);
        sourceEl = cloneContainer;
        cleanup = () => {
          if (cloneContainer.parentNode)
            cloneContainer.parentNode.removeChild(cloneContainer);
        };
      } else {
        const activityWrap = document.querySelector(".max-w-720");
        if (!activityWrap) throw new Error("ไม่พบข้อมูลตารางกิจกรรม");

        const cloneContainer = document.createElement("div");
        cloneContainer.className = "schedule-print-view";
        cloneContainer.style.position = "fixed";
        cloneContainer.style.left = "0";
        cloneContainer.style.top = "0";
        cloneContainer.style.opacity = "1";
        cloneContainer.style.pointerEvents = "none";
        cloneContainer.style.zIndex = "-99999";
        cloneContainer.style.width = "880px";

        // Header for activity tab using safe DOM nodes (Zero innerHTML)
        const header = document.createElement("div");
        const headerTitle = document.createElement("h1");
        headerTitle.className = "page-head-print";
        headerTitle.textContent = "ตารางกิจกรรมสาขา";

        const metaHeader = document.createElement("div");
        metaHeader.className = "print-metadata-header";

        const metaDept = document.createElement("span");
        metaDept.textContent =
          "สาขาวิชาเทคโนโลยีธุรกิจดิจิทัล — ปฏิทินและกิจกรรมสาขา";

        const metaDate = document.createElement("span");
        metaDate.textContent = `สร้างเมื่อ: ${new Date().toLocaleDateString("th-TH")} ${new Date().toLocaleTimeString("th-TH", { hour: "2-digit", minute: "2-digit" })} น.`;

        metaHeader.appendChild(metaDept);
        metaHeader.appendChild(metaDate);
        header.appendChild(headerTitle);
        header.appendChild(metaHeader);
        cloneContainer.appendChild(header);

        const clonedActivity = activityWrap.cloneNode(true);
        clonedActivity.style.maxWidth = "100%";
        clonedActivity.querySelectorAll(".btn").forEach((el) => el.remove());
        cloneContainer.appendChild(clonedActivity);

        document.body.appendChild(cloneContainer);
        sourceEl = cloneContainer;
        cleanup = () => {
          if (cloneContainer.parentNode)
            cloneContainer.parentNode.removeChild(cloneContainer);
        };
      }

      const canvas = await html2canvas(sourceEl, {
        scale: 2,
        useCORS: true,
        backgroundColor: "#ffffff",
        logging: false,
        scrollX: 0,
        scrollY: 0,
        windowWidth: 1350,
      });

      if (cleanup) cleanup();

      const link = document.createElement("a");
      link.download = targetFileName + ".png";
      link.href = canvas.toDataURL("image/png");
      document.body.appendChild(link);
      link.click();
      document.body.removeChild(link);
    } catch (err) {
      console.error("Export PNG failed:", err);
      alert(
        "เกิดข้อผิดพลาดในการสร้างรูปภาพ: " +
          (err.message || "กรุณาลองใหม่อีกครั้ง"),
      );
    } finally {
      if (btn) {
        btn.disabled = false;
        btn.innerHTML = originalHtml;
      }
    }
  }

  // Expose functions globally for backward compatibility
  window.exportSchedulePrint = exportSchedulePrint;
  window.exportSchedulePDF = exportSchedulePDF;
  window.exportSchedulePNG = exportSchedulePNG;
})(typeof window !== "undefined" ? window : this, document);
