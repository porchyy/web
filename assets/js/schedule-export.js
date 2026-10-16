/**
 * schedule-export.js - Client-side UI & Export Controls for Schedule & Timetable
 * Complies with RULES.md (Rule 4.1 & 4.2: External JS & Safe DOM Construction)
 */
document.addEventListener('DOMContentLoaded', () => {
  // Mobile Tab Switching
  const tabs = document.querySelectorAll('.day-tab');
  const slotGroups = document.querySelectorAll('.slot-group');

  tabs.forEach(tab => {
    tab.addEventListener('click', () => {
      const day = tab.getAttribute('data-day');
      tabs.forEach(t => {
        t.classList.remove('is-active');
        t.setAttribute('aria-selected', 'false');
      });
      tab.classList.add('is-active');
      tab.setAttribute('aria-selected', 'true');

      slotGroups.forEach(grp => {
        if (grp.getAttribute('data-day') === day) {
          grp.removeAttribute('hidden');
        } else {
          grp.setAttribute('hidden', '');
        }
      });
    });
  });

  // Interactive Course Slot Click -> Open Details Modal
  const modalEl = document.getElementById('courseDetailModal');
  if (modalEl && typeof bootstrap !== 'undefined') {
    const detailModal = bootstrap.Modal.getOrCreateInstance(modalEl);

    document.addEventListener('click', (e) => {
      const card = e.target.closest('.js-slot-card');
      if (!card) return;

      const code    = card.getAttribute('data-code') || '';
      const name    = card.getAttribute('data-name') || '';
      const type    = card.getAttribute('data-type') || 'regular';
      const credit  = card.getAttribute('data-credit') || '3';
      const teacher = card.getAttribute('data-teacher') || '-';
      const room    = card.getAttribute('data-room') || '-';
      const time    = card.getAttribute('data-time') || '';
      const day     = card.getAttribute('data-day') || '';
      const group   = card.getAttribute('data-group') || '';

      const elCode = document.getElementById('detailSubjectCode');
      const elName = document.getElementById('detailSubjectName');
      const elCredit = document.getElementById('detailCredit');
      const elTime = document.getElementById('detailTime');
      const elTeacher = document.getElementById('detailTeacher');
      const elRoom = document.getElementById('detailRoom');
      const elGroup = document.getElementById('detailGroup');
      const typeBadge = document.getElementById('detailCourseType');

      if (elCode) elCode.textContent = code;
      if (elName) elName.textContent = name;
      if (elCredit) elCredit.textContent = `${credit} หน่วยกิต`;
      if (elTime) elTime.textContent = `${day} ${time}`;
      if (elTeacher) elTeacher.textContent = teacher;
      if (elRoom) elRoom.textContent = room;
      if (elGroup) elGroup.textContent = group;

      if (typeBadge) {
        if (type === 'professional') {
          typeBadge.className = 'badge bg-warning text-dark';
          typeBadge.textContent = 'วิชาชีพเฉพาะ';
        } else {
          typeBadge.className = 'badge bg-light text-muted border';
          typeBadge.textContent = 'วิชาสามัญ / พื้นฐาน';
        }
      }

      detailModal.show();
    });
  }

  // Bind Export Buttons
  const btnPrint = document.getElementById('btn-export-print');
  if (btnPrint) {
    btnPrint.addEventListener('click', exportSchedulePrint);
  }

  const btnPDF = document.getElementById('btn-export-pdf');
  if (btnPDF) {
    btnPDF.addEventListener('click', exportSchedulePDF);
  }

  const btnPNG = document.getElementById('btn-export-png');
  if (btnPNG) {
    btnPNG.addEventListener('click', exportSchedulePNG);
  }
});

function getScheduleConfig() {
  const root = document.getElementById('schedule-root') || document.body;
  return {
    exportFileName: root.dataset.exportFileName || 'ตารางเรียน',
    selectedGroup: root.dataset.selectedGroup || 'ปวช.2/1',
    isClassTab: root.dataset.isClassTab === '1'
  };
}

function exportSchedulePrint() {
  window.print();
}

function exportSchedulePDF() {
  const cfg = getScheduleConfig();
  const originalTitle = document.title;
  document.title = cfg.exportFileName;
  window.print();
  const restoreTitle = () => {
    document.title = originalTitle;
    window.removeEventListener('afterprint', restoreTitle);
  };
  window.addEventListener('afterprint', restoreTitle);
  setTimeout(restoreTitle, 2000);
}

function loadHtml2Canvas() {
  if (typeof html2canvas !== 'undefined') {
    return Promise.resolve();
  }
  return new Promise((resolve, reject) => {
    const s = document.createElement('script');
    s.src = 'https://cdn.jsdelivr.net/npm/html2canvas@1.4.1/dist/html2canvas.min.js';
    s.onload = resolve;
    s.onerror = () => reject(new Error('ไม่สามารถโหลดไลบรารีสำหรับสร้างรูปภาพได้'));
    document.head.appendChild(s);
  });
}

function createCaptureHeader(titleText, subText) {
  const header = document.createElement('div');
  header.style.display = 'flex';
  header.style.justifyContent = 'space-between';
  header.style.alignItems = 'center';
  header.style.paddingBottom = '12px';
  header.style.marginBottom = '16px';
  header.style.borderBottom = '2px solid #193651';

  const titleCol = document.createElement('div');
  const h = document.createElement('div');
  h.style.fontSize = '18px';
  h.style.fontWeight = '700';
  h.style.color = '#193651';
  h.textContent = titleText;
  titleCol.appendChild(h);

  if (subText) {
    const sub = document.createElement('div');
    sub.style.fontSize = '12px';
    sub.style.color = '#64748B';
    sub.style.marginTop = '2px';
    sub.textContent = subText;
    titleCol.appendChild(sub);
  }

  const dateCol = document.createElement('div');
  dateCol.style.fontSize = '12px';
  dateCol.style.color = '#94A3B8';
  dateCol.style.textAlign = 'right';
  dateCol.textContent = 'สร้างเมื่อ: ' + new Date().toLocaleDateString('th-TH');

  header.appendChild(titleCol);
  header.appendChild(dateCol);
  return header;
}

async function exportSchedulePNG() {
  const btn = document.getElementById('btn-export-png');
  const originalHtml = btn ? btn.innerHTML : '';
  if (btn) {
    btn.disabled = true;
    const loadingSpan = document.createElement('span');
    loadingSpan.textContent = 'กำลังสร้างภาพ...';
    btn.textContent = '';
    btn.appendChild(loadingSpan);
  }

  try {
    await loadHtml2Canvas();
    const cfg = getScheduleConfig();
    let sourceEl;
    let cleanup = null;

    if (document.fonts && document.fonts.ready) {
      await document.fonts.ready;
    }

    if (cfg.isClassTab) {
      const originalWrap = document.querySelector('.timetable-wrap-user');
      if (!originalWrap) throw new Error('ไม่พบข้อมูลตารางเรียน');

      const cloneContainer = document.createElement('div');
      cloneContainer.style.position = 'fixed';
      cloneContainer.style.left = '0';
      cloneContainer.style.top = '0';
      cloneContainer.style.opacity = '0';
      cloneContainer.style.pointerEvents = 'none';
      cloneContainer.style.zIndex = '-99999';
      cloneContainer.style.width = '1320px';
      cloneContainer.style.background = '#ffffff';
      cloneContainer.style.padding = '28px';
      cloneContainer.style.boxSizing = 'border-box';
      cloneContainer.style.fontFamily = "'Prompt', -apple-system, BlinkMacSystemFont, sans-serif";

      const captureStyle = document.createElement('style');
      captureStyle.textContent = `
        .timetable-wrap-user table.week-table {
          width: 100% !important;
          table-layout: fixed !important;
          border-collapse: collapse !important;
        }
        .timetable-wrap-user .cell-slot {
          height: auto !important;
          min-height: 84px !important;
          display: flex !important;
          flex-direction: column !important;
          justify-content: space-between !important;
          box-shadow: none !important;
          transform: none !important;
          padding: 8px 10px !important;
        }
        .timetable-wrap-user .cell-slot .s,
        .timetable-wrap-user .cell-slot .slot-name {
          display: block !important;
          -webkit-line-clamp: unset !important;
          -webkit-box-orient: unset !important;
          overflow: visible !important;
          white-space: normal !important;
          word-break: break-word !important;
          font-family: 'Prompt', 'Kanit', sans-serif !important;
          font-size: 11.5px !important;
          font-weight: 600 !important;
          line-height: 1.45 !important;
          margin: 3px 0 6px 0 !important;
          color: #1e293b !important;
        }
        .timetable-wrap-user .cell-slot .r {
          display: flex !important;
          justify-content: space-between !important;
          margin-top: auto !important;
        }
        .timetable-wrap-user .cell-slot .t-name,
        .timetable-wrap-user .cell-slot .r-name {
          font-family: 'Prompt', sans-serif !important;
          font-size: 10.5px !important;
          white-space: nowrap !important;
        }
      `;
      cloneContainer.appendChild(captureStyle);

      const header = createCaptureHeader(
        'แผนกวิชาเทคโนโลยีธุรกิจดิจิทัล — ตารางเรียนระดับชั้น ' + cfg.selectedGroup,
        'หลักสูตรประกาศนียบัตรวิชาชีพ (ปวช.) / ชั้นสูง (ปวส.)'
      );
      cloneContainer.appendChild(header);

      const clonedTableWrap = originalWrap.cloneNode(true);
      clonedTableWrap.style.overflow = 'visible';
      clonedTableWrap.style.display = 'block';
      clonedTableWrap.style.width = '100%';
      cloneContainer.appendChild(clonedTableWrap);

      document.body.appendChild(cloneContainer);
      sourceEl = cloneContainer;
      cleanup = () => {
        if (cloneContainer.parentNode) cloneContainer.parentNode.removeChild(cloneContainer);
      };
    } else {
      const activityWrap = document.querySelector('.activity-timeline-user') || document.querySelector('.activity-list-container');
      if (!activityWrap) throw new Error('ไม่พบข้อมูลตารางกิจกรรม');

      const cloneContainer = document.createElement('div');
      cloneContainer.style.position = 'fixed';
      cloneContainer.style.left = '0';
      cloneContainer.style.top = '0';
      cloneContainer.style.opacity = '0';
      cloneContainer.style.pointerEvents = 'none';
      cloneContainer.style.zIndex = '-99999';
      cloneContainer.style.width = '1000px';
      cloneContainer.style.background = '#ffffff';
      cloneContainer.style.padding = '28px';
      cloneContainer.style.boxSizing = 'border-box';
      cloneContainer.style.fontFamily = "'Prompt', -apple-system, BlinkMacSystemFont, sans-serif";

      const header = createCaptureHeader(
        'แผนกวิชาเทคโนโลยีธุรกิจดิจิทัล — ปฏิทินและกิจกรรมแผนก',
        null
      );
      cloneContainer.appendChild(header);

      const clonedActivity = activityWrap.cloneNode(true);
      clonedActivity.style.maxWidth = '100%';
      cloneContainer.appendChild(clonedActivity);

      document.body.appendChild(cloneContainer);
      sourceEl = cloneContainer;
      cleanup = () => {
        if (cloneContainer.parentNode) cloneContainer.parentNode.removeChild(cloneContainer);
      };
    }

    const canvas = await html2canvas(sourceEl, {
      scale: 2,
      useCORS: true,
      backgroundColor: '#ffffff',
      logging: false,
      scrollX: 0,
      scrollY: 0,
      windowWidth: 1400
    });

    if (cleanup) cleanup();

    const link = document.createElement('a');
    link.download = cfg.exportFileName + '.png';
    link.href = canvas.toDataURL('image/png');
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
  } catch (err) {
    console.error('Export PNG failed:', err);
    alert('เกิดข้อผิดพลาดในการสร้างรูปภาพ: ' + (err.message || 'กรุณาลองใหม่อีกครั้ง'));
  } finally {
    if (btn) {
      btn.disabled = false;
      btn.innerHTML = originalHtml;
    }
  }
}
