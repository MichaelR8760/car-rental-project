// dl_verification.js
// JavaScript to handle driver license verification form submission

document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('dlVerificationForm');
    
    // Handle form submission
    form.addEventListener('submit', async (e) => {
        e.preventDefault();
        
        const verifyButton = document.getElementById('verifyButton');
        const buttonText = document.getElementById('buttonText');
        const spinner = document.getElementById('spinner');
        
        // Disable button and show loading
        verifyButton.disabled = true;
        buttonText.textContent = 'Verifying...';
        spinner.style.display = 'inline-block';
        
        // Get form data
        const dlNumber = document.getElementById('dlNumber').value;
        const dlState = document.getElementById('dlState').value;
        const dlExpiration = document.getElementById('dlExpiration').value;
        const dateOfBirth = document.getElementById('dateOfBirth').value;
        
        // Basic validation
        if (!dlNumber || !dlState || !dlExpiration || !dateOfBirth) {
            alert('Please fill in all required fields');
            resetButton();
            return;
        }
        
        // Validate expiration date is in the future
        const today = new Date();
        const expirationDate = new Date(dlExpiration);
        if (expirationDate <= today) {
            alert('License expiration date must be in the future');
            resetButton();
            return;
        }
        
        try {
            // Mock verification process - simulate API delay
            await new Promise(resolve => setTimeout(resolve, 2000));
            
            // Prepare form data for submission
            const formData = new FormData();
            formData.append('dl_number', dlNumber);
            formData.append('dl_state', dlState);
            formData.append('license_expiration_date', dlExpiration);
            formData.append('date_of_birth', dateOfBirth);
            
            // Send to PHP for processing
            const response = await fetch('process_dl_verification.php', {
                method: 'POST',
                body: formData
            });
            
            const result = await response.json();
            
            if (result.success) {
                alert('Driver license verified successfully!');
                // Redirect back to checkout
                window.location.href = 'checkout.html';
            } else {
                alert('Verification failed: ' + (result.error || 'Unknown error occurred'));
                resetButton();
            }
        
        } catch (error) {
            console.error('Error during verification:', error);
            alert('Error during verification process');
            resetButton();
        }
    });
    
    // Function to reset button state
    function resetButton() {
        const verifyButton = document.getElementById('verifyButton');
        const buttonText = document.getElementById('buttonText');
        const spinner = document.getElementById('spinner');
        
        verifyButton.disabled = false;
        buttonText.textContent = 'Verify License';
        spinner.style.display = 'none';
    }
});