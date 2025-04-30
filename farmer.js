
// Reusable alert functions
document.addEventListener("DOMContentLoaded", function () {
// Buttons on Farmer Dashboard
const recordHarvestBtn = document.querySelector(".action-bar .button");
if (recordHarvestBtn) {
recordHarvestBtn.addEventListener("click", () => {
    alert("Redirecting to harvest recording form...");
});
}

// View Details buttons
document.querySelectorAll("button.button").forEach(btn => {
if (btn.textContent.includes("View Details")) {
    btn.addEventListener("click", () => {
        alert("Opening harvest/order details...");
    });
}
if (btn.textContent.includes("Track")) {
    btn.addEventListener("click", () => {
        alert("Tracking shipment...");
    });
}
if (btn.textContent.includes("Receipt")) {
    btn.addEventListener("click", () => {
        alert("Showing receipt...");
    });
}
});

// Place New Order button (Retailer Dashboard)
const placeOrderBtn = document.querySelector(".action-bar .button");
if (placeOrderBtn && placeOrderBtn.textContent.includes("Place New Order")) {
placeOrderBtn.addEventListener("click", () => {
    alert("Redirecting to order form...");
});
}

// Optional: Row hover highlight
document.querySelectorAll("tbody tr").forEach(row => {
row.addEventListener("mouseenter", () => {
    row.style.backgroundColor = "#f1f1f1";
});
row.addEventListener("mouseleave", () => {
    row.style.backgroundColor = "";
});
});
});

