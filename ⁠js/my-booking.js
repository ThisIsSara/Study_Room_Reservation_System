
document.addEventListener("DOMContentLoaded", function () {
    const dateInput = document.getElementById("booking-date") || document.getElementById("edit-date");

    if (dateInput) {
        // Prevent selecting dates in the past
        const today = new Date().toISOString().split("T")[0];
        dateInput.setAttribute("min", today);

        // AJAX Event: Check booked slots when date changes
        dateInput.addEventListener("change", function () {
            const selectedDate = this.value;
            if (!selectedDate) return;

            // Send AJAX request to PHP backend
            fetch(`php/check_availability.php?date=${selectedDate}`)
                .then(response => response.json())
                .then(bookedSlots => {
                    const timeSlots = document.querySelectorAll('input[name="time"], input[name="edit-time"]');
                    
                    timeSlots.forEach(slot => {
                        const parentLabel = slot.closest('.time-slot');
                        
                        // Reset all slots
                        slot.disabled = false;
                        if (parentLabel) parentLabel.style.opacity = "1";

                        // Disable if the time slot is already booked
                        if (bookedSlots.includes(slot.value)) {
                            slot.disabled = true;
                            if (parentLabel) {
                                parentLabel.style.opacity = "0.4";
                                parentLabel.title = "This slot is already booked";
                            }
                        }
                    });
                })
                .catch(err => console.error("Error fetching available slots:", err));
        });
    }

    // Ensure user selects a time slot before submitting
    const bookingForm = document.querySelector(".booking-form-card form, .edit-form-card form");
    if (bookingForm) {
        bookingForm.addEventListener("submit", function (e) {
            const selectedTime = document.querySelector('input[name="time"]:checked, input[name="edit-time"]:checked');
            if (!selectedTime) {
                alert("Please select a time slot for your reservation.");
                e.preventDefault();
            }
        });
    }
});
