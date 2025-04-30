// Retailer.js
document.addEventListener("DOMContentLoaded", () => {
    const orderButton = document.querySelector(".action-bar .button");
    if (orderButton && orderButton.textContent.includes("Place New Order")) {
        orderButton.addEventListener("click", () => {
            alert("Redirecting to order form...");
        });
    }

    document.querySelectorAll("button.button").forEach(btn => {
        if (btn.textContent === "View Details") {
            btn.addEventListener("click", () => {
                alert("Viewing order details...");
            });
        }

        if (btn.textContent === "Track") {
            btn.addEventListener("click", () => {
                alert("Tracking delivery status...");
            });
        }

        if (btn.textContent === "Receipt") {
            btn.addEventListener("click", () => {
                alert("Opening receipt...");
            });
        }
    });

    // Optional: Add hover effect to rows
    document.querySelectorAll("tbody tr").forEach(row => {
        row.addEventListener("mouseenter", () => {
            row.style.backgroundColor = "#f9f9f9";
        });
        row.addEventListener("mouseleave", () => {
            row.style.backgroundColor = "";
        });
    });
});
