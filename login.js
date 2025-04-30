function switchToLogin() {
    document.getElementById("login-tab").classList.add("active");
    document.getElementById("signup-tab").classList.remove("active");
  
    document.getElementById("login-form").classList.add("active");
    document.getElementById("signup-form").classList.remove("active");
  }
  
  function switchToSignup() {
    document.getElementById("signup-tab").classList.add("active");
    document.getElementById("login-tab").classList.remove("active");
  
    document.getElementById("signup-form").classList.add("active");
    document.getElementById("login-form").classList.remove("active");
  }
  
  document.getElementById("loginForm").addEventListener("submit", function (e) {
    e.preventDefault();
    alert("Logged in successfully!");
  });
  
  document.getElementById("signupForm").addEventListener("submit", function (e) {
    e.preventDefault();
    const password = document.getElementById("signup-password").value;
    const confirm = document.getElementById("signup-confirm").value;
  
    if (password !== confirm) {
      alert("Passwords do not match!");
      return;
    }
  
    alert("Signed up successfully!");
  });
  