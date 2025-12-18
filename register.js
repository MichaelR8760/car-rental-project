// Registration form handler
// Collects user input and submits to registration endpoint

document.addEventListener("DOMContentLoaded", () => {
    const form = document.querySelector("form");
  
    form.addEventListener("submit", async (e) => {
      e.preventDefault(); // Prevent default form submission
  
      // Collect form values
      const firstName = document.getElementById('firstName').value;
      const lastName = document.getElementById('lastName').value;
      const username = document.getElementById('username').value;
      const email = document.getElementById('email').value;
      const password = document.getElementById('password').value;
      const phoneNumber = document.getElementById('phoneNumber').value.trim();
  
      await register(firstName, lastName, username, email, password, phoneNumber);
    });
});

// Display error message to user
function showError(message) {
    const alertPlaceholder = document.getElementById('alertPlaceholder');
    alertPlaceholder.innerHTML = `
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
        ${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    `;
}

// Submit registration data to server
async function register(firstName, lastName, username, email, password, phoneNumber) {
    const formData = new URLSearchParams();
    formData.append('first_name', firstName);
    formData.append('last_name', lastName);
    formData.append('username', username);
    formData.append('email', email);
    formData.append('password', password);
    formData.append('phone_number', phoneNumber);

    try {
        const response = await fetch('register.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded'
            },
            body: formData.toString()
        });

        // Handle successful registration redirect
        if (response.redirected) {
            alert("Registration Successful. Please log in.");
            window.location.href = response.url;
        } else if (!response.ok) {
            const errorText = await response.text();
            showError("Registration failed: " + errorText);
        } 

    } catch (error) {
        showError("Registration could not be completed: " + error.message);
    }
}