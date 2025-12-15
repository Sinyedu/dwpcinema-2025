document.addEventListener("DOMContentLoaded", () => {
  const ticketID = window.ticketID;
  const messageBox = document.getElementById("messageBox");
  const replyForm = document.getElementById("replyForm");
  const newMessage = document.getElementById("newMessage");
  const createTicketForm = document.getElementById("createTicketForm");
  const supportUnreadBadge = document.getElementById("supportUnreadBadge");
  const csrfToken = document.querySelector('input[name="csrf_token"]')?.value;

  async function fetchJSON(url, options = {}) {
    try {
      const res = await fetch(url, options);
      if (!res.ok) throw new Error(`HTTP ${res.status}`);

      const data = await res.json();
      return data;
    } catch (err) {
      console.error("Fetch error:", err);
      showToast("Failed to fetch data.", "error", 5000);
      return null;
    }
  }

  async function pollMessages() {
    if (!ticketID || !messageBox) return;

    const messages = await fetchJSON(
      `support.php?ticketID=${ticketID}&fetchMessages=1`,
      {
        headers: {
          "X-Requested-With": "XMLHttpRequest",
          "X-CSRF-Token": csrfToken,
        },
      }
    );

    if (!messages || !Array.isArray(messages)) {
      return;
    }

    messageBox.innerHTML = "";
    messages.forEach((msg) => {
      const div = document.createElement("div");
      div.className =
        msg.senderRole === "admin" ? "text-red-600 mb-2" : "text-blue-600 mb-2";
      div.innerHTML = `<strong>${msg.senderRole}:</strong> ${msg.message} <span class="text-gray-400 text-xs block">${msg.createdAt}</span>`;
      messageBox.appendChild(div);
    });
    messageBox.scrollTop = messageBox.scrollHeight;
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

  pollMessages();
  pollUnread();
  setInterval(pollMessages, 5000);
  setInterval(pollUnread, 5000);

  if (replyForm) {
    replyForm.addEventListener("submit", async (e) => {
      e.preventDefault();

      const ticketEl = document.querySelector(`[data-ticket-id="${ticketID}"]`);
      const statusDiv = ticketEl?.querySelector(".text-xs.text-gray-500");
      const status = statusDiv?.innerText.split("•").pop().trim() || "open";

      if (status === "closed") {
        showToast(
          "This ticket is closed. You cannot send a reply.",
          "error",
          5000
        );
        return;
      }

      const message = newMessage.value.trim();
      if (!message) return;

      const formData = new FormData();
      formData.append("replyTicketID", ticketID);
      formData.append("replyMessage", message);
      formData.append("csrf_token", csrfToken);

      const data = await fetchJSON("support.php", {
        method: "POST",
        body: formData,
        headers: { "X-Requested-With": "XMLHttpRequest" },
      });

      if (data && data.success) {
        newMessage.value = "";
        const div = document.createElement("div");
        div.className = "text-blue-600 mb-2";
        const now = new Date().toLocaleString();
        div.innerHTML = `<strong>You:</strong> ${message} <span class="text-gray-400 text-xs block">${now}</span>`;
        messageBox.appendChild(div);
        messageBox.scrollTop = messageBox.scrollHeight;
        showToast("Message sent successfully!", "success", 3000);
      } else {
        showToast(data?.error || "Failed to send message!", "error", 5000);
      }
    });
  }

  if (createTicketForm) {
    createTicketForm.addEventListener("submit", async (e) => {
      e.preventDefault();
      const formData = new FormData(createTicketForm);
      showToast("Sending your ticket...", "info", 3000);

      const data = await fetchJSON("support.php", {
        method: "POST",
        body: formData,
        headers: { "X-Requested-With": "XMLHttpRequest" },
      });

      if (data && data.success) {
        showToast("Ticket created successfully!", "success", 3000);
        setTimeout(() => {
          window.location.href = `support.php?ticketID=${data.ticketID}`;
        }, 1000);
      } else {
        showToast(data?.error || "Failed to create ticket!", "error", 5000);
      }
    });
  }
});

function updateTicketListStatus(ticketID, status) {
  const ticketEl = document.querySelector(`[data-ticket-id="${ticketID}"]`);
  if (ticketEl) {
    const statusDiv = ticketEl.querySelector(".text-xs.text-gray-500");
    if (statusDiv) {
      const parts = statusDiv.innerText.split("•");
      parts[parts.length - 1] = ` ${status}`;
      statusDiv.innerText = parts.join("•");
    }

    if (status === "closed") {
      ticketEl.classList.add("opacity-50", "cursor-not-allowed");
    } else {
      ticketEl.classList.remove("opacity-50", "cursor-not-allowed");
    }
  }
}
