<?php
session_start();
require_once 'Database.php';

$db = new Database();

$search = $_GET['search'] ?? '';
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 10; // 每页显示的动漫数量
$offset = ($page - 1) * $limit;

$sql = "SELECT * FROM animes WHERE title LIKE ? LIMIT ? OFFSET ?";
$stmt = $db->prepare($sql);
$searchParam = "%$search%";
$stmt->bind_param("sii", $searchParam, $limit, $offset);
$stmt->execute();
$result = $stmt->get_result();

// 获取总记录数
$sql_count = "SELECT COUNT(*) as total FROM animes WHERE title LIKE ?";
$stmt_count = $db->prepare($sql_count);
$stmt_count->bind_param("s", $searchParam);
$stmt_count->execute();
$total_result = $stmt_count->get_result()->fetch_assoc();
$total_records = $total_result['total'];
$total_pages = ceil($total_records / $limit);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Anime Streaming</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/alpinejs@2.8.2/dist/alpine.min.js" defer></script>

</head>
<body class="bg-gray-100">
  <div class="container mx-auto p-4">
    <h1 class="text-3xl font-bold mb-4">追光动漫</h1>

    <?php if (isset($_SESSION['loggedin'])): ?>
      <p class="mb-4">Logged in as <?php echo htmlspecialchars($_SESSION['username'], ENT_QUOTES, 'UTF-8'); ?> | <a href="logout.php" class="text-blue-500">Logout</a></p>
    <?php else: ?>
      <p class="mb-4"><a href="login.php" class="text-blue-500">Login</a> | <a href="register.php" class="text-blue-500">Register</a></p>
    <?php endif; ?>

    <form method="GET" action="index.php" class="mb-4">
      <input type="text" name="search" placeholder="Search for an anime..." class="p-2 border rounded w-full focus:outline-none focus:ring-2 focus:ring-blue-500" value="<?php echo htmlspecialchars($search, ENT_QUOTES, 'UTF-8'); ?>">
      <button type="submit" class="bg-blue-500 text-white px-4 py-2 rounded mt-2 hover:bg-blue-600 transition duration-200">Search</button>
    </form>
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
      <?php
      if ($result->num_rows > 0) {
          while($row = $result->fetch_assoc()) {
              echo "<div class='bg-white p-4 rounded shadow'>";
              echo "<h2 class='text-xl font-bold mb-2'>" . htmlspecialchars($row["title"], ENT_QUOTES, 'UTF-8') . "</h2>";
              echo "<p class='mb-2'>" . htmlspecialchars($row["description"], ENT_QUOTES, 'UTF-8') . "</p>";
              echo "<a href='anime_detail.php?id=" . (int)$row["id"] . "' class='bg-blue-500 text-white px-4 py-2 rounded'>Watch Now</a>";
              echo "</div>";
          }
      } else {
          echo "没有找到任何动漫。";
      }
      ?>
    </div>

    <div class="mt-4">
      <?php if ($total_pages > 1): ?>
        <nav class="flex justify-center mt-4">
          <ul class="flex">
            <?php if ($page > 1): ?>
              <li><a href="?search=<?php echo htmlspecialchars($search, ENT_QUOTES, 'UTF-8'); ?>&page=<?php echo $page - 1; ?>" class="bg-blue-500 text-white px-4 py-2 rounded">Previous</a></li>
            <?php endif; ?>
            <?php for ($i = 1; $i <= $total_pages; $i++): ?>
              <li class="mx-1">
                <a href="?search=<?php echo htmlspecialchars($search, ENT_QUOTES, 'UTF-8'); ?>&page=<?php echo $i; ?>" class="bg-blue-500 text-white px-4 py-2 rounded <?php echo $i == $page ? 'bg-blue-700' : ''; ?>">
                  <?php echo $i; ?>
                </a>
              </li>
            <?php endfor; ?>
            <?php if ($page < $total_pages): ?>
              <li><a href="?search=<?php echo htmlspecialchars($search, ENT_QUOTES, 'UTF-8'); ?>&page=<?php echo $page + 1; ?>" class="bg-blue-500 text-white px-4 py-2 rounded">Next</a></li>
            <?php endif; ?>
          </ul>
        </nav>
      <?php endif; ?>
    </div>
  </div>
</body>
</html>

<?php
$db->close();
?>
