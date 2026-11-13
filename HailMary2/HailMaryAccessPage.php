<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Hail Mary | Student Login</title>

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
                    data-bs-toggle="collapse"
                    data-bs-target="#navMenu">
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
                        <a class="nav-link" href="HailMarysignup.php">Sign-Up</a>
                    </li>

                    <li class="nav-item">
                        <a class="nav-link" href="HailMaryReport.php">Report</a>
                    </li>

                    <li class="nav-item">
                        <a class="nav-link active" href="HailMaryAccessPage.php">
                            Sign In
                        </a>
                    </li>

                </ul>
            </div>
        </div>
    </nav>

    <main class="container my-5">

        <section class="card shadow p-4 mx-auto"
                 style="max-width: 500px;">

            <h2 class="text-center mb-3">Student Login</h2>

            <p class="text-center">
                Sign in to manage your Hail Mary account and listings.
            </p>

            <?php if (isset($_GET['error'])): ?>
                <div class="alert alert-danger">
                    Invalid email or password. Please try again.
                </div>
            <?php endif; ?>

            <?php if (isset($_GET['registered'])): ?>
                <div class="alert alert-success">
                    Your account was created! You can now sign in.
                </div>
            <?php endif; ?>

            <form action="HailMaryLogin.php" method="POST">

                <div class="mb-3">
                    <label for="school_email" class="form-label">
                        School Email
                    </label>

                    <input type="email"
                           class="form-control"
                           id="school_email"
                           name="school_email"
                           placeholder="Enter your school email"
                           required>
                </div>

                <div class="mb-3">
                    <label for="password" class="form-label">
                        Password
                    </label>

                    <input type="password"
                           class="form-control"
                           id="password"
                           name="password"
                           placeholder="Enter your password"
                           required>
                </div>

                <button type="submit" class="btn btn-dark w-100">
                    Sign In
                </button>

            </form>

            <hr>

            <p class="text-center mb-2">
                Don't have an account?
            </p>

            <a href="HailMarysignup.php"
               class="btn btn-primary w-100">
                Create an Account
            </a>

        </section>

    </main>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>
