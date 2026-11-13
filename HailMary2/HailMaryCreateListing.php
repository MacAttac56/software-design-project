<?php
require_once "db.php";

function stopWithError(string $message, int $status = 400): void {
    http_response_code($status);
    echo '<!DOCTYPE html><html lang="en"><meta charset="UTF-8">';
    echo '<meta name="viewport" content="width=device-width, initial-scale=1">';
    echo '<title>Hail Mary | Listing Error</title>';
    echo '<body style="font-family:Arial;max-width:700px;margin:50px auto;padding:20px">';
    echo '<h1>Unable to post listing</h1><p>';
    echo htmlspecialchars($message, ENT_QUOTES, 'UTF-8');
    echo '</p><p><a href="HailMaryUploadListing.php">Return to upload form</a></p>';
    echo '</body></html>';
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    stopWithError('Please submit the listing form.', 405);
}

$sellerId = filter_var($_POST['seller_id'] ?? null, FILTER_VALIDATE_INT);
$title = trim($_POST['title'] ?? '');
$description = trim($_POST['description'] ?? '');
$categoryId = filter_var($_POST['category_id'] ?? null, FILTER_VALIDATE_INT);
$condition = $_POST['condition'] ?? '';
$priceInput = $_POST['price'] ?? '';

if (!$sellerId || !$categoryId || $title === '' ||
    strlen($title) > 150 || $description === '' ||
    !is_numeric($priceInput) || (float)$priceInput <= 0 ||
    (float)$priceInput > 99999999.99) {
    stopWithError('Check the title, description, seller, category, and price.');
}

if (!in_array($condition, ['New', 'Like New', 'Good', 'Fair'], true)) {
    stopWithError('Select a valid item condition.');
}

if (!isset($_FILES['images']) || !is_array($_FILES['images']['name'])) {
    stopWithError('Please choose at least one picture.');
}

$files = $_FILES['images'];
$count = count($files['name']);

if ($count < 1 || $count > 5) {
    stopWithError('Upload between 1 and 5 pictures.');
}

// Check the selected student and category against the database.
$check = $pdo->prepare("SELECT Student_ID FROM Students WHERE Student_ID = ?");
$check->execute([$sellerId]);
if (!$check->fetch()) {
    stopWithError('The selected student does not exist.');
}

$check = $pdo->prepare("SELECT Category_ID FROM Categories WHERE Category_ID = ?");
$check->execute([$categoryId]);
if (!$check->fetch()) {
    stopWithError('The selected category does not exist.');
}

$uploadDir = __DIR__ . '/uploads/listings/';

if (!is_dir($uploadDir) &&
    !mkdir($uploadDir, 0755, true) && !is_dir($uploadDir)) {
    stopWithError('Could not create the image upload folder.', 500);
}

$finfo = new finfo(FILEINFO_MIME_TYPE);
$allowedTypes = [
    'image/jpeg' => 'jpg',
    'image/png'  => 'png',
    'image/webp' => 'webp'
];

$validatedImages = [];

for ($i = 0; $i < $count; $i++) {
    if ($files['error'][$i] !== UPLOAD_ERR_OK) {
        stopWithError('One of the pictures failed to upload. Try again.');
    }

    if ($files['size'][$i] <= 0 || $files['size'][$i] > 5 * 1024 * 1024) {
        stopWithError('Each picture must be no larger than 5 MB.');
    }

    if (!is_uploaded_file($files['tmp_name'][$i])) {
        stopWithError('An uploaded picture could not be verified.');
    }

    $mime = $finfo->file($files['tmp_name'][$i]);
    if (!isset($allowedTypes[$mime]) ||
        @getimagesize($files['tmp_name'][$i]) === false) {
        stopWithError('Pictures must be valid JPG, PNG, or WebP images.');
    }

    $filename = bin2hex(random_bytes(16)) . '.' . $allowedTypes[$mime];

    $validatedImages[] = [
        'temporary_path' => $files['tmp_name'][$i],
        'filename' => $filename,
        'relative_path' => 'uploads/listings/' . $filename
    ];
}

$movedPaths = [];

try {
    $pdo->beginTransaction();

    $stmt = $pdo->prepare(
        "INSERT INTO Listings
         (Seller_ID, Title, Description, Price, Category_ID, `Condition`, Status)
         VALUES (?, ?, ?, ?, ?, ?, 'Active')"
    );

    $stmt->execute([
        $sellerId,
        $title,
        $description,
        number_format((float)$priceInput, 2, '.', ''),
        $categoryId,
        $condition
    ]);

    $listingId = (int)$pdo->lastInsertId();

    $imageStmt = $pdo->prepare(
        "INSERT INTO Listing_Images (Listing_ID, Image_URL, Sort_Order)
         VALUES (?, ?, ?)"
    );

    foreach ($validatedImages as $index => $image) {
        $destination = $uploadDir . $image['filename'];

        if (!move_uploaded_file($image['temporary_path'], $destination)) {
            throw new RuntimeException('Could not save an uploaded picture.');
        }

        $movedPaths[] = $destination;

        $imageStmt->execute([
            $listingId,
            $image['relative_path'],
            $index + 1
        ]);
    }

    $pdo->commit();

    header('Location: HailMaryListings.php?posted=1');
    exit;

} catch (Throwable $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }

    foreach ($movedPaths as $path) {
        if (is_file($path)) {
            unlink($path);
        }
    }

    error_log('Hail Mary listing upload failed: ' . $e->getMessage());
    stopWithError('The listing could not be saved. Check your database and upload-folder permissions.', 500);
}
?>

