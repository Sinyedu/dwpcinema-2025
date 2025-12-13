document.addEventListener('DOMContentLoaded', () => {
    const badge = document.getElementById('supportUnreadBadge');

    async function updateUnreadCount() {
        try {
            const res = await fetch('support.php?fetchUnreadCount=1');
            const data = await res.json();
            const count = data.unreadCount || 0;
            if (count > 0) {
                badge.textContent = count;
                badge.classList.remove('hidden');
            } else {
                badge.classList.add('hidden');
            }
        } catch (err) {
            console.error('Failed to fetch unread messages count:', err);
        }
    }

    updateUnreadCount();
    setInterval(updateUnreadCount, 5000);
});
