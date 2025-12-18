// edit_vehicle.js
// JavaScript to handle editing vehicle details
// and updating sessionStorage accordingly.

// Load vehicle details into form on page load
document.addEventListener("DOMContentLoaded", () => {
    loadVehicleDetailsForEdit();
    setupEditForm();
});

// Load vehicle details from sessionStorage and populate form
function loadVehicleDetailsForEdit() {
    const vehicleData = JSON.parse(sessionStorage.getItem('selectedVehicle'));
    console.log('Vehicle data from sessionStorage:', vehicleData);
    
    // If no vehicle data, redirect to inventory
    if (!vehicleData) {
        alert('No vehicle selected for editing');
        window.location.href = 'inventory.php';
        return;
    }
    
    // Redirect to the same page but with VIN parameter so your existing PHP code works
    if (!window.location.search.includes('vin=')) {
        window.location.href = `edit_vehicle.php?vin=${vehicleData.vin}`;
        return;
    }
}

// Setup form submission handler
function setupEditForm() {
    const form = document.getElementById('vehicleForm');
    form.addEventListener('submit', async (e) => {
        e.preventDefault();
        
        const submitButton = form.querySelector('button[type="submit"]');
        const originalText = submitButton.textContent;
        
        // Disable submit button and show loading
        submitButton.disabled = true;
        submitButton.textContent = 'Updating...';
        
        try {
            const formData = new FormData(form);
            
            const response = await fetch('save_vehicle.php', {
                method: 'POST',
                body: formData
            });
            
            const result = await response.json();
            
            // Handle response
            if (result.success) {
                showAlert('Vehicle updated successfully!', 'success');
                
                // Update sessionStorage with new data if VIN changed
                const newVin = formData.get('car_vin');
                const vehicleData = JSON.parse(sessionStorage.getItem('selectedVehicle'));
                vehicleData.vin = newVin;
                vehicleData.make = formData.get('make');
                vehicleData.model = formData.get('model');
                vehicleData.year = formData.get('year');
                vehicleData.daily_rate = formData.get('daily_rate');
                sessionStorage.setItem('selectedVehicle', JSON.stringify(vehicleData));
                
                // Redirect back to inventory after a short delay
                setTimeout(() => {
                    window.location.href = 'inventory.php';
                }, 1500);
            
            } else {
                showAlert('Error updating vehicle: ' + (result.error || 'Unknown error'), 'danger');
            }
            
            // If the response is not ok, show error
        } catch (error) {
            console.error('Error updating vehicle:', error);
            showAlert('Error updating vehicle: ' + error.message, 'danger');
        } finally {
            // Re-enable submit button
            submitButton.disabled = false;
            submitButton.textContent = originalText;
        }
    });
}

// Function to show alert messages
function showAlert(message, type) {
    const alertPlaceholder = document.getElementById('alertPlaceholder');
    const alert = document.createElement('div');
    alert.className = `alert alert-${type} alert-dismissible fade show`;
    alert.innerHTML = `
        ${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    `;
    alertPlaceholder.innerHTML = '';
    alertPlaceholder.appendChild(alert);
    
    // Auto-dismiss after 5 seconds
    setTimeout(() => {
        if (alert.parentNode) {
            alert.remove();
        }
    }, 5000);
}