
<?php
session_start();
require_once 'db.php';

// Make sure the student is signed in.
if (!isset($_SESSION['student_id'])) {
    header('Location: HailMaryAccessPage.php');
    exit;
}

$studentId = (int) $_SESSION['student_id'];

// Create a CSRF token for account actions.
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

function e($value) {
    return htmlspecialchars((string)($value ?? ''), ENT_QUOTES, 'UTF-8');
}

try {
    // Get the student's account information.
    $stmt = $pdo->prepare("
        SELECT s.Student_ID, s.Name, s.School_Email,
               s.Phone_Number, sc.School_Name
        FROM Students s
        LEFT JOIN Schools sc ON s.School_ID = sc.School_ID
        WHERE s.Student_ID = ?
        LIMIT 1
    ");
    $stmt->execute([$studentId]);
    $student = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$student) {
        session_destroy();
        header('Location: HailMaryAccessPage.php');
        exit;
    }

    // Get only listings uploaded by this student.
    $stmt = $pdo->prepare("
        SELECT l.Listing_ID, l.Title, l.Description,
               l.Price, l.Condition, l.Status, l.Created_Date,
               c.Description AS Category,
               (
                   SELECT li.Image_URL
                   FROM Listing_Images li
                   WHERE li.Listing_ID = l.Listing_ID
                   ORDER BY li.Sort_Order ASC
                   LIMIT 1
               ) AS Main_Image
        FROM Listings l
        LEFT JOIN Categories c ON l.Category_ID = c.Category_ID
        WHERE l.Seller_ID = ?
        ORDER BY l.Created_Date DESC
    ");
    $stmt->execute([$studentId]);
    $listings = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    error_log($e->getMessage());
    exit("Unable to load your account right now.");
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Hail Mary | My Account</title>

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
        <a class="navbar-brand" href="HailMaryhomepage.php">Hail Mary</a>

        <button class="navbar-toggler" type="button"
                data-bs-toggle="collapse" data-bs-target="#navMenu">
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
                    <a class="nav-link" href="HailMaryCart.php">My Cart</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link active" href="HailMaryAccount.php">My Account</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="HailMaryLogout.php">Sign Out</a>
                </li>
            </ul>
        </div>
    </div>
</nav>

<main class="container my-5">

    <?php if (isset($_GET['updated'])): ?>
        <div class="alert alert-success">
            Your account information was updated.
        </div>
    <?php endif; ?>

    <?php if (isset($_GET['deleted'])): ?>
        <div class="alert alert-success">
            Your listing was deleted.
        </div>
    <?php endif; ?>

    <?php if (isset($_GET['error'])): ?>
        <div class="alert alert-danger">
            Something went wrong. Please try again.
        </div>
    <?php endif; ?>

    <h2 class="mb-4">My Account</h2>

    <!-- Account information -->
    <section class="card shadow p-4 mb-5">
        <h3 class="mb-3">Account Information</h3>

        <p><strong>Name:</strong> <?= e($student['Name']) ?></p>
        <p><strong>School:</strong> <?= e($student['School_Name']) ?></p>
        <p><strong>School Email:</strong> <?= e($student['School_Email']) ?></p>
        <p><strong>Phone Number:</strong> <?= e($student['Phone_Number']) ?></p>

        <button class="btn btn-dark"
                type="button"
                data-bs-toggle="collapse"
                data-bs-target="#editAccount">
            Edit Account Information
        </button>

        <div class="collapse mt-4" id="editAccount">
            <form action="HailMaryUpdateAccount.php" method="POST">

                <input type="hidden" name="csrf_token"
                       value="<?= e($_SESSION['csrf_token']) ?>">

                <div class="mb-3">
                    <label class="form-label" for="name">Name</label>
                    <input type="text" class="form-control"
                           id="name" name="name"
                           value="<?= e($student['Name']) ?>" required>
                </div>

                <div class="mb-3">
                    <label class="form-label" for="phone">Phone Number</label>
                    <input type="tel" class="form-control"
                           id="phone" name="phone"
                           value="<?= e($student['Phone_Number']) ?>" required>
                </div>

                <hr>

                <h5>Change Password</h5>
                <p class="text-muted">
                    Leave these fields blank if you do not want to change your password.
                </p>

                <div class="mb-3">
                    <label class="form-label" for="current_password">
                        Current Password
                    </label>
                    <input type="password" class="form-control"
                           id="current_password" name="current_password">
                </div>

                <div class="mb-3">
                    <label class="form-label" for="new_password">
                        New Password
                    </label>
                    <input type="password" class="form-control"
                           id="new_password" name="new_password"
                           minlength="8">
                </div>

                <div class="mb-3">
                    <label class="form-label" for="confirm_password">
                        Confirm New Password
                    </label>
                    <input type="password" class="form-control"
                           id="confirm_password" name="confirm_password"
                           minlength="8">
                </div>

                <button type="submit" class="btn btn-primary">
                    Save Changes
                </button>
            </form>
        </div>
    </section>

    <!-- Student's listings -->
    <section>
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h3>My Listings</h3>
            <a href="HailMaryUploadListing.php" class="btn btn-primary">
                Upload New Listing
            </a>
        </div>

        <?php if (empty($listings)): ?>
            <div class="alert alert-info">
                You haven't uploaded any listings yet.
            </div>
        <?php else: ?>

            <div class="row g-4">

                <?php foreach ($listings as $listing): ?>
                    <div class="col-md-6 col-lg-4">
                        <div class="card shadow h-100">

                            <?php if (!empty($listing['Main_Image'])): ?>
                                <img src="<?= e($listing['Main_Image']) ?>"
                                     class="card-img-top"
                                     alt="<?= e($listing['Title']) ?>"
                                     style="height:220px; object-fit:cover;">
                            <?php else: ?>
                                <div class="bg-light d-flex align-items-center justify-content-center"
                                     style="height:220px;">
                                    No Image Available
                                </div>
                            <?php endif; ?>

                            <div class="card-body">
                                <h5 class="card-title">
                                    <?= e($listing['Title']) ?>
                                </h5>

                                <p class="card-text">
                                    <?= e($listing['Description']) ?>
                                </p>

                                <p><strong>Price:</strong>
                                    $<?= number_format((float)$listing['Price'], 2) ?>
                                </p>

                                <p><strong>Category:</strong>
                                    <?= e($listing['Category']) ?>
                                </p>

                                <p><strong>Condition:</strong>
                                    <?= e($listing['Condition']) ?>
                                </p>

                                <p><strong>Status:</strong>
                                    <?= e($listing['Status']) ?>
                                </p>

                                <form action="HailMaryDeleteListing.php"
                                      method="POST"
                                      onsubmit="return confirm('Are you sure you want to delete this listing?');">

                                    <input type="hidden" name="listing_id"
                                           value="<?= (int)$listing['Listing_ID'] ?>">

                                    <input type="hidden" name="csrf_token"
                                           value="<?= e($_SESSION['csrf_token']) ?>">

                                    <button type="submit"
                                            class="btn btn-danger w-100">
                                        Delete Listing
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>

            </div>
        <?php endif; ?>
    </section>

</main>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>
