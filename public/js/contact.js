document.addEventListener("DOMContentLoaded", () => {
    let category = document.getElementById("category");
    let wrapper = document.getElementById("tournamentWrapper");

    if (!category || !wrapper) {
        console.warn("contact.js: Required elements missing");
        return;
    }

    category.addEventListener("change", () => {
        if (category.value === "Reservation") {
            wrapper.classList.remove("hidden");
        } else {
            wrapper.classList.add("hidden");
        }
    });
});
