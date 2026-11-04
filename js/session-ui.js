// Update the navigation to reflect a PHP login session.
document.addEventListener("DOMContentLoaded", async function () {
    try {
        const response = await fetch("php/me.php", { credentials: "same-origin", cache: "no-store" });
        if (!response.ok) return;
        const session = await response.json();
        if (!session.loggedIn) return;

        const navbar = document.querySelector(".nav-buttons");
        if (!navbar) return;
        navbar.querySelectorAll('a[href="login.html"], a[href="register.html"]').forEach(link => link.remove());

        const name = document.createElement("span");
        name.textContent = "Hi, " + session.name;
        name.className = "user-greeting";
        navbar.appendChild(name);

        const logout = document.createElement("a");
        logout.href = "php/logout.php";
        logout.className = "login-btn";
        logout.textContent = "Logout";
        navbar.appendChild(logout);
    } catch (error) {
        // Leave navigation unchanged when PHP is not running.
    }
});
