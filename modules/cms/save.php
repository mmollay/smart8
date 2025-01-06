<?
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['content'])) {
    $content = $_POST['content'];
    file_put_contents('saved_state.html', $content);
    echo json_encode(['success' => true]);
}