/* 
JavaScript to handle form submission for adding a vehicle
and display error messages using Bootstrap alerts.
*/

document.addEventListener('DOMContentLoaded', () => {
  const form = document.querySelector('form');

  form.addEventListener('submit', async (e) => {
    e.preventDefault();

    const formData = new FormData(form);

    try {
      const response = await fetch('add_vehicle.php', {
        method: 'POST',
        body: formData,
      });

      if (response.ok) {
        const result = await response.text();
        if (result === 'success') {
          alert('Vehicle added successfully');
          form.reset();
        } else {
          showError('Error: ' + result);
        }
      } else {
        showError('Failed to add vehicle');
      }
    } catch (error) {
      showError('Error: ' + error.message);
    }
  });
});

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
