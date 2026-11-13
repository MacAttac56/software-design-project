<?php
require_once __DIR__ . '/db.php';

function aiSearchListings(PDO $pdo, array $args): array {
    $sql = "SELECT Listing_ID, Title, Description, Price, Category_ID, `Condition`, `Status` FROM Listings WHERE `Status` = 'Active'";
    $params = [];
    if (isset($args['query']) && trim((string)$args['query']) !== '') {
        $words = preg_split('/\s+/', trim((string)$args['query']));
        foreach (array_slice($words, 0, 5) as $i => $word) {
            $key = ':word' . $i;
            $sql .= " AND (Title LIKE $key OR Description LIKE $key)";
            $params[$key] = '%' . $word . '%';
        }
    }
    if (isset($args['max_price']) && is_numeric($args['max_price'])) {
        $sql .= ' AND Price <= :max_price';
        $params[':max_price'] = max(0, (float)$args['max_price']);
    }
    if (isset($args['category_id']) && is_numeric($args['category_id'])) {
        $sql .= ' AND Category_ID = :category_id';
        $params[':category_id'] = (int)$args['category_id'];
    }
    $sql .= ' ORDER BY Price ASC LIMIT 20';
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function aiGetListing(PDO $pdo, int $id): array {
    $stmt = $pdo->prepare("SELECT Listing_ID, Title, Description, Price, Category_ID, `Condition`, `Status` FROM Listings WHERE Listing_ID = ? LIMIT 1");
    $stmt->execute([$id]);
    return $stmt->fetch(PDO::FETCH_ASSOC) ?: ['error' => 'Listing not found'];
}

function aiGetCategories(PDO $pdo): array {
    $stmt = $pdo->query('SELECT * FROM Categories LIMIT 50');
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function aiAddToCart(PDO $pdo, int $studentId, int $listingId): array {
    $pdo->beginTransaction();
    try {
        $stmt = $pdo->prepare("SELECT Listing_ID, Title FROM Listings WHERE Listing_ID = ? AND `Status` = 'Active' FOR UPDATE");
        $stmt->execute([$listingId]);
        $listing = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$listing) {
            $pdo->rollBack();
            return ['error' => 'Listing is unavailable'];
        }
        $stmt = $pdo->prepare('SELECT Cart_ID FROM Cart WHERE Student_ID = ? AND Listing_ID = ? LIMIT 1');
        $stmt->execute([$studentId, $listingId]);
        if ($stmt->fetch()) {
            $pdo->rollBack();
            return ['message' => 'This item is already in your cart'];
        }
        $stmt = $pdo->prepare('INSERT INTO Cart (Student_ID, Listing_ID, Added_At) VALUES (?, ?, NOW())');
        $stmt->execute([$studentId, $listingId]);
        $pdo->commit();
        return ['message' => 'Added to cart', 'listing_id' => $listingId, 'title' => $listing['Title']];
    } catch (Throwable $e) {
        if ($pdo->inTransaction()) $pdo->rollBack();
        throw $e;
    }
}
