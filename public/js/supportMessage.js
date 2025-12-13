document.addEventListener("DOMContentLoaded", () => {
  const ticketID = window.ticketID;
  const messageBox = document.getElementById("messageBox");
  const replyForm = document.getElementById("replyForm");
  const newMessage = document.getElementById("newMessage");
  const createTicketForm = document.getElementById("createTicketForm");
  const supportUnreadBadge = document.getElementById("supportUnreadBadge");

  async function fetchJSON(url, options = {}) {
    try {
      const res = await fetch(url, options);
      if (!res.ok) throw new Error(`HTTP ${res.status}`);

      const text = await res.text();

      return JSON.parse(text);
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
        headers: { "X-Requested-With": "XMLHttpRequest" },
      }
    );

    if (!messages || !Array.isArray(messages)) return;

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
      headers: { "X-Requested-With": "XMLHttpRequest" },
    });

    if (!data) return;

    const count = data.unreadCount || 0;
    if (count > 0) {
      supportUnreadBadge.textContent = count;
      supportUnreadBadge.classList.remove("hidden");
    } else {
      supportUnreadBadge.classList.add("hidden");
    }
  }

  pollMessages();
  pollUnread();
  setInterval(pollMessages, 5000);
  setInterval(pollUnread, 7000);

  if (replyForm) {
    replyForm.addEventListener("submit", async (e) => {
      e.preventDefault();
      const message = newMessage.value.trim();
      if (!message) return;

      const formData = new FormData();
      formData.append("replyTicketID", ticketID);
      formData.append("replyMessage", message);

      try {
        const data = await fetchJSON("support.php", {
          method: "POST",
          body: formData,
          headers: { "X-Requested-With": "XMLHttpRequest" },
        });

        if (data && data.success) {
          const div = document.createElement("div");
          div.className = "text-blue-600 mb-2 fade-in";
          const now = new Date().toLocaleString();
          div.innerHTML = `<strong>You:</strong> ${message} <span class="text-gray-400 text-xs block">${now}</span>`;
          messageBox.appendChild(div);
          messageBox.scrollTop = messageBox.scrollHeight;

          newMessage.value = "";
          showToast("Message sent successfully!", "success", 3000);
        } else {
          showToast(data?.error || "Failed to send message!", "error", 5000);
        }
      } catch (err) {
        console.error(err);
        showToast("Unexpected error.", "error", 5000);
      }
    });
  }

  if (createTicketForm) {
    createTicketForm.addEventListener("submit", async (e) => {
      e.preventDefault();
      const formData = new FormData(createTicketForm);

      showToast("Sending your ticket...", "info", 3000);

      try {
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
      } catch (err) {
        console.error(err);
        showToast("Unexpected error.", "error", 5000);
      }
    });
  }
});
