<?php
require_once "db.php";

$students = $pdo->query(
    "SELECT Student_ID, Name FROM Students ORDER BY Name"
)->fetchAll();

$categories = $pdo->query(
    "SELECT Category_ID, Description FROM Categories ORDER BY Description"
)->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hail Mary | Upload Listing</title>
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
                data-bs-toggle="collapse" data-bs-target="#navMenu"
                aria-controls="navMenu" aria-expanded="false"
                aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navMenu">
            <ul class="navbar-nav ms-auto">
                <li class="nav-item"><a class="nav-link" href="HailMaryhomepage.php">Home</a></li>
                <li class="nav-item"><a class="nav-link active" href="HailMaryListings.php">Listings</a></li>
                <li class="nav-item"><a class="nav-link" href="HailMaryMessages.php">Messages</a></li>
                <li class="nav-item"><a class="nav-link" href="HailMarysignup.php">Sign-Up</a></li>
                <li class="nav-item"><a class="nav-link" href="HailMaryReport.php">Report</a></li>
            </ul>
        </div>
    </div>
</nav>

<main class="container py-5">
    <section class="signup-card mx-auto">
        <h2>Post an Item</h2>
        <p class="signup-intro">
            Add your item to the campus marketplace.
        </p>

        <div class="alert alert-warning">
            <strong>Testing mode:</strong> Select the student who is posting.
            This dropdown will be replaced by login when accounts are implemented.
        </div>

       <form action="HailMaryCreateListing.php" method="POST"
      enctype="multipart/form-data">

            <div class="mb-3">
                <label for="seller_id" class="form-label">Test Seller</label>
                <select class="form-select" id="seller_id" name="seller_id" required>
                    <option value="">Select a student</option>
                    <?php foreach ($students as $student): ?>
                        <option value="<?= (int)$student['Student_ID'] ?>">
                            <?= htmlspecialchars($student['Name']) ?>
                            (ID <?= (int)$student['Student_ID'] ?>)
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="mb-3">
                <label for="title" class="form-label">Item Title</label>
                <input class="form-control" type="text" id="title"
                       name="title" maxlength="150" required>
            </div>

            <div class="mb-3">
                <label for="description" class="form-label">Description</label>
                <textarea class="form-control" id="description"
                          name="description" rows="4" required></textarea>
            </div>

            <div class="mb-3">
                <label for="price" class="form-label">Price ($)</label>
                <input class="form-control" type="number" id="price"
                       name="price" min="0.01" max="99999999.99"
                       step="0.01" required>
            </div>

            <div class="mb-3">
                <label for="category_id" class="form-label">Category</label>
                <select class="form-select" id="category_id"
                        name="category_id" required>
                    <option value="">Select a category</option>
                    <?php foreach ($categories as $category): ?>
                        <option value="<?= (int)$category['Category_ID'] ?>">
                            <?= htmlspecialchars($category['Description']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="mb-3">
                <label for="condition" class="form-label">Condition</label>
                <select class="form-select" id="condition" name="condition" required>
                    <option value="New">New</option>
                    <option value="Like New">Like New</option>
                    <option value="Good">Good</option>
                    <option value="Fair">Fair</option>
                </select>
            </div>

            <div class="mb-4">
                <label for="images" class="form-label">Item Pictures</label>
                <input class="form-control" type="file" id="images"
                       name="images[]" accept=".jpg,.jpeg,.png,.webp"
                       multiple required>
                <div class="form-text">
                    Choose up to 5 JPG, PNG, or WebP images. Maximum 5 MB each.
                </div>
            </div>

            <button class="signup-button" type="submit">Post Listing</button>
        </form>
    </section>
</main>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

