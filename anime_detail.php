<?php
session_start();
require_once 'Database.php';

$id = $_GET['id'] ?? '';
if (empty($id)) {
    die("Invalid anime ID.");
}

$db = new Database();

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_SESSION['loggedin'])) {
    $comment = htmlspecialchars($_POST['comment'], ENT_QUOTES, 'UTF-8');
    $rating = (int)$_POST['rating'];
    $user_id = $_SESSION['username'];

    $stmt = $db->prepare("INSERT INTO comments (anime_id, user_id, comment, rating) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("issi", $id, $user_id, $comment, $rating);
    $stmt->execute();
}

$stmt = $db->prepare("SELECT * FROM animes WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$anime = $stmt->get_result()->fetch_assoc();

$stmt_comments = $db->prepare("SELECT * FROM comments WHERE anime_id = ?");
$stmt_comments->bind_param("i", $id);
$stmt_comments->execute();
$result_comments = $stmt_comments->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($anime['title'], ENT_QUOTES, 'UTF-8'); ?></title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/dplayer/dist/DPlayer.min.css">
    <script src="https://cdn.jsdelivr.net/npm/dplayer/dist/DPlayer.min.js"></script>
</head>
<body class="bg-gray-100">
  <div class="container mx-auto p-4">
    <h1 class="text-3xl font-bold mb-4"><?php echo htmlspecialchars($anime['title'], ENT_QUOTES, 'UTF-8'); ?></h1>
    <p class="mb-4"><?php echo htmlspecialchars($anime['description'], ENT_QUOTES, 'UTF-8'); ?></p>
    <div id="player" class="mb-4"></div>
    <script>
      const dplayer = new DPlayer({
        container: document.getElementById('player'),
        autoplay: true,
        video: {
          url: '<?php echo htmlspecialchars($anime['video_url'], ENT_QUOTES, 'UTF-8'); ?>',
        },
      });
    </script>
    <a href="index.php" class="text-blue-500">Back to home</a>

    <?php if (isset($_SESSION['loggedin'])): ?>
    <form method="POST" action="anime_detail.php?id=<?php echo htmlspecialchars($id, ENT_QUOTES, 'UTF-8'); ?>" class="mt-4">
      <textarea name="comment" class="w-full p-2 border rounded focus:outline-none focus:ring-2 focus:ring-blue-500" placeholder="Leave a comment..." required></textarea>
      <div class="mt-2">
        <label class="block text-gray-700">Rating:</label>
        <select name="rating" class="w-full p-2 border rounded focus:outline-none focus:ring-2 focus:ring-blue-500">
          <option value="1">1</option>
          <option value="2">2</option>
          <option value="3">3</option>
          <option value="4">4</option>
          <option value="5">5</option>
        </select>
      </div>
      <button type="submit" class="bg-blue-500 text-white px-4 py-2 rounded mt-2 hover:bg-blue-600 transition duration-200">Submit</button>
    </form>
    <?php else: ?>
    <p class="mt-4 text-red-500">You must be logged in to leave a comment.</p>
    <?php endif; ?>

    <div class="mt-4">
      <h2 class="text-2xl font-bold mb-2">Comments</h2>
      <?php while($row = $result_comments->fetch_assoc()): ?>
      <div class="bg-white p-4 rounded shadow mb-4">
        <p class="text-gray-700"><?php echo htmlspecialchars($row['comment'], ENT_QUOTES, 'UTF-8'); ?></p>
        <p class="text-gray-500">Rating: <?php echo (int)$row['rating']; ?></p>
        <p class="text-gray-500 text-sm">Posted on: <?php echo htmlspecialchars($row['comment_date'], ENT_QUOTES, 'UTF-8'); ?></p>
      </div>
      <?php endwhile; ?>
    </div>
  </div>
</body>
</html>

<?php
$db->close();
?>
