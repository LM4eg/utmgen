<?php
session_start(); // Запускаем сессию
require 'db.php';

// Устанавливаем заголовок Content-Type для JSON
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $url = $_POST['url'] ?? '';
    $source = $_POST['source'] ?? '';
    $medium = $_POST['medium'] ?? '';
    $campaign = $_POST['campaign'] ?? '';
    $content = $_POST['content'] ?? '';
    $term = $_POST['term'] ?? '';

    // Validate URL
    if (!filter_var($url, FILTER_VALIDATE_URL)) {
        echo json_encode(['error' => 'Invalid URL']);
        exit;
    }

    // Generate UTM link
    $utmParams = [
        'utm_source' => $source,
        'utm_medium' => $medium,
        'utm_campaign' => $campaign,
        'utm_content' => $content,
        'utm_term' => $term
    ];

    $query = http_build_query(array_filter($utmParams));
    $fullUrl = $url . '?' . $query;

    // Save to DB only if user is logged in
    if (isset($_SESSION['user_id'])) {
        $userId = $_SESSION['user_id'];
        try {
            $stmt = $pdo->prepare("INSERT INTO generated_links (user_id, original_url, utm_source, utm_medium, utm_campaign, utm_content, utm_term, full_url) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([$userId, $url, $source, $medium, $campaign, $content, $term, $fullUrl]);
        } catch (Exception $e) {
            error_log("Database error: " . $e->getMessage());
        }
    }

    // Возвращаем корректный результат
    echo json_encode(['link' => htmlspecialchars($fullUrl)]);
}