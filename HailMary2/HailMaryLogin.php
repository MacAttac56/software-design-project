
<?php
session_start();

require_once __DIR__ . '/db.php';

// Only allow POST requests.
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: HailMaryAccessPage.php');
    exit;
}

// Get submitted login information.
$email = trim($_POST['school_email'] ?? '');
$password = $_POST['password'] ?? '';

// Validate input.
if (
    !filter_var($email, FILTER_VALIDATE_EMAIL) ||
    $password === ''
) {
    header('Location: HailMaryAccessPage.php?error=1');
    exit;
}

try {

    // Find student by school email.
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

    // Verify student account and password.
    if (
        !$student ||
        empty($student['Password_Hash']) ||
        !password_verify(
            $password,
            $student['Password_Hash']
        )
    ) {
        header('Location: HailMaryAccessPage.php?error=1');
        exit;
    }

    // Prevent session fixation.
    session_regenerate_id(true);

    // Store logged-in student information.
    $_SESSION['student_id'] =
        (int)$student['Student_ID'];

    $_SESSION['student_name'] =
        $student['Name'];

    $_SESSION['school_email'] =
        $student['School_Email'];

    // Initialize AI assistant session.
    $_SESSION['ai_csrf'] =
        bin2hex(random_bytes(32));

    // Clear previous AI conversation.
    $_SESSION['ai_history'] = [];

    // Redirect to AI shopping assistant.
    header('Location: HailMaryAI.php');
    exit;

} catch (PDOException $e) {

    error_log(
        'Hail Mary login error: ' .
        $e->getMessage()
    );

    http_response_code(500);

    exit(
        'Unable to sign in right now. Please try again later.'
    );
}
?>
