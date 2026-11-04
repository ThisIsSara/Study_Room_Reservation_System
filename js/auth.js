document.addEventListener("DOMContentLoaded", function () {
    const params = new URLSearchParams(window.location.search);
    const messages = {
        invalid: "Please check the information you entered.",
        credentials: "Incorrect email or password.",
        duplicate: "An account with this email already exists.",
        database: "Could not connect to the database.",
        login_required: "Please log in to continue."
    };
    const error = params.get("error");
    if (error && messages[error]) alert(messages[error]);
    if (params.get("registered") === "1") alert("Account created. You can now log in.");
    if (params.get("logged_out") === "1") alert("You have logged out.");

    
    // 1. Validation for Registration (register.html)
    const registerForm = document.querySelector(".register-box form");
    if (registerForm) {
        registerForm.addEventListener("submit", function (e) {
            const emailInput = document.getElementById("register-email");
            const passwordInput = document.getElementById("register-password");
            const confirmPasswordInput = document.getElementById("confirm-password");

            // Check email format
            if (!emailInput.value.includes("@")) {
                alert("Please enter a valid email address.");
                e.preventDefault();
                return;
            }

            // Check if passwords match
            if (passwordInput.value !== confirmPasswordInput.value) {
                alert("Passwords do not match! Please check again.");
                e.preventDefault();
                return;
            }

            // Password length check
            if (passwordInput.value.length < 8) {
                alert("Password must be at least 8 characters long.");
                e.preventDefault();
                return;
            }
        });
    }

    // 2. Validation for Login (login.html)
    const loginForm = document.querySelector(".login-box form");
    if (loginForm) {
        loginForm.addEventListener("submit", function (e) {
            const emailInput = document.getElementById("email");
            const passwordInput = document.getElementById("password");

            if (emailInput.value.trim() === "" || passwordInput.value.trim() === "") {
                alert("Please fill in both Email and Password fields.");
                e.preventDefault();
            }
        });
    }
});
