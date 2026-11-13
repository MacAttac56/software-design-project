<?php
require_once "db.php";

$sql = "SELECT
            l.Listing_ID,
            l.Title,
            l.Description,
            l.Price,
            l.`Condition`,
            c.Description AS Category,
            s.Name AS Seller,
            sc.School_Name
        FROM Listings l
        JOIN Students s ON l.Seller_ID = s.Student_ID
        JOIN Schools sc ON s.School_ID = sc.School_ID
        JOIN Categories c ON l.Category_ID = c.Category_ID
        WHERE l.Status = 'Active'
        ORDER BY l.Created_Date DESC";

$listings = $pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Welcome to Hail Mary</title>

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
                <li class="nav-item">
                    <a class="nav-link active" href="HailMaryhomepage.php">Home</a>
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
                
                </li>
            </ul>
        </div>
    </div>
</nav>

<main class="container py-4">
    <h2>Welcome to Hail Mary</h2>
    <p>Your campus marketplace starts here.</p>

    <div class="d-flex flex-wrap justify-content-between align-items-center mt-4 mb-3">
        <h3>Recent Listings</h3>
        <a href="HailMaryListings.php" class="btn btn-primary">
            Browse Marketplace
        </a>
    </div>

    <div class="row">
        <?php if (empty($listings)): ?>
            <p>No active listings are available right now.</p>
        <?php else: ?>
            <?php foreach ($listings as $listing): ?>
                <div class="col-md-4 mb-4">
                    <div class="card h-100">
                        <div class="card-body">
                            <h5 class="card-title">
                                <?= htmlspecialchars($listing["Title"]) ?>
                            </h5>

                            <p class="card-text">
                                <?= htmlspecialchars($listing["Description"] ?? "") ?>
                            </p>

                            <p>
                                <strong>Price:</strong>
                                $<?= number_format((float)$listing["Price"], 2) ?>
                            </p>

                            <p>
                                <strong>Category:</strong>
                                <?= htmlspecialchars($listing["Category"]) ?>
                            </p>

                            <p>
                                <strong>Condition:</strong>
                                <?= htmlspecialchars($listing["Condition"] ?? "Not specified") ?>
                            </p>

                            <p>
                                <strong>Seller:</strong>
                                <?= htmlspecialchars($listing["Seller"]) ?>
                            </p>

                            <p class="text-muted">
                                <?= htmlspecialchars($listing["School_Name"]) ?>
                            </p>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</main>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
