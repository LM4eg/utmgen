<?php
session_start(); // Запускаем сессию
session_destroy(); // Очищаем сессию
header('Location: index.php'); // Перенаправляем на страницу входа
exit;
?>