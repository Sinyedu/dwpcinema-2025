document.addEventListener("DOMContentLoaded", () => {
  const supportUnreadBadge = document.getElementById("supportUnreadBadge");
  const csrfToken = window.csrfToken; // global CSRF token
  const createTicketForm = document.getElementById("createTicketForm");

  async function fetchJSON(url, options = {}) {
    try {
      const res = await fetch(url, options);
      if (!res.ok) throw new Error(`HTTP ${res.status}`);
      return await res.json();
    } catch (err) {
      console.error("Fetch error:", err);
      return null;
    }
  }

  async function pollUnread() {
    if (!supportUnreadBadge) return;

    const data = await fetchJSON("support.php?fetchUnreadCount=1", {
      headers: {
        "X-Requested-With": "XMLHttpRequest",
        "X-CSRF-Token": csrfToken,
      },
    });

    if (!data) return;

    const count = data.unreadCount || 0;
    supportUnreadBadge.textContent = count;
    supportUnreadBadge.classList.toggle("hidden", count === 0);
  }

  pollUnread();
  setInterval(pollUnread, 7000);
});

if (createTicketForm) {
  createTicketForm.addEventListener("submit", async (e) => {
    e.preventDefault();
    const formData = new FormData(createTicketForm);

    showToast("Sending your ticket...", "info", 7000);

    await new Promise((resolve) => setTimeout(resolve, 1500));
    try {
      const res = await fetch("support.php", {
        method: "POST",
        body: formData,
        headers: { "X-Requested-With": "XMLHttpRequest" },
      });
      const data = await res.json();
      if (data.success) {
        showToast("Ticket created successfully!", "success", 7000);

        setTimeout(() => {
          window.location.href = `support.php?ticketID=${data.ticketID}`;
        }, 5000);
      } else {
        console.error(data.error);
        showToast(data.error || "Failed to create ticket!", "error", 7000);
      }
    } catch (err) {
      console.error(err);
      showToast("An unexpected error occurred.", "error", 7000);
    }
  });
}
