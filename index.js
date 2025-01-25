// Toggle between login and registration forms
function toggleForm() {
    var loginForm = document.getElementById('login-form');
    var registerForm = document.getElementById('register-form');

    // Toggle the forms
    if (loginForm.style.display === 'none') {
        loginForm.style.display = 'block';
        registerForm.style.display = 'none';
    } else {
        loginForm.style.display = 'none';
        registerForm.style.display = 'block';
    }
}
