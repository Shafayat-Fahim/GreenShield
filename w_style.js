// Retailer Modals
const addRetailerBtn = document.getElementById("addRetailerBtn");
const addRetailerModal = document.getElementById("addRetailerModal");
const closeAddRetailer = document.getElementById("closeAddRetailer");

const editRetailerModal = document.getElementById("editRetailerModal");
const closeEditRetailer = document.getElementById("closeEditRetailer");
const editRetailerBtns = document.querySelectorAll(".editRetailerBtn");

// Retailer Modal Handlers
if (addRetailerBtn) {
    addRetailerBtn.onclick = () => addRetailerModal.style.display = "block";
}
if (closeAddRetailer) {
    closeAddRetailer.onclick = () => addRetailerModal.style.display = "none";
}
if (editRetailerBtns) {
    editRetailerBtns.forEach(btn => {
        btn.onclick = () => {
            document.getElementById("editRetailerId").value = btn.dataset.id;
            document.getElementById("editRetailerName").value = btn.dataset.name;
            document.getElementById("editRetailerLocation").value = btn.dataset.location;
            document.getElementById("editRetailerContact").value = btn.dataset.contact;
            document.getElementById("editRetailerOrders").value = btn.dataset.orders;
            editRetailerModal.style.display = "block";
        };
    });
}
if (closeEditRetailer) {
    closeEditRetailer.onclick = () => editRetailerModal.style.display = "none";
}

// Add to window.onclick handler
if (event.target == addRetailerModal) addRetailerModal.style.display = "none";
if (event.target == editRetailerModal) editRetailerModal.style.display = "none";