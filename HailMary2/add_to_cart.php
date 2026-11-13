
<?php
require_once 'db.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    exit('Method not allowed');
}

$listingId = filter_input(INPUT_POST, 'listing_id', FILTER_VALIDATE_INT);

// Assignment demo: replace with the logged-in student's ID when login is implemented.
$studentId = 1;

if (!$listingId || $listingId < 1) {
    http_response_code(400);
    exit('Invalid listing');
}

try {
    // Confirm that the listing exists and is active.
    $check = $pdo->prepare("
        SELECT Listing_ID
        FROM Listings
        WHERE Listing_ID = ? AND Status = 'Active'
    ");
    $check->execute([$listingId]);

    if (!$check->fetchColumn()) {
        http_response_code(404);
        exit('Listing not found');
    }

    // Check whether this student already has this listing in their cart.
    $duplicateCheck = $pdo->prepare("
        SELECT Cart_ID
        FROM Cart
        WHERE Student_ID = ? AND Listing_ID = ?
    ");
    $duplicateCheck->execute([$studentId, $listingId]);

    if ($duplicateCheck->fetchColumn()) {
        header('Location: HailMaryListings.php?duplicate=1', true, 303);
        exit;
    }

    // Add the listing only if it is not already in the cart.
    $insert = $pdo->prepare("
        INSERT INTO Cart (Student_ID, Listing_ID)
        VALUES (?, ?)
    ");
    $insert->execute([$studentId, $listingId]);

    header('Location: HailMaryListings.php?added=1', true, 303);
    exit;

} catch (PDOException $e) {
    error_log($e->getMessage());
    http_response_code(500);
    exit('Could not add listing to cart. Please try again.');
}
?>

