<!DOCTYPE html>
<html lang="en">
<head>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Noto+Serif+Ahom:wght@400;700&display=swap" rel="stylesheet">
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>RevLink Rentals - Home</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.5/dist/css/bootstrap.min.css" rel="stylesheet"
    integrity="sha384-SgOJa3DmI69IUzQ2PVdRZhwQ+dy64/BUtbMJw1MZ8t5HZApcHrRKUc4W0kG879m7" crossorigin="anonymous">
  <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
  <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
  <script defer src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.5/dist/js/bootstrap.bundle.min.js"crossorigin="anonymous"></script>
  <link href="main.css" rel="stylesheet">
</head>
<body>

  <!--Navigation Bar-->
  <?php include 'navbar.php'; ?>



  <!--Homepage Content (is the same for everyone)-->
  <section class="homepage-header">
    <h1 class="homepage-title">Skip the Worry. Start the Journey.</h1>

    <div class="search-container">
      <form id="carSearchForm">
        <div class="search-row">

          <!--Pick Up Location-->
          <div class="search-field location-input">
            <label for="pickupLocation">Pick up Location</label>
            <select id="pickupLocation" required>
              <option value="">Select City</option>
              <option value="rochester">Rochester, MN</option>
              <option value="minneapolis">Minneapolis, MN</option>
              <option value="chicago">Chicago, IL</option>
              <option value="milwaukee">Milwaukee, WI</option>
            </select>
          </div>

          <!--Pick up date -->
          <div class="search-field">
            <label for="pickupDate">Pick up Date</label>
            <input type="date" id="pickupDate" required>
          </div>

          <!--Pick up time -->
          <div class="search-field">
            <label for="pickupTime">Pick up Time</label>
            <select id="pickupTime" required>
              <option value="">Select Time</option>
              <option value="00:00">12:00 AM</option>
              <option value="00:30">12:30 AM</option>
              <option value="01:00">1:00 AM</option>
              <option value="01:30">1:30 AM</option>
              <option value="02:00">2:00 AM</option>
              <option value="02:30">2:30 AM</option>
              <option value="03:00">3:00 AM</option>
              <option value="03:30">3:30 AM</option>
              <option value="04:00">4:00 AM</option>
              <option value="04:30">4:30 AM</option>
              <option value="05:00">5:00 AM</option>
              <option value="05:30">5:30 AM</option>
              <option value="06:00">6:00 AM</option>
              <option value="06:30">6:30 AM</option>
              <option value="07:00">7:00 AM</option>
              <option value="07:30">7:30 AM</option>
              <option value="08:00">8:00 AM</option>
              <option value="08:30">8:30 AM</option>
              <option value="09:00">9:00 AM</option>
              <option value="09:30">9:30 AM</option>
              <option value="10:00">10:00 AM</option>
              <option value="10:30">10:30 AM</option>
              <option value="11:00">11:00 AM</option>
              <option value="11:30">11:30 AM</option>
              <option value="12:00">12:00 PM</option>
              <option value="12:30">12:30 PM</option>
              <option value="13:00">1:00 PM</option>
              <option value="13:30">1:30 PM</option>
              <option value="14:00">2:00 PM</option>
              <option value="14:30">2:30 PM</option>
              <option value="15:00">3:00 PM</option>
              <option value="15:30">3:30 PM</option>
              <option value="16:00">4:00 PM</option>
              <option value="16:30">4:30 PM</option>
              <option value="17:00">5:00 PM</option>
              <option value="17:30">5:30 PM</option>
              <option value="18:00">6:00 PM</option>
              <option value="18:30">6:30 PM</option>
              <option value="19:00">7:00 PM</option>
              <option value="19:30">7:30 PM</option>
              <option value="20:00">8:00 PM</option>
              <option value="20:30">8:30 PM</option>
              <option value="21:00">9:00 PM</option>
              <option value="21:30">9:30 PM</option>
              <option value="22:00">10:00 PM</option>
              <option value="22:30">10:30 PM</option>
              <option value="23:00">11:00 PM</option>
              <option value="23:30">11:30 PM</option>
            </select>
          </div>
        </div>

        <!-- Drop off location -->
        <div class="search-row">
          <div class="search-field location-input">
            <label for="dropoffLocation">Drop off Location</label>
            <select id="dropoffLocation" required>
              <option value="">Select City</option>
              <option value="rochester">Rochester, MN</option>
              <option value="minneapolis">Minneapolis, MN</option>
              <option value="chicago">Chicago, IL</option>
              <option value="milwaukee">Milwaukee, WI</option>
            </select>
          </div>

          <!-- Drop off date -->
          <div class="search-field">
            <label for="dropoffDate">Drop off Date</label>
            <input type="date" id="dropoffDate" required>
          </div>

          <!-- Drop off Time -->
          <div class="search-field">
            <label for="dropoffTime">Drop off Time</label>
            <select id="dropoffTime" required>
              <option value="">Select Time</option>
              <option value="00:00">12:00 AM</option>
              <option value="00:30">12:30 AM</option>
              <option value="01:00">1:00 AM</option>
              <option value="01:30">1:30 AM</option>
              <option value="02:00">2:00 AM</option>
              <option value="02:30">2:30 AM</option>
              <option value="03:00">3:00 AM</option>
              <option value="03:30">3:30 AM</option>
              <option value="04:00">4:00 AM</option>
              <option value="04:30">4:30 AM</option>
              <option value="05:00">5:00 AM</option>
              <option value="05:30">5:30 AM</option>
              <option value="06:00">6:00 AM</option>
              <option value="06:30">6:30 AM</option>
              <option value="07:00">7:00 AM</option>
              <option value="07:30">7:30 AM</option>
              <option value="08:00">8:00 AM</option>
              <option value="08:30">8:30 AM</option>
              <option value="09:00">9:00 AM</option>
              <option value="09:30">9:30 AM</option>
              <option value="10:00">10:00 AM</option>
              <option value="10:30">10:30 AM</option>
              <option value="11:00">11:00 AM</option>
              <option value="11:30">11:30 AM</option>
              <option value="12:00">12:00 PM</option>
              <option value="12:30">12:30 PM</option>
              <option value="13:00">1:00 PM</option>
              <option value="13:30">1:30 PM</option>
              <option value="14:00">2:00 PM</option>
              <option value="14:30">2:30 PM</option>
              <option value="15:00">3:00 PM</option>
              <option value="15:30">3:30 PM</option>
              <option value="16:00">4:00 PM</option>
              <option value="16:30">4:30 PM</option>
              <option value="17:00">5:00 PM</option>
              <option value="17:30">5:30 PM</option>
              <option value="18:00">6:00 PM</option>
              <option value="18:30">6:30 PM</option>
              <option value="19:00">7:00 PM</option>
              <option value="19:30">7:30 PM</option>
              <option value="20:00">8:00 PM</option>
              <option value="20:30">8:30 PM</option>
              <option value="21:00">9:00 PM</option>
              <option value="21:30">9:30 PM</option>
              <option value="22:00">10:00 PM</option>
              <option value="22:30">10:30 PM</option>
              <option value="23:00">11:00 PM</option>
              <option value="23:30">11:30 PM</option>
            </select>
          </div>
        </div>

        <!-- Search Button -->
        <button type="submit" class="btn-search">
          View Available Vehicles
        </button>
      </form>
    </div>
  </section>

  <!--Website Content-->
  <section class="benefits-section">
  <div class="container">
    <div class="benefits-grid">
      <div class="benefit-card">
        <div class="benefit-icon"> 
          <img src="choose_ride.png" alt="Choose card icon">
        </div>
        <h3>Choose Your Perfect Ride</h3>
        <p>Browse luxury sedans, SUVs, sports cars, and more. Reserve your ideal ride in just a few minutes.</p>
      </div>
      
      <div class="benefit-card">
        <div class="benefit-icon">
          <img src="car_rewards.png" alt="Earn rewards icon">
        </div>
        <h3>Earn While You Drive</h3>
        <p>Get 5% back on every rentals as credits. Refer friends to earn rewards. VIP members save up to 20%.</p>
      </div>
      
      <div class="benefit-card">
        <div class="benefit-icon">
          <img src="change_date.png" alt="Change date icon">
        </div>
        <h3>Change Plans Anytime</h3>
        <p>Modify your booking, swap vehicles, or extend your rental with zero change fees. Full refund on cancellations made 24 hours or more before your rental date.  </p>
      </div>
      
      <div class="benefit-card">
        <div class="benefit-icon">
          <img src="car_insurance.png" alt="Insurance icon">
        </div>
        <h3>Complete Coverage Included</h3>
        <p>Full insurance included - collision, liability, and theft covered with no deductible.</p>
      </div>
    </div>
  </div>
</section>

<!-- Phone/Road Image Section -->
<section class="help-section">
  <div class="container">
    <div class="help-content">
      <div class="help-text">
        <div class="review-text">"Traveling has never been easier."</div>
        <div class="review-info">— Jane, Minneapolis</div>
      </div>
      <div class="help-text">
        <div class="review-text">"Quick booking, great service!"</div>
        <div class="review-info">— John, Milwaukee</div>
      </div>

      <div class="help-text">
        <div class="review-text">"The smoothest rental experience."</div>
        <div class="review-info">— Jordan, Chicago</div>
      </div>
            

    </div>
  </div>
</section>

<!-- Locations Grid Section -->
<section class="locations-section">
  <div class="locations-container">
    <h2>Our Locations</h2>

    <div class="map-container">
      <div id="map"></div>
    </div>
    <div class="locations-grid">
    <div class="location-card">
    <img src="rochester_minnesota.jpg" alt="Rochester, MN">
    <h3>Rochester, MN</h3>
    <p>Downtown location near Mayo Clinic. Easy airport access and flexible pickup times.</p>
    <a href="inventory.php?location=1" class="location-link">View Fleet</a>
  </div>
  
  <div class="location-card">
    <img src="minneapolis_minnesota.jpg" alt="Minneapolis, MN">
    <h3>Minneapolis, MN</h3>
    <p>Prime location in Uptown. Minutes from MSP airport with 24/7 pickup available.</p>
    <a href="inventory.php?location=2" class="location-link">View Fleet</a>
  </div>
  
  <div class="location-card">
    <img src="chicago_illinois.jpg" alt="Chicago, IL">
    <h3>Chicago, IL</h3>
    <p>Heart of the Loop. Walk to Navy Pier, Millennium Park, and all major attractions.</p>
    <a href="inventory.php?location=3" class="location-link">View Fleet</a>
  </div>
  
  <div class="location-card">
    <img src="milwaukee_wisconsin.jpg" alt="Milwaukee, WI">
    <h3>Milwaukee, WI</h3>
    <p>Third Ward location. Perfect for brewery tours and Lake Michigan adventures.</p>
    <a href="inventory.php?location=4" class="location-link">View Fleet</a>
  </div>
</div>
</section>

  <script src="homepage.js"></script>

</body>

</html>