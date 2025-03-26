<?php
session_start();
require 'db.php';

// Проверка авторизации
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$user_id = $_SESSION['user_id'];

// Получение всех сгенерированных ссылок текущего пользователя
$stmt = $pdo->prepare("SELECT * FROM generated_links WHERE user_id = ? ORDER BY created_at DESC");
$stmt->execute([$user_id]);
$links = $stmt->fetchAll();

// Добавление или обновление комментария
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $link_id = $_POST['link_id'];
    $comment = trim($_POST['comment']);

    if (!empty($comment)) {
        // Проверяем, существует ли комментарий для этой ссылки
        $stmt = $pdo->prepare("SELECT id FROM comments WHERE link_id = ? AND user_id = ?");
        $stmt->execute([$link_id, $user_id]);
        $existingComment = $stmt->fetch();

        if ($existingComment) {
            // Обновляем существующий комментарий
            $stmt = $pdo->prepare("UPDATE comments SET comment = ? WHERE id = ?");
            $stmt->execute([$comment, $existingComment['id']]);
        } else {
            // Создаем новый комментарий
            $stmt = $pdo->prepare("INSERT INTO comments (link_id, user_id, comment) VALUES (?, ?, ?)");
            $stmt->execute([$link_id, $user_id, $comment]);
        }
    }
}

// Удаление комментария
if (isset($_GET['delete_comment']) && isset($_GET['link_id'])) {
    $comment_id = $_GET['delete_comment'];
    $link_id = $_GET['link_id'];

    $stmt = $pdo->prepare("DELETE FROM comments WHERE id = ? AND user_id = ?");
    $stmt->execute([$comment_id, $user_id]);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Generated Links</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">
        <h1 class="text-center">Generated Links</h1>
        <?php if (empty($links)): ?>
            <p class="text-center">No links generated yet.</p>
        <?php else: ?>
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>Full URL</th>
                        <th>Comment</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($links as $link): ?>
                        <tr>
                            <td>
                                <!-- Отображение полной ссылки -->
                                <a href="<?php echo htmlspecialchars($link['full_url']); ?>" target="_blank">
                                    <?php echo htmlspecialchars($link['full_url']); ?>
                                </a>
                            </td>
                            <td>
                                <!-- Отображение комментария -->
                                <?php
                                $stmt = $pdo->prepare("SELECT * FROM comments WHERE link_id = ? AND user_id = ?");
                                $stmt->execute([$link['id'], $user_id]);
                                $comment = $stmt->fetch();
                                if ($comment): ?>
                                    <div class="alert alert-info">
                                        <?php echo htmlspecialchars($comment['comment']); ?>
                                        <a href="?delete_comment=<?php echo $comment['id']; ?>&link_id=<?php echo $link['id']; ?>" class="btn btn-sm btn-danger float-end">Delete</a>
                                    </div>
                                    <form method="POST" class="mt-2">
                                        <input type="hidden" name="link_id" value="<?php echo $link['id']; ?>">
                                        <textarea class="form-control" name="comment" rows="2"><?php echo htmlspecialchars($comment['comment']); ?></textarea>
                                        <button type="submit" class="btn btn-sm btn-primary mt-2">Update Comment</button>
                                    </form>
                                <?php else: ?>
                                    <!-- Форма добавления комментария -->
                                    <form method="POST" class="mt-2">
                                        <input type="hidden" name="link_id" value="<?php echo $link['id']; ?>">
                                        <textarea class="form-control" name="comment" rows="2" placeholder="Add a comment..."></textarea>
                                        <button type="submit" class="btn btn-sm btn-primary mt-2">Add Comment</button>
                                    </form>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
        <a href="index.php" class="btn btn-secondary">Back to Generator</a>
    </div>
</body>
</html>