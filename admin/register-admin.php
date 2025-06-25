<?php
require_once '../includes/db.php';

$message = '';

if ($_SERVER["REQUEST_METHOD"] === "POST") {
  $name = trim($_POST['name']);
  $email = trim($_POST['email']);
  $password = password_hash($_POST['password'], PASSWORD_BCRYPT); 

  // Check if email already exists
  $stmt = $conn->prepare("SELECT id FROM table_admins WHERE email = ?");
  $stmt->bind_param("s", $email);
  $stmt->execute();
  $stmt->store_result();

  if ($stmt->num_rows > 0) {
    $message = "Email already registered.";
  } else {
    $stmt = $conn->prepare("INSERT INTO table_admins (name, email, password) VALUES (?, ?, ?)");
    $stmt->bind_param("sss", $name, $email, $password);
    if ($stmt->execute()) {
      $message = "Admin registered successfully. <a href='admin/index.php'>Login here</a>";
    } else {
      $message = "Error during registration.";
    }
  }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Register Admin</title>
  <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 min-h-screen flex items-center justify-center">

  <div class="bg-white p-8 rounded-lg shadow-md w-full max-w-md">
    <h2 class="text-2xl font-bold mb-6 text-center">Admin Registration</h2>
    
    <?php if ($message): ?>
      <p class="mb-4 text-center text-red-500"><?= $message ?></p>
    <?php endif; ?>

    <form method="POST">
      <input type="text" name="name" placeholder="Full Name" class="w-full mb-4 px-4 py-2 border rounded" required />
      <input type="email" name="email" placeholder="Email" class="w-full mb-4 px-4 py-2 border rounded" required />
      <input type="password" name="password" placeholder="Password" class="w-full mb-4 px-4 py-2 border rounded" required />
      
      <button type="submit" class="w-full bg-indigo-600 text-white py-2 rounded hover:bg-indigo-700 transition">Register</button>
    </form>
  </div>

</body>
</html>
