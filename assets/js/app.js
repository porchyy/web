/**
 * Digital Business Technology - Core Client Script
 * Lightweight vanilla JS for tab switching, password toggles, and confirmations
 */
document.addEventListener("DOMContentLoaded", () => {
  // 1. Mobile Schedule Day Tabs
  const dayTabs = document.querySelectorAll(".day-tab[data-day]");
  const slotGroups = document.querySelectorAll(".slot-group[data-day]");

  if (dayTabs.length && slotGroups.length) {
    dayTabs.forEach((tab) => {
      tab.addEventListener("click", () => {
        const targetDay = tab.getAttribute("data-day");

        // Update tabs state
        dayTabs.forEach((t) =>
          t.setAttribute("aria-selected", t === tab ? "true" : "false"),
        );

        // Show matching slot group, hide others
        slotGroups.forEach((group) => {
          if (group.getAttribute("data-day") === targetDay) {
            group.removeAttribute("hidden");
          } else {
            group.setAttribute("hidden", "");
          }
        });
      });
    });
  }

  // 2. Password Visibility Toggle
  const pwToggles = document.querySelectorAll(".pw-toggle");
  pwToggles.forEach((btn) => {
    btn.addEventListener("click", () => {
      const input = btn.closest(".pw-field")?.querySelector("input");
      if (!input) return;

      const isPassword = input.getAttribute("type") === "password";
      input.setAttribute("type", isPassword ? "text" : "password");
      btn.setAttribute(
        "aria-label",
        isPassword ? "ซ่อนรหัสผ่าน" : "แสดงรหัสผ่าน",
      );

      // Toggle SVG icon between eye and eye-off
      const svg = btn.querySelector("svg");
      if (svg) {
        if (isPassword) {
          svg.innerHTML = '<path d="M3 3l18 18"/><path d="M10.6 5.1A10 10 0 0 1 12 5c6.5 0 10 7 10 7a17 17 0 0 1-3 3.9M6.6 6.6C3.8 8.4 2 12 2 12s3.5 7 10 7a9.7 9.7 0 0 0 5.4-1.6"/><path d="M9.9 9.9a3 3 0 0 0 4.2 4.2"/>';
          svg.style.color = 'var(--c-navy)';
        } else {
          svg.innerHTML = '<path d="M2 12s3.5-7 10-7 10 7 10 7-3.5 7-10 7S2 12 2 12z"/><circle cx="12" cy="12" r="3"/>';
          svg.style.color = '';
        }
      }
    });
  });


  // 4. File input preview helper for image uploads
  const imageInputs = document.querySelectorAll(
    'input[type="file"][data-preview-target]',
  );
  imageInputs.forEach((input) => {
    input.addEventListener("change", () => {
      const targetId = input.getAttribute("data-preview-target");
      const targetImg = document.getElementById(targetId);
      if (targetImg && input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = (e) => {
          targetImg.src = e.target.result;
        };
        reader.readAsDataURL(input.files[0]);
      }
    });
  });
});
