// Shows the unread-support-reply count on the navbar badge (logged-in users only;
// the script tag is only rendered for them). Ticket creation lives in supportMessage.js.
document.addEventListener("DOMContentLoaded", () => {
  const badge = document.getElementById("supportUnreadBadge");
  if (!badge) return;

  async function pollUnread() {
    try {
      const res = await fetch("support.php?fetchUnreadCount=1", {
        headers: { "X-Requested-With": "XMLHttpRequest" },
      });
      if (!res.ok) return;
      const { unreadCount = 0 } = await res.json();
      badge.textContent = unreadCount;
      badge.classList.toggle("hidden", unreadCount === 0);
    } catch (err) {
      console.error("Unread poll failed:", err);
    }
  }

  pollUnread();
  setInterval(pollUnread, 15000);
});
