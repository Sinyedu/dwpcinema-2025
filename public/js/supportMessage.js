const ticketID = window.ticketID;
const messageBox = document.getElementById('messageBox');
const replyForm = document.getElementById('replyForm');
const newMessage = document.getElementById('newMessage');

function formatTimestamp(ts) {
    const d = new Date(ts);
    return d.toLocaleString([], { dateStyle: 'short', timeStyle: 'short' });
}

async function pollMessages() {
    if (!ticketID) return;
    try {
        const res = await fetch(`support.php?ticketID=${ticketID}&fetchMessages=1`);
        const messages = await res.json();

        messageBox.innerHTML = '';
        messages.forEach(msg => {
            const div = document.createElement('div');
            div.className = msg.senderRole === 'admin' ? 'text-red-600 mb-2' : 'text-blue-600 mb-2';
            div.innerHTML = `<strong>${msg.senderRole}:</strong> ${msg.message}
                             <span class="text-gray-400 text-xs block">${formatTimestamp(msg.createdAt)}</span>`;
            messageBox.appendChild(div);
        });

        messageBox.scrollTop = messageBox.scrollHeight;
    } catch (err) {
        console.error("Error fetching messages:", err);
    }
}

replyForm.addEventListener('submit', async (e) => {
    e.preventDefault();
    const message = newMessage.value.trim();
    if (!message) return;

    const tempDiv = document.createElement('div');
    tempDiv.className = 'text-blue-600 mb-2';
    const now = new Date();
    tempDiv.innerHTML = `<strong>user:</strong> ${message}
                         <span class="text-gray-400 text-xs block">${formatTimestamp(now)}</span>`;
    messageBox.appendChild(tempDiv);
    messageBox.scrollTop = messageBox.scrollHeight;
    newMessage.value = '';

    try {
        const res = await fetch('support.php', {
            method: 'POST',
            body: new URLSearchParams({
                replyTicketID: ticketID,
                replyMessage: message
            })
        });
        const data = await res.json();

        if (!data.success) {
            console.error(data.error ?? "Unknown error sending message");
            messageBox.removeChild(tempDiv);
        }
    } catch (err) {
        console.error("Error sending message:", err);
        messageBox.removeChild(tempDiv);
    }
});

setInterval(pollMessages, 2000);
pollMessages();
