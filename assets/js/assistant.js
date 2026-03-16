(function () {
    const root = document.querySelector("[data-style-assistant]");

    if (!root) {
        return;
    }

    const bubble = root.querySelector(".assistant-bubble");
    const panel = root.querySelector(".assistant-panel");
    const closeButton = root.querySelector(".assistant-close");
    const form = root.querySelector(".assistant-form");
    const input = root.querySelector(".assistant-input");
    const messages = root.querySelector(".assistant-messages");
    const suggestions = root.querySelector(".assistant-suggestions");
    const externalTriggers = document.querySelectorAll("[data-assistant-open]");
    const apiUrl = root.dataset.apiUrl;
    const baseUrl = root.dataset.baseUrl || "";

    function setOpenState(isOpen) {
        panel.hidden = !isOpen;
        bubble.setAttribute("aria-expanded", String(isOpen));

        if (isOpen) {
            input.focus();
        }
    }

    function escapeHtml(value) {
        return String(value)
            .replace(/&/g, "&amp;")
            .replace(/</g, "&lt;")
            .replace(/>/g, "&gt;")
            .replace(/"/g, "&quot;")
            .replace(/'/g, "&#39;");
    }

    function formatCurrency(value) {
        return new Intl.NumberFormat("en-IN", {
            style: "currency",
            currency: "INR",
            maximumFractionDigits: 0
        }).format(value);
    }

    function scrollMessagesToBottom() {
        messages.scrollTop = messages.scrollHeight;
    }

    function appendMessage(role, text) {
        const message = document.createElement("div");
        message.className = `assistant-message assistant-message--${role}`;
        message.innerHTML = `<p>${escapeHtml(text)}</p>`;
        messages.appendChild(message);
        scrollMessagesToBottom();
        return message;
    }

    function renderProducts(container, products) {
        if (!Array.isArray(products) || products.length === 0) {
            return;
        }

        const list = document.createElement("div");
        list.className = "assistant-products";

        const items = products.map((product) => {
            const imageUrl = `${baseUrl}/${String(product.image_url || "").replace(/^\/+/, "")}`;
            const detailUrl = `${baseUrl}/php/products/product-details.php?id=${encodeURIComponent(product.product_id)}`;
            return `
                <article class="assistant-product">
                    <a href="${escapeHtml(detailUrl)}" class="assistant-product-image">
                        <img src="${escapeHtml(imageUrl)}" alt="${escapeHtml(product.name)}">
                    </a>
                    <div>
                        <a href="${escapeHtml(detailUrl)}" class="assistant-product-title">
                            <h4>${escapeHtml(product.name)}</h4>
                        </a>
                        <p class="assistant-meta">${escapeHtml(product.category_name)} | ${escapeHtml(product.gender_name)}</p>
                        <p class="assistant-price">${escapeHtml(formatCurrency(product.price))}</p>
                        <div class="assistant-product-actions">
                            <a href="${escapeHtml(detailUrl)}" class="assistant-product-link">View details</a>
                            <form method="POST" action="${escapeHtml(baseUrl)}/php/cart/add_to_cart.php">
                                <input type="hidden" name="product_id" value="${escapeHtml(product.product_id)}">
                                <input type="hidden" name="redirect_to" value="${escapeHtml(detailUrl)}">
                                <button type="submit">Add to cart</button>
                            </form>
                        </div>
                    </div>
                </article>
            `;
        }).join("");

        list.innerHTML = items;
        container.appendChild(list);
        scrollMessagesToBottom();
    }

    function renderSuggestions(nextSuggestions) {
        if (!Array.isArray(nextSuggestions) || nextSuggestions.length === 0) {
            return;
        }

        suggestions.innerHTML = nextSuggestions.map((label) => (
            `<button type="button" class="assistant-chip" data-assistant-prompt="${escapeHtml(label)}">${escapeHtml(label)}</button>`
        )).join("");
    }

    async function sendMessage(messageText) {
        const cleanedMessage = messageText.trim();
        if (!cleanedMessage) {
            return;
        }

        appendMessage("user", cleanedMessage);
        const loadingMessage = appendMessage("status", "Looking for matching products...");
        input.value = "";

        try {
            const response = await fetch(apiUrl, {
                method: "POST",
                headers: {
                    "Content-Type": "application/json"
                },
                body: JSON.stringify({ message: cleanedMessage })
            });

            const payload = await response.json();
            loadingMessage.remove();

            const botMessage = appendMessage("bot", payload.reply || "Here are a few suggestions from the catalog.");
            renderProducts(botMessage, payload.products || []);
            renderSuggestions(payload.suggestions || []);
        } catch (error) {
            loadingMessage.remove();
            appendMessage("bot", "The assistant could not fetch recommendations right now. Please try again.");
        }
    }

    bubble.addEventListener("click", function () {
        setOpenState(panel.hidden);
    });

    closeButton.addEventListener("click", function () {
        setOpenState(false);
    });

    externalTriggers.forEach(function (trigger) {
        trigger.addEventListener("click", function (event) {
            event.preventDefault();
            setOpenState(true);
        });
    });

    form.addEventListener("submit", function (event) {
        event.preventDefault();
        sendMessage(input.value);
    });

    suggestions.addEventListener("click", function (event) {
        const chip = event.target.closest("[data-assistant-prompt]");
        if (!chip) {
            return;
        }

        sendMessage(chip.dataset.assistantPrompt || "");
    });
})();
