document.addEventListener("DOMContentLoaded", () => {
  const ticketID = window.ticketID;
  const messageBox = document.getElementById("messageBox");
  const replyForm = document.getElementById("replyForm");
  const newMessage = document.getElementById("newMessage");
  const createTicketForm = document.getElementById("createTicketForm");
  const csrfToken = document.querySelector('input[name="csrf_token"]')?.value;
  const ajaxHeaders = { "X-Requested-With": "XMLHttpRequest" };

  async function fetchJSON(url, options = {}) {
    try {
      const res = await fetch(url, options);
      const data = await res.json();
      // 4xx responses still carry a JSON { error } we want to show.
      return data;
    } catch (err) {
      console.error("Fetch error:", err);
      showToast("Failed to fetch data.", "error", 5000);
      return null;
    }
  }

  // Message text is user-supplied: always build nodes with textContent, never innerHTML.
  function messageNode(role, text, time) {
    const div = document.createElement("div");
    div.className = role === "admin" ? "text-red-600 mb-2" : "text-blue-600 mb-2";

    const strong = document.createElement("strong");
    strong.textContent = `${role}:`;

    const stamp = document.createElement("span");
    stamp.className = "text-gray-400 text-xs block";
    stamp.textContent = time;

    div.append(strong, " ", text, " ", stamp);
    return div;
  }

  async function pollMessages() {
    if (!ticketID || !messageBox) return;

    const messages = await fetchJSON(
      `support.php?ticketID=${encodeURIComponent(ticketID)}&fetchMessages=1`,
      { headers: ajaxHeaders }
    );
    if (!Array.isArray(messages)) return;

    messageBox.replaceChildren(
      ...messages.map((m) => messageNode(m.senderRole, m.message, m.createdAt))
    );
    messageBox.scrollTop = messageBox.scrollHeight;
  }

  pollMessages();
  setInterval(pollMessages, 5000);

  if (replyForm) {
    replyForm.addEventListener("submit", async (e) => {
      e.preventDefault();

      const ticketEl = document.querySelector(`[data-ticket-id="${ticketID}"]`);
      if (ticketEl?.dataset.ticketStatus === "closed") {
        showToast("This ticket is closed. You cannot send a reply.", "error", 5000);
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
        headers: ajaxHeaders,
      });

      if (data && data.success) {
        newMessage.value = "";
        messageBox.appendChild(messageNode("You", message, new Date().toLocaleString()));
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
      showToast("Sending your ticket...", "info", 3000);

      const data = await fetchJSON("support.php", {
        method: "POST",
        body: new FormData(createTicketForm),
        headers: ajaxHeaders,
      });

      if (data && data.success) {
        showToast("Ticket created successfully!", "success", 3000);
        setTimeout(() => {
          window.location.href = `support.php?ticketID=${encodeURIComponent(data.ticketID)}`;
        }, 1000);
      } else {
        showToast(data?.error || "Failed to create ticket!", "error", 5000);
      }
    });
  }
});
