const createTicketForm = document.getElementById("createTicketForm");

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
