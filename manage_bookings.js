// manage_bookings.js
// JavaScript to manage and display bookings
// Allows modification and cancellation of bookings

let currentBookingId = null;

// Load bookings when page loads
document.addEventListener('DOMContentLoaded', function () {
  loadBookings();
});

// Load all bookings
function loadBookings() {
  fetch('get_all_bookings.php')
    .then((response) => response.json())
    .then((data) => {
      if (data.success) {
        displayBookings(data.bookings);
      } else {
        console.error('Error loading bookings:', data.error);
      }
    })
    .catch((error) => {
      console.error('Error:', error);
    });
}

// Display bookings in containers
function displayBookings(bookings) {
  const container = document.getElementById('bookingsContainer');
  container.innerHTML = '';

  if (bookings.length === 0) {
    container.innerHTML = '<div class="booking-card"><p>No bookings found.</p></div>';
    return;
  }

  bookings.forEach((booking) => {
    const bookingElement = createBookingElement(booking);
    container.appendChild(bookingElement);
  });
}

// Create booking element (rectangular container)
function createBookingElement(booking) {
  const div = document.createElement('div');
  div.className = 'booking-card d-flex justify-content-between align-items-center';

  const statusClass = getStatusClass(booking.status);

  div.innerHTML = `
        <div>
            <span class="badge ${statusClass}">${booking.status.toUpperCase()}</span>
            <strong>Booking Date:</strong> ${formatDate(booking.start_date)} - ${formatDate(booking.end_date)}
            <br>
            <strong>Vehicle:</strong> ${booking.year} ${booking.make} ${booking.model}
            <br>
            <strong>Confirmation:</strong> ${booking.confirmation_code}
            <strong>Customer:</strong> ${booking.first_name} ${booking.last_name}
            <br>
            <strong>Customer DL Last 4:</strong> ${booking.dl_last_four}
            <br>
            <strong>Total Cost:</strong> $${parseFloat(booking.total_cost).toFixed(2)}
            <strong>Pickup Location:</strong> ${booking.pickup_location || 'N/A'}
            <br>
        </div>
        <div class="d-flex gap-2">
        ${
          booking.status === 'upcoming'
            ? `
            <button class="btn btn-modify-res btn-sm" onclick="openModifyModal(${booking.booking_id})">Modify Reservation</button>
            <button class="btn btn-cancel-res btn-sm" onclick="cancelBooking(${booking.booking_id})">Cancel Reservation</button>
        `
            : booking.status === 'active'
            ? `
            <button class="btn btn-extend-res btn-sm" onclick="openModifyModal(${booking.booking_id})">Extend Reservation</button>
        `
            : ''
        }
        </div>
    `;

  return div;
}

// Get status badge class
function getStatusClass(status) {
  switch (status) {
    case 'upcoming':
      return 'bg-primary';
    case 'active':
      return 'bg-success';
    case 'completed':
      return 'bg-secondary';
    case 'cancelled':
      return 'bg-danger';
    default:
      return 'bg-secondary';
  }
}

// Format date
function formatDate(dateString) {
  const date = new Date(dateString);
  return date.toLocaleDateString('en-US', {
    month: 'short',
    day: 'numeric',
    year: 'numeric',
  });
}

// Open modify modal
function openModifyModal(bookingId) {
  currentBookingId = bookingId;

  // Get booking details
  const formData = new FormData();
  formData.append('booking_id', bookingId);

  fetch('get_booking_details.php', {
    method: 'POST',
    body: formData,
  })
    .then((response) => response.json())
    .then((data) => {
      if (data.success) {
        displayBookingDetailsInModal(data.booking);
        const modal = new bootstrap.Modal(document.getElementById('modifyModal'));
        modal.show();
      } else {
        alert('Error loading booking details');
      }
    })
    .catch((error) => {
      console.error('Error:', error);
      alert('Error loading booking details');
    });
}

// Display booking details in modal
function displayBookingDetailsInModal(booking) {
  const detailsDiv = document.getElementById('bookingDetails');
  detailsDiv.innerHTML = `
        <h6>Current Booking Details:</h6>
        <p><strong>Vehicle:</strong> ${booking.year} ${booking.make} ${booking.model}</p>
        <p><strong>Current Dates:</strong> ${formatDate(booking.start_date)} - ${formatDate(booking.end_date)}</p>
        <p><strong>Total Cost:</strong> $${booking.total_cost}</p>
        <p><strong>Confirmation:</strong> ${booking.confirmation_code}</p>
        <hr>
    `;

  // Pre-fill form with current dates
  document.getElementById('newStartDate').value = booking.start_date;
  document.getElementById('newEndDate').value = booking.end_date;
}

// Save changes
document.getElementById('saveChanges').addEventListener('click', function () {
  const newStartDate = document.getElementById('newStartDate').value;
  const newEndDate = document.getElementById('newEndDate').value;

  if (!newStartDate || !newEndDate) {
    alert('Please select both dates');
    return;
  }

  if (new Date(newStartDate) >= new Date(newEndDate)) {
    alert('End date must be after start date');
    return;
  }

  const formData = new FormData();
  formData.append('booking_id', currentBookingId);
  formData.append('start_date', newStartDate);
  formData.append('end_date', newEndDate);

  fetch('update_booking.php', {
    method: 'POST',
    body: formData,
  })
    .then((response) => response.json())
    .then((data) => {
      if (data.success) {
        alert('Booking updated successfully!');
        bootstrap.Modal.getInstance(document.getElementById('modifyModal')).hide();
        loadBookings(); // Reload bookings
      } else {
        alert('Error updating booking: ' + data.error);
      }
    })
    .catch((error) => {
      console.error('Error:', error);
      alert('Error updating booking');
    });
});

// Cancel booking
function cancelBooking(bookingId) {
  if (confirm('Are you sure you want to cancel this booking? This action cannot be undone.')) {
    const formData = new FormData();
    formData.append('booking_id', bookingId);

    // TODO: Create cancel_booking.php endpoint
    fetch('cancel_booking.php', {
      method: 'POST',
      body: formData,
    })
      .then((response) => response.json())
      .then((data) => {
        if (data.success) {
          alert('Booking cancelled successfully!');
          loadBookings(); // Reload bookings
        } else {
          alert('Error cancelling booking: ' + data.error);
        }
      })
      .catch((error) => {
        console.error('Error:', error);
        alert('Error cancelling booking');
      });
  }
}

// Add this to the bottom of manage_bookings.js
document.addEventListener('click', function (e) {
  if (e.target && e.target.id === 'updateCustomerBtn') {
    if (currentBookingId) {
      window.location.href = `edit_customer_details.php?booking_id=${currentBookingId}`;
    }
  }
});
