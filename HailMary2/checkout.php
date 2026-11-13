
<?php
session_start();
require_once 'db.php';

// Temporary student ID for your assignment demo.
// Replace with authenticated session ID when login is integrated.
$studentId = 1;
$error = '';

try {
    $stmt = $pdo->prepare("
        SELECT
            c.Cart_ID,
            l.Listing_ID,
            l.Title,
            l.Price
        FROM Cart c
        INNER JOIN Listings l
            ON c.Listing_ID = l.Listing_ID
        WHERE c.Student_ID = ?
        ORDER BY c.Added_At DESC
    ");

    $stmt->execute([$studentId]);
    $cartItems = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    error_log($e->getMessage());
    http_response_code(500);
    exit('Unable to load your shopping cart.');
}

$total = 0;

foreach ($cartItems as $item) {
    $total += (float)$item['Price'];
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $fullName = trim($_POST['full_name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $address = trim($_POST['address'] ?? '');
    $city = trim($_POST['city'] ?? '');
    $state = trim($_POST['state'] ?? '');
    $zip = trim($_POST['zip_code'] ?? '');

    if (
        $fullName === '' ||
        $email === '' ||
        $address === '' ||
        $city === '' ||
        $state === '' ||
        $zip === ''
    ) {
        $error = 'Please complete all shipping fields.';

    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Please enter a valid email address.';

    } elseif (empty($cartItems)) {
        $error = 'Your shopping cart is empty.';

    } else {

        try {
            $pdo->beginTransaction();

            // Lock the cart rows and read current prices.
            $stmt = $pdo->prepare("
                SELECT
                    l.Title,
                    l.Price
                FROM Cart c
                INNER JOIN Listings l
                    ON c.Listing_ID = l.Listing_ID
                WHERE c.Student_ID = ?
                FOR UPDATE
            ");

            $stmt->execute([$studentId]);
            $items = $stmt->fetchAll(PDO::FETCH_ASSOC);

            if (empty($items)) {
                throw new RuntimeException(
                    'Your shopping cart is empty.'
                );
            }

            $descriptions = [];
            $orderTotal = 0;

            foreach ($items as $item) {
                $descriptions[] = $item['Title'];
                $orderTotal += (float)$item['Price'];
            }

            // Save checkout and shipping information.
            $insert = $pdo->prepare("
                INSERT INTO Checkout (
                    Student_ID,
                    items_descriptions,
                    total_price,
                    Full_Name,
                    Email,
                    Address,
                    City,
                    State,
                    Zip_Code,
                    Payment_Method
                )
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
            ");

            $insert->execute([
                $studentId,
                implode(', ', $descriptions),
                number_format($orderTotal, 2, '.', ''),
                $fullName,
                $email,
                $address,
                $city,
                $state,
                $zip,
                'Demo'
            ]);

            $orderId = $pdo->lastInsertId();

            // Empty the student's cart after checkout.
            $delete = $pdo->prepare("
                DELETE FROM Cart
                WHERE Student_ID = ?
            ");

            $delete->execute([$studentId]);

            $pdo->commit();

            header(
                'Location: checkout.php?success=1&order_id=' .
                urlencode($orderId),
                true,
                303
            );
            exit;

        } catch (Throwable $e) {

            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }

            error_log($e->getMessage());

            $error = 'Checkout failed. Please try again.';
        }
    }
}

function e($value) {
    return htmlspecialchars(
        (string)$value,
        ENT_QUOTES,
        'UTF-8'
    );
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, initial-scale=1.0">

    <title>Hail Mary | Checkout</title>

    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css"
        rel="stylesheet">

    <link rel="stylesheet"
          href="HailMarystylesheet.css">
</head>

<body>

<header class="site-banner">
    <h1>Hail Mary</h1>
    <p>Buy, Sell, and Trade with College Students</p>
</header>

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

            <a class="nav-link"
               href="HailMaryCart.php">
                My Cart
            </a>

        </div>
    </div>
</nav>

<main class="container py-5">

<?php if (
    isset($_GET['success']) &&
    $_GET['success'] === '1'
): ?>

    <div class="alert alert-success text-center">

        <h2>Order Placed Successfully!</h2>

        <p>
            Your Hail Mary demo checkout
            has been completed.
        </p>

        <p>
            Order Number:
            <strong>
                <?= e($_GET['order_id'] ?? '') ?>
            </strong>
        </p>

        <p>No actual payment was charged.</p>

        <a href="HailMaryListings.php"
           class="btn btn-primary">
            Continue Shopping
        </a>

    </div>

<?php elseif (empty($cartItems)): ?>

    <h2>Checkout</h2>

    <div class="alert alert-info">
        Your shopping cart is empty.
    </div>

    <a href="HailMaryListings.php"
       class="btn btn-primary">
        Browse Listings
    </a>

<?php else: ?>

    <h2 class="mb-4">Checkout</h2>

    <?php if ($error !== ''): ?>

        <div class="alert alert-danger">
            <?= e($error) ?>
        </div>

    <?php endif; ?>

    <div class="row g-4">

        <div class="col-md-7">

            <div class="card shadow-sm">

                <div class="card-body">

                    <h4 class="mb-4">
                        Shipping Information
                    </h4>

                    <form method="POST"
                          action="checkout.php">

                        <div class="mb-3">
                            <label class="form-label">
                                Full Name
                            </label>

                            <input
                                type="text"
                                name="full_name"
                                class="form-control"
                                value="<?= e($_POST['full_name'] ?? '') ?>"
                                required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">
                                Email Address
                            </label>

                            <input
                                type="email"
                                name="email"
                                class="form-control"
                                value="<?= e($_POST['email'] ?? '') ?>"
                                required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">
                                Street Address
                            </label>

                            <input
                                type="text"
                                name="address"
                                class="form-control"
                                value="<?= e($_POST['address'] ?? '') ?>"
                                required>
                        </div>

                        <div class="row">

                            <div class="col-md-5 mb-3">
                                <label class="form-label">
                                    City
                                </label>

                                <input
                                    type="text"
                                    name="city"
                                    class="form-control"
                                    value="<?= e($_POST['city'] ?? '') ?>"
                                    required>
                            </div>

                            <div class="col-md-4 mb-3">
                                <label class="form-label">
                                    State
                                </label>

                                <input
                                    type="text"
                                    name="state"
                                    class="form-control"
                                    value="<?= e($_POST['state'] ?? '') ?>"
                                    required>
                            </div>

                            <div class="col-md-3 mb-3">
                                <label class="form-label">
                                    ZIP Code
                                </label>

                                <input
                                    type="text"
                                    name="zip_code"
                                    class="form-control"
                                    value="<?= e($_POST['zip_code'] ?? '') ?>"
                                    required>
                            </div>

                        </div>

                        <h5 class="mt-3">
                            Payment Method
                        </h5>

                        <div class="alert alert-secondary">
                            Demo Payment — No real payment
                            information is required.
                        </div>

                        <button
                            type="submit"
                            class="btn btn-success w-100">

                            Place Order -
                            $<?= number_format($total, 2) ?>

                        </button>

                    </form>

                </div>
            </div>
        </div>

        <div class="col-md-5">

            <div class="card shadow-sm">

                <div class="card-body">

                    <h4 class="mb-4">
                        Order Summary
                    </h4>

                    <?php foreach ($cartItems as $item): ?>

                        <div class="d-flex
                                    justify-content-between
                                    mb-3">

                            <span>
                                <?= e($item['Title']) ?>
                            </span>

                            <span>
                                $<?= number_format(
                                    (float)$item['Price'],
                                    2
                                ) ?>
                            </span>

                        </div>

                    <?php endforeach; ?>

                    <hr>

                    <div class="d-flex
                                justify-content-between">

                        <strong>Total</strong>

                        <strong>
                            $<?= number_format($total, 2) ?>
                        </strong>

                    </div>

                    <a href="HailMaryCart.php"
                       class="btn btn-outline-secondary
                              w-100 mt-4">

                        Return to Cart

                    </a>

                </div> 
            </div>

        </div>
    </div>

<?php endif; ?>

</main>

<script
    src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js">
</script>

</body>
</html>
