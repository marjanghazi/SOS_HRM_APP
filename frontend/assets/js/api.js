// api.js
const API_URL = "http://localhost/project-root/backend/api";

// Your custom app token (must match the one in PHP)
const APP_TOKEN = "abc123"; // <-- Change this to your production token

function getToken() {
  return localStorage.getItem("token");
}

// Generic API request function
async function apiRequest(endpoint, method = "GET", body = null) {
  const headers = { "Content-Type": "application/json" };

  // Send JWT in Authorization if available
  if (getToken()) {
    headers["Authorization"] = "Bearer " + getToken();
  }

  const response = await fetch(`${API_URL}/${endpoint}`, {
    method,
    headers,
    body: body ? JSON.stringify(body) : null,
  });

  return response.json();
}
