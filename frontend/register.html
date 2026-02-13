<!DOCTYPE html>
<html>
<head>
<title>Register - Dummy Data</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

<div class="container mt-5">
  <div class="card p-4 shadow">
    <h3 class="mb-3">Add Dummy Employees</h3>

    <button onclick="addDummyData()" class="btn btn-primary w-100 mb-3">Add Dummy Data</button>

    <p id="message" class="mt-2 text-success"></p>
  </div>
</div>

<script>
// Dummy users array
const dummyUsers = [
  {
    name: 'Fahad Ahmad Sultan',
    status: 'Permanent',
    designation: 'Software Implementation Officer (FMS)',
    designation_code: '102825',
    erp_number: '102825',
    appointment_date: '2024-12-02',
    current_posting: 'Cash in Transit (CIT) - Multan',
    last_posting: 'Cash in Transit (CIT) - Multan',
    line_manager: 'Zahid Ashraf',
    segment_head: 'Azeem Javed',
    segment: 'IT Operations',
    segment_id: 501,
    shift: 'General Shift',
    current_salary: 45000,
    email: 'fahad.sultan@company.com',
    phone: '03011234567',
    emergency_contact: '03011234567',
    gender: 'male',
    password: '123456',
    profile_image: 'assets/men.jpg'
  },
  {
    name: 'Areeba Khan',
    status: 'Permanent',
    designation: 'HR Executive',
    designation_code: '102830',
    erp_number: '102830',
    appointment_date: '2023-06-15',
    current_posting: 'HR Department - Lahore',
    last_posting: 'HR Department - Lahore',
    line_manager: 'Samiya Riaz',
    segment_head: 'Zain Ali',
    segment: 'Human Resources',
    segment_id: 502,
    shift: 'Morning Shift',
    current_salary: 38000,
    email: 'areeba.khan@company.com',
    phone: '03021234568',
    emergency_contact: '03021234568',
    gender: 'female',
    password: '123456',
    profile_image: 'assets/women.jpg'
  },
  {
    name: 'Bilal Ahmed',
    status: 'Contract',
    designation: 'Accounts Assistant',
    designation_code: '102840',
    erp_number: '102840',
    appointment_date: '2022-11-01',
    current_posting: 'Accounts - Karachi',
    last_posting: 'Accounts - Karachi',
    line_manager: 'Shoaib Iqbal',
    segment_head: 'Naveed Shah',
    segment: 'Finance',
    segment_id: 503,
    shift: 'Evening Shift',
    current_salary: 25000,
    email: 'bilal.ahmed@company.com',
    phone: '03031234569',
    emergency_contact: '03031234569',
    gender: 'male',
    password: '123456',
    profile_image: 'assets/men.jpg'
  }
  // Add more dummy users here
];

// Add dummy users
async function addDummyData() {
  const message = document.getElementById('message');
  message.textContent = 'Adding dummy users...';

  for (const user of dummyUsers) {
    await register(user); // pass each user directly to register()
  }

  message.textContent = 'Dummy data added successfully!';
}
</script>

<script>

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
// Modified register function to accept userData
async function register(userData) {
    const payload = userData || {
        name: document.getElementById("name").value.trim(),
        status: document.getElementById("status").value,
        designation: document.getElementById("designation").value.trim(),
        designation_code: document.getElementById("designation_code").value.trim(),
        erp_number: document.getElementById("erp_number").value.trim(),
        appointment_date: document.getElementById("appointment_date").value,
        current_posting: document.getElementById("current_posting").value.trim(),
        last_posting: document.getElementById("last_posting").value.trim(),
        line_manager: document.getElementById("line_manager").value.trim(),
        segment_head: document.getElementById("segment_head").value.trim(),
        segment: document.getElementById("segment").value.trim(),
        segment_id: document.getElementById("segment_id").value,
        shift: document.getElementById("shift").value.trim(),
        current_salary: document.getElementById("current_salary").value,
        email: document.getElementById("email").value.trim(),
        phone: document.getElementById("phone").value.trim(),
        emergency_contact: document.getElementById("emergency_contact").value.trim(),
        gender: document.getElementById("gender").value,
        password: document.getElementById("password").value,
        profile_image: document.getElementById("profile_image").value.trim()
    };

    try {
        const res = await apiRequest("register.php", "POST", payload);
        if(document.getElementById("message")) {
            document.getElementById("message").innerText = res.message || res.error;
        }
        return res;
    } catch (err) {
        if(document.getElementById("message")) {
            document.getElementById("message").innerText = "Something went wrong.";
        }
        console.error(err);
    }
}
</script>
</body>
</html>
