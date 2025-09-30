// JavaScript to load and display booking confirmation details
// and handle modification of booking dates.

document.addEventListener('DOMContentLoaded', () => {
  loadBookingDetails();
});

// Load booking details from server
async function loadBookingDetails() {
  const bookingId = sessionStorage.getItem('bookingId');

  // If no booking ID, redirect to inventory
  if (!bookingId) {
    window.location.href = 'inventory.php';
    return;
  }

  // Fetch booking details
  try {
    const response = await fetch('get_booking_details.php', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/x-www-form-urlencoded',
      },
      body: new URLSearchParams({
        booking_id: bookingId,
      }),
    });

    const data = await response.json();

    // If successful, display booking details
    if (data.success) {
      displayBookingDetails(data.booking);
    } else {
      // Show error if booking details cannot be loaded
      document.getElementById('confirmation_code').textContent = 'Error loading booking details';
      console.error('Error:', data.error);
    }
  } catch (error) {
    // Log and show error
    console.error('Error loading booking details:', error);
    document.getElementById('confirmation_code').textContent = 'Error loading booking details';
  }
}

function displayBookingDetails(booking) {
  // Display booking information
  document.getElementById('confirmation_code').textContent = booking.confirmation_code || 'N/A';
  document.getElementById('transaction_id').textContent = booking.transaction_id || 'N/A';

  // Display vehicle information
  document.getElementById('vehicle_title').textContent = `${booking.year} ${booking.make} ${booking.model}`;
  document.getElementById('vehicle_features').innerHTML = `
        <small>Color: ${booking.color || 'N/A'} | Type: ${booking.type || 'N/A'} | Seats: ${booking.seats}</small>
    `;

  // Display rental period
  document.getElementById('start_date').textContent = formatDate(booking.start_date);
  document.getElementById('end_date').textContent = formatDate(booking.end_date);

  // Calculate and display total days
  const startDate = new Date(booking.start_date);
  const endDate = new Date(booking.end_date);
  const totalDays = Math.ceil((endDate - startDate) / (1000 * 60 * 60 * 24));
  document.getElementById('total_days').textContent = totalDays;
  document.getElementById('rental_days').textContent = totalDays;

  // Display cost information
  document.getElementById('daily_rate').textContent = `$${parseFloat(booking.daily_rate).toFixed(2)}`;
  document.getElementById('total_cost').textContent = `$${parseFloat(booking.total_cost).toFixed(2)}`;
}

// Format date to a readable string
function formatDate(dateString) {
  const date = new Date(dateString);
  return date.toLocaleDateString('en-US', {
    weekday: 'long',
    year: 'numeric',
    month: 'long',
    day: 'numeric',
  });
}
