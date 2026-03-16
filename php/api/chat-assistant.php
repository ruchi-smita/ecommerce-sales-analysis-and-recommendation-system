<?php
header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode([
        'reply' => 'Please send your request as a POST message.',
        'products' => [],
        'suggestions' => [],
    ]);
    exit;
}

function normalize_text(string $value): string
{
    $value = strtolower($value);
    $value = preg_replace('/[^a-z0-9\s]/', ' ', $value);
    $value = preg_replace('/\s+/', ' ', $value);

    return trim($value);
}

function contains_any(string $haystack, array $needles): bool
{
    foreach ($needles as $needle) {
        if ($needle !== '' && strpos($haystack, $needle) !== false) {
            return true;
        }
    }

    return false;
}

function detect_gender(string $message): ?string
{
    if (contains_any($message, ['women', 'woman', 'ladies', 'lady', 'girl', 'female', 'her'])) {
        return 'women';
    }

    if (contains_any($message, ['men', 'man', 'gents', 'gent', 'boy', 'male', 'him'])) {
        return 'men';
    }

    return null;
}

function detect_budget(string $originalMessage): ?int
{
    if (preg_match('/\b(?:under|below|less than|max|upto|up to)\s*(\d+)\s*k\b/i', $originalMessage, $matches)) {
        return (int) $matches[1] * 1000;
    }

    if (preg_match('/\b(?:under|below|less than|max|upto|up to)\s*(?:rs\.?|inr)?\s*(\d{3,6})\b/i', $originalMessage, $matches)) {
        return (int) $matches[1];
    }

    if (preg_match('/\b(?:rs\.?|inr)\s*(\d{3,6})\b/i', $originalMessage, $matches)) {
        return (int) $matches[1];
    }

    if (preg_match('/\b(\d+)\s*k\b/i', $originalMessage, $matches)) {
        return (int) $matches[1] * 1000;
    }

    return null;
}

function normalize_gender_name(string $genderName): string
{
    $genderName = normalize_text($genderName);

    if (contains_any($genderName, ['women', 'woman', 'ladies', 'lady', 'female'])) {
        return 'women';
    }

    if (contains_any($genderName, ['men', 'man', 'gents', 'gent', 'male'])) {
        return 'men';
    }

    return 'unisex';
}

function normalize_category_name(string $categoryName): string
{
    $categoryName = normalize_text($categoryName);

    if (contains_any($categoryName, ['shoe', 'footwear', 'sandal', 'heel', 'boot'])) {
        return 'footwear';
    }

    if (contains_any($categoryName, ['accessor', 'bag', 'wallet', 'jewel'])) {
        return 'accessories';
    }

    if (contains_any($categoryName, ['cloth', 'apparel', 'clothing', 'dress', 'fashion'])) {
        return 'clothing';
    }

    return $categoryName;
}

function infer_product_tags(array $product): array
{
    $text = normalize_text($product['name'] . ' ' . $product['category_name']);
    $tags = [];

    $tagMap = [
        'dress' => ['dress', 'gown'],
        'heels' => ['heel', 'heels'],
        'shirt' => ['shirt', 'tee', 't shirt', 'top'],
        'jacket' => ['jacket', 'blazer', 'coat'],
        'jeans' => ['jean', 'jeans', 'denim'],
        'shoes' => ['shoe', 'shoes', 'loafer', 'sneaker', 'boot', 'footwear'],
        'wallet' => ['wallet'],
        'bag' => ['bag', 'handbag', 'purse', 'tote'],
        'accessory' => ['accessory', 'accessories'],
    ];

    foreach ($tagMap as $tag => $keywords) {
        if (contains_any($text, $keywords)) {
            $tags[] = $tag;
        }
    }

    return array_values(array_unique($tags));
}

function build_intent_profiles(): array
{
    return [
        'party' => [
            'terms' => ['party', 'night', 'evening', 'birthday', 'date', 'club', 'cocktail'],
            'types' => ['dress', 'heels', 'jacket', 'shirt', 'shoes', 'bag'],
            'categories' => ['clothing', 'footwear', 'accessories'],
            'label' => 'party look',
        ],
        'traditional' => [
            'terms' => ['traditional', 'ethnic', 'festival', 'festive', 'wedding', 'classic'],
            'types' => ['dress', 'jacket', 'bag', 'heels'],
            'categories' => ['clothing', 'accessories'],
            'label' => 'traditional look',
        ],
        'casual' => [
            'terms' => ['casual', 'daily', 'everyday', 'college', 'relaxed', 'weekend'],
            'types' => ['shirt', 'jeans', 'shoes', 'wallet'],
            'categories' => ['clothing', 'footwear'],
            'label' => 'casual wear',
        ],
        'office' => [
            'terms' => ['office', 'formal', 'work', 'meeting', 'smart'],
            'types' => ['shirt', 'jacket', 'shoes', 'wallet'],
            'categories' => ['clothing', 'footwear', 'accessories'],
            'label' => 'office wear',
        ],
        'gift' => [
            'terms' => ['gift', 'present', 'surprise'],
            'types' => ['bag', 'wallet', 'accessory'],
            'categories' => ['accessories'],
            'label' => 'gift ideas',
        ],
        'footwear' => [
            'terms' => ['footwear', 'shoes', 'shoe', 'heels', 'sneakers', 'boots', 'sandals'],
            'types' => ['shoes', 'heels'],
            'categories' => ['footwear'],
            'label' => 'footwear picks',
        ],
    ];
}

function detect_intent(string $message, array $profiles): ?string
{
    foreach ($profiles as $intent => $profile) {
        if (contains_any($message, $profile['terms'])) {
            return $intent;
        }
    }

    return null;
}

function detect_requested_categories(string $message): array
{
    $requested = [];

    $categoryTerms = [
        'clothing' => ['clothing', 'clothes', 'outfit', 'wear', 'dress', 'shirt', 'jacket', 'jeans', 'tops'],
        'footwear' => ['footwear', 'shoe', 'shoes', 'heels', 'sneakers', 'boots', 'sandals'],
        'accessories' => ['accessories', 'accessory', 'bag', 'handbag', 'wallet', 'purse'],
    ];

    foreach ($categoryTerms as $category => $terms) {
        if (contains_any($message, $terms)) {
            $requested[] = $category;
        }
    }

    return $requested;
}

function extract_message_keywords(string $message): array
{
    $keywords = array_filter(explode(' ', $message), static function ($token): bool {
        $stopWords = [
            'i', 'me', 'my', 'need', 'want', 'show', 'for', 'a', 'an', 'the', 'with',
            'and', 'or', 'to', 'under', 'below', 'look', 'wear', 'something', 'please',
            'give', 'find', 'style', 'help',
        ];

        return strlen($token) >= 3 && !in_array($token, $stopWords, true);
    });

    return array_values(array_unique($keywords));
}

function score_product(
    array $product,
    ?string $intent,
    array $profiles,
    ?string $requestedGender,
    array $requestedCategories,
    array $messageKeywords
): int {
    $score = 0;
    $productName = normalize_text($product['name']);
    $productGender = normalize_gender_name($product['gender_name']);
    $productCategory = normalize_category_name($product['category_name']);
    $productTags = infer_product_tags($product);

    if ($requestedGender !== null && $productGender === $requestedGender) {
        $score += 5;
    }

    if (!empty($requestedCategories) && in_array($productCategory, $requestedCategories, true)) {
        $score += 4;
    }

    if ($intent !== null) {
        $profile = $profiles[$intent];

        if (in_array($productCategory, $profile['categories'], true)) {
            $score += 4;
        }

        foreach ($profile['types'] as $type) {
            if (in_array($type, $productTags, true)) {
                $score += 3;
            }
        }
    }

    foreach ($messageKeywords as $keyword) {
        if (strpos($productName, $keyword) !== false) {
            $score += 2;
        }
    }

    if ($score === 0 && empty($messageKeywords)) {
        $score = 1;
    }

    return $score;
}

function format_budget(?int $budget): string
{
    if ($budget === null) {
        return '';
    }

    return ' under Rs ' . number_format($budget);
}

function build_reply(
    ?string $intent,
    array $profiles,
    ?string $gender,
    ?int $budget,
    bool $budgetWasRelaxed,
    array $products
): string {
    $intentLabel = $intent !== null ? $profiles[$intent]['label'] : 'style picks';
    $genderLabel = $gender !== null ? ' for ' . $gender : '';
    $budgetLabel = format_budget($budget);

    if (empty($products)) {
        return 'I could not find a strong match yet. Try asking for party, traditional, casual, office, or accessories with a budget.';
    }

    $reply = 'Here are some ' . $intentLabel . $genderLabel . $budgetLabel . ' from your catalog.';

    if ($intent === 'traditional') {
        $reply .= ' If your catalog has limited ethnic pieces, I am prioritizing the closest elegant options.';
    }

    if ($budgetWasRelaxed) {
        $reply .= ' I did not find enough items within that budget, so I included the closest matches above it too.';
    }

    return $reply;
}

function build_suggestions(?string $intent): array
{
    $defaultSuggestions = [
        'Party look for women',
        'Traditional style under 2500',
        'Casual daily wear for men',
    ];

    $intentSuggestions = [
        'party' => [
            'Party look under 3000',
            'Women party footwear',
            'Accessories for a night out',
        ],
        'traditional' => [
            'Traditional style for women',
            'Festival look with accessories',
            'Classic outfit under 2500',
        ],
        'casual' => [
            'Casual college wear',
            'Everyday shoes under 2000',
            'Men casual basics',
        ],
        'office' => [
            'Office wear for men',
            'Formal shoes under 3000',
            'Smart accessories for work',
        ],
        'gift' => [
            'Gift ideas under 1500',
            'Handbags for gifting',
            'Wallet recommendations',
        ],
        'footwear' => [
            'Women heels for party',
            'Comfortable daily shoes',
            'Men footwear under 2500',
        ],
    ];

    return $intent !== null && isset($intentSuggestions[$intent])
        ? $intentSuggestions[$intent]
        : $defaultSuggestions;
}

$rawInput = file_get_contents('php://input');
$payload = json_decode($rawInput, true);
$message = trim((string) ($payload['message'] ?? ''));

if ($message === '') {
    http_response_code(422);
    echo json_encode([
        'reply' => 'Tell me what you need, like "party wear for women" or "traditional under 2000".',
        'products' => [],
        'suggestions' => build_suggestions(null),
    ]);
    exit;
}

$normalizedMessage = normalize_text($message);
$profiles = build_intent_profiles();
$intent = detect_intent($normalizedMessage, $profiles);
$requestedGender = detect_gender($normalizedMessage);
$requestedCategories = detect_requested_categories($normalizedMessage);
$budget = detect_budget($message);
$messageKeywords = extract_message_keywords($normalizedMessage);

require_once "../../config/database.php";

$stmt = $conn->query(
    "SELECT
        p.product_id,
        p.name,
        p.price,
        p.image_url,
        p.created_at,
        c.category_name,
        g.gender_name
     FROM products p
     JOIN categories c ON p.category_id = c.category_id
     JOIN genders g ON p.gender_id = g.gender_id
     ORDER BY p.created_at DESC"
);

$catalog = $stmt->fetchAll(PDO::FETCH_ASSOC);
$scoredProducts = [];

foreach ($catalog as $product) {
    $score = score_product(
        $product,
        $intent,
        $profiles,
        $requestedGender,
        $requestedCategories,
        $messageKeywords
    );

    if ($score <= 0) {
        continue;
    }

    $product['score'] = $score;
    $scoredProducts[] = $product;
}

usort($scoredProducts, static function (array $left, array $right): int {
    if ($left['score'] === $right['score']) {
        return (float) $left['price'] <=> (float) $right['price'];
    }

    return $right['score'] <=> $left['score'];
});

$budgetWasRelaxed = false;

if ($budget !== null) {
    $withinBudget = array_values(array_filter($scoredProducts, static function (array $product) use ($budget): bool {
        return (float) $product['price'] <= $budget;
    }));

    if (!empty($withinBudget)) {
        $scoredProducts = $withinBudget;
    } else {
        $budgetWasRelaxed = true;
    }
}

$topProducts = array_slice($scoredProducts, 0, 4);
$reply = build_reply($intent, $profiles, $requestedGender, $budget, $budgetWasRelaxed, $topProducts);

$responseProducts = array_map(static function (array $product): array {
    return [
        'product_id' => (int) $product['product_id'],
        'name' => $product['name'],
        'price' => (float) $product['price'],
        'image_url' => $product['image_url'],
        'category_name' => $product['category_name'],
        'gender_name' => $product['gender_name'],
    ];
}, $topProducts);

echo json_encode([
    'reply' => $reply,
    'products' => $responseProducts,
    'suggestions' => build_suggestions($intent),
]);
