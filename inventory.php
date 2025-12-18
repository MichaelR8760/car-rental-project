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

<body>
    <!--Navigation Bar-->
    <?php include 'navbar.php'; ?>

    <!--Display Inventory -->
    <div class="container mt-4">
        <div class="row">
            <div class="col-12">
                <h2 id="locationHeader"></h2>
            </div>
            <!-- Vehicle list container -->
            <div id="vehicleList" class="row"></div>

            <!-- Error Message -->
            <div id="errorContainer" class="text-center" style="display: none;"></div>
        </div>
    </div>


    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.5/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-k6d4wzSIapyDyv1kpU366/PK5hCdSbCRGRCMv+eplOQJWyd1fbcAu9OCUj5zNLiq" crossorigin="anonymous">
        </script>

    <script>
        const userRole = '<?php echo $userRole; ?>';
    </script>

    <script src="inventory.js"></script>

    <div class="filter-tab" id="filterTab" onclick="toggleFilter()">
        <span>FILTERS</span>
    </div>

    <div class="filter-panel" id="filterPanel">
        <div class="filter-header">
            <h5>Filter Vehicles</h5>
            <button class="btn-close" onclick="closeFilter()">&times;</button>
        </div>
        <div class="filter-content">

            <!-- Location Filter -->
            <div class="mb-3">
                <label for="location" class="form-label">Location</label>
                <select class="form-select" id="location">
                    <option value="">All Locations</option>
                    <option value="1">Rochester Location</option>
                    <option value="2">Minneapolis Location</option>
                    <option value="3">Chicago Location</option>
                    <option value="4">Milwaukee Location</option>
                </select>
            </div>

            <!-- Fuel Type Filter -->
            <div class="mb-3">
                <label for="fuelType" class="form-label">Fuel Type</label>
                <select class="form-select" id="fuelType">
                    <option value="">All Fuel Types</option>
                    <option value="gasoline">Gasoline</option>
                    <option value="diesel">Diesel</option>
                    <option value="hybrid">Hybrid</option>
                    <option value="electric">Electric</option>
                </select>
            </div>

            <!-- Vehicle Type Filter -->
            <div class="mb-3">
                <label for="vehicleType" class="form-label">Vehicle Type</label>
                <select class="form-select" id="vehicleType">
                    <option value="">All Vehicle Types</option>
                    <option value="economy">Economy Cars</option>
                    <option value="compact">Compact Cars</option>
                    <option value="luxury">Luxury Vehicles</option>
                    <option value="sports">Sports Cars</option>
                    <option value="suvs">SUVs</option>
                    <option value="trucks">Trucks</option>
                    <option value="movingTrucks">Moving Trucks</option>
                    <option value="vans">Vans</option>
                    <option value="electric">Electric</option>
                    <option value="hybrid">Hybrid</option>
                </select>
            </div>

            <!-- Vehicle Status Filter (Admin Only) -->
            <?php if ($userRole === 'admin'): ?>
            <div class="mb-3">
                <label for="status" class="form-label">Vehicle Status</label>
                <select class="form-select" id="status">
                    <option value="">All Status</option>
                    <option value="available">Available</option>
                    <option value="pending_maintenance">Pending Maintenance</option>
                    <option value="under_maintenance">Under Maintenance</option>
                    <option value="rented">Rented</option>
                </select>
            </div>
            <?php endif; ?>

            <!-- Seats Filter -->
            <div class="mb-3">
                <label for="seats" class="form-label">Number of Seats</label>
                <select class="form-select" id="seats">
                    <option value="">Any</option>
                    <option value="2">2 Seats</option>
                    <option value="4">4 Seats</option>
                    <option value="5">5 Seats</option>
                    <option value="6">6 Seats</option>
                    <option value="7">7 Seats</option>
                    <option value="8">8+ Seats</option>
                </select>
            </div>

            <!-- Price Range Filter-->
            <div class="mb-3">
                <label for="maxPrice" class="form-label">Maximum Daily Rate</label>
                <input type="range" class="form-range" id="maxPrice" min="20" max="500" value="500">
                <div id="priceDisplay">Up to $500 per day</div>
            </div>
        </div>

        <div class="filter-actions">
            <button class="btn btn-clear-filter" onclick="clearFilters()">Clear All</button>
            <button class="btn btn-success" onclick="applyFilters()">Apply Filters</button>
        </div>
    </div>

</body>
</html>