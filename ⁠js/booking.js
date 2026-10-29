document.addEventListener("DOMContentLoaded", function () {
    const dateInput = document.getElementById("booking-date") || document.getElementById("edit-date");

    if (dateInput) {
        // Disable past dates
        const today = new Date().toISOString().split("T")[0];
        dateInput.setAttribute("min", today);

        // Fetch booked slots via AJAX when date changes
        dateInput.addEventListener("change", function () {
            const selectedDate = this.value;
            if (!selectedDate) return;

            fetch(`php/check_availability.php?date=${selectedDate}`)
                .then(response => response.json())
                .then(bookedSlots => {
                    const timeSlots = document.querySelectorAll('input[name="time"], input[name="edit-time"]');
                    
                    timeSlots.forEach(slot => {
                        const parentLabel = slot.closest('.time-slot');
                        slot.disabled = false;
                        if (parentLabel) parentLabel.style.opacity = "1";

                        // Disable slot if already booked
                        if (bookedSlots.includes(slot.value)) {
                            slot.disabled = true;
                            if (parentLabel) parentLabel.style.opacity = "0.4";
                        }
                    });
                })
                .catch(err => console.error("Error fetching slots:", err));
        });
    }

    // Check if time slot is selected
    const bookingForm = document.querySelector(".booking-form-card form, .edit-form-card form");
    if (bookingForm) {
        bookingForm.addEventListener("submit", function (e) {
            const selectedTime = document.querySelector('input[name="time"]:checked, input[name="edit-time"]:checked');
            if (!selectedTime) {
                alert("Please select a time slot.");
                e.preventDefault();
            }
        });
    }
});
