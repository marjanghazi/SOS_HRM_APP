<!DOCTYPE html>
<html>
<head>
    <title>Login</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100">

<div class="container mt-5">
    <div class="card p-4 shadow">
        <h3>Login</h3>
        <input id="erp_number" 
               type="text" 
               maxlength="6"
               pattern="\d{6}"
               class="form-control mb-2" 
               placeholder="6-digit erp_number">
        <input id="password" type="password" class="form-control mb-2" placeholder="Password">
        <button onclick="login()" class="btn btn-primary w-100">Login</button>
        <p class="mt-2 text-danger" id="error"></p>
        <a href="register.html">Register</a>
    </div>
</div>

<script src="assets/js/api.js"></script>
<script src="assets/js/login.js"></script>

</body>
</html>
