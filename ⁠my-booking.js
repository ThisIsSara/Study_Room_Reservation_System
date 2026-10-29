document.addEventListener("DOMContentLoaded", function () {
    const cancelButtons = document.querySelectorAll(".cancel-booking-btn");

    // Handle booking cancellation
    cancelButtons.forEach(button => {
        button.addEventListener("click", function () {
            const bookingItem = this.closest(".booking-item");
            const roomName = bookingItem.querySelector("h2").innerText;

            if (confirm(`Are you sure you want to cancel ${roomName}?`)) {
                alert("Booking cancelled successfully.");
                bookingItem.remove();
            }
        });
    });
});
