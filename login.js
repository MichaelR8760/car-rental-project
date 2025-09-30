// login.js
// JavaScript to handle login form submission

// Wait for the DOM to load
document.addEventListener('DOMContentLoaded', function() {
    document.getElementById('loginForm').addEventListener('submit', function(e) {
        e.preventDefault();
        
        const identifier = document.getElementById('identifier').value;
        const password = document.getElementById('password').value;
        
        login(identifier, password);
    });
});

// Function to handle login
async function login(identifier, password) {
    const formData = new URLSearchParams();
    formData.append('identifier', identifier);
    formData.append('password', password);

    try {
        const response = await fetch('login.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded'
            },
            body: formData.toString()
        });

        const result = await response.json();

        if (result.success) {
            // Successful login - redirect to homepage
            window.location.href = result.redirect;
        } else {
            // Show error message
            showError(result.error);
        }

    } catch (error) {
        console.error('Login failed:', error);
        showError('An error occurred during login. Please try again.');
    }
}

// Function to display error messages
function showError(message) {
    const alertPlaceholder = document.getElementById('alertPlaceholder');
    alertPlaceholder.innerHTML = `
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
        ${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    `;
}