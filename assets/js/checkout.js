document.addEventListener("DOMContentLoaded", () => {
    const form = document.getElementById("checkout-form");
    if (!form) return;

    const phoneRegex = /^[6-9]\d{9}$/;     // Indian phone numbers
    const pincodeRegex = /^\d{6}$/;        // Indian pincode

    form.addEventListener("submit", (e) => {
        const fullName = document.getElementById("full_name").value.trim();
        const phone = document.getElementById("phone").value.trim();
        const address = document.getElementById("address").value.trim();
        const city = document.getElementById("city").value.trim();
        const state = document.getElementById("state").value.trim();
        const pincode = document.getElementById("pincode").value.trim();

        const paymentMethod = form.querySelector(
            'input[name="payment_method"]:checked'
        );

        // HARD validation (no mercy)
        if (!fullName || !phone || !address || !city || !state || !pincode) {
            alert("All fields are required.");
            e.preventDefault();
            return;
        }

        if (!phoneRegex.test(phone)) {
            alert("Enter a valid 10-digit phone number.");
            e.preventDefault();
            return;
        }

        if (!pincodeRegex.test(pincode)) {
            alert("Enter a valid 6-digit pincode.");
            e.preventDefault();
            return;
        }

        if (!paymentMethod) {
            alert("Please select a payment method.");
            e.preventDefault();
            return;
        }

        // Prevent double submission
        const submitBtn = form.querySelector("button[type='submit']");
        if (submitBtn) {
            submitBtn.disabled = true;
            submitBtn.textContent = "Placing Order...";
        }
    });
});
