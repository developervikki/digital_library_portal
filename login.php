<?php
session_start();
require_once 'includes/db.php';

$error = ''; 

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);

    $stmt = $conn->prepare("SELECT * FROM table_students WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $res = $stmt->get_result();

    if ($res->num_rows === 1) {
        $user = $res->fetch_assoc();

        if (password_verify($password, $user['password'])) {
            $_SESSION['student_id'] = $user['id'];
            $_SESSION['student_name'] = $user['name'];
            header("Location: ./student/dashboard.php");
            exit;
        } else {
            $error = "Invalid password.";
        }
    } else {
        $error = "User not found.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Student Login - Digital Library</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <style>
    body {
      background: linear-gradient(to right, #0f172a, #1e293b);
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
    <h2 class="text-3xl font-bold mb-6 text-center">Student Login</h2>

    <?php if ($error): ?>
      <div class="mb-4 p-3 text-red-500 bg-red-100 rounded">
        <?= htmlspecialchars($error) ?>
      </div>
    <?php endif; ?>

    <form action="login.php" method="POST" class="space-y-4">
      <div>
        <label class="block mb-1 text-sm font-medium">Email</label>
        <input type="email" name="email" required class="w-full px-4 py-2 rounded bg-gray-800 text-white border border-gray-600" />
      </div>

      <div>
        <label class="block mb-1 text-sm font-medium">Password</label>
        <input type="password" name="password" required class="w-full px-4 py-2 rounded bg-gray-800 text-white border border-gray-600" />
      </div>

      <button type="submit" class="w-full py-2 rounded bg-indigo-600 hover:bg-indigo-700 font-semibold">
        Login
      </button>
    </form>

    <p class="mt-4 text-center text-gray-400 text-sm">
      Don’t have an account? <a href="register.php" class="text-blue-400 hover:underline">Register here</a>
    </p>
  </div>

</body>
</html>
