document.addEventListener("DOMContentLoaded", () => {
    const cartRows = document.querySelectorAll(".cart-row");

    cartRows.forEach(row => {
        const incBtn = row.querySelector(".inc");
        const decBtn = row.querySelector(".dec");
        const removeBtn = row.querySelector(".remove-btn");

        incBtn.addEventListener("click", () => changeQty(row, 1));
        decBtn.addEventListener("click", () => changeQty(row, -1));
        removeBtn.addEventListener("click", () => removeItem(row));
    });
});

/* ==============================
   CHANGE QUANTITY
================================ */
function changeQty(row, delta) {
    const qtyEl = row.querySelector(".qty");
    let qty = parseInt(qtyEl.innerText);

    if (qty + delta < 1) return; // prevent 0 or negative

    qty += delta;
    qtyEl.innerText = qty;

    updateItemTotal(row, qty);
    updateGrandTotal();

    syncQtyWithServer(row.dataset.productId, qty);
}

/* ==============================
   UPDATE ITEM TOTAL
================================ */
function updateItemTotal(row, qty) {
    const price = parseFloat(row.dataset.price);
    const totalEl = row.querySelector(".item-total");

    const total = price * qty;
    totalEl.innerText = "₹" + total.toFixed(2);
}

/* ==============================
   UPDATE GRAND TOTAL
================================ */
function updateGrandTotal() {
    let grandTotal = 0;

    document.querySelectorAll(".cart-row").forEach(row => {
        const qty = parseInt(row.querySelector(".qty").innerText);
        const price = parseFloat(row.dataset.price);
        grandTotal += qty * price;
    });

    document.getElementById("grand-total").innerText =
        "₹" + grandTotal.toFixed(2);
}

/* ==============================
   SYNC WITH SERVER (AJAX)
================================ */
function syncQtyWithServer(productId, qty) {
    fetch("update_cart.php", {
        method: "POST",
        headers: {
            "Content-Type": "application/json"
        },
        body: JSON.stringify({
            product_id: productId,
            quantity: qty
        })
    })
    .then(res => res.json())
    .then(data => {
        if (!data.success) {
            console.error("Cart sync failed");
        }
    })
    .catch(err => console.error("AJAX error:", err));
}

/* ==============================
   REMOVE ITEM
================================ */
function removeItem(row) {
    const productId = row.dataset.productId;

    fetch("remove_from_cart.php", {
        method: "POST",
        headers: {
            "Content-Type": "application/json"
        },
        body: JSON.stringify({ product_id: productId })
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            row.remove();
            updateGrandTotal();

            // If cart becomes empty
            if (document.querySelectorAll(".cart-row").length === 0) {
                location.reload();
            }
        }
    })
    .catch(err => console.error("Remove failed:", err));
}
