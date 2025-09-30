let bookedDates = [];

// Fetch booked dates for this vehicle from the server
async function loadBookedDates(carVin) {
  try {
    const response = await fetch(`get_booked_dates.php?vin=${carVin}`);
    bookedDates = await response.json(); // expects array of "YYYY-MM-DD" strings
    disableBookedDatesUI();
  } catch (error) {
    console.error('Failed to load booked dates:', error);
  }
}

// Disable booked dates in the calendar UI
function disableBookedDatesUI() {
  const pickup = document.getElementById('pickupDate');
  const dropoff = document.getElementById('dropoffDate');

  // Disable selection of booked dates
  function disableInput(e) {
    if (bookedDates.includes(e.target.value)) {
      alert('This date is unavailable. Please select another date.');
      e.target.value = '';
    }
  }

  // Attach event listeners
  pickup.addEventListener('change', disableInput);
  dropoff.addEventListener('change', disableInput);
}

// On page load, call this with the selected vehicle VIN
window.onload = () => {
  const vehicle = JSON.parse(sessionStorage.getItem('selectedVehicle'));
  if (vehicle) {
    loadBookedDates(vehicle.vin);
  }
};
