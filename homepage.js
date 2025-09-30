// homepage.js
// JavaScript to handle homepage interactions
// including the car search form and map initialization using Leaflet.js

// Handle car search form submission
document.getElementById('carSearchForm').addEventListener('submit', function(e) {
    e.preventDefault();

    const startDate = document.getElementById('pickupDate').value;
    const endDate = document.getElementById('dropoffDate').value;
    const pickupLocation = document.getElementById('pickupLocation').value;
    const pickupTime = document.getElementById('pickupTime').value;
    const dropoffTime = document.getElementById('dropoffTime').value;
    const dropoffLocation = document.getElementById('dropoffLocation').value;

    if (!startDate || !endDate || !pickupLocation || !pickupTime || !dropoffTime || !dropoffLocation) {
        alert('Please fill in all required fields.');
        return;
    }

    // Save search criteria to sessionStorage
    sessionStorage.setItem('selectedDates', JSON.stringify({
        pickup: startDate,
        dropoff: endDate,
        pickupTime: pickupTime,
        dropoffTime: dropoffTime,
        dropoffLocation: dropoffLocation,
        pickupLocation: pickupLocation

    }));

    // Redirect to inventory with query parameters
    let url = `inventory.php?start_date=${startDate}&end_date=${endDate}&pickup_location=${pickupLocation}&pickup_time=${pickupTime}&dropoff_time=${dropoffTime}&dropoff_location=${dropoffLocation}`;
    const locationMap = {
        'rochester': '1',
        'minneapolis': '2',
        'chicago': '3',
        'milwaukee': '4'
    };

    // Append location ID if valid
    if (locationMap[pickupLocation]) {
        url += `&location=${locationMap[pickupLocation]}`;
    }

    window.location.href = url;

});

// Initialize Leaflet map
var map = L.map('map').setView([43.5, -90], 6);

L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
    attribution: '© OpenStreetMap contributors'
}).addTo(map);

// Add markers for each location
L.marker([44.0234, -92.4802]).addTo(map).bindPopup('<b>Rochester, MN</b>');
L.marker([44.9778, -93.2650]).addTo(map).bindPopup('<b>Minneapolis, MN</b>');
L.marker([41.8781, -87.6298]).addTo(map).bindPopup('<b>Chicago, IL</b>');
L.marker([43.0389, -87.9065]).addTo(map).bindPopup('<b>Milwaukee, WI</b>');
