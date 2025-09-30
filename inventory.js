// Initialize page on load
window.onload = async function () {
  await loadVehicles();
  checkUrlParameters();
  displayLocationHeader();
};

// Get current user role
function checkUserRole() {
  return userRole;
}

// Load and display all vehicles
async function loadVehicles() {
  const vehicleListElement = document.getElementById('vehicleList');
  try {
    vehicleList = await getVehicles();
    if (vehicleList.length == 0) {
      const errorMsg = document.createElement('p');
      errorMsg.innerHTML = 'No cars are available. Please try again.';
      errorMsg.style.textAlign = 'center';
      errorMsg.style.fontSize = '30px';
      vehicleListElement.appendChild(errorMsg);
    } else {
      // Display each vehicle
      for (i = 0; i < vehicleList.length; i++) {
        const vehicleCard = document.createElement('div');
        vehicleCard.className = 'vehicle-card mb-4 p-3 rounded';
        vehicleCard.innerHTML = `
                    <div class="card-flip-container">
                    <div class="card-flip-inner">
            <!-- FRONT SIDE -->
                        <div class="card-front">
                            <div class="row g-3">
                                <div class="col-md-4">
                                    <img src="${
                                      vehicleList[i].image_url ||
                                      'https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcRoOMI1dHZB9gLDSnd-tykuLOULyZCIc2KOrg&s'
                                    }" class="img-fluid" alt="Image of ${vehicleList[i].year} ${vehicleList[i].make} ${vehicleList[i].model}">
                                </div>
                                <div class="col-md-8">
                                    <div class="vehicle-type">
                                        <h4>${vehicleList[i].type}</h4>
                                    </div>
    
                                    <div class="vehicle-name">
                                        <h5>${vehicleList[i].make} ${vehicleList[i].model} or similar</h5>
                                    </div>
    
                                    <div class="vehicle-info">
                                        <p><img src="person_icon.png" alt="Seats People" class="person-icon"> ${
                                          vehicleList[i].seats
                                        } | <img src="fuel_icon.png" alt="Fuel Type" class="fuel-icon"> ${vehicleList[i].fuel_type}</p>
                                     </div>
    
                                    <div class="vehicle-actions">
                                        ${
                                          checkUserRole() === 'admin'
                                            ? `<button type="button" class="btn btn-custom" onclick="editVehicle('${vehicleList[i].car_vin}', '${vehicleList[i].year}', '${vehicleList[i].make}', '${vehicleList[i].model}', '${vehicleList[i].image_url}', '${vehicleList[i].daily_rate}')">Edit</button>`
                                            : `<button type="button" class="btn btn-custom" onclick="rentVehicle('${vehicleList[i].car_vin}', '${vehicleList[i].year}', '${vehicleList[i].make}', '${vehicleList[i].model}', '${vehicleList[i].image_url}', '${vehicleList[i].daily_rate}')">Rent</button>`
                                        }
                                        <button type="button" class="btn btn-info ms-2" onclick="flipCard(this)">View Features</button>
                                    </div>
                                </div>
                            </div>

                            <p class="daily-rate"><strong>Daily Rate:</strong> $${vehicleList[i].daily_rate} per day</p>
                        </div>
            
            <!-- BACK SIDE -->
            <div class="card-back">
                <h5>Features</h5>
                <p>${vehicleList[i].features || 'No features listed'}</p>
                <button type="button" class="btn btn-secondary btn-sm" onclick="flipCard(this)">Back to Details</button>
            </div>
        </div>
    </div>
`;
        vehicleListElement.appendChild(vehicleCard);
      }
    }
  } catch (error) {
    const errorMsg = document.createElement('p');
    errorMsg.innerHTML = 'Unable to load cars.' + error;
    errorMsg.style.textAlign = 'center';
    errorMsg.style.fontSize = '30px';
    vehicleListElement.appendChild(errorMsg);
  }
}

// Display location name in header
function displayLocationHeader(locationId) {
  const locationNames = {
    1: 'Rochester, MN',
    2: 'Minneapolis, MN',
    3: 'Chicago, IL',
    4: 'Milwaukee, WI',
  };

  const headerElement = document.getElementById('locationHeader');
  if (locationId && locationNames[locationId]) {
    headerElement.textContent = `Location: ${locationNames[locationId]}`;
  } else {
    headerElement.textContent = '';
  }
}

// Fetch vehicles from server
async function getVehicles() {
  const response = await fetch(`get_vehicles.php?cache_bust=${Date.now()}`);
  const data = await response.json();
  return data;
}

// Flip vehicle card to show features
function flipCard(button) {
  const cardInner = button.closest('.card-flip-inner');
  cardInner.classList.toggle('flipped');
}

// Toggle filter panel visibility
function toggleFilter() {
  const panel = document.getElementById('filterPanel');
  panel.classList.toggle('open');
}

// Close filter panel
function closeFilter() {
  const panel = document.getElementById('filterPanel');
  panel.classList.remove('open');
}

// Reset all filters to default
function clearFilters() {
  document.getElementById('fuelType').value = '';
  document.getElementById('vehicleType').value = '';
  document.getElementById('seats').value = '';
  document.getElementById('maxPrice').value = '500';
  document.getElementById('location').value = '';
  document.getElementById('status').value = '';
  updatePriceDisplay();

  displayFilteredVehicles(vehicleList);
}

// Apply selected filters to vehicle list
function applyFilters() {
  const fuelType = document.getElementById('fuelType').value;
  const vehicleType = document.getElementById('vehicleType').value;
  const seats = document.getElementById('seats').value;
  const maxPrice = document.getElementById('maxPrice').value;
  const location = document.getElementById('location').value;
  const statusElement = document.getElementById('status');
  const status = statusElement ? statusElement.value : '';

  const filteredVehicles = vehicleList.filter((vehicle) => {
    return (
      (!fuelType || vehicle.fueltype === fuelType) &&
      (!vehicleType || vehicle.vehicleType === vehicleType) &&
      (!seats || vehicle.seats === parseInt(seats) || (seats == '8' && vehicle.seats >= 8)) &&
      vehicle.daily_rate <= parseFloat(maxPrice) &&
      (!location || vehicle.location_id === parseInt(location)) &&
      (!status || vehicle.status === status)
    );
  });

  displayFilteredVehicles(filteredVehicles);
  closeFilter();
}

// Display filtered vehicle results
function displayFilteredVehicles(vehicles) {
  const vehicleListElement = document.getElementById('vehicleList');
  vehicleListElement.innerHTML = '';

  if (vehicles.length === 0) {
    const errorMsg = document.createElement('p');
    errorMsg.innerHTML = 'No vehicles match your filters.';
    errorMsg.style.textAlign = 'center';
    errorMsg.style.fontSize = '24px';
    vehicleListElement.appendChild(errorMsg);
  } else {
    for (i = 0; i < vehicles.length; i++) {
      const vehicleCard = document.createElement('div');
      vehicleCard.className = 'vehicle-card mb-4 p-3 rounded';
      vehicleCard.innerHTML = `
                    <div class="card-flip-container">
                    <div class="card-flip-inner">
            <!-- FRONT SIDE -->
                        <div class="card-front">
                            <div class="row g-3">
                                <div class="col-md-4">
                                    <img src="${
                                      vehicleList[i].image_url ||
                                      'https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcRoOMI1dHZB9gLDSnd-tykuLOULyZCIc2KOrg&s'
                                    }" class="img-fluid" alt="Image of ${vehicleList[i].year} ${vehicleList[i].make} ${vehicleList[i].model}">
                                </div>
                                <div class="col-md-8">
                                    <div class="vehicle-type">
                                        <h4>${vehicleList[i].type}</h4>
                                    </div>
    
                                    <div class="vehicle-name">
                                        <h5>${vehicleList[i].make} ${vehicleList[i].model} or similar</h5>
                                    </div>
    
                                    <div class="vehicle-info">
                                        <p><img src="person_icon.png" alt="Seats People" class="person-icon"> ${
                                          vehicleList[i].seats
                                        } | <img src="fuel_icon.png" alt="Fuel Type" class="fuel-icon"> ${vehicleList[i].fuel_type}</p>
                                     </div>
    
                                    <div class="vehicle-actions">
                                        ${
                                          checkUserRole() === 'admin'
                                            ? `<button type="button" class="btn btn-custom" onclick="editVehicle('${vehicleList[i].car_vin}', '${vehicleList[i].year}', '${vehicleList[i].make}', '${vehicleList[i].model}', '${vehicleList[i].image_url}', '${vehicleList[i].daily_rate}')">Edit</button>`
                                            : `<button type="button" class="btn btn-custom" onclick="rentVehicle('${vehicleList[i].car_vin}', '${vehicleList[i].year}', '${vehicleList[i].make}', '${vehicleList[i].model}', '${vehicleList[i].image_url}', '${vehicleList[i].daily_rate}')">Rent</button>`
                                        }
                                        <button type="button" class="btn btn-info ms-2" onclick="flipCard(this)">View Features</button>
                                    </div>
                                </div>
                            </div>

                            <p class="daily-rate"><strong>Daily Rate:</strong> $${vehicleList[i].daily_rate} per day</p>
                        </div>
            
            <!-- BACK SIDE -->
            <div class="card-back">
                <h5>Features</h5>
                <p>${vehicleList[i].features || 'No features listed'}</p>
                <button type="button" class="btn btn-secondary btn-sm" onclick="flipCard(this)">Back to Details</button>
            </div>
        </div>
    </div>
`;
      vehicleListElement.appendChild(vehicleCard);
    }
  }
}

// Update price display as slider moves
function updatePriceDisplay() {
  const priceSlider = document.getElementById('maxPrice');
  const priceDisplay = document.getElementById('priceDisplay');
  priceDisplay.textContent = `Up to $${priceSlider.value} per day`;
}

// Initialize price slider listener
document.addEventListener('DOMContentLoaded', function () {
  document.getElementById('maxPrice').addEventListener('input', updatePriceDisplay);
});

// Store vehicle data and redirect to checkout
function rentVehicle(vin, year, make, model, imageUrl, dailyRate) {
  const fullVehicle = vehicleList.find((v) => v.car_vin === vin);

  const vehicleData = {
    vin: vin,
    year: year,
    make: make,
    model: model,
    image_url: imageUrl,
    daily_rate: dailyRate,
    location_id: fullVehicle.location_id,
  };
  sessionStorage.setItem('selectedVehicle', JSON.stringify(vehicleData));
  window.location.href = 'checkout.html';
}

// Store vehicle data and redirect to edit page
function editVehicle(vin, year, make, model, imageUrl, dailyRate) {
  const vehicleData = {
    vin: vin,
    year: year,
    make: make,
    model: model,
    image_url: imageUrl,
    daily_rate: dailyRate,
  };
  sessionStorage.setItem('selectedVehicle', JSON.stringify(vehicleData));
  window.location.href = 'edit_vehicle.php';
}

// Check URL parameters and apply filters if present
function checkUrlParameters() {
  const urlParams = new URLSearchParams(window.location.search);
  const vehicleType = urlParams.get('vehicleType');
  const fuelType = urlParams.get('fuelType');
  const location = urlParams.get('location');
  const status = urlParams.get('status');
  const startDate = urlParams.get('start_date');
  const endDate = urlParams.get('end_date');

  // If date range provided, filter by availability
  if (startDate && endDate) {
    filterVehiclesByDates(startDate, endDate, location);
    return;
  }

  // Set filter values from URL parameters
  if (vehicleType) {
    document.getElementById('vehicleType').value = vehicleType;
  }

  if (fuelType) {
    document.getElementById('fuelType').value = fuelType;
  }

  if (location) {
    document.getElementById('location').value = location;
    displayLocationHeader(location);
  }

  if (status) {
    document.getElementById('status').value = status;
  }

  // Apply filters if any were set
  if (vehicleType || fuelType || location || status) {
    applyFilters();
  }
}

// Filter vehicles by date availability
async function filterVehiclesByDates(startDate, endDate, location) {
  try {
    let url = `get_vehicles.php?start_date=${startDate}&end_date=${endDate}`;
    if (location) {
      url += `&location=${location}`;
    }

    const response = await fetch(url);
    vehicleList = await response.json();
    displayFilteredVehicles(vehicleList);
  } catch (error) {
    displayFilteredVehicles([]);
  }
}