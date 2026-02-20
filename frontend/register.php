<!DOCTYPE html>
<html>
<head>
<title>Register - Dummy Data</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

<div class="container mt-5">
  <div class="card p-4 shadow">
    <h3 class="mb-3">Add Dummy Employees (Role ID: 1)</h3>
    
    <div class="alert alert-info">
      <strong>Manager IDs from Database:</strong><br>
      - Line Manager: <span class="fw-bold">102830</span> (Areeba Khan)<br>
      - HR ID: <span class="fw-bold">101322</span> (Ahmad Mustafa)<br>
      - Segment Head: <span class="fw-bold">102840</span> (Bilal Ahmed)<br>
      - Attendance ID: <span class="fw-bold">101323</span> (Ahmad Fiaz)<br>
      - CFO ID: <span class="fw-bold">408</span><br>
      - Accountant ID: <span class="fw-bold">125</span><br>
      - Admin ID: <span class="fw-bold">594</span>
    </div>

    <button onclick="addDummyData()" class="btn btn-primary w-100 mb-3">Add 10 Dummy Employees</button>
    <button onclick="addSingleDummy()" class="btn btn-secondary w-100 mb-3">Add Single Dummy Employee</button>

    <p id="message" class="mt-2 text-success fw-bold"></p>
    <div id="results" class="mt-3"></div>
  </div>
</div>

<script>
// Manager IDs from your database - CORRECTED
const MANAGERS = {
  line_manager: '102830',    // Areeba Khan
  hr_id: '101322',           // Ahmad Mustafa (HR Manager)
  segment_head: '102840',     // Bilal Ahmed (Segment Head)
  attendance_id: '101323',    // Ahmad Fiaz (Attendance Head)
  cfo_id: '408',
  accountant_id: '125',
  admin_id: '594'
};

// 10 Dummy users with role_id 1
const dummyUsers = [
  {
    name: 'Muhammad Ali',
    status: 'Permanent',
    designation: 'Software Developer',
    designation_code: '102901',
    erp_number: '102901',
    appointment_date: '2025-01-15',
    current_posting: 'IT Department - Lahore',
    last_posting: 'IT Department - Lahore',
    line_manager: MANAGERS.line_manager,
    segment_head: MANAGERS.segment_head,
    segment: 'IT Operations',
    segment_id: 501,
    shift: 'Morning Shift',
    current_salary: 55000,
    email: 'muhammad.ali@company.com',
    phone: '03011234571',
    emergency_contact: '03011234571',
    gender: 'male',
    password: '123456',
    profile_image: 'assets/men.jpg',
    role_id: 1,
    hr_id: MANAGERS.hr_id,
    attendance_id: MANAGERS.attendance_id,
    cfo_id: MANAGERS.cfo_id,
    accountant_id: MANAGERS.accountant_id,
    admin_id: MANAGERS.admin_id
  },
  {
    name: 'Sana Javed',
    status: 'Permanent',
    designation: 'HR Assistant',
    designation_code: '102902',
    erp_number: '102902',
    appointment_date: '2025-01-20',
    current_posting: 'HR Department - Lahore',
    last_posting: 'HR Department - Lahore',
    line_manager: MANAGERS.line_manager,
    segment_head: MANAGERS.segment_head,
    segment: 'Human Resources',
    segment_id: 502,
    shift: 'Morning Shift',
    current_salary: 42000,
    email: 'sana.javed@company.com',
    phone: '03021234572',
    emergency_contact: '03021234572',
    gender: 'female',
    password: '123456',
    profile_image: 'assets/women.jpg',
    role_id: 1,
    hr_id: MANAGERS.hr_id,
    attendance_id: MANAGERS.attendance_id,
    cfo_id: MANAGERS.cfo_id,
    accountant_id: MANAGERS.accountant_id,
    admin_id: MANAGERS.admin_id
  },
  {
    name: 'Usman Khan',
    status: 'Probation',
    designation: 'Accounts Officer',
    designation_code: '102903',
    erp_number: '102903',
    appointment_date: '2025-02-01',
    current_posting: 'Accounts - Karachi',
    last_posting: 'Accounts - Karachi',
    line_manager: MANAGERS.line_manager,
    segment_head: MANAGERS.segment_head,
    segment: 'Finance',
    segment_id: 503,
    shift: 'Evening Shift',
    current_salary: 38000,
    email: 'usman.khan@company.com',
    phone: '03031234573',
    emergency_contact: '03031234573',
    gender: 'male',
    password: '123456',
    profile_image: 'assets/men.jpg',
    role_id: 1,
    hr_id: MANAGERS.hr_id,
    attendance_id: MANAGERS.attendance_id,
    cfo_id: MANAGERS.cfo_id,
    accountant_id: MANAGERS.accountant_id,
    admin_id: MANAGERS.admin_id
  },
  {
    name: 'Fatima Akhtar',
    status: 'Permanent',
    designation: 'Marketing Executive',
    designation_code: '102904',
    erp_number: '102904',
    appointment_date: '2024-11-10',
    current_posting: 'Marketing - Lahore',
    last_posting: 'Marketing - Lahore',
    line_manager: MANAGERS.line_manager,
    segment_head: MANAGERS.segment_head,
    segment: 'Marketing',
    segment_id: 504,
    shift: 'Morning Shift',
    current_salary: 47000,
    email: 'fatima.akhtar@company.com',
    phone: '03041234574',
    emergency_contact: '03041234574',
    gender: 'female',
    password: '123456',
    profile_image: 'assets/women.jpg',
    role_id: 1,
    hr_id: MANAGERS.hr_id,
    attendance_id: MANAGERS.attendance_id,
    cfo_id: MANAGERS.cfo_id,
    accountant_id: MANAGERS.accountant_id,
    admin_id: MANAGERS.admin_id
  },
  {
    name: 'Ahmed Raza',
    status: 'Contract',
    designation: 'Network Administrator',
    designation_code: '102905',
    erp_number: '102905',
    appointment_date: '2025-01-05',
    current_posting: 'IT Operations - Islamabad',
    last_posting: 'IT Operations - Islamabad',
    line_manager: MANAGERS.line_manager,
    segment_head: MANAGERS.segment_head,
    segment: 'IT Operations',
    segment_id: 501,
    shift: 'General Shift',
    current_salary: 52000,
    email: 'ahmed.raza@company.com',
    phone: '03051234575',
    emergency_contact: '03051234575',
    gender: 'male',
    password: '123456',
    profile_image: 'assets/men.jpg',
    role_id: 1,
    hr_id: MANAGERS.hr_id,
    attendance_id: MANAGERS.attendance_id,
    cfo_id: MANAGERS.cfo_id,
    accountant_id: MANAGERS.accountant_id,
    admin_id: MANAGERS.admin_id
  },
  {
    name: 'Zara Malik',
    status: 'Permanent',
    designation: 'Business Analyst',
    designation_code: '102906',
    erp_number: '102906',
    appointment_date: '2024-12-20',
    current_posting: 'Business Development - Lahore',
    last_posting: 'Business Development - Lahore',
    line_manager: MANAGERS.line_manager,
    segment_head: MANAGERS.segment_head,
    segment: 'Business Development',
    segment_id: 505,
    shift: 'Morning Shift',
    current_salary: 58000,
    email: 'zara.malik@company.com',
    phone: '03061234576',
    emergency_contact: '03061234576',
    gender: 'female',
    password: '123456',
    profile_image: 'assets/women.jpg',
    role_id: 1,
    hr_id: MANAGERS.hr_id,
    attendance_id: MANAGERS.attendance_id,
    cfo_id: MANAGERS.cfo_id,
    accountant_id: MANAGERS.accountant_id,
    admin_id: MANAGERS.admin_id
  },
  {
    name: 'Hamza Ali',
    status: 'Probation',
    designation: 'Customer Support Representative',
    designation_code: '102907',
    erp_number: '102907',
    appointment_date: '2025-02-10',
    current_posting: 'Customer Support - Karachi',
    last_posting: 'Customer Support - Karachi',
    line_manager: MANAGERS.line_manager,
    segment_head: MANAGERS.segment_head,
    segment: 'Customer Support',
    segment_id: 506,
    shift: 'Evening Shift',
    current_salary: 32000,
    email: 'hamza.ali@company.com',
    phone: '03071234577',
    emergency_contact: '03071234577',
    gender: 'male',
    password: '123456',
    profile_image: 'assets/men.jpg',
    role_id: 1,
    hr_id: MANAGERS.hr_id,
    attendance_id: MANAGERS.attendance_id,
    cfo_id: MANAGERS.cfo_id,
    accountant_id: MANAGERS.accountant_id,
    admin_id: MANAGERS.admin_id
  },
  {
    name: 'Ayesha Siddiqui',
    status: 'Permanent',
    designation: 'Content Writer',
    designation_code: '102908',
    erp_number: '102908',
    appointment_date: '2024-10-15',
    current_posting: 'Content Department - Lahore',
    last_posting: 'Content Department - Lahore',
    line_manager: MANAGERS.line_manager,
    segment_head: MANAGERS.segment_head,
    segment: 'Content',
    segment_id: 507,
    shift: 'Morning Shift',
    current_salary: 40000,
    email: 'ayesha.siddiqui@company.com',
    phone: '03081234578',
    emergency_contact: '03081234578',
    gender: 'female',
    password: '123456',
    profile_image: 'assets/women.jpg',
    role_id: 1,
    hr_id: MANAGERS.hr_id,
    attendance_id: MANAGERS.attendance_id,
    cfo_id: MANAGERS.cfo_id,
    accountant_id: MANAGERS.accountant_id,
    admin_id: MANAGERS.admin_id
  },
  {
    name: 'Omar Farooq',
    status: 'Contract',
    designation: 'Quality Assurance Engineer',
    designation_code: '102909',
    erp_number: '102909',
    appointment_date: '2025-01-25',
    current_posting: 'QA Department - Lahore',
    last_posting: 'QA Department - Lahore',
    line_manager: MANAGERS.line_manager,
    segment_head: MANAGERS.segment_head,
    segment: 'Quality Assurance',
    segment_id: 508,
    shift: 'General Shift',
    current_salary: 45000,
    email: 'omar.farooq@company.com',
    phone: '03091234579',
    emergency_contact: '03091234579',
    gender: 'male',
    password: '123456',
    profile_image: 'assets/men.jpg',
    role_id: 1,
    hr_id: MANAGERS.hr_id,
    attendance_id: MANAGERS.attendance_id,
    cfo_id: MANAGERS.cfo_id,
    accountant_id: MANAGERS.accountant_id,
    admin_id: MANAGERS.admin_id
  },
  {
    name: 'Hina Khan',
    status: 'Permanent',
    designation: 'Graphic Designer',
    designation_code: '102910',
    erp_number: '102910',
    appointment_date: '2024-09-05',
    current_posting: 'Design Department - Lahore',
    last_posting: 'Design Department - Lahore',
    line_manager: MANAGERS.line_manager,
    segment_head: MANAGERS.segment_head,
    segment: 'Design',
    segment_id: 509,
    shift: 'Morning Shift',
    current_salary: 43000,
    email: 'hina.khan@company.com',
    phone: '03101234580',
    emergency_contact: '03101234580',
    gender: 'female',
    password: '123456',
    profile_image: 'assets/women.jpg',
    role_id: 1,
    hr_id: MANAGERS.hr_id,
    attendance_id: MANAGERS.attendance_id,
    cfo_id: MANAGERS.cfo_id,
    accountant_id: MANAGERS.accountant_id,
    admin_id: MANAGERS.admin_id
  }
];

// API configuration
const API_URL = "http://localhost/project-root/backend/api";
const APP_TOKEN = "abc123";

function getToken() {
  return localStorage.getItem("token");
}

async function apiRequest(endpoint, method = "GET", body = null) {
  const headers = { "Content-Type": "application/json" };

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

async function register(userData) {
  try {
    const res = await apiRequest("register.php", "POST", userData);
    return res;
  } catch (err) {
    console.error(err);
    return { error: "Something went wrong." };
  }
}

async function addDummyData() {
  const message = document.getElementById('message');
  const results = document.getElementById('results');
  message.textContent = 'Adding 10 dummy employees...';
  results.innerHTML = '';
  
  let success = 0;
  let failed = 0;
  
  for (let i = 0; i < dummyUsers.length; i++) {
    const user = dummyUsers[i];
    const result = await register(user);
    
    const resultDiv = document.createElement('div');
    resultDiv.className = result.message ? 'alert alert-success mt-2' : 'alert alert-danger mt-2';
    resultDiv.innerHTML = `<strong>${user.name}</strong> (ERP: ${user.erp_number}): ${result.message || result.error || 'Failed'}`;
    results.appendChild(resultDiv);
    
    if (result.message) {
      success++;
    } else {
      failed++;
    }
    
    // Small delay between requests to avoid overwhelming the server
    await new Promise(resolve => setTimeout(resolve, 500));
  }
  
  message.textContent = `✅ Completed! ${success} employees added successfully, ${failed} failed.`;
}

async function addSingleDummy() {
  const message = document.getElementById('message');
  const results = document.getElementById('results');
  
  // Add just the first dummy user
  const user = dummyUsers[0];
  message.textContent = `Adding ${user.name}...`;
  results.innerHTML = '';
  
  const result = await register(user);
  
  const resultDiv = document.createElement('div');
  resultDiv.className = result.message ? 'alert alert-success' : 'alert alert-danger';
  resultDiv.innerHTML = `<strong>${user.name}</strong> (ERP: ${user.erp_number}): ${result.message || result.error || 'Failed'}`;
  results.appendChild(resultDiv);
  
  if (result.message) {
    message.textContent = `✅ Successfully added ${user.name}`;
  } else {
    message.textContent = `❌ Failed to add ${user.name}`;
  }
}
</script>
</body>
</html>