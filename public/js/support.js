async function pollMessages(ticketID) {
    try {
        const res = await fetch(`/support.php?ticketID=${ticketID}&fetchMessages=1`);
        const messages = await res.json();
        const box = document.getElementById("messageBox");
        box.innerHTML = "";

        messages.forEach(msg => {
            const div = document.createElement("div");
            div.className = msg.senderRole === "admin" ? "text-red-600" : "text-blue-600";
            div.innerHTML = `<strong>${msg.senderRole}:</strong> ${msg.message}`;
            box.appendChild(div);
        });
    } catch (err) {
        console.error(err);
    }
}

document.getElementById("replyForm").addEventListener("submit", async function(e) {
    e.preventDefault();
    const message = document.getElementById("newMessage").value;
    const ticketID = window.ticketID;

    if (!message) return;

    const res = await fetch("/support.php", {
        method: "POST",
        body: new URLSearchParams({
            replyTicketID: ticketID,
            replyMessage: message
        })
    });

    const data = await res.json();
    if (data.success) {
        document.getElementById("newMessage").value = '';
        pollMessages(ticketID);
    }
});

setInterval(() => {
    if (window.ticketID) pollMessages(window.ticketID);
}, 2000);
