document.addEventListener("DOMContentLoaded", function() {
    const checkboxes = document.querySelectorAll(".seatCheckbox");
    const totalPriceEl = document.getElementById("totalPrice");

    function updateTotal() {
        let total = 0;
        checkboxes.forEach(cb => {
            if(cb.checked) {
                const tierID = cb.dataset.tier;
                total += parseFloat(tierPrices[tierID]);
            }
        });
        totalPriceEl.textContent = total.toFixed(2);
    }

    checkboxes.forEach(cb => {
        cb.addEventListener("change", updateTotal);
    });

    updateTotal();
});
