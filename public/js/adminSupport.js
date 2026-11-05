document.addEventListener("DOMContentLoaded", () => {
  const ticketInput = document.querySelector('input[name="ticketID"]');
  if (!ticketInput) return; // no ticket selected

  const ticketID = ticketInput.value;
  const messageBox = document.getElementById("messageBox");
  const replyForm = document.getElementById("replyForm");
  const closeBtn = document.getElementById("closeTicketBtn");
  const reopenBtn = document.getElementById("reopenTicketBtn");

  async function sendRequest(url, options = {}) {
    options.headers = { ...options.headers, "X-Requested-With": "XMLHttpRequest" };
    try {
      const res = await fetch(url, options);
      return await res.json(); // 4xx responses carry a JSON { error }
    } catch (err) {
      console.error("Request failed:", err);
      showToast("An error occurred. Check console.", "error");
      return null;
    }
  }

  function updateTicketListStatus(newStatus) {
    const statusDiv = document
      .querySelector(`a[href="?ticketID=${ticketID}"]`)
      ?.querySelector(".text-xs.text-gray-400");
    if (statusDiv) {
      const parts = statusDiv.textContent.split("•");
      parts[parts.length - 1] = ` ${newStatus}`;
      statusDiv.textContent = parts.join("•");
    }
  }

  function showToast(message, type = "success", duration = 3000) {
    const container = document.getElementById("toastContainer");
    if (!container) return;

    const toast = document.createElement("div");
    toast.className = `px-4 py-2 rounded shadow text-white ${
      type === "success" ? "bg-green-600" : "bg-red-600"
    } opacity-0 transition-opacity duration-300`;
    toast.textContent = message;
    container.appendChild(toast);

    requestAnimationFrame(() => toast.classList.add("opacity-100"));
    setTimeout(() => {
      toast.classList.remove("opacity-100");
      toast.addEventListener("transitionend", () => toast.remove());
    }, duration);
  }

  function showStatus(status) {
    messageBox.dataset.ticketStatus = status;
    closeBtn.style.display = status === "closed" ? "none" : "inline-block";
    reopenBtn.style.display = status === "closed" ? "inline-block" : "none";
  }

  async function changeStatus(action, doneMessage) {
    const data = await sendRequest("support.php", {
      method: "POST",
      body: new URLSearchParams({ ticketID, action, csrf_token: window.csrfToken }),
    });
    if (data?.success) {
      showStatus(data.status);
      updateTicketListStatus(data.status);
      showToast(doneMessage, "success");
    } else {
      showToast(data?.error || "Failed to update ticket.", "error");
    }
  }

  if (replyForm) {
    replyForm.addEventListener("submit", async (e) => {
      e.preventDefault();
      const messageInput = replyForm.querySelector('textarea[name="message"]');
      const message = messageInput.value.trim();
      if (!message) return;

      const formData = new FormData();
      formData.append("ticketID", ticketID);
      formData.append("message", message);
      formData.append("csrf_token", window.csrfToken);

      const data = await sendRequest("support.php", { method: "POST", body: formData });
      if (data?.success) {
        // Message text is user input: build nodes with textContent, never innerHTML.
        const div = document.createElement("div");
        div.className = "mb-2 text-red-600";
        const strong = document.createElement("strong");
        strong.textContent = "admin:";
        const stamp = document.createElement("span");
        stamp.className = "text-gray-400 text-xs block";
        stamp.textContent = new Date().toLocaleString();
        div.append(strong, " ", message, " ", stamp);

        messageBox.appendChild(div);
        messageBox.scrollTop = messageBox.scrollHeight;
        messageInput.value = "";
        showToast("Reply sent!", "success");
      } else {
        showToast(data?.error || "Failed to send reply.", "error");
      }
    });
  }

  closeBtn?.addEventListener("click", () => changeStatus("close", "Ticket closed!"));
  reopenBtn?.addEventListener("click", () => changeStatus("reopen", "Ticket reopened!"));

  showStatus(messageBox.dataset.ticketStatus === "closed" ? "closed" : "open");
});
