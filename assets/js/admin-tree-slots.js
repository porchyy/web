/**
 * admin-tree-slots.js - Interactive Tree-like Slot Management & HTML5 Drag-and-Drop
 * 
 * Strict Vanilla JS (Ponytail) - Zero external dependencies.
 * Supports Member Reordering and Head/President Position Swapping.
 * Safe DOM manipulation - Zero innerHTML string injections.
 */

(function () {
  'use strict';

  let draggedItemEl = null;
  let isSwapping = false;

  function showToast(message, isError = false) {
    if (typeof window.showToast === 'function') {
      window.showToast(message, isError ? 'error' : 'success');
      return;
    }

    let toastEl = document.getElementById('tree-toast');
    if (!toastEl) {
      toastEl = document.createElement('div');
      toastEl.id = 'tree-toast';
      toastEl.className = 'admin-toast';
      document.body.appendChild(toastEl);
    }

    toastEl.textContent = message;
    toastEl.classList.remove('is-error');
    if (isError) {
      toastEl.classList.add('is-error');
    }
    toastEl.classList.add('is-active');

    setTimeout(() => {
      toastEl.classList.remove('is-active');
    }, 2800);
  }

  function initTreeDragDrop(gridEl) {
    if (!gridEl) return;

    const cards = gridEl.querySelectorAll('.tree-slot-card[draggable="true"]');
    const endpoint = gridEl.dataset.reorderEndpoint;
    const csrfToken = gridEl.dataset.csrf;

    cards.forEach((cardEl) => {
      cardEl.addEventListener('dragstart', (e) => {
        draggedItemEl = cardEl;
        isSwapping = false;
        cardEl.classList.add('is-dragging');
        e.dataTransfer.effectAllowed = 'move';
        e.dataTransfer.setData('text/plain', cardEl.dataset.id || '');
      });

      cardEl.addEventListener('dragend', () => {
        cardEl.classList.remove('is-dragging');
        gridEl.querySelectorAll('.tree-slot-card').forEach((el) => {
          el.classList.remove('is-drag-over');
        });

        if (isSwapping) {
          draggedItemEl = null;
          return;
        }

        draggedItemEl = null;

        // Persist new order
        saveNewOrder(gridEl, endpoint, csrfToken);
      });

      cardEl.addEventListener('dragover', (e) => {
        e.preventDefault();
        e.dataTransfer.dropEffect = 'move';

        if (!draggedItemEl || draggedItemEl === cardEl) return;

        const bounding = cardEl.getBoundingClientRect();
        const offset = e.clientY - bounding.top;
        const isPastMiddle = offset > bounding.height / 2;

        if (isPastMiddle) {
          cardEl.after(draggedItemEl);
        } else {
          cardEl.before(draggedItemEl);
        }
      });

      cardEl.addEventListener('dragenter', (e) => {
        e.preventDefault();
        if (draggedItemEl !== cardEl) {
          cardEl.classList.add('is-drag-over');
        }
      });

      cardEl.addEventListener('dragleave', () => {
        cardEl.classList.remove('is-drag-over');
      });

      cardEl.addEventListener('drop', (e) => {
        e.preventDefault();
        cardEl.classList.remove('is-drag-over');
      });
    });
  }

  function initHeadDropzone(dropzoneEl, gridEl) {
    if (!dropzoneEl || !gridEl) return;

    const endpoint = gridEl.dataset.reorderEndpoint;
    const csrfToken = gridEl.dataset.csrf;

    dropzoneEl.addEventListener('dragover', (e) => {
      e.preventDefault();
      if (!draggedItemEl) return;
      e.dataTransfer.dropEffect = 'move';
      dropzoneEl.classList.add('is-drag-over');
    });

    dropzoneEl.addEventListener('dragenter', (e) => {
      e.preventDefault();
      if (draggedItemEl) {
        dropzoneEl.classList.add('is-drag-over');
      }
    });

    dropzoneEl.addEventListener('dragleave', () => {
      dropzoneEl.classList.remove('is-drag-over');
    });

    dropzoneEl.addEventListener('drop', (e) => {
      e.preventDefault();
      dropzoneEl.classList.remove('is-drag-over');

      if (!draggedItemEl) return;
      const draggedId = draggedItemEl.dataset.id;
      const currentHeadId = dropzoneEl.dataset.id;

      if (!draggedId || draggedId === currentHeadId) return;

      const memberName = draggedItemEl.querySelector('.fw-bold')?.textContent?.trim() || 'สมาชิก';
      const isClub = endpoint.indexOf('club') !== -1;
      const roleLabel = isClub ? 'ประธานชมรมวิชาชีพ' : 'หัวหน้าแผนกวิชา';

      const confirmMsg = `ต้องการแต่งตั้ง "${memberName}" เป็น${roleLabel} หรือไม่? (ตำแหน่งเดิมจะถูกสลับเป็นสมาชิกตามโครงสร้าง)`;

      const executeSwap = () => {
        isSwapping = true;
        dropzoneEl.classList.add('is-loading');

        const formData = new FormData();
        formData.append('sub_action', 'swap_head');
        formData.append('csrf_token', csrfToken || '');
        formData.append('new_head_id', draggedId);

        fetch(endpoint, {
          method: 'POST',
          body: formData,
          headers: {
            'X-Requested-With': 'XMLHttpRequest',
          },
        })
          .then((res) => {
            if (!res.ok) throw new Error('เกิดข้อผิดพลาดในการสลับตำแหน่ง');
            return res.json();
          })
          .then((data) => {
            if (data.success) {
              showToast(`แต่งตั้ง "${memberName}" เป็น${roleLabel}เรียบร้อยแล้ว`);
              setTimeout(() => {
                window.location.reload();
              }, 600);
            } else {
              dropzoneEl.classList.remove('is-loading');
              isSwapping = false;
              showToast(data.message || 'ไม่สามารถสลับตำแหน่งได้', true);
            }
          })
          .catch((err) => {
            dropzoneEl.classList.remove('is-loading');
            isSwapping = false;
            showToast(err.message || 'ไม่สามารถติดต่อเซิร์ฟเวอร์ได้', true);
          });
      };

      if (window.Modal && typeof window.Modal.confirm === 'function') {
        window.Modal.confirm({
          title: `สลับตำแหน่ง${roleLabel}`,
          message: confirmMsg,
          confirmText: 'ยืนยันการแต่งตั้ง',
          cancelText: 'ยกเลิก',
          confirmVariant: 'btn-navy',
        }).then((confirmed) => {
          if (confirmed) {
            executeSwap();
          }
        });
      } else if (window.confirm(confirmMsg)) {
        executeSwap();
      }
    });
  }

  function saveNewOrder(gridEl, endpoint, csrfToken) {
    if (!endpoint) return;

    const currentCards = gridEl.querySelectorAll('.tree-slot-card[data-id]');
    const orderIds = Array.from(currentCards).map((card) => parseInt(card.dataset.id, 10)).filter((id) => !isNaN(id));

    if (orderIds.length === 0) return;

    // Visual loading state
    gridEl.classList.add('is-loading');

    const formData = new FormData();
    formData.append('sub_action', 'reorder');
    formData.append('csrf_token', csrfToken || '');
    formData.append('order_ids', JSON.stringify(orderIds));

    fetch(endpoint, {
      method: 'POST',
      body: formData,
      headers: {
        'X-Requested-With': 'XMLHttpRequest',
      },
    })
      .then((res) => {
        if (!res.ok) throw new Error('บันทึกลำดับไม่สำเร็จ');
        return res.json();
      })
      .then((data) => {
        gridEl.classList.remove('is-loading');
        if (data.success) {
          showToast('บันทึกลำดับตำแหน่งเรียบร้อยแล้ว');
          // Update visual badges
          updateOrderBadges(gridEl);
        } else {
          showToast(data.message || 'เกิดข้อผิดพลาดในการบันทึก', true);
        }
      })
      .catch((err) => {
        gridEl.classList.remove('is-loading');
        showToast(err.message || 'ไม่สามารถติดต่อเซิร์ฟเวอร์ได้', true);
      });
  }

  function updateOrderBadges(gridEl) {
    const badges = gridEl.querySelectorAll('.tree-slot-order-badge');
    badges.forEach((badgeEl, index) => {
      badgeEl.textContent = '#' + (index + 1);
    });
  }

  document.addEventListener('DOMContentLoaded', () => {
    const grids = document.querySelectorAll('[data-tree-grid]');
    grids.forEach((gridEl) => {
      initTreeDragDrop(gridEl);
      const headDropzones = document.querySelectorAll('[data-head-dropzone]');
      headDropzones.forEach((headDropzone) => {
        initHeadDropzone(headDropzone, gridEl);
      });
    });
  });
})();
