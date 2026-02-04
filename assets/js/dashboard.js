console.log("dashboard.js loaded");


// 1️⃣ Ask PHP for sales data
fetch("../analytics/sales_report.php")
    .then(response => response.json())
    .then(data => {

        // 2️⃣ Extract labels (dates) and values (revenue)
        const labels = data.map(item => item.date);
        const revenue = data.map(item => item.revenue);

        // 3️⃣ Get canvas context
        const ctx = document.getElementById("salesChart").getContext("2d");

        // 4️⃣ Create chart
        new Chart(ctx, {
            type: "line",   // line chart
            data: {
                labels: labels,
                datasets: [{
                    label: "Daily Revenue",
                    data: revenue,
                    borderWidth: 2,
                    fill: false
                }]
            },
            options: {
                responsive: true,
                scales: {
                    y: {
                        beginAtZero: true,
                    }
                }
            }
        });

    })
    .catch(error => {
        console.error("Error loading sales data:", error);
    });
// ---------- CATEGORY CHART ----------

if (typeof categorySalesData !== "undefined" && categorySalesData.length > 0) {

    const categoryLabels = categorySalesData.map(item => item.category);
    const categoryRevenue = categorySalesData.map(item => item.revenue);

    const ctxCategory = document
        .getElementById("categoryChart")
        .getContext("2d");

    new Chart(ctxCategory, {
        type: "doughnut", // change to "bar" if you want
        data: {
            labels: categoryLabels,
            datasets: [{
                label: "Category Revenue",
                data: categoryRevenue
            }]
        },
        options: {
            responsive: true,
        }
    });

} else {
    console.warn("No category data available for chart");
}
// ---------- CUSTOMER INSIGHTS CHART ----------

if (typeof customerInsightsData !== "undefined" && customerInsightsData.length > 0) {

    // Take top 5 customers only (dashboard rule)
    const topCustomers = customerInsightsData.slice(0, 5);

    const customerNames = topCustomers.map(c => c.name);
    const customerSpent = topCustomers.map(c => c.total_spent);

    const ctxCustomer = document
        .getElementById("customerChart")
        .getContext("2d");

    new Chart(ctxCustomer, {
        type: "bar",
        data: {
            labels: customerNames,
            datasets: [{
                label: "Total Spent (₹)",
                data: customerSpent,
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            scales: {
                y: {
                    beginAtZero: true,
                    
                }
            }
        }
    });

} else {
    console.warn("No customer insights data available");
}
