(function(){
  const base = '/api/support';
  const $ = (sel) => document.querySelector(sel);
  let lastTimestamp = null;
  let polling = null;

  function formatMessageHtml(m) {
    const role = m.senderRole === 'admin' ? 'Support' : 'You';
    const whoClass = m.senderRole === 'admin' ? 'text-left' : 'text-right';
    return `<div class="mb-3 ${whoClass}">
      <div class="inline-block bg-gray-100 p-2 rounded shadow-sm">
        <div class="text-sm">${escapeHtml(m.message)}</div>
        <div class="text-xs text-gray-400 mt-1">${m.createdAt}</div>
      </div>
    </div>`;
  }

  function escapeHtml(s) {
    return String(s).replace(/[&<>"']/g, function (m) {
      return ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#039;'}[m]);
    });
  }

  async function fetchMessages() {
    if (typeof CURRENT_TICKET_ID === 'undefined') return;
    const url = `${base}/get_messages.php?ticketID=${CURRENT_TICKET_ID}` + (lastTimestamp ? `&since=${encodeURIComponent(lastTimestamp)}` : '');
    try {
      const res = await fetch(url, { credentials: 'same-origin' });
      const data = await res.json();
      if (data.success) {
        const container = document.getElementById('messages');
        data.messages.forEach(m => {
          container.insertAdjacentHTML('beforeend', formatMessageHtml(m));
          lastTimestamp = m.createdAt;
        });
        if (data.messages.length) container.scrollTop = container.scrollHeight;
      }
    } catch(e) {
    }
  }

  async function sendMessage(message) {
    const form = new FormData();
    form.append('ticketID', CURRENT_TICKET_ID);
    form.append('message', message);
    const res = await fetch(`${base}/send_message.php`, {
      method: 'POST',
      body: form,
      credentials: 'same-origin'
    });
    return res.json();
  }

  function startPolling() {
    if (polling) clearInterval(polling);
    fetchMessages();
    polling = setInterval(fetchMessages, 2000);
  }

  document.addEventListener('DOMContentLoaded', function(){
    const createForm = document.getElementById('createTicketForm');
    if (createForm) {
      createForm.addEventListener('submit', async function(e){
        e.preventDefault();
        const formData = new FormData(createForm);
        const res = await fetch(createForm.action, { method: 'POST', body: formData, credentials: 'same-origin' });
        const data = await res.json();
        if (data.success) {
          window.location.href = `/views/ticket.php?ticketID=${data.ticketID}`;
        } else {
          alert(data.error || 'Error creating ticket');
        }
      });
    }

    const msgForm = document.getElementById('messageForm');
    if (msgForm) {
      startPolling();
      msgForm.addEventListener('submit', async function(e){
        e.preventDefault();
        const input = document.getElementById('msgInput');
        const txt = input.value.trim();
        if (!txt) return;
        const data = await sendMessage(txt);
        if (data.success) {
          input.value = '';
          const container = document.getElementById('messages');
          container.insertAdjacentHTML('beforeend', `<div class="mb-3 text-right"><div class="inline-block bg-blue-100 text-black p-2 rounded">${escapeHtml(txt)}<div class="text-xs text-gray-400 mt-1">${new Date().toISOString().slice(0,19).replace('T',' ')}</div></div></div>`);
          container.scrollTop = container.scrollHeight;
        } else {
          alert(data.error || 'Send failed');
        }
      });
    }
  });
})();
