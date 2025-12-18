<!--
    User Registration Form
    Allows new customers to create an account with personal information.
    Validates input and submits to registration endpoint via register.js
-->

<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>RevLink Rentals - Home</title>
    <link 
      href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.5/dist/css/bootstrap.min.css" 
      rel="stylesheet" 
      integrity="sha384-SgOJa3DmI69IUzQ2PVdRZhwQ+dy64/BUtbMJw1MZ8t5HZApcHrRKUc4W0kG879m7" 
      crossorigin="anonymous"
    >
    <script 
      defer 
      src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.5/dist/js/bootstrap.bundle.min.js" 
      integrity="sha384-k6d4wzSIapyDyv1kpU366/PK5hCdSbCRGRCMv+eplOQJWyd1fbcAu9OCUj5zNLiq" 
      crossorigin="anonymous"
    ></script>
    <link href="main.css" rel="stylesheet">
  </head>
  <body>
  <!-- Navigation Bar -->
    <?php include 'navbar.php'; ?>

  <div class="container mt-5">
    <div class="row justify-content-center">
      <div class="col-md-6">
       <div class="card p-4">
        <h4 class="text-center mb-3">Create your Account</h4>
        
        <!-- Alert messages display here -->
        <div id="alertPlaceholder" class="mt-3"></div>
        
        <!-- Registration Form -->
        <form id="registrationForm">
          <!-- Name Fields -->
          <div class="row">
            <div class="col-md-6 mb-3">
              <label for="firstName" class="form-label">First Name</label>
              <input type="text" class="form-control" id="firstName" name="first_name" placeholder="Enter first name" required>
            </div>
            <div class="col-md-6 mb-3">
              <label for="lastName" class="form-label">Last Name</label>
              <input type="text" class="form-control" id="lastName" name="last_name" placeholder="Enter last name" required>
            </div>
          </div>
          
          <!-- Username -->
          <div class="mb-3">
            <label for="username" class="form-label">Username</label>
            <input type="username" class="form-control" id="username" placeholder="Enter a username" name="username" required>
          </div>
          
          <!-- Email -->
          <div class="mb-3">
            <label for="email" class="form-label">Email</label>
            <input type="email" class="form-control" id="email" placeholder="Enter your email" name="email" required>
          </div>
          
          <!-- Phone Number -->
          <div class="mb-3">
            <label for="phoneNumber" class="form-label">Phone Number</label>
            <input type="tel" class="form-control" id="phoneNumber" name="phone_number" placeholder="Enter your phone number" required>
          </div>
          
          <!-- Password with validation pattern -->
          <div class="mb-3">
            <label for="password" class="form-label">Password</label>
            <input type="password" class="form-control" id="password" placeholder="Enter your password" name="password" pattern="(?=.*\d)(?=.*[a-z])(?=.*[A-Z]).{8,}" title="Must contain at least one number and one uppercase and lowercase letter, and at least 8 or more characters" required>
          </div>

          <!-- Submit and Login Link -->
          <div class="d-grid gap-2">
            <button type="submit" class="btn btn-register">Register</button>
            <a href="register_form.php" class="btn btn-outline-secondary">Already have an account? Login</a>
          </div>
        </form>
        </div>
      </div>
    </div>
  </div>
  <script src="register.js"></script>
  </body>
</html>