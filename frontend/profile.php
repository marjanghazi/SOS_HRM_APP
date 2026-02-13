<!DOCTYPE html>
<html>
<head>
    <title>Profile</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100">

<div class="container mt-5">
    <div class="card p-4 shadow">
        <h3>Profile</h3>
        <p id="profile"></p>
        <button onclick="logout()" class="btn btn-danger">Logout</button>
    </div>
</div>

<script src="assets/js/api.js"></script>
<script src="assets/js/profile.js"></script>

</body>
</html>
