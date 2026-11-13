
<?php
require_once 'db.php';

// Assignment demo: DeJon's Student_ID = 1
$studentId = 1;

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    exit('Method not allowed');
}

$cartId = filter_input(
    INPUT_POST,
    'cart_id',
    FILTER_VALIDATE_INT
);

if (!$cartId || $cartId < 1) {
    http_response_code(400);
    exit('Invalid cart item.');
}

try {
    $sql = "DELETE FROM Cart
            WHERE Cart_ID = ?
            AND Student_ID = ?";

    $stmt = $pdo->prepare($sql);
    $stmt->execute([$cartId, $studentId]);

    header('Location: HailMaryCart.php?removed=1', true, 303);
    exit;

} catch (PDOException $e) {
    error_log($e->getMessage());
    http_response_code(500);
    exit('Could not remove item from cart.');
}
?>
