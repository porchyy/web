(function () {
  'use strict';

  document.addEventListener('DOMContentLoaded', () => {
    const editor = document.getElementById('timetableModeEditor');
    if (!editor) return;

    const modeInput = document.getElementById('timetableDisplayMode');
    const status = document.getElementById('timetableModeStatus');
    const buttons = editor.querySelectorAll('[data-timetable-mode-choice]');
    const panels = document.querySelectorAll('[data-timetable-mode-panel]');

    function selectMode(mode) {
      modeInput.value = mode;
      panels.forEach(panel => {
        const inactive = panel.dataset.timetableModePanel !== mode;
        panel.hidden = inactive;
        if (panel.tagName === 'FIELDSET') panel.disabled = inactive;
      });
      buttons.forEach(button => {
        const active = button.dataset.timetableModeChoice === mode;
        button.setAttribute('aria-pressed', String(active));
        button.classList.toggle('btn-navy', active);
        button.classList.toggle('btn-line', !active);
      });
      status.textContent = mode === editor.dataset.savedMode
        ? 'รูปแบบที่เลือกตรงกับที่เผยแพร่บนหน้าเว็บ'
        : 'ยังไม่ได้บันทึกโหมดใหม่ กดบันทึกรูปแบบเพื่อเปลี่ยนการแสดงผลบนหน้าเว็บ';
    }

    buttons.forEach(button => button.addEventListener('click', () => selectMode(button.dataset.timetableModeChoice)));
    const groupSelect = document.getElementById('adminClassGroupSelect');
    groupSelect.addEventListener('change', () => {
      window.location.href = groupSelect.dataset.groupUrl + encodeURIComponent(groupSelect.value);
    });
    selectMode(modeInput.value);
  });
})();
