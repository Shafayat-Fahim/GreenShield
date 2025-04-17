document.addEventListener("DOMContentLoaded", () => {
    // Tabs functionality
    const tabs = document.querySelectorAll(".tab");
    const contents = document.querySelectorAll(".tab-content");
  
    tabs.forEach(tab => {
      tab.addEventListener("click", () => {
        tabs.forEach(t => t.classList.remove("active"));
        contents.forEach(c => c.classList.remove("active"));
  
        tab.classList.add("active");
        document.getElementById(tab.dataset.tab).classList.add("active");
      });
    });
  
    // Sidebar toggle
    const toggleBtn = document.getElementById("sidebar-toggle");
    const sidebar = document.getElementById("sidebar");
    const mainContent = document.getElementById("main-content");
    const toggleIcon = document.getElementById("toggle-icon");
  
    toggleBtn.addEventListener("click", () => {
      sidebar.classList.toggle("collapsed");
      mainContent.classList.toggle("expanded");
      toggleIcon.classList.toggle("fa-chevron-left");
      toggleIcon.classList.toggle("fa-chevron-right");
    });
  
    // Charts
    new Chart(document.getElementById("lossByCategoryChart"), {
      type: "doughnut",
      data: {
        labels: ["Storage Conditions", "Transportation Damage", "Quality Rejection", "Expiration"],
        datasets: [{
          data: [72, 38, 25, 21],
          backgroundColor: ["#e53935", "#ff9800", "#4caf50", "#2196f3"]
        }]
      },
      options: {
        responsive: true,
        plugins: {
          legend: {
            display: false
          }
        }
      }
    });
  
    new Chart(document.getElementById("lossTrendChart"), {
      type: "line",
      data: {
        labels: ["May", "Jun", "Jul", "Aug", "Sep", "Oct", "Nov", "Dec", "Jan", "Feb", "Mar", "Apr"],
        datasets: [{
          label: "Total Loss (kg)",
          data: [140, 120, 160, 130, 150, 170, 140, 155, 145, 130, 160, 156],
          backgroundColor: "rgba(76, 175, 80, 0.2)",
          borderColor: "#4caf50",
          tension: 0.3,
          fill: true
        }]
      },
      options: {
        responsive: true,
        plugins: {
          legend: {
            position: 'top'
          }
        }
      }
    });
  
    new Chart(document.getElementById("topLossCategoriesChart"), {
      type: "bar",
      data: {
        labels: ["Storage", "Transport", "Rejection", "Expiration"],
        datasets: [{
          label: "Loss (kg)",
          data: [72, 38, 25, 21],
          backgroundColor: ["#e53935", "#ff9800", "#4caf50", "#2196f3"]
        }]
      },
      options: {
        responsive: true,
        plugins: {
          legend: { display: false }
        }
      }
    });
  });
  