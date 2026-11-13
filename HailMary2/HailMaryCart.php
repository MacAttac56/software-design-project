
<?php
require_once "db.php";

// Assignment demo: Student_ID 1
// TODO: Replace with the logged-in student's session ID.
$studentId = 1;

// Retrieve shopping cart items
$sql = "SELECT
            c.Cart_ID,
            l.Listing_ID,
            l.Title,
            l.Price,
            c.Added_At
        FROM Cart c
        INNER JOIN Listings l
            ON c.Listing_ID = l.Listing_ID
        WHERE c.Student_ID = ?
        ORDER BY c.Added_At DESC, c.Cart_ID DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute([$studentId]);

$cartItems = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Calculate cart total
$total = 0;

foreach ($cartItems as $item) {
    $total += (float)$item['Price'];
}

// Check for successful removal
$itemRemoved = isset($_GET['removed'])
    && $_GET['removed'] === '1';

// Check for successful addition
$itemAdded = isset($_GET['added'])
    && $_GET['added'] === '1';
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">

    <meta name="viewport"
          content="width=device-width, initial-scale=1.0">

    <title>Hail Mary | My Shopping Cart</title>

    <!-- Bootstrap CSS -->
    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css"
        rel="stylesheet"
    >

    <!-- Custom Styles -->
    <link rel="stylesheet"
          href="HailMarystylesheet.css">

    <style>
        .cart-container {
            max-width: 1100px;
            margin: auto;
        }

        .order-summary {
            border-radius: 12px;
            border: 1px solid #dee2e6;
        }

        .order-summary .card-body {
            padding: 25px;
        }

        .checkout-button {
            font-size: 17px;
            font-weight: 600;
            padding: 13px;
        }

        .continue-button {
            padding: 12px;
        }

        .cart-total {
            font-size: 22px;
            font-weight: bold;
            color: #198754;
        }
    </style>
</head>

<body>

<!-- Website Header -->
<header class="site-banner">
    <h1>Hail Mary</h1>
    <p>Buy, Sell, and Trade with College Students</p>
</header>

<!-- Navigation Bar -->
<nav class="navbar navbar-expand-lg navbar-dark custom-navbar">
    <div class="container-fluid">

        <a class="navbar-brand"
           href="HailMaryhomepage.php">
            Hail Mary
        </a>

        <div class="navbar-nav ms-auto">

            <a class="nav-link"
               href="HailMaryhomepage.php">
                Home
            </a>

            <a class="nav-link"
               href="HailMaryListings.php">
                Listings
            </a>

            <a class="nav-link active"
               aria-current="page"
               href="HailMaryCart.php">
                My Cart
            </a>

        </div>
    </div>
</nav>

<!-- Main Shopping Cart -->
<main class="container cart-container py-5">

    <h2 class="mb-4">
        My Shopping Cart
    </h2>

    <!-- Item Removed Message -->
    <?php if ($itemRemoved): ?>

        <div class="alert alert-success"
             role="status">
            Item removed from your cart successfully.
        </div>

    <?php endif; ?>

    <!-- Item Added Message -->
    <?php if ($itemAdded): ?>

        <div class="alert alert-success"
             role="status">
            Item successfully added to your cart!
        </div>

    <?php endif; ?>

    <!-- Empty Shopping Cart -->
    <?php if (empty($cartItems)): ?>

        <div class="alert alert-info mt-4">

            Your shopping cart is currently empty.

            <a href="HailMaryListings.php"
               class="alert-link">
                Browse listings
            </a>

            to find something to purchase.

        </div>

        <a href="HailMaryListings.php"
           class="btn btn-primary mt-3">
            Continue Shopping
        </a>

    <?php else: ?>

        <!-- Shopping Cart Table -->
        <div class="table-responsive">

            <table class="table table-striped table-hover align-middle mt-4">

                <thead class="table-dark">
                    <tr>
                        <th scope="col">Item</th>
                        <th scope="col">Price</th>
                        <th scope="col">Date Added</th>
                        <th scope="col">Action</th>
                    </tr>
                </thead>

                <tbody>

                    <?php foreach ($cartItems as $item): ?>

                        <tr>

                            <!-- Item Name -->
                            <td>
                                <?= htmlspecialchars(
                                    $item['Title'],
                                    ENT_QUOTES,
                                    'UTF-8'
                                ) ?>
                            </td>

                            <!-- Item Price -->
                            <td>
                                $<?= number_format(
                                    (float)$item['Price'],
                                    2
                                ) ?>
                            </td>

                            <!-- Date Added -->
                            <td>
                                <?= htmlspecialchars(
                                    $item['Added_At'],
                                    ENT_QUOTES,
                                    'UTF-8'
                                ) ?>
                            </td>

                            <!-- Remove Item -->
                            <td>

                                <form
                                    method="POST"
                                    action="remove_from_cart.php"
                                    onsubmit="return confirm('Remove this item from your cart?');"
                                >

                                    <input
                                        type="hidden"
                                        name="cart_id"
                                        value="<?= (int)$item['Cart_ID'] ?>"
                                    >

                                    <button
                                        type="submit"
                                        class="btn btn-danger btn-sm"
                                    >
                                        Remove
                                    </button>

                                </form>

                            </td>

                        </tr>

                    <?php endforeach; ?>

                </tbody>

            </table>

        </div>

        <!-- Order Summary -->
        <div class="row justify-content-end mt-4">

            <div class="col-md-5">

                <div class="card shadow-sm order-summary">

                    <div class="card-body">

                        <h4 class="card-title mb-4">
                            Order Summary
                        </h4>

                        <!-- Item Count -->
                        <div class="d-flex justify-content-between mb-3">

                            <span>
                                Items:
                            </span>

                            <strong>
                                <?= count($cartItems) ?>
                            </strong>

                        </div>

                        <!-- Subtotal -->
                        <div class="d-flex justify-content-between mb-3">

                            <span>
                                Subtotal:
                            </span>

                            <strong>
                                $<?= number_format($total, 2) ?>
                            </strong>

                        </div>

                        <hr>

                        <!-- Total -->
                        <div class="d-flex justify-content-between align-items-center mb-4">

                            <h5 class="mb-0">
                                Total:
                            </h5>

                            <span class="cart-total">
                                $<?= number_format($total, 2) ?>
                            </span>

                        </div>

                        <!-- Proceed to Checkout -->
                        <a
                            href="checkout.php"
                            class="btn btn-success w-100 mb-3 checkout-button"
                        >
                            Proceed to Checkout
                        </a>

                        <!-- Continue Shopping -->
                        <a
                            href="HailMaryListings.php"
                            class="btn btn-outline-primary w-100 continue-button"
                        >
                            Continue Shopping
                        </a>

                    </div>

                </div>

            </div>

        </div>

    <?php endif; ?>

</main>

<!-- Bootstrap JavaScript -->
<script
    src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js">
</script>

</body>
</html>
