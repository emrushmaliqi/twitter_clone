<?php
include './layout.php';

$response = ['status' => 'failed', 'message' => null];

if ($_SERVER['REQUEST_METHOD'] == 'GET' && isset($_GET['id']) && !is_null($_GET['id'])) {
    $sql = "DELETE FROM tweets WHERE id = ? AND user_id = ?";
    $stmt = $conn->prepare($sql);
    if ($stmt->execute([$_GET['id'], $_SESSION['logged']['id']])) {
        $response = ['status' => 'success', 'message' => 'Tweet deleted successfully'];
    } else $response['message'] = 'Something went wrong';
} else $response['message'] = 'Invalid request';

echo json_encode($response);
