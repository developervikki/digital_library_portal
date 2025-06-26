<?php
session_start(); 
require_once 'includes/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name     = trim($_POST['name']);
    $email    = trim($_POST['email']);
    $phone    = trim($_POST['phone']);
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT); // Secure password hash

    // Upload profile photo
    $photoPath = '';
    if (!empty($_FILES['profile_photo']['name'])) {
        $uploadDir = 'uploads/profile_photos/';
        $filename = uniqid() . '_' . basename($_FILES['profile_photo']['name']);
        $targetPath = $uploadDir . $filename;

        if (move_uploaded_file($_FILES['profile_photo']['tmp_name'], $targetPath)) {
            $photoPath = $targetPath;
        }
    }

    // Insert into DB
    $stmt = $conn->prepare("INSERT INTO table_students (name, email, phone, password, profile_photo) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("sssss", $name, $email, $phone, $password, $photoPath);

    if ($stmt->execute()) {
        $_SESSION['user_id'] = $stmt->insert_id;
        header("Location: dashboard.php");
        exit();
    } else {
        echo "<script>alert('Registration failed. Try again.');</script>";
    }
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Register - Digital Library</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <style>
    body {
      background: linear-gradient(120deg, #1e293b, #0f172a);
    }
    .glass {
      background: rgba(255, 255, 255, 0.05);
      backdrop-filter: blur(10px);
      border: 1px solid rgba(255, 255, 255, 0.2);
    }
  </style>
</head>
<body class="min-h-screen flex items-center justify-center text-white font-sans">

  <div class="glass p-8 rounded-2xl shadow-lg w-full max-w-md mx-4 relative z-10">
    <h2 class="text-3xl font-bold mb-6 text-center">Create Student Account</h2>
    <form action="register.php" method="POST" enctype="multipart/form-data" class="space-y-4">
      
      <div>
        <label class="block mb-1 text-sm font-medium">Full Name</label>
        <input type="text" name="name" required class="w-full px-4 py-2 rounded bg-gray-800 text-white border border-gray-600 focus:outline-none focus:ring-2 focus:ring-blue-500" />
      </div>

      <div>
        <label class="block mb-1 text-sm font-medium">Email</label>
        <input type="email" name="email" required class="w-full px-4 py-2 rounded bg-gray-800 text-white border border-gray-600 focus:outline-none focus:ring-2 focus:ring-blue-500" />
      </div>

      <div>
        <label class="block mb-1 text-sm font-medium">Phone Number</label>
        <input type="text" name="phone" required class="w-full px-4 py-2 rounded bg-gray-800 text-white border border-gray-600 focus:outline-none focus:ring-2 focus:ring-blue-500" />
      </div>

      <div>
        <label class="block mb-1 text-sm font-medium">Password</label>
        <input type="password" name="password" required class="w-full px-4 py-2 rounded bg-gray-800 text-white border border-gray-600 focus:outline-none focus:ring-2 focus:ring-blue-500" />
      </div>

      <div>
        <label class="block mb-1 text-sm font-medium">Profile Photo</label>
        <input type="file" name="profile_photo" accept="image/*" class="w-full px-4 py-2 rounded bg-gray-800 text-white border border-gray-600" />
      </div>

      <button type="submit" class="w-full py-2 rounded bg-indigo-600 hover:bg-indigo-700 transition font-semibold">
        Register
      </button>
    </form>

    <p class="mt-4 text-center text-gray-400 text-sm">
      Already have an account?
      <a href="./login.php" class="text-blue-400 hover:underline">Login here</a>
    </p>
  </div>

</body>
</html>
