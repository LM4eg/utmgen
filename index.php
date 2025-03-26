<?php
session_start(); // Запускаем сессию
require 'db.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>UTM Generator</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-light bg-light">
        <div class="container-fluid">
            <a class="navbar-brand" href="#">UTM Generator</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse justify-content-end" id="navbarNav">
                <?php if (isset($_SESSION['user_id'])): ?>
                    <a class="nav-link" href="view_links.php">View Links</a>
                    <a class="nav-link" href="logout.php">Logout</a>
                <?php else: ?>
                    <a class="nav-link" href="login.php">Login</a>
                    <a class="nav-link" href="register.php">Register</a>
                <?php endif; ?>
            </div>
        </div>
    </nav>

    <div class="container mt-5">
        <h1 class="text-center">UTM Link Generator</h1>
        <form id="utmForm" class="mt-4">
            <div class="mb-3">
                <label for="url" class="form-label">URL:</label>
                <input type="url" class="form-control" id="url" name="url" required>
            </div>
            <div class="mb-3">
                <label for="source" class="form-label">UTM Source:</label>
                <input type="text" class="form-control" id="source" name="source" required>
            </div>
            <div class="mb-3">
                <label for="medium" class="form-label">UTM Medium:</label>
                <input type="text" class="form-control" id="medium" name="medium" required>
            </div>
            <div class="mb-3">
                <label for="campaign" class="form-label">UTM Campaign:</label>
                <input type="text" class="form-control" id="campaign" name="campaign" required>
            </div>
            <div class="mb-3">
                <label for="content" class="form-label">UTM Content (Optional):</label>
                <input type="text" class="form-control" id="content" name="content">
            </div>
            <div class="mb-3">
                <label for="term" class="form-label">UTM Term (Optional):</label>
                <input type="text" class="form-control" id="term" name="term">
            </div>
            <button type="submit" class="btn btn-primary">Generate Link</button>
        </form>
        <div id="result" class="mt-4"></div>
    </div>

    <script>
    document.getElementById('utmForm').addEventListener('submit', function(e) {
        e.preventDefault();

        const formData = new FormData(this);

        fetch('generate.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json()) // Преобразуем ответ в JSON
        .then(result => {
            console.log('Server response:', result); // Отладочная информация

            if (result.link) {
                const resultDiv = document.createElement('div');
                resultDiv.className = 'alert alert-success';

                const linkInput = document.createElement('input');
                linkInput.type = 'text';
                linkInput.value = result.link;
                linkInput.readOnly = true;
                linkInput.className = 'form-control mb-2';

                const copyButton = document.createElement('button');
                copyButton.textContent = 'Copy';
                copyButton.className = 'btn btn-sm btn-primary';
                copyButton.addEventListener('click', () => {
                    linkInput.select();
                    document.execCommand('copy');
                    alert('Link copied to clipboard!');
                });

                resultDiv.appendChild(linkInput);
                resultDiv.appendChild(copyButton);

                document.getElementById('result').innerHTML = ''; // Очищаем предыдущий результат
                document.getElementById('result').appendChild(resultDiv);
            } else if (result.error) {
                document.getElementById('result').innerHTML = `<div class="alert alert-danger">${result.error}</div>`;
            } else {
                document.getElementById('result').innerHTML = `<div class="alert alert-danger">An unexpected error occurred. Server response: ${JSON.stringify(result)}</div>`;
            }
        })
        .catch(error => {
            console.error('Error processing server response:', error); // Отладочная информация
            document.getElementById('result').innerHTML = `<div class="alert alert-danger">Failed to generate the link. Error: ${error.message}</div>`;
        });
    });
</script>
</body>
</html>