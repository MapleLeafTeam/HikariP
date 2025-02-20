<?php
// login.php

session_start();
require_once 'Database.php';

$db = new Database();
$message = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST['username'];
    $password = $_POST['password'];

    $stmt = $db->prepare("SELECT * FROM users WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();
        if (password_verify($password, $user['password'])) {
            $_SESSION['loggedin'] = true;
            $_SESSION['username'] = $user['username'];
            header("Location: index.php");
            exit;
        } else {
            $message = 'Incorrect password.';
        }
    } else {
        $message = 'Username not found.';
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
</head>
<body class="bg-gray-100">
  <div class="container mx-auto p-4">
    <h1 class="text-3xl font-bold mb-4">Login</h1>
    <?php if ($message): ?>
      <p class="text-red-500 mb-4"><?php echo htmlspecialchars($message, ENT_QUOTES, 'UTF-8'); ?></p>
    <?php endif; ?>
    <form method="POST" action="login.php" class="bg-white p-4 rounded shadow">
      <div class="mb-4">
        <label for="username" class="block text-gray-700">Username</label>
        <input type="text" name="username" id="username" class="p-2 border rounded w-full focus:outline-none focus:ring-2 focus:ring-blue-500" required>
      </div>
      <div class="mb-4">
        <label for="password" class="block text-gray-700">Password</label>
        <input type="password" name="password" id="password" class="p-2 border rounded w-full focus:outline-none focus:ring-2 focus:ring-blue-500" required>
      </div>
      <button type="submit" class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600 transition duration-200">Login</button>
    </form>
    <p class="mt-4">Don't have an account? <a href="register.php" class="text-blue-500">Register here</a>.</p>
  </div>
</body>
</html>

<?php
$db->close();
?>
