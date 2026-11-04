document.addEventListener("DOMContentLoaded", function () {
    const bookingForm = document.querySelector(".booking-form-card form");
    const editForm = document.querySelector(".edit-form-card form");
    const dateInput = document.getElementById("booking-date");

    if (bookingForm && dateInput) {
        const room = bookingForm.dataset.roomId;
        const timeSlots = bookingForm.querySelectorAll('input[name="time"]');
        const submitButton = bookingForm.querySelector('button[type="submit"]');

        const now = new Date();
        const today = [
            now.getFullYear(),
            String(now.getMonth() + 1).padStart(2, "0"),
            String(now.getDate()).padStart(2, "0")
        ].join("-");

        dateInput.min = today;
        let availabilityReady = false;
        let requestNumber = 0;

        function setSlotsDisabled(disabled) {
            timeSlots.forEach(slot => {
                slot.disabled = disabled;
                if (disabled) slot.checked = false;

                const label = slot.closest(".time-slot");
                if (label) {
                    label.style.opacity = disabled ? "0.4" : "1";
                    label.title = "";
                }
            });
        }

        async function checkAvailability() {
            const date = dateInput.value;
            const currentRequest = ++requestNumber;

            availabilityReady = false;
            submitButton.disabled = true;
            setSlotsDisabled(true);

            if (!date || !room) return;

            try {
                const url = `php/check_availability.php?date=${encodeURIComponent(date)}&room=${encodeURIComponent(room)}`;

                const response = await fetch(url);
                const bookedSlots = await response.json();

                if (currentRequest !== requestNumber) return;

                if (!response.ok || !Array.isArray(bookedSlots)) {
                    throw new Error("Unable to check availability");
                }

                timeSlots.forEach(slot => {
                    const booked = bookedSlots.includes(slot.value);
                    slot.disabled = booked;

                    const label = slot.closest(".time-slot");
                    if (label) {
                        label.style.opacity = booked ? "0.4" : "1";
                        label.title = booked ? "This slot is already booked" : "";
                    }
                });

                availabilityReady = true;
                submitButton.disabled = false;

            } catch (error) {
                if (currentRequest !== requestNumber) return;

                console.error("Availability error:", error);
                alert("Could not check availability. Please try again.");
            }
        }

        dateInput.addEventListener("change", checkAvailability);

        bookingForm.addEventListener("submit", async function (event) {
            event.preventDefault();

            const selectedTime = bookingForm.querySelector('input[name="time"]:checked');

            if (!dateInput.value || !selectedTime) {
                alert("Please select a date and time slot.");
                return;
            }

            if (!availabilityReady || selectedTime.disabled) {
                alert("Please select an available time slot.");
                return;
            }

            submitButton.disabled = true;
            availabilityReady = false;

            const formData = new FormData();
            formData.append("booking-date", dateInput.value);
            formData.append("room", room);
            formData.append("time", selectedTime.value);

            try {
                const response = await fetch("php/create_booking.php", {
                    method: "POST",
                    body: formData
                });

                const result = await response.json();

                if (!response.ok || !result.success) {
                    throw new Error(result.error || "Booking failed");
                }

                alert("Booking confirmed successfully!");

                bookingForm.reset();
                await checkAvailability();

            } catch (error) {
                alert(error.message);
                await checkAvailability();
            }
        });

        submitButton.disabled = true;

        if (dateInput.value) {
            checkAvailability();
        } else {
            setSlotsDisabled(true);
        }
    }

    // Keep the existing edit form validation.
    if (editForm) {
        editForm.addEventListener("submit", function (event) {
            const selectedTime = editForm.querySelector(
                'input[name="edit-time"]:checked'
            );

            if (!selectedTime) {
                alert("Please select a time slot.");
                event.preventDefault();
            }
        });
    }
});
