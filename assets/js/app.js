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

      // Toggle SVG visually
      const svg = btn.querySelector("svg");
      if (svg) {
        svg.style.opacity = isPassword ? "0.6" : "1";
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
