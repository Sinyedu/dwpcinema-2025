document.addEventListener("DOMContentLoaded", () => {
  const ticketID = document.querySelector('input[name="ticketID"]').value;
  const messageBox = document.getElementById("messageBox");
  const replyForm = document.getElementById("replyForm");
  const closeBtn = document.getElementById("closeTicketBtn");
  const reopenBtn = document.getElementById("reopenTicketBtn");

  async function sendRequest(url, options = {}) {
    options.headers = {
      ...options.headers,
      "X-Requested-With": "XMLHttpRequest",
    };
    try {
      const res = await fetch(url, options);
      if (!res.ok) throw new Error(`HTTP ${res.status}`);
      return await res.json();
    } catch (err) {
      console.error("Request failed:", err);
      showToast("An error occurred. Check console.", "error");
      return null;
    }
  }

  function updateTicketListStatus(newStatus) {
    const ticketLink = document.querySelector(
      `a[href="?ticketID=${ticketID}"]`
    );
    if (ticketLink) {
      const statusDiv = ticketLink.querySelector(".text-xs.text-gray-500");
      if (statusDiv) {
        const parts = statusDiv.innerText.split("•");
        parts[parts.length - 1] = ` ${newStatus}`;
        statusDiv.innerText = parts.join("•");
      }
    }
  }

  function showToast(message, type = "success", duration = 3000) {
    const container = document.getElementById("toastContainer");
    if (!container) return;

    const toast = document.createElement("div");
    toast.className = `px-4 py-2 rounded shadow text-white ${
      type === "success" ? "bg-green-600" : "bg-red-600"
    } opacity-0 transition-opacity duration-300`;
    toast.innerText = message;
    container.appendChild(toast);

    requestAnimationFrame(() => toast.classList.add("opacity-100"));
    setTimeout(() => {
      toast.classList.remove("opacity-100");
      toast.addEventListener("transitionend", () => toast.remove());
    }, duration);
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

      const data = await sendRequest("support.php", {
        method: "POST",
        body: formData,
      });
      if (data?.success) {
        const div = document.createElement("div");
        div.className = "mb-2 text-red-600";
        div.innerHTML = `<strong>admin:</strong> ${message} <span class="text-gray-400 text-xs block">${new Date().toLocaleString()}</span>`;
        messageBox.appendChild(div);
        messageBox.scrollTop = messageBox.scrollHeight;
        messageInput.value = "";
        showToast("Reply sent!", "success");
      } else {
        showToast(data?.error || "Failed to send reply.", "error");
      }
    });
  }

  if (closeBtn) {
    closeBtn.addEventListener("click", async () => {
      const data = await sendRequest("support.php", {
        method: "POST",
        body: new URLSearchParams({ ticketID, action: "close" }),
      });

      if (data?.success) {
        messageBox.dataset.ticketStatus = "closed";
        closeBtn.style.display = "none";
        reopenBtn.style.display = "inline-block";
        updateTicketListStatus(data.status);
        showToast("Ticket closed!", "success");
      } else {
        showToast(data?.error || "Failed to close ticket.", "error");
      }
    });
  }

  if (reopenBtn) {
    reopenBtn.addEventListener("click", async () => {
      const data = await sendRequest("support.php", {
        method: "POST",
        body: new URLSearchParams({ ticketID, action: "reopen" }),
      });

      if (data?.success) {
        messageBox.dataset.ticketStatus = "open";
        reopenBtn.style.display = "none";
        closeBtn.style.display = "inline-block";
        updateTicketListStatus(data.status);
        showToast("Ticket reopened!", "success");
      } else {
        showToast(data?.error || "Failed to reopen ticket.", "error");
      }
    });
  }

  const status = messageBox.dataset.ticketStatus || "open";
  if (status === "closed") {
    closeBtn.style.display = "none";
    reopenBtn.style.display = "inline-block";
  } else {
    closeBtn.style.display = "inline-block";
    reopenBtn.style.display = "none";
  }
});
