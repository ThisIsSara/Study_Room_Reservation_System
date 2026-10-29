document.addEventListener("DOMContentLoaded", function () {
    
    // Validate registration form
    const registerForm = document.querySelector(".register-box form");
    if (registerForm) {
        registerForm.addEventListener("submit", function (e) {
            const email = document.getElementById("register-email").value;
            const password = document.getElementById("register-password").value;
            const confirmPassword = document.getElementById("confirm-password").value;

            if (!email.includes("@")) {
                alert("Please enter a valid email address.");
                e.preventDefault();
            } else if (password !== confirmPassword) {
                alert("Passwords do not match!");
                e.preventDefault();
            } else if (password.length < 6) {
                alert("Password must be at least 6 characters.");
                e.preventDefault();
            }
        });
    }

    // Validate login form
    const loginForm = document.querySelector(".login-box form");
    if (loginForm) {
        loginForm.addEventListener("submit", function (e) {
            const email = document.getElementById("email").value;
            const password = document.getElementById("password").value;

            if (!email.trim() || !password.trim()) {
                alert("Please fill in all fields.");
                e.preventDefault();
            }
        });
    }
});
