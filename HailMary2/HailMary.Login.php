
<?php
session_start();

require_once 'db.php';

// Only accept POST requests.
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: HailMaryAccessPage.php');
    exit;
}

// Collect login information.
$email = trim($_POST['school_email'] ?? '');
$password = $_POST['password'] ?? '';

// Validate submitted information.
if (
    !filter_var($email, FILTER_VALIDATE_EMAIL) ||
    $password === ''
) {
    header('Location: HailMaryAccessPage.php?error=1');
    exit;
}

try {

    // Find the student account.
    $stmt = $pdo->prepare("
        SELECT
            Student_ID,
            Name,
            School_Email,
            Password_Hash
        FROM Students
        WHERE School_Email = ?
        LIMIT 1
    ");

    $stmt->execute([$email]);

    $student = $stmt->fetch(PDO::FETCH_ASSOC);

    // Check account and password.
    if (
        !$student ||
        empty($student['Password_Hash']) ||
        !password_verify($password, $student['Password_Hash'])
    ) {
        header('Location: HailMaryAccessPage.php?error=1');
        exit;
    }

    // Create a secure session.
    session_regenerate_id(true);

    $_SESSION['student_id'] = (int)$student['Student_ID'];
    $_SESSION['student_name'] = $student['Name'];
    $_SESSION['school_email'] = $student['School_Email'];

    // Redirect after successful login.
    header('Location: HailMaryListings.php?loggedin=1');
    exit;

} catch (PDOException $e) {

    error_log('Hail Mary login error: ' . $e->getMessage());

    http_response_code(500);
    exit('Unable to sign in right now. Please try again later.');
}
?>
