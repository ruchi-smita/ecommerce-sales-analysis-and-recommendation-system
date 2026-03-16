<?php
session_start();

require_once "../../config/database.php";
require_once "../../includes/product-catalog.php";

ensure_product_catalog_schema($conn);

function product_review_stars(int $rating): string
{
    return str_repeat('&#9733;', max(0, min(5, $rating)))
        . str_repeat('&#9734;', max(0, 5 - min(5, $rating)));
}

function product_review_has_purchase(PDO $conn, int $userId, int $productId): bool
{
    $stmt = $conn->prepare(
        "SELECT 1
         FROM orders o
         JOIN order_items oi ON oi.order_id = o.order_id
         WHERE o.user_id = ?
           AND oi.product_id = ?
           AND LOWER(o.order_status) <> 'cancelled'
         LIMIT 1"
    );
    $stmt->execute([$userId, $productId]);

    return (bool) $stmt->fetchColumn();
}

function product_review_upload_path(?string $relativePath): ?string
{
    if ($relativePath === null || $relativePath === '') {
        return null;
    }

    $trimmedPath = ltrim(str_replace('\\', '/', $relativePath), '/');

    return $_SERVER['DOCUMENT_ROOT'] . "/ecommerce_sales_analysis/" . $trimmedPath;
}

function delete_review_image(?string $relativePath): void
{
    $absolutePath = product_review_upload_path($relativePath);

    if ($absolutePath !== null && is_file($absolutePath)) {
        @unlink($absolutePath);
    }
}

function upload_review_image(array $file, ?string &$errorMessage): ?string
{
    if (($file['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) {
        return null;
    }

    if (($file['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
        $errorMessage = 'Review photo upload failed. Please try again.';
        return null;
    }

    if (($file['size'] ?? 0) > 3 * 1024 * 1024) {
        $errorMessage = 'Review photo must be under 3MB.';
        return null;
    }

    $tmpName = $file['tmp_name'] ?? '';
    $detectedType = '';

    if (is_file($tmpName) && function_exists('finfo_open')) {
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        if ($finfo !== false) {
            $detectedType = (string) finfo_file($finfo, $tmpName);
            finfo_close($finfo);
        }
    }

    $allowedTypes = [
        'image/jpeg' => 'jpg',
        'image/png' => 'png',
        'image/webp' => 'webp',
    ];

    if (!isset($allowedTypes[$detectedType])) {
        $errorMessage = 'Only JPG, PNG, or WEBP review images are allowed.';
        return null;
    }

    $uploadDirectory = $_SERVER['DOCUMENT_ROOT'] . "/ecommerce_sales_analysis/assets/images/reviews/";

    if (!is_dir($uploadDirectory) && !mkdir($uploadDirectory, 0777, true) && !is_dir($uploadDirectory)) {
        $errorMessage = 'Could not prepare the review upload folder.';
        return null;
    }

    $fileName = uniqid('review_', true) . '.' . $allowedTypes[$detectedType];
    $destination = $uploadDirectory . $fileName;

    if (!move_uploaded_file($tmpName, $destination)) {
        $errorMessage = 'Could not save the uploaded review image.';
        return null;
    }

    return 'assets/images/reviews/' . $fileName;
}

function product_description_text(array $product): string
{
    $storedDescription = trim((string) ($product['description'] ?? ''));

    if ($storedDescription !== '') {
        return $storedDescription;
    }

    return $product['name']
        . ' brings together '
        . strtolower($product['category_name'])
        . ' styling for '
        . strtolower($product['gender_name'])
        . " wardrobes. It is a versatile pick for everyday wear, dress-up moments, and repeat use.";
}

$cartCount = isset($_SESSION['cart']) ? array_sum($_SESSION['cart']) : 0;
$userId = isset($_SESSION['user_id']) ? (int) $_SESSION['user_id'] : null;
$rawProductId = isset($_GET['id']) ? trim((string) $_GET['id']) : '';
$productId = ctype_digit($rawProductId) ? (int) $rawProductId : 0;

if (!$productId) {
    http_response_code(404);
    die("Product not found.");
}

$productStmt = $conn->prepare(
    "SELECT
        p.product_id,
        p.name,
        p.price,
        p.image_url,
        p.description,
        p.category_id,
        p.created_at,
        c.category_name,
        g.gender_name
     FROM products p
     JOIN categories c ON c.category_id = p.category_id
     JOIN genders g ON g.gender_id = p.gender_id
     WHERE p.product_id = ?
     LIMIT 1"
);
$productStmt->execute([$productId]);
$product = $productStmt->fetch(PDO::FETCH_ASSOC);

if (!$product) {
    http_response_code(404);
    die("Product not found.");
}

$flash = $_SESSION['product_review_flash'] ?? null;
unset($_SESSION['product_review_flash']);

$formState = [
    'rating' => '5',
    'title' => '',
    'review_text' => '',
    'remove_review_image' => false,
];
$formError = '';
$formSuccess = $flash['type'] ?? '' ? ($flash['type'] === 'success' ? $flash['message'] : '') : '';
$formMessageType = $flash['type'] ?? '';

$existingReview = null;
$hasPurchased = false;

if ($userId !== null) {
    $hasPurchased = product_review_has_purchase($conn, $userId, $productId);

    $myReviewStmt = $conn->prepare(
        "SELECT review_id, rating, title, review_text, review_image_url
         FROM product_reviews
         WHERE user_id = ?
           AND product_id = ?
         LIMIT 1"
    );
    $myReviewStmt->execute([$userId, $productId]);
    $existingReview = $myReviewStmt->fetch(PDO::FETCH_ASSOC) ?: null;

    if ($existingReview) {
        $formState = [
            'rating' => (string) $existingReview['rating'],
            'title' => (string) ($existingReview['title'] ?? ''),
            'review_text' => (string) $existingReview['review_text'],
            'remove_review_image' => false,
        ];
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_review'])) {
    $formMessageType = 'error';
    $formState = [
        'rating' => trim((string) ($_POST['rating'] ?? '')),
        'title' => trim((string) ($_POST['title'] ?? '')),
        'review_text' => trim((string) ($_POST['review_text'] ?? '')),
        'remove_review_image' => isset($_POST['remove_review_image']),
    ];

    if ($userId === null) {
        $formError = 'Please log in before submitting a review.';
    } elseif (!$hasPurchased) {
        $formError = 'Reviews are only available after you purchase this product.';
    } else {
        $rating = (int) $formState['rating'];
        $title = $formState['title'];
        $reviewText = $formState['review_text'];

        if ($rating < 1 || $rating > 5) {
            $formError = 'Please choose a rating between 1 and 5.';
        } elseif ($title !== '' && strlen($title) > 120) {
            $formError = 'Review title must stay under 120 characters.';
        } elseif (strlen($reviewText) < 12) {
            $formError = 'Please write at least a short review before submitting.';
        } else {
            $uploadError = null;
            $finalImagePath = $existingReview['review_image_url'] ?? null;
            $oldImageToDelete = null;

            if ($formState['remove_review_image'] && $finalImagePath) {
                $oldImageToDelete = $finalImagePath;
                $finalImagePath = null;
            }

            if (!empty($_FILES['review_image']['name'])) {
                $newImagePath = upload_review_image($_FILES['review_image'], $uploadError);

                if ($newImagePath === null && $uploadError !== null) {
                    $formError = $uploadError;
                } else {
                    if (!empty($existingReview['review_image_url'])) {
                        $oldImageToDelete = $existingReview['review_image_url'];
                    }
                    $finalImagePath = $newImagePath;
                }
            }

            if ($formError === '') {
                if ($existingReview) {
                    $saveStmt = $conn->prepare(
                        "UPDATE product_reviews
                         SET rating = ?, title = ?, review_text = ?, review_image_url = ?
                         WHERE review_id = ?"
                    );
                    $saveStmt->execute([
                        $rating,
                        $title !== '' ? $title : null,
                        $reviewText,
                        $finalImagePath,
                        $existingReview['review_id'],
                    ]);
                } else {
                    $saveStmt = $conn->prepare(
                        "INSERT INTO product_reviews
                            (product_id, user_id, rating, title, review_text, review_image_url)
                         VALUES (?, ?, ?, ?, ?, ?)"
                    );
                    $saveStmt->execute([
                        $productId,
                        $userId,
                        $rating,
                        $title !== '' ? $title : null,
                        $reviewText,
                        $finalImagePath,
                    ]);
                }

                if ($oldImageToDelete !== null && $oldImageToDelete !== $finalImagePath) {
                    delete_review_image($oldImageToDelete);
                }

                $_SESSION['product_review_flash'] = [
                    'type' => 'success',
                    'message' => $existingReview
                        ? 'Your review has been updated.'
                        : 'Thanks for sharing your review.',
                ];

                header("Location: /ecommerce_sales_analysis/php/products/product-details.php?id=" . $productId . "#reviews");
                exit;
            }
        }
    }
}

$summaryStmt = $conn->prepare(
    "SELECT COUNT(*) AS review_count, AVG(rating) AS average_rating
     FROM product_reviews
     WHERE product_id = ?"
);
$summaryStmt->execute([$productId]);
$summary = $summaryStmt->fetch(PDO::FETCH_ASSOC) ?: ['review_count' => 0, 'average_rating' => null];

$reviewsStmt = $conn->prepare(
    "SELECT
        pr.review_id,
        pr.rating,
        pr.title,
        pr.review_text,
        pr.review_image_url,
        pr.created_at,
        u.name AS reviewer_name
     FROM product_reviews pr
     JOIN users u ON u.user_id = pr.user_id
     WHERE pr.product_id = ?
     ORDER BY pr.updated_at DESC, pr.created_at DESC"
);
$reviewsStmt->execute([$productId]);
$reviews = $reviewsStmt->fetchAll(PDO::FETCH_ASSOC);

$relatedStmt = $conn->prepare(
    "SELECT product_id, name, price, image_url
     FROM products
     WHERE category_id = ?
       AND product_id <> ?
     ORDER BY created_at DESC
     LIMIT 4"
);
$relatedStmt->execute([$product['category_id'], $productId]);
$relatedProducts = $relatedStmt->fetchAll(PDO::FETCH_ASSOC);

$averageRating = $summary['average_rating'] !== null ? round((float) $summary['average_rating'], 1) : null;
$reviewCount = (int) ($summary['review_count'] ?? 0);
$currentPath = "/ecommerce_sales_analysis/php/products/product-details.php?id=" . $productId;
$productDescription = product_description_text($product);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($product['name']); ?> | FASHIONLY</title>
    <link rel="stylesheet" href="/ecommerce_sales_analysis/assets/css/product-details.css">
</head>
<body>

<div class="top-nav">
    <div class="nav-left">
        <a href="/ecommerce_sales_analysis/index.php" class="logo">
            FASHIONLY
        </a>
    </div>

    <div class="nav-right">
        <a href="/ecommerce_sales_analysis/index.php">Home</a>
        <a href="/ecommerce_sales_analysis/php/products/products.php">Products</a>
        <a href="#reviews">Reviews</a>
        <a href="#style-assistant-panel" data-assistant-open>AI Assistant</a>
        <a href="/ecommerce_sales_analysis/php/cart/cart.php">
            Cart (<?= $cartCount; ?>)
        </a>
    </div>
</div>

<main class="product-page">
    <a href="/ecommerce_sales_analysis/php/products/products.php" class="back-link">Back to products</a>

    <?php if ($formSuccess !== '' || $formError !== '' || ($flash && $flash['type'] === 'error')): ?>
        <div class="notice notice--<?= htmlspecialchars($formError !== '' ? 'error' : $formMessageType); ?>">
            <?= htmlspecialchars($formError !== '' ? $formError : ($formSuccess !== '' ? $formSuccess : (string) $flash['message'])); ?>
        </div>
    <?php endif; ?>

    <section class="product-hero">
        <div class="product-media">
            <img
                src="/ecommerce_sales_analysis/<?= htmlspecialchars($product['image_url']); ?>"
                alt="<?= htmlspecialchars($product['name']); ?>"
            >
        </div>

        <div class="product-summary">
            <div class="eyebrow">
                <span><?= htmlspecialchars($product['category_name']); ?></span>
                <span><?= htmlspecialchars($product['gender_name']); ?></span>
            </div>

            <h1><?= htmlspecialchars($product['name']); ?></h1>

            <div class="rating-row">
                <?php if ($averageRating !== null): ?>
                    <span class="stars"><?= product_review_stars((int) round($averageRating)); ?></span>
                    <span class="rating-copy"><?= number_format($averageRating, 1); ?>/5 from <?= $reviewCount; ?> review<?= $reviewCount === 1 ? '' : 's'; ?></span>
                <?php else: ?>
                    <span class="rating-copy">No reviews yet. Be the first verified buyer to share one.</span>
                <?php endif; ?>
            </div>

            <p class="price">Rs <?= number_format((float) $product['price'], 2); ?></p>

            <p class="description"><?= nl2br(htmlspecialchars($productDescription)); ?></p>

            <div class="info-grid">
                <article>
                    <strong>Why it works</strong>
                    <p>See the full item first, then add it to your cart when it feels right.</p>
                </article>
                <article>
                    <strong>Review access</strong>
                    <p>Only customers who purchased this product can upload a verified review photo.</p>
                </article>
            </div>

            <div class="product-actions">
                <form method="POST" action="/ecommerce_sales_analysis/php/cart/add_to_cart.php">
                    <input type="hidden" name="product_id" value="<?= (int) $product['product_id']; ?>">
                    <input type="hidden" name="redirect_to" value="<?= htmlspecialchars($currentPath); ?>">
                    <button type="submit" class="primary-btn">Add to Cart</button>
                </form>

                <a href="#reviews" class="secondary-btn">Read Reviews</a>
            </div>

            <ul class="service-points">
                <li>Verified-buyer reviews only</li>
                <li>Customer review photos supported</li>
                <li>Related products shown below</li>
            </ul>
        </div>
    </section>

    <section id="reviews" class="reviews-section">
        <div class="review-form-card">
            <div class="section-heading">
                <h2><?= $existingReview ? 'Update Your Review' : 'Write a Review'; ?></h2>
                <p>Share fit, comfort, quality, or styling tips to help the next buyer.</p>
            </div>

            <?php if ($userId === null): ?>
                <div class="review-state">
                    <p>Log in first, then you can leave a review after purchasing this product.</p>
                    <a href="/ecommerce_sales_analysis/php/auth/login.php" class="secondary-btn">Log In</a>
                </div>
            <?php elseif (!$hasPurchased): ?>
                <div class="review-state">
                    <p>Purchase this product once and the review form will unlock for you here.</p>
                </div>
            <?php else: ?>
                <div class="review-state review-state--success">
                    <p>You purchased this product, so your review will show as a verified buyer review.</p>
                </div>

                <form method="POST" enctype="multipart/form-data" class="review-form">
                    <div class="form-row">
                        <label for="rating">Rating</label>
                        <select id="rating" name="rating" required>
                            <?php for ($rating = 5; $rating >= 1; $rating--): ?>
                                <option value="<?= $rating; ?>" <?= $formState['rating'] === (string) $rating ? 'selected' : ''; ?>>
                                    <?= $rating; ?> out of 5
                                </option>
                            <?php endfor; ?>
                        </select>
                    </div>

                    <div class="form-row">
                        <label for="title">Short title</label>
                        <input
                            id="title"
                            type="text"
                            name="title"
                            maxlength="120"
                            value="<?= htmlspecialchars($formState['title']); ?>"
                            placeholder="Example: Great fit and good fabric"
                        >
                    </div>

                    <div class="form-row">
                        <label for="review_text">Your review</label>
                        <textarea
                            id="review_text"
                            name="review_text"
                            rows="6"
                            required
                            placeholder="Tell shoppers what this product looked like in person, how it felt, and when you wore it."
                        ><?= htmlspecialchars($formState['review_text']); ?></textarea>
                    </div>

                    <div class="form-row">
                        <label for="review_image">Upload a review photo</label>
                        <input id="review_image" type="file" name="review_image" accept=".jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp">
                    </div>

                    <?php if (!empty($existingReview['review_image_url'])): ?>
                        <div class="current-review-photo">
                            <img
                                src="/ecommerce_sales_analysis/<?= htmlspecialchars($existingReview['review_image_url']); ?>"
                                alt="Current review photo"
                            >
                            <label class="checkbox-row">
                                <input type="checkbox" name="remove_review_image" value="1" <?= $formState['remove_review_image'] ? 'checked' : ''; ?>>
                                Remove current photo
                            </label>
                        </div>
                    <?php endif; ?>

                    <button type="submit" name="submit_review" class="primary-btn">
                        <?= $existingReview ? 'Update Review' : 'Submit Review'; ?>
                    </button>
                </form>
            <?php endif; ?>
        </div>

        <div class="review-list-card">
            <div class="section-heading">
                <h2>Customer Reviews</h2>
                <p><?= $reviewCount; ?> verified review<?= $reviewCount === 1 ? '' : 's'; ?> on this product.</p>
            </div>

            <?php if (empty($reviews)): ?>
                <div class="empty-reviews">
                    <p>No reviews yet. The first buyer who shares feedback will appear here.</p>
                </div>
            <?php else: ?>
                <div class="review-list">
                    <?php foreach ($reviews as $review): ?>
                        <article class="review-card">
                            <div class="review-card__top">
                                <div>
                                    <strong><?= htmlspecialchars($review['reviewer_name']); ?></strong>
                                    <span class="verified-badge">Verified buyer</span>
                                </div>
                                <span class="review-date"><?= date("d M Y", strtotime($review['created_at'])); ?></span>
                            </div>

                            <div class="review-stars"><?= product_review_stars((int) $review['rating']); ?></div>

                            <?php if (!empty($review['title'])): ?>
                                <h3><?= htmlspecialchars($review['title']); ?></h3>
                            <?php endif; ?>

                            <p><?= nl2br(htmlspecialchars($review['review_text'])); ?></p>

                            <?php if (!empty($review['review_image_url'])): ?>
                                <div class="review-photo">
                                    <img
                                        src="/ecommerce_sales_analysis/<?= htmlspecialchars($review['review_image_url']); ?>"
                                        alt="Customer review photo for <?= htmlspecialchars($product['name']); ?>"
                                    >
                                </div>
                            <?php endif; ?>
                        </article>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </section>

    <?php if (!empty($relatedProducts)): ?>
        <section class="related-section">
            <div class="section-heading">
                <h2>Related Picks</h2>
                <p>More items from the same category you may want to compare.</p>
            </div>

            <div class="related-grid">
                <?php foreach ($relatedProducts as $related): ?>
                    <article class="related-card">
                        <a href="/ecommerce_sales_analysis/php/products/product-details.php?id=<?= (int) $related['product_id']; ?>" class="related-card__image">
                            <img
                                src="/ecommerce_sales_analysis/<?= htmlspecialchars($related['image_url']); ?>"
                                alt="<?= htmlspecialchars($related['name']); ?>"
                            >
                        </a>

                        <div class="related-card__body">
                            <a href="/ecommerce_sales_analysis/php/products/product-details.php?id=<?= (int) $related['product_id']; ?>" class="related-card__title">
                                <?= htmlspecialchars($related['name']); ?>
                            </a>
                            <strong>Rs <?= number_format((float) $related['price'], 2); ?></strong>

                            <div class="related-card__actions">
                                <a href="/ecommerce_sales_analysis/php/products/product-details.php?id=<?= (int) $related['product_id']; ?>" class="secondary-btn secondary-btn--small">
                                    View Details
                                </a>

                                <form method="POST" action="/ecommerce_sales_analysis/php/cart/add_to_cart.php">
                                    <input type="hidden" name="product_id" value="<?= (int) $related['product_id']; ?>">
                                    <input type="hidden" name="redirect_to" value="<?= htmlspecialchars($currentPath); ?>">
                                    <button type="submit" class="primary-btn primary-btn--small">Add to Cart</button>
                                </form>
                            </div>
                        </div>
                    </article>
                <?php endforeach; ?>
            </div>
        </section>
    <?php endif; ?>
</main>

<?php include __DIR__ . "/../../includes/assistant-widget.php"; ?>

</body>
</html>
