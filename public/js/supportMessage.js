document.addEventListener('DOMContentLoaded', () => {
    const ticketID = window.ticketID;
    const messageBox = document.getElementById('messageBox');
    const replyForm = document.getElementById('replyForm');
    const newMessage = document.getElementById('newMessage');
    const createTicketForm = document.getElementById('createTicketForm');

    async function pollMessages() {
        if (!ticketID || !messageBox) return;
        try {
            const res = await fetch(`support.php?ticketID=${ticketID}&fetchMessages=1`);
            const messages = await res.json();
            messageBox.innerHTML = '';
            messages.forEach(msg => {
                const div = document.createElement('div');
                div.className = msg.senderRole === 'admin' ? 'text-red-600 mb-2' : 'text-blue-600 mb-2';
                div.innerHTML = `<strong>${msg.senderRole}:</strong> ${msg.message} <span class="text-gray-400 text-xs block">${msg.createdAt}</span>`;
                messageBox.appendChild(div);
            });
            messageBox.scrollTop = messageBox.scrollHeight;
        } catch (err) {
            console.error(err);
            showToast('Failed to load messages.', 'error');
        }
    }

    if (replyForm) {
        replyForm.addEventListener('submit', async e => {
            e.preventDefault();
            const message = newMessage.value.trim();
            if (!message) return;

            try {
                const res = await fetch('support.php', {
                    method: 'POST',
                    body: new URLSearchParams({
                        replyTicketID: ticketID,
                        replyMessage: message
                    })
                });
                const data = await res.json();
                if (data.success) {
                    newMessage.value = '';
                    pollMessages();
                    showToast('Message sent successfully!', 'success');
                } else {
                    console.error(data.error);
                    showToast('Failed to send message!', 'error');
                }
            } catch (err) {
                console.error(err);
                showToast('An unexpected error occurred.', 'error');
            }
        });
    }

    if (createTicketForm) {
        createTicketForm.addEventListener('submit', async e => {
            e.preventDefault();
            const formData = new FormData(createTicketForm);

            try {
                const res = await fetch('support.php', {
                    method: 'POST',
                    body: formData,
                    headers: { 'X-Requested-With': 'XMLHttpRequest' }
                });
                const data = await res.json();
                if (data.success) {
                    showToast('Ticket created successfully!', 'success');
                    window.location.href = `support.php?ticketID=${data.ticketID}`;
                } else {
                    console.error(data.error);
                    showToast('Failed to create ticket!', 'error');
                }
            } catch (err) {
                console.error(err);
                showToast('An unexpected error occurred.', 'error');
            }
        });
    }

    pollMessages();
    setInterval(pollMessages, 2000);
});
