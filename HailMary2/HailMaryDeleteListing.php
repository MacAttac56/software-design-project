
<?php
session_start();
require_once 'db.php';

if (!isset($_SESSION['student_id'])) {
    header('Location: HailMaryAccessPage.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: HailMaryAccount.php');
    exit;
}

// Verify CSRF token.
if (
    !isset($_POST['csrf_token']) ||
    !hash_equals($_SESSION['csrf_token'] ?? '', $_POST['csrf_token'])
) {
    header('Location: HailMaryAccount.php?error=1');
    exit;
}

$studentId = (int) $_SESSION['student_id'];
$listingId = filter_input(INPUT_POST, 'listing_id', FILTER_VALIDATE_INT);

if (!$listingId || $listingId <= 0) {
    header('Location: HailMaryAccount.php?error=1');
    exit;
}

try {
    $pdo->beginTransaction();

    // Confirm this listing belongs to the logged-in student.
    $stmt = $pdo->prepare("
        SELECT Listing_ID
        FROM Listings
        WHERE Listing_ID = ? AND Seller_ID = ?
        FOR UPDATE
    ");
    $stmt->execute([$listingId, $studentId]);

    if (!$stmt->fetch()) {
        $pdo->rollBack();
        header('Location: HailMaryAccount.php?error=1');
        exit;
    }

    // Remove associated image records first.
    $stmt = $pdo->prepare("
        DELETE FROM Listing_Images
        WHERE Listing_ID = ?
    ");
    $stmt->execute([$listingId]);

    // Delete the listing itself.
    $stmt = $pdo->prepare("
        DELETE FROM Listings
        WHERE Listing_ID = ? AND Seller_ID = ?
    ");
    $stmt->execute([$listingId, $studentId]);

    $pdo->commit();

    header('Location: HailMaryAccount.php?deleted=1');
    exit;

} catch (PDOException $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }

    error_log($e->getMessage());
    header('Location: HailMaryAccount.php?error=1');
    exit;
}
?>
