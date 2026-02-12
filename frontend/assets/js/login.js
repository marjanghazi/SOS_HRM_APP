// login.js
async function login() {
  const erp_number = document.getElementById("erp_number").value.trim();
  const password = document.getElementById("password").value;
  const errorEl = document.getElementById("error");

  errorEl.innerText = "";

  // Validate erp_number
  if (!/^\d{6}$/.test(erp_number)) {
    errorEl.innerText = "erp_number must be 6 digits";
    return;
  }

  // Send login request with custom token
  const res = await apiRequest("login.php", "POST", {
    erp_number,
    password,
    token: APP_TOKEN,
  });

  if (res.success && res.token) {
    // Save JWT in localStorage
    localStorage.setItem("token", res.token);
    // Also save user data in localStorage for profile page
    localStorage.setItem("userData", JSON.stringify(res.data));
    window.location.href = "profile.html";
  } else {
    errorEl.innerText = res.error || "Login failed";
  }
}
