document.addEventListener("DOMContentLoaded", function () {
    const cancelButtons = document.querySelectorAll(".cancel-booking-btn");

    cancelButtons.forEach(button => {
        button.addEventListener("click", function (e) {
            const bookingItem = this.closest(".booking-item");
            const roomName = bookingItem.querySelector("h2").innerText;
            const bookingDate = bookingItem.querySelector(".booking-details-row strong").innerText;

            const confirmCancel = confirm(`Are you sure you want to cancel your reservation for ${roomName} on ${bookingDate}?`);
            
            if (confirmCancel) {
                alert("Booking cancelled successfully.");
                bookingItem.remove(); // Remove element dynamically from DOM
            }
        });
    });
});





