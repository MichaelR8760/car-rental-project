<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width" , initial-scale="1.0">
    <title>Add New Vehicle - RevLink Rentals</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.5/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-SgOJa3DmI69IUzQ2PVdRZhwQ+dy64/BUtbMJw1MZ8t5HZApcHrRKUc4W0kG879m7" crossorigin="anonymous">
</head>

<body>

    <!--Navigation Bar-->
    <?php include 'navbar.php'; ?>

    <!-- Add Vehicle Form -->
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card p-4">
                    <h4 class="text-center mb-4"> Add New Vehicle</h4>
                    <div id="alertPlaceholder"></div>

                    <form id="vehicleForm" action="add_vehicle.php" method="POST" enctype="multipart/form-data">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="carVin" class="form-label">VIN *</label>
                                <input type="text" class="form-control" id="carVin" name="car_vin" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="licensePlate" class="form-label">License Plate *</label>
                                <input type="text" class="form-control" id="licensePlate" name="license_plate" required>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label for="make" class="form-label">Make *</label>
                                <input type="text" class="form-control" id="make" name="make"
                                    placeholder="Honda, Toyota, Ford, etc" required>
                            </div>

                            <div class="col-md-4 mb-3">
                                <label for="model" class="form-label">Model *</label>
                                <input type="text" class="form-control" id="model" name="model"
                                    placeholder="Civic, Camry, Focus, etc" required>
                            </div>

                            <div class="col-md-4 mb-3">
                                <label for="year" class="form-label">Year* </label>
                                <input type="number" class="form-control" id="year" name="year" min="1990" max="2025"
                                    required>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label for="color" class="form-label">Color</label>
                                <input type="text" class="form-control" id="color" name="color"
                                    placeholder="Grey, White, Blue, etc">
                            </div>

                            <div class="col-md-4 mb-3">
                                <label for="type" class="form-label">Type</label>
                                <select class="form-control" id="type" name="type">
                                    <option value="">Select Type</option>"
                                    <option value="economy">Economy</option>
                                    <option value="compact">Compact</option>
                                    <option value="sports">Sports</option>
                                    <option value="luxury">Luxury</option>
                                    <option value="suvs">SUVs</option>
                                    <option value="trucks">Trucks</option>
                                    <option value="movingTrucks">Moving Trucks</option>
                                    <option value="vans">Vans</option>
                                    <option value="hybrid">Hybrid</option>
                                    <option value="electric">Electric</option>
                                </select>
                                </select>
                            </div>

                            <div class="col-md-4 mb-3">
                                <label for="seats" class="form-label">Seats *</label>
                                <input type="number" class="form-control" id="seats" name="seats" min="2" max="15"
                                    required>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label for="fuelType" class="form-label">Fuel Type *</label>
                                <select class="form-control" id="fuelType" name="fuel_type" required>
                                    <option value="">Select Fuel Type</option>
                                    <option value="gasoline">Gasoline</option>
                                    <option value="diesel">Diesel</option>
                                    <option value="hybrid">Hybrid</option>
                                    <option value="electric">Electric</option>
                                </select>
                            </div>

                            <div class="col-md-4 mb-3">
                                <label for="mpg" class="form-label">MPG</label>
                                <input type="number" class="form-control" id="mpg" name="mpg" min="1" max="200">
                            </div>

                            <div class="col-md-4 mb-3">
                                <label for="dailyRate" class="form-label">Daily Rate *</label>
                                <input type="number" class="form-control" id="dailyRate" name="daily_rate" step="0.01"
                                    min="0" required>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="locationId" class="form-label">Location</label>
                                <select class="form-control" id="locationId" name="location_ID">
                                    <option value="">Select Location</option>
                                    <option value="1">Rochester, MN</option>
                                    <option value="2">Minneapolis, MN</option>
                                    <option value="3">Chicago, IL</option>
                                    <option value="4">Milwaukee, WI</option>
                                </select>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="carImage" class="form-label">Vehicle Image</label>
                                <input type="file" class="form-control" id="carImage" name="car_image" accept="image/*">
                                <div class="form-text">Upload a photo of the vehicle.</div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="status" class="form-label">Status</label>
                                <select class="form-control" id="status" name="status">
                                    <option value="available">Available</option>
                                    <option value="pending_maintenance">Pending Maintenance</option>
                                    <option value="under_maintenance">Under Maintenance</option>
                                    <option value="rented">Rented</option>
                                </select>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="lastMaintenance" class="form-label">Last Maintenance Date</label>
                                <input type="date" class="form-control" id="lastMaintenance"
                                    name="last_maintenance_date">
                            </div>
                        </div>

                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-primary">Add Vehicle</button>
                            <a href="#" class="btn btn-outline-secondary">Cancel</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <script src="add_vehicle.js"></script>
</body>

</html>