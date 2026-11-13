
<?php
require_once 'db.php';

// Get active marketplace listings and their main images.
$sql = "
    SELECT
        l.Listing_ID,
        l.Title,
        l.Description,
        l.Price,
        l.Condition,
        l.Created_Date,
        c.Description AS Category_Name,
        s.Name AS Seller_Name,
        sch.School_Name,
        (
            SELECT li.Image_URL
            FROM Listing_Images li
            WHERE li.Listing_ID = l.Listing_ID
            ORDER BY li.Sort_Order ASC, li.Listing_Image_ID ASC
            LIMIT 1
        ) AS Main_Image
    FROM Listings l
    LEFT JOIN Categories c
        ON l.Category_ID = c.Category_ID
    LEFT JOIN Students s
        ON l.Seller_ID = s.Student_ID
    LEFT JOIN Schools sch
        ON s.School_ID = sch.School_ID
    WHERE l.Status = 'Active'
    ORDER BY l.Created_Date DESC
";

$stmt = $pdo->prepare($sql);
$stmt->execute();
$listings = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Hail Mary | Listings</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css"
          rel="stylesheet">

    <link rel="stylesheet" href="HailMarystylesheet.css">
</head>

<body>

    <!-- Shared Banner -->
    <header class="site-banner">
        <h1>Hail Mary</h1>
        <p>Buy, Sell, and Trade with College Students</p>
    </header>

    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-dark custom-navbar">
        <div class="container-fluid">

            <a class="navbar-brand" href="HailMaryhomepage.php">
                Hail Mary
            </a>

            <button class="navbar-toggler" type="button"
                    data-bs-toggle="collapse"
                    data-bs-target="#navMenu"
                    aria-controls="navMenu"
                    aria-expanded="false"
                    aria-label="Toggle navigation">

                <span class="navbar-toggler-icon"></span>
            </button>

            <div class="collapse navbar-collapse" id="navMenu">

                <ul class="navbar-nav ms-auto">

                    <li class="nav-item">
                        <a class="nav-link" href="HailMaryhomepage.php">
                            Home
                        </a>
                    </li>

                    <li class="nav-item">
                        <a class="nav-link active" href="HailMaryListings.php">
                            Listings
                        </a>
                    </li>

                    <li class="nav-item">
                        <a class="nav-link" href="HailMaryMessages.php">
                            Messages
                        </a>
                    </li>

                    <li class="nav-item">
                        <a class="nav-link" href="HailMarysignup.php">
                            Sign-Up
                        </a>
                    </li>

                    <li class="nav-item">
                        <a class="nav-link" href="HailMaryReport.php">
                            Report
                        </a>
                    </li>

                  
                    </li>

                
                        </a>
                    </li>

                </ul>
            </div>
        </div>
    </nav>

    <!-- Marketplace Listings -->
    <main class="container my-5">

        <div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-3">

            <div>
                <h2>Campus Marketplace</h2>
                <p class="text-muted mb-0">
                    Browse items posted by college students.
                </p>
            </div>

            <div class="d-flex gap-2">

                <a href="HailMaryUploadListing.php"
                   class="btn btn-primary">
                    Upload Listing
                </a>

                <a href="HailMaryCart.php"
                   class="btn btn-dark">
                    My Cart
                </a>

            </div>

        </div>

        <!-- Success Messages -->

        <?php if (isset($_GET['posted'])): ?>
            <div class="alert alert-success">
                Your listing was successfully uploaded!
            </div>
        <?php endif; ?>

        <?php if (isset($_GET['added'])): ?>
            <div class="alert alert-success">
                Listing successfully added to your cart!
            </div>
        <?php endif; ?>

        <?php if (isset($_GET['duplicate'])): ?>
            <div class="alert alert-warning">
                This listing is already in your cart!
            </div>
        <?php endif; ?>

        <!-- Listing Cards -->

        <?php if (count($listings) > 0): ?>

            <div class="row g-4">

                <?php foreach ($listings as $listing): ?>

                    <div class="col-md-6 col-lg-4">

                        <div class="card h-100 shadow-sm">

                            <?php if (!empty($listing['Main_Image'])): ?>

                                <img
                                    src="<?php echo htmlspecialchars($listing['Main_Image']); ?>"
                                    class="card-img-top listing-image"
                                    alt="<?php echo htmlspecialchars($listing['Title']); ?>"
                                >

                            <?php else: ?>

                                <div class="listing-image-placeholder">
                                    No Image Available
                                </div>

                            <?php endif; ?>

                            <div class="card-body d-flex flex-column">

                                <h5 class="card-title">
                                    <?php echo htmlspecialchars($listing['Title']); ?>
                                </h5>

                                <p class="card-text">
                                    <?php echo nl2br(htmlspecialchars($listing['Description'])); ?>
                                </p>

                                <h4 class="text-success">
                                    $<?php echo number_format((float)$listing['Price'], 2); ?>
                                </h4>

                                <p class="mb-1">
                                    <strong>Category:</strong>
                                    <?php echo htmlspecialchars($listing['Category_Name'] ?? 'Uncategorized'); ?>
                                </p>

                                <p class="mb-1">
                                    <strong>Condition:</strong>
                                    <?php echo htmlspecialchars($listing['Condition'] ?? 'Not specified'); ?>
                                </p>

                                <p class="mb-1">
                                    <strong>Seller:</strong>
                                    <?php echo htmlspecialchars($listing['Seller_Name'] ?? 'Unknown'); ?>
                                </p>

                                <p class="mb-3">
                                    <strong>School:</strong>
                                    <?php echo htmlspecialchars($listing['School_Name'] ?? 'Unknown'); ?>
                                </p>

                                <div class="mt-auto">

                                    <form action="add_to_cart.php" method="POST">

                                        <input type="hidden"
                                               name="listing_id"
                                               value="<?php echo (int)$listing['Listing_ID']; ?>">

                                        <button type="submit"
                                                class="btn btn-dark w-100">
                                            Add to Cart
                                        </button>

                                    </form>

                                </div>

                            </div>

                        </div>

                    </div>

                <?php endforeach; ?>

            </div>

        <?php else: ?>

            <div class="alert alert-info text-center">
                No active listings are available right now.
            </div>

        <?php endif; ?>

    </main>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>
