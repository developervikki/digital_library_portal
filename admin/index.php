<?php 
session_start();
require_once '../includes/db.php';

// ✅ Redirect to dashboard if already logged in as admin
if (isset($_SESSION['user_id']) && ($_SESSION['user_role'] ?? '') === 'admin') { 
    header("Location: dashboard.php");
    exit();
} 
 
$error = '';

// ✅ Handle login POST request
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $email = trim($_POST['email']);
    $password = $_POST['password'];

    $stmt = $conn->prepare("SELECT id, name, password FROM table_admins WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows === 1) {
        $stmt->bind_result($admin_id, $admin_name, $hashed_password);
        $stmt->fetch();

        if (password_verify($password, $hashed_password)) {
            // ✅ Store session values
            $_SESSION['user_id'] = $admin_id;
            $_SESSION['user_name'] = $admin_name;
            $_SESSION['user_role'] = 'admin';

            header("Location: dashboard.php");
            exit();
        } else {
            $error = "Invalid password.";
        }
    } else {
        $error = "Admin not found.";
    }

    $stmt->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Admin Login - Digital Library</title>
  <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-900 text-white min-h-screen flex items-center justify-center">

  <form method="POST" class="bg-gray-800 p-6 rounded-lg shadow-lg w-full max-w-sm">
    <h2 class="text-xl font-bold mb-4 text-center">Admin Login</h2>

    <?php if (!empty($error)): ?>
      <p class="text-red-400 mb-2 text-sm text-center"><?= htmlspecialchars($error) ?></p>
    <?php endif; ?>

    <input type="email" name="email" placeholder="Email" required class="w-full mb-4 px-4 py-2 rounded bg-gray-700 border border-gray-600" />
    <input type="password" name="password" placeholder="Password" required class="w-full mb-4 px-4 py-2 rounded bg-gray-700 border border-gray-600" />
    <button type="submit" class="w-full bg-indigo-600 hover:bg-indigo-700 px-4 py-2 rounded">Login</button>
  </form>

</body>
</html>
