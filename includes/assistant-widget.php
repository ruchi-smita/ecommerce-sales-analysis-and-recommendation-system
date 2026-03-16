<?php
$assistantBaseUrl = $assistantBaseUrl ?? '/ecommerce_sales_analysis';
$assistantApiUrl = $assistantApiUrl ?? $assistantBaseUrl . '/php/api/chat-assistant.php';
$assistantPrompts = [
    'Party look for women',
    'Traditional style under 2500',
    'Casual daily wear for men',
    'Show me footwear for party',
];
?>
<link rel="stylesheet" href="<?= htmlspecialchars($assistantBaseUrl); ?>/assets/css/assistant.css">

<div
    class="style-assistant"
    data-style-assistant
    data-api-url="<?= htmlspecialchars($assistantApiUrl); ?>"
    data-base-url="<?= htmlspecialchars($assistantBaseUrl); ?>"
>
    <section id="style-assistant-panel" class="assistant-panel" hidden>
        <div class="assistant-header">
            <div>
                <strong>AI Assistant</strong>
                <p>Click a suggestion or type what you want, and I will help you shop faster.</p>
            </div>

            <button type="button" class="assistant-close" aria-label="Close assistant">
                x
            </button>
        </div>

        <div class="assistant-messages" aria-live="polite">
            <div class="assistant-message assistant-message--bot">
                <p>Start chatting whenever you are ready. Try "party wear for women" or "traditional look under 2000".</p>
            </div>
        </div>

        <div class="assistant-suggestions">
            <?php foreach ($assistantPrompts as $prompt): ?>
                <button type="button" class="assistant-chip" data-assistant-prompt="<?= htmlspecialchars($prompt); ?>">
                    <?= htmlspecialchars($prompt); ?>
                </button>
            <?php endforeach; ?>
        </div>

        <form class="assistant-form">
            <label class="assistant-label" for="assistant-message-input">Shopping request</label>
            <input
                id="assistant-message-input"
                class="assistant-input"
                type="text"
                name="message"
                placeholder="I need a traditional look for women"
                autocomplete="off"
                required
            >
            <button type="submit" class="assistant-send">Send</button>
        </form>
    </section>

    <button
        type="button"
        class="assistant-bubble"
        aria-controls="style-assistant-panel"
        aria-expanded="false"
        aria-label="Open AI assistant"
    >
        AI
    </button>
</div>

<script src="<?= htmlspecialchars($assistantBaseUrl); ?>/assets/js/assistant.js" defer></script>
