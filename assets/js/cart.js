document.addEventListener("DOMContentLoaded", () => {
    const cartContainer = document.querySelector(".cart-container");
    const grandTotalEl = document.getElementById("grand-total");

    if (!cartContainer) return;

    cartContainer.addEventListener("click", (e) => {
        const row = e.target.closest(".cart-row");
        if (!row) return;

        const productId = row.dataset.productId;
        const price = parseFloat(row.dataset.price);
        const qtyEl = row.querySelector(".qty");

        // INCREMENT
        if (e.target.classList.contains("inc")) {
            updateQty(productId, "inc", qtyEl, price, row);
        }

        // DECREMENT
        if (e.target.classList.contains("dec")) {
            updateQty(productId, "dec", qtyEl, price, row);
        }

        // REMOVE
        if (e.target.classList.contains("remove-btn")) {
            removeItem(productId, row);
        }
    });

    function updateQty(id, action, qtyEl, price, row) {
        fetch(`update_qty.php?id=${id}&action=${action}`)
            .then(() => {
                let qty = parseInt(qtyEl.textContent);

                if (action === "inc") qty++;
                if (action === "dec" && qty > 1) qty--;

                qtyEl.textContent = qty;

                const itemTotalEl = row.querySelector("span:last-of-type");
                itemTotalEl.textContent = `₹${(qty * price).toFixed(2)}`;

                recalculateGrandTotal();
            })
            .catch(err => console.error("Qty update failed", err));
    }

    function removeItem(id, row) {
        fetch(`remove_from_cart.php?id=${id}`)
            .then(() => {
                row.remove();
                recalculateGrandTotal();

                if (document.querySelectorAll(".cart-row").length === 0) {
                    location.reload(); // cart empty → reload
                }
            })
            .catch(err => console.error("Remove failed", err));
    }

    function recalculateGrandTotal() {
        let total = 0;

        document.querySelectorAll(".cart-row").forEach(row => {
            const price = parseFloat(row.dataset.price);
            const qty = parseInt(row.querySelector(".qty").textContent);
            total += price * qty;
        });

        grandTotalEl.textContent = `₹${total.toFixed(2)}`;
    }
});
