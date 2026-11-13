
<?php
session_start();

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store');

require_once __DIR__ . '/ai_config.php';
require_once __DIR__ . '/ai_tools.php';

// Return a JSON response.
function respond(int $status, array $body): void {
    http_response_code($status);

    echo json_encode(
        $body,
        JSON_INVALID_UTF8_SUBSTITUTE
    );

    exit;
}

// Only accept POST requests.
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    respond(405, [
        'error' => 'POST required'
    ]);
}

// Verify CSRF token.
$sessionToken = $_SESSION['ai_csrf'] ?? '';
$requestToken = $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';

if (
    !is_string($sessionToken) ||
    $sessionToken === '' ||
    !is_string($requestToken) ||
    !hash_equals($sessionToken, $requestToken)
) {
    respond(403, [
        'error' => 'Invalid CSRF token'
    ]);
}

// Verify student login session.
if (
    !isset($_SESSION['student_id']) ||
    !filter_var(
        $_SESSION['student_id'],
        FILTER_VALIDATE_INT
    )
) {
    respond(401, [
        'error' => 'Please log in before using the AI assistant.'
    ]);
}

$studentId = (int)$_SESSION['student_id'];

// Read JSON request.
$input = json_decode(
    file_get_contents('php://input'),
    true
);

if (!is_array($input)) {
    respond(400, [
        'error' => 'Invalid JSON'
    ]);
}

$message = trim(
    (string)($input['message'] ?? '')
);

if (
    $message === '' ||
    mb_strlen($message) > 1000
) {
    respond(400, [
        'error' => 'Message must be 1–1000 characters'
    ]);
}

// Check OpenAI configuration.
if (HAILMARY_OPENAI_API_KEY === '') {
    respond(503, [
        'error' => 'OpenAI API key is not configured.'
    ]);
}

if (!extension_loaded('curl')) {
    respond(503, [
        'error' => 'PHP cURL extension is required.'
    ]);
}

// AI agent tools.
$tools = [

    [
        'type' => 'function',
        'function' => [
            'name' => 'search_listings',

            'description' =>
                'Search active Hail Mary marketplace listings by keyword, budget, or category.',

            'parameters' => [
                'type' => 'object',

                'properties' => [
                    'query' => [
                        'type' => 'string'
                    ],

                    'max_price' => [
                        'type' => 'number'
                    ],

                    'category_id' => [
                        'type' => 'integer'
                    ]
                ],

                'additionalProperties' => false
            ]
        ]
    ],

    [
        'type' => 'function',
        'function' => [
            'name' => 'get_listing',

            'description' =>
                'Retrieve detailed information about a specific marketplace listing.',

            'parameters' => [
                'type' => 'object',

                'properties' => [
                    'listing_id' => [
                        'type' => 'integer'
                    ]
                ],

                'required' => [
                    'listing_id'
                ],

                'additionalProperties' => false
            ]
        ]
    ],

    [
        'type' => 'function',
        'function' => [
            'name' => 'get_categories',

            'description' =>
                'Retrieve available marketplace categories.',

            'parameters' => [
                'type' => 'object',
                'properties' => new stdClass(),
                'additionalProperties' => false
            ]
        ]
    ]
];

// Send conversation to OpenAI.
function callModel(
    array $messages,
    array $tools
): array {

    $payload = [
        'model' => HAILMARY_OPENAI_MODEL,
        'messages' => $messages,
        'tools' => $tools,
        'tool_choice' => 'auto',
        'temperature' => 0.3
    ];

    $ch = curl_init(
        'https://api.openai.com/v1/chat/completions'
    );

    curl_setopt_array($ch, [

        CURLOPT_POST => true,

        CURLOPT_HTTPHEADER => [
            'Content-Type: application/json',
            'Authorization: Bearer ' .
                HAILMARY_OPENAI_API_KEY
        ],

        CURLOPT_POSTFIELDS =>
            json_encode($payload),

        CURLOPT_RETURNTRANSFER => true,

        CURLOPT_TIMEOUT => 35
    ]);

    $raw = curl_exec($ch);

    $status = curl_getinfo(
        $ch,
        CURLINFO_HTTP_CODE
    );

    $error = curl_error($ch);

    curl_close($ch);

    if ($raw === false) {
        throw new RuntimeException(
            'AI network error: ' . $error
        );
    }

    $data = json_decode($raw, true);

    if ($status < 200 || $status >= 300) {

        error_log(
            'OpenAI API error HTTP ' .
            $status . ': ' .
            substr($raw, 0, 600)
        );

        throw new RuntimeException(
            'OpenAI API request failed.'
        );
    }

    if (!isset($data['choices'][0]['message'])) {
        throw new RuntimeException(
            'Unexpected AI response.'
        );
    }

    return $data['choices'][0]['message'];
}

try {

    // Initialize conversation history.
    if (
        !isset($_SESSION['ai_history']) ||
        !is_array($_SESSION['ai_history'])
    ) {
        $_SESSION['ai_history'] = [];
    }

    // Keep the most recent conversation.
    $history = array_slice(
        $_SESSION['ai_history'],
        -12
    );

    // AI system instructions.
    $systemPrompt = "
You are the Hail Mary AI Shopping Assistant.

Hail Mary is an online marketplace where
college students buy, sell, and trade items.

Your responsibilities:

1. Help students find products.
2. Recommend products based on budgets.
3. Search real marketplace listings.
4. Explain product details.
5. Compare available products.
6. Suggest affordable alternatives.
7. Help students make informed purchases.

Always use database tools when answering
questions about actual products, prices,
categories, or availability.

Never invent marketplace listings.

If a search returns no results, try a
broader search when appropriate.

Never claim to have purchased an item,
placed an order, or added an item to
the shopping cart.

Never request credit card information.

Provide clear, helpful, concise answers.

When recommending products, include
the listing ID and price when available.
";

    $messages = array_merge(

        [
            [
                'role' => 'system',
                'content' => $systemPrompt
            ]
        ],

        $history,

        [
            [
                'role' => 'user',
                'content' => $message
            ]
        ]
    );

    // Allow up to four rounds of tool use.
    for ($round = 0; $round < 4; $round++) {

        $answer = callModel(
            $messages,
            $tools
        );

        $messages[] = $answer;

        // Stop if the AI has finished.
        if (empty($answer['tool_calls'])) {
            break;
        }

        // Execute requested database tools.
        foreach ($answer['tool_calls'] as $call) {

            $name =
                $call['function']['name'] ?? '';

            $args = json_decode(
                $call['function']['arguments'] ?? '{}',
                true
            );

            if (!is_array($args)) {
                $args = [];
            }

            switch ($name) {

                case 'search_listings':

                    $result = aiSearchListings(
                        $pdo,
                        $args
                    );

                    break;

                case 'get_listing':

                    $result = aiGetListing(
                        $pdo,
                        (int)($args['listing_id'] ?? 0)
                    );

                    break;

                case 'get_categories':

                    $result = aiGetCategories(
                        $pdo
                    );

                    break;

                default:

                    $result = [
                        'error' => 'Unknown tool'
                    ];
            }

            // Return tool results to the AI.
            $messages[] = [
                'role' => 'tool',

                'tool_call_id' => $call['id'],

                'content' => json_encode(
                    $result,
                    JSON_INVALID_UTF8_SUBSTITUTE
                )
            ];
        }
    }

    // Check whether the AI completed its response.
    if (!empty($answer['tool_calls'])) {
        throw new RuntimeException(
            'AI tool limit reached.'
        );
    }

    $reply = trim(
        (string)($answer['content'] ?? '')
    );

    if ($reply === '') {
        $reply =
            'I could not generate a recommendation. Please try again.';
    }

    // Save conversation history.
    $_SESSION['ai_history'] = array_slice(

        array_merge(

            $history,

            [
                [
                    'role' => 'user',
                    'content' => $message
                ],

                [
                    'role' => 'assistant',
                    'content' => $reply
                ]
            ]
        ),

        -12
    );

    respond(200, [
        'reply' => $reply
    ]);

} catch (Throwable $e) {

    error_log(
        'HailMary AI: ' .
        $e->getMessage()
    );

    respond(500, [
        'error' =>
            'AI request failed. Check the PHP error log and API configuration.'
    ]);
}
?>
