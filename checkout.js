// checkout.js
// JavaScript for checkout page
// Handles loading vehicle details, calculating costs,
// managing payment methods, and processing the booking.
// Uses Stripe for payment processing.

// Initialize Stripe
const stripe = Stripe(
  'pk_test_51S5zySHAHJVoBuDNptIz7KdNwEEooQjwBJXUwCggbaq3ValpbM3oYX6PyoyBIqqQK3QPN0yFasHIVZDEtHdReG1d00HovIsM8G'
);

document.addEventListener('DOMContentLoaded', () => {
  loadSavedDates();
  loadVehicleDetails();
  setupCheckoutForm();
  setupCostCalculation();
  loadPaymentMethods();
  restoreFormData();
});

// Load previously selected dates from sessionStorage
function loadSavedDates() {
  const savedDates = sessionStorage.getItem('selectedDates');

  if (savedDates) {
    const dates = JSON.parse(savedDates);
    document.getElementById('pickupDate').value = dates.pickup;
    document.getElementById('dropoffDate').value = dates.dropoff;
    calculateAndDisplayCost(); // Calculate cost when dates are loaded
  }
}

// Load vehicle details from sessionStorage
function loadVehicleDetails() {
  const vehicleData = JSON.parse(sessionStorage.getItem('selectedVehicle'));

  // If no vehicle data, redirect to inventory
  if (!vehicleData) {
    window.location.href = 'inventory.php';
    return;
  }

  // Display vehicle details
  function getLocationName(locationId) {
    const locationNames = {
      1: 'Rochester, MN',
      2: 'Minneapolis, MN',
      3: 'Chicago, IL',
      4: 'Milwaukee, WI',
    };
    return locationNames[locationId] || 'Location TBD';
  }

  // Populate vehicle summary
  document.getElementById('vehicleImage').src = vehicleData.image_url || 'default-car.jpg';
  document.getElementById(
    'vehicleName'
  ).textContent = `${vehicleData.year} ${vehicleData.make} ${vehicleData.model} Or Similar`;
  document.getElementById('vehicleRate').textContent = `$${vehicleData.daily_rate} per day`;
  document.getElementById('vehicleLocation').textContent = `Pickup: ${getLocationName(vehicleData.location_id)}`;
}

// Calculate cost when dates change
function setupCostCalculation() {
  document.getElementById('pickupDate').addEventListener('change', calculateAndDisplayCost);
  document.getElementById('dropoffDate').addEventListener('change', calculateAndDisplayCost);
}

// Calculate and display total cost
async function calculateAndDisplayCost() {
  const startDate = document.getElementById('pickupDate').value;
  const endDate = document.getElementById('dropoffDate').value;
  const vehicleData = JSON.parse(sessionStorage.getItem('selectedVehicle'));

  // If dates or vehicle data are missing, do nothing
  if (!startDate || !endDate || !vehicleData) {
    return;
  }

  // Call server to calculate cost
  try {
    const response = await fetch('calculate_cost.php', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/x-www-form-urlencoded',
      },
      body: new URLSearchParams({
        vehicle_vin: vehicleData.vin,
        start_date: startDate,
        end_date: endDate,
      }),
    });

    const data = await response.json();
    // If successful, display total cost
    if (data.success) {
      document.getElementById('totalCost').textContent = `Total Cost: $${data.total_cost}`;
    } else {
      // Show error if cost cannot be calculated
      document.getElementById('totalCost').textContent = 'Error calculating cost';
    }
  } catch (error) {
    // Log and show error
    console.error('Error calculating cost:', error);
    document.getElementById('totalCost').textContent = 'Error calculating cost';
  }
}

// Setup form submission handling
function setupCheckoutForm() {
  const form = document.getElementById('checkoutForm');
  form.addEventListener('submit', async (e) => {
    e.preventDefault();

    // Disable submit button and show loading
    const submitButton = document.getElementById('submit-button');
    const buttonText = document.getElementById('button-text');
    const spinner = document.getElementById('spinner');

    // Check if payment method is selected
    const paymentMethod = document.getElementById('paymentMethod').value;
    if (!paymentMethod || paymentMethod === '') {
      alert('Please select a payment method before completing your reservation.');

      // Re-enable submit button
      submitButton.disabled = false;
      buttonText.textContent = 'Complete Reservation';
      spinner.style.display = 'none';
      return;
    }

    // Disable button and show spinner
    submitButton.disabled = true;
    buttonText.textContent = 'Processing...';
    spinner.style.display = 'inline-block';

    // Gather form data
    const formData = new FormData(form);
    const vehicleData = JSON.parse(sessionStorage.getItem('selectedVehicle'));

    // Get dates for cost calculation
    const startDate = formData.get('pickup_date');
    const endDate = formData.get('dropoff_date');

    try {
      // First, get the total cost
      const costResponse = await fetch('calculate_cost.php', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: new URLSearchParams({
          vehicle_vin: vehicleData.vin,
          start_date: startDate,
          end_date: endDate,
        }),
      });

      // If cost calculation fails, throw error
      const costData = await costResponse.json();

      if (!costData.success) {
        throw new Error(costData.error);
      }

      // Create payment intent
      const paymentResponse = await fetch('create_payment_intent.php', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: new URLSearchParams({
          amount: Math.round(costData.total_cost * 100), // Stripe uses cents
        }),
      });

      const paymentData = await paymentResponse.json();

      if (!paymentData.success) {
        throw new Error(paymentData.error);
      }

      // Confirm payment with Stripe
      const { error, paymentIntent } = await stripe.confirmCardPayment(paymentData.client_secret, {
        payment_method: paymentMethod, // Use selected payment method ID from dropdown
      });

      // If payment fails, show error
      if (error) {
        alert('Payment failed: ' + error.message);

        // Re-enable submit button
        submitButton.disabled = false;
        buttonText.textContent = 'Complete Reservation';
        spinner.style.display = 'none';
      } else if (paymentIntent.status === 'succeeded') {
        // Payment successful, now create booking
        formData.append('vehicle_vin', vehicleData.vin);
        formData.append('payment_intent_id', paymentIntent.id);
        formData.append('total_cost', costData.total_cost);

        // Send booking data to server
        const bookingResponse = await fetch('create_reservation.php', {
          method: 'POST',
          body: formData,
        });

        const result = await bookingResponse.json();

        // If booking requires DL verification, redirect
        if (result.error === 'DL_VERIFICATION_REQUIRED') {
          alert('Redirecting to DL verification');
          window.location.href = 'dl_verification.html';
          return;
        }

        // If booking successful, redirect to confirmation
        if (result.success) {
          sessionStorage.setItem('bookingId', result.booking_id);
          alert('Reservation created successfully!');
          window.location.href = 'booking_confirmation.html';
        } else {
          alert('Booking failed: ' + (result.error || 'Unknown error occurred'));

          // Re-enable submit button
          submitButton.disabled = false;
          buttonText.textContent = 'Complete Reservation';
          spinner.style.display = 'none';
        }
      }
    } catch (error) {
      alert('Error: ' + error.message);

      // Re-enable submit button
      submitButton.disabled = false;
      buttonText.textContent = 'Complete Reservation';
      spinner.style.display = 'none';
    }
  });
}

// Fetch and display vehicle details
async function fetchVehicleDetails(vin) {
  try {
    const response = await fetch(`get_vehicle_details.php?vin=${vin}`);
    const vehicle = await response.json();

    document.getElementById('vehicleSummary').innerHTML = `
            <h5>${vehicle.year} ${vehicle.make} ${vehicle.model}</h5>
            <p>Daily Rate: $${vehicle.daily_rate} per day</p>
        `;
  } catch (error) {
    console.error('Error loading vehicle details:', error);
  }
}

// Handle add payment method button
document.getElementById('addPaymentMethod').addEventListener('click', function () {
  saveFormData();
  window.location.href = 'create_payment_method_session.php';
});

// Load payment methods when page loads
async function loadPaymentMethods() {
  try {
    const response = await fetch('get_payment_methods.php');
    const data = await response.json();

    // If successful, populate dropdown
    if (data.success) {
      const dropdown = document.getElementById('paymentMethod');

      // Clear existing options (keep the default one)
      dropdown.innerHTML = '<option value="">-- Select Payment Method --</option>';

      // Add saved payment methods
      data.payment_methods.forEach((method) => {
        const option = document.createElement('option');
        option.value = method.id;
        option.textContent = `${method.brand.toUpperCase()} ending in ${method.last4}`;
        dropdown.appendChild(option);
      });
    }
  } catch (error) {
    console.error('Error loading payment methods:', error);
  }
}

// Save form data to sessionStorage
function saveFormData() {
  const formData = {
    pickupDate: document.getElementById('pickupDate').value,
    dropoffDate: document.getElementById('dropoffDate').value,
    firstName: document.getElementById('firstName').value,
    lastName: document.getElementById('lastName').value,
    email: document.getElementById('email').value,
    phone: document.getElementById('phone').value,
  };
  sessionStorage.setItem('checkoutFormData', JSON.stringify(formData));
}

// Restore form data from sessionStorage
function restoreFormData() {
  const savedData = sessionStorage.getItem('checkoutFormData');
  if (savedData) {
    const formData = JSON.parse(savedData);
    document.getElementById('pickupDate').value = formData.pickupDate || '';
    document.getElementById('dropoffDate').value = formData.dropoffDate || '';
    document.getElementById('firstName').value = formData.firstName || '';
    document.getElementById('lastName').value = formData.lastName || '';
    document.getElementById('email').value = formData.email || '';
    document.getElementById('phone').value = formData.phone || '';

    // Recalculate cost if dates were restored
    if (formData.pickupDate && formData.dropoffDate) {
      calculateAndDisplayCost();
    }
  }
}
