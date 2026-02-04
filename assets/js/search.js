document.addEventListener("DOMContentLoaded", () => {
    const searchInput = document.querySelector('input[name="q"]');
    const resultsContainer = document.querySelector(".products-grid");

    if (!searchInput || !resultsContainer) return;

    let debounceTimer = null;

    searchInput.addEventListener("input", () => {
        clearTimeout(debounceTimer);

        debounceTimer = setTimeout(() => {
            const query = searchInput.value.trim();

            fetchResults(query);
        }, 400); // debounce delay
    });

    function fetchResults(query) {
        const url = `search.php?q=${encodeURIComponent(query)}`;

        fetch(url, {
            headers: {
                "X-Requested-With": "XMLHttpRequest"
            }
        })
            .then(response => response.text())
            .then(html => {
                // Parse returned HTML
                const parser = new DOMParser();
                const doc = parser.parseFromString(html, "text/html");

                const newResults = doc.querySelector(".products-grid");

                if (newResults) {
                    resultsContainer.innerHTML = newResults.innerHTML;
                } else {
                    resultsContainer.innerHTML = "<p>No products found.</p>";
                }
            })
            .catch(error => {
                console.error("Search failed:", error);
            });
    }
});
