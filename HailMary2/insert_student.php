<?php

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

header('Content-Type: text/html; charset=utf-8');

function displayResult(string $title, string $message): void {
    echo '<!DOCTYPE html><html lang="en"><head>';
    echo '<meta charset="UTF-8">';
    echo '<meta name="viewport" content="width=device-width, initial-scale=1.0">';
    echo '<title>Hail Mary | Registration</title>';
    echo '<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">';
    echo '</head><body class="container py-5">';
    echo '<div class="card shadow p-4 mx-auto" style="max-width:600px">';
    echo '<h2>' . htmlspecialchars($title, ENT_QUOTES, 'UTF-8') . '</h2>';
    echo '<p>' . htmlspecialchars($message, ENT_QUOTES, 'UTF-8') . '</p>';
    echo '<a href="HailMarysignup.php" class="btn btn-primary">Back to Sign-Up</a>';
    echo '</div></body></html>';
}

// Only accept POST requests.
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    displayResult('Method not allowed', 'Please submit the registration form.');
    exit;
}

// Collect form information.
$name = trim($_POST['student_name'] ?? '');
$email = trim($_POST['school_email'] ?? '');
$phone = trim($_POST['phone_number'] ?? '');

$schoolId = filter_var(
    $_POST['school_id'] ?? null,
    FILTER_VALIDATE_INT
);

$password = $_POST['password'] ?? '';
$confirmPassword = $_POST['confirm_password'] ?? '';

// Validate student information.
if (
    $name === '' ||
    strlen($name) > 100 ||
    !$schoolId ||
    $schoolId < 1 ||
    !filter_var($email, FILTER_VALIDATE_EMAIL) ||
    strlen($email) > 150 ||
    !preg_match('/^[0-9()+. -]{7,20}$/', $phone)
) {
    http_response_code(422);
    displayResult(
        'Invalid information',
        'Please check your name, school, email, and phone number.'
    );
    exit;
}

// Validate password length.
if (strlen($password) < 8 || strlen($password) > 72) {
    http_response_code(422);
    displayResult(
        'Invalid password',
        'Your password must contain between 8 and 72 characters.'
    );
    exit;
}

// Confirm both passwords match.
if ($password !== $confirmPassword) {
    http_response_code(422);
    displayResult(
        'Passwords do not match',
        'Please return to the sign-up page and enter matching passwords.'
    );
    exit;
}

try {

    $db = new mysqli(
        'localhost',
        'root',
        '',
        'GroupHailMary'
    );

    $db->set_charset('utf8mb4');

    // Verify selected school.
    $checkSchool = $db->prepare(
        'SELECT School_ID FROM Schools WHERE School_ID = ?'
    );

    $checkSchool->bind_param('i', $schoolId);
    $checkSchool->execute();
    $checkSchool->store_result();

    if ($checkSchool->num_rows === 0) {
        $checkSchool->close();
        $db->close();

        http_response_code(422);
        displayResult('Invalid school', 'The selected school does not exist.');
        exit;
    }

    $checkSchool->close();

    // Check for an existing school email.
    $checkEmail = $db->prepare(
        'SELECT Student_ID FROM Students WHERE School_Email = ?'
    );

    $checkEmail->bind_param('s', $email);
    $checkEmail->execute();
    $checkEmail->store_result();

    if ($checkEmail->num_rows > 0) {
        $checkEmail->close();
        $db->close();

        header('Location: HailMarysignup.php?error=email');
        exit;
    }

    $checkEmail->close();

    // Securely hash the password.
    $passwordHash = password_hash($password, PASSWORD_DEFAULT);

    // New students are not verified yet.
    $verified = 0;

    // Save student information and password hash.
    $stmt = $db->prepare(
        'INSERT INTO Students
        (Name, School_ID, School_Email, Phone_Number,
         Email_Verified, Phone_Verified, Password_Hash)
        VALUES (?, ?, ?, ?, ?, ?, ?)'
    );

    $stmt->bind_param(
        'sissiis',
        $name,
        $schoolId,
        $email,
        $phone,
        $verified,
        $verified,
        $passwordHash
    );

    $stmt->execute();

    $newId = $db->insert_id;

    $stmt->close();
    $db->close();

    // Redirect to login after successful registration.
    header('Location: HailMaryAccessPage.php?registered=1');
    exit;

} catch (mysqli_sql_exception $e) {

    error_log('Hail Mary registration failed: ' . $e->getMessage());

    http_response_code(500);

    if ($e->getCode() === 1062) {
        displayResult(
            'Duplicate registration',
            'This school email is already registered.'
        );
    } else {
        displayResult(
            'Registration failed',
            'Unable to create your account. Check your database columns and settings.'
        );
    }
}
?>
