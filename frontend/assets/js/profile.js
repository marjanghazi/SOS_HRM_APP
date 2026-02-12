// profile.js
function loadProfile() {
  const userData = localStorage.getItem("userData");
  const profileEl = document.getElementById("profile");

  if (!userData) {
    // No user data → redirect to login
    window.location.href = "index.html";
    return;
  }

  const user = JSON.parse(userData);

  profileEl.innerHTML = `
        <strong>Name:</strong> ${user.name} <br>
        <strong>Email:</strong> ${user.email} <br>
        <strong>erp_number:</strong> ${user.erp_number}
    `;
}

function logout() {
  localStorage.removeItem("token");
  localStorage.removeItem("userData");
  window.location.href = "index.html";
}

// Load profile on page load
loadProfile();
