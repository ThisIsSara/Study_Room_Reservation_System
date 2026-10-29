document.addEventListener("DOMContentLoaded", function () {
    const searchInput = document.querySelector(".room-search");
    const selectFilter = document.querySelector(".room-select");
    const roomCards = document.querySelectorAll(".room-list-card");

    function filterRooms() {
        const query = searchInput ? searchInput.value.toLowerCase() : "";
        const selectedCapacity = selectFilter ? selectFilter.value : "";

        roomCards.forEach(card => {
            const title = card.querySelector("h3").innerText.toLowerCase();
            const capacityText = card.querySelector("p").innerText; 
            const capacityNumber = capacityText.replace(/[^0-9]/g, "");

            const matchesSearch = title.includes(query);
            const matchesCapacity = selectedCapacity === "" || capacityNumber === selectedCapacity;

            if (matchesSearch && matchesCapacity) {
                card.style.display = "flex";
            } else {
                card.style.display = "none";
            }
        });
    }

    if (searchInput) searchInput.addEventListener("input", filterRooms);
    if (selectFilter) selectFilter.addEventListener("change", filterRooms);
});
