
<?php
require_once __DIR__ . '/db.php';

$keyFile = '/Applications/XAMPP/xamppfiles/hailmary-private/openai_key';

$apiKey = '';

if (is_readable($keyFile)) {
    $apiKey = trim(file_get_contents($keyFile));
}

define('HAILMARY_OPENAI_API_KEY', $apiKey);
define('HAILMARY_OPENAI_MODEL', 'gpt-4o-mini');
?>
