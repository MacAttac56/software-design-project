<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Hail Mary | Sign-Up</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css"
          rel="stylesheet">
    <link rel="stylesheet" href="HailMarystylesheet.css">
</head>

<body>

    <header class="site-banner">
        <h1>Hail Mary</h1>
        <p>Buy, Sell, and Trade with College Students</p>
    </header>

    <nav class="navbar navbar-expand-lg navbar-dark custom-navbar">
        <div class="container-fluid">

            <a class="navbar-brand" href="HailMaryhomepage.php">
                Hail Mary
            </a>

            <button class="navbar-toggler" type="button"
                    data-bs-toggle="collapse" data-bs-target="#navMenu"
                    aria-controls="navMenu" aria-expanded="false"
                    aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>

            <div class="collapse navbar-collapse" id="navMenu">
                <ul class="navbar-nav ms-auto">

                    <li class="nav-item">
                        <a class="nav-link" href="HailMaryhomepage.php">Home</a>
                    </li>

                    <li class="nav-item">
                        <a class="nav-link" href="HailMaryListings.php">Listings</a>
                    </li>

                    <li class="nav-item">
                        <a class="nav-link" href="HailMaryMessages.php">Messages</a>
                    </li>

                    <li class="nav-item">
                        <a class="nav-link active" href="HailMarysignup.php">Sign-Up</a>
                    </li>

                    <li class="nav-item">
                        <a class="nav-link" href="HailMaryReport.php">Report</a>
                    </li>

                    

                </ul>
            </div>
        </div>
    </nav>

    <main class="signup-page">

        <section class="signup-card">

            <h2>Create Your Hail Mary Account</h2>

            <p class="signup-intro">
                Join your campus marketplace to buy, sell, and trade
                with other college students.
            </p>

            <?php if (isset($_GET['error'])): ?>
                <div class="alert alert-danger">
                    <?php
                    if ($_GET['error'] === 'email') {
                        echo "That school email is already registered.";
                    } elseif ($_GET['error'] === 'password') {
                        echo "Your passwords did not match.";
                    } else {
                        echo "Registration failed. Please check your information.";
                    }
                    ?>
                </div>
            <?php endif; ?>

            <form action="insert_student.php" method="POST">

                <div class="mb-3">
                    <label for="student_name" class="form-label">
                        Student Name
                    </label>

                    <input class="form-control"
                           id="student_name"
                           name="student_name"
                           type="text"
                           maxlength="100"
                           autocomplete="name"
                           required>
                </div>

                <div class="mb-3">
                    <label for="school_id" class="form-label">
                        College or University
                    </label>

                    <select class="form-select"
                            id="school_id"
                            name="school_id"
                            required>

                        <option value="">Select your school</option>
                        <option value="1">Virginia State University</option>
                        <option value="2">Virginia Commonwealth University</option>
                        <option value="3">Virginia Tech</option>
                        <option value="4">Old Dominion University</option>
                        <option value="5">George Mason University</option>

                    </select>
                </div>

                <div class="mb-3">
                    <label for="school_email" class="form-label">
                        School Email
                    </label>

                    <input class="form-control"
                           id="school_email"
                           name="school_email"
                           type="email"
                           maxlength="150"
                           autocomplete="email"
                           required>

                    <div class="form-text">
                        Use your college email address.
                    </div>
                </div>

                <div class="mb-3">
                    <label for="phone_number" class="form-label">
                        Phone Number
                    </label>

                    <input class="form-control"
                           id="phone_number"
                           name="phone_number"
                           type="tel"
                           maxlength="20"
                           pattern="[0-9()+. -]{7,20}"
                           title="Enter a valid phone number"
                           autocomplete="tel"
                           required>
                </div>

                <div class="mb-3">
                    <label for="password" class="form-label">
                        Password
                    </label>

                    <input class="form-control"
                           id="password"
                           name="password"
                           type="password"
                           minlength="8"
                           autocomplete="new-password"
                           required>

                    <div class="form-text">
                        Password must contain at least 8 characters.
                    </div>
                </div>

                <div class="mb-4">
                    <label for="confirm_password" class="form-label">
                        Confirm Password
                    </label>

                    <input class="form-control"
                           id="confirm_password"
                           name="confirm_password"
                           type="password"
                           minlength="8"
                           autocomplete="new-password"
                           required>
                </div>

                <button type="submit" class="signup-button">
                    Create Account
                </button>

            </form>

            <hr>

            <p class="text-center">
                Already have an account?
            </p>

            <a href="HailMaryAccessPage.php"
               class="btn btn-dark w-100">
                Sign In
            </a>

            <p class="signup-note">
                Your information will be submitted for registration.
            </p>

        </section>

    </main>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>

