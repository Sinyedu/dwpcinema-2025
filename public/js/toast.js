function showToast(message, type = 'info', duration = 7000) {
    const toast = document.createElement('div');
    toast.textContent = message;
    toast.className = `fixed top-5 right-5 px-4 py-2 rounded shadow text-white z-50 transition-opacity duration-300 ${
        type === 'success' ? 'bg-green-600' :
        type === 'error' ? 'bg-red-600' :
        'bg-gray-600'
    }`;
    document.body.appendChild(toast);
    setTimeout(() => toast.remove(), duration);
}