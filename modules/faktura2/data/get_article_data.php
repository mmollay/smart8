<?php
include(__DIR__ . '/../f_config.php');

$articles = getArticleArray($db);

header('Content-Type: application/json');
echo json_encode($articles);
?>