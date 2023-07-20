<?php

include './layout.php';

$response = ['status' => 'failed', 'message' => null];
if ($_SERVER['REQUEST_METHOD'] == 'GET' && isset($_GET['id']) && !is_null($_GET['id'])) {
    $sql = "DELETE FROM follows WHERE follower = ? AND following = ?";
    $stmt = $conn->prepare($sql);
    if ($stmt->execute([$_GET['id'], $_SESSION['logged']['id']]))
        $response = ['status' => 'success', 'message' => 'Follow removed successfully'];
    else $response['message'] = $stmt->errorInfo()[2];
} else $response['message'] = 'Invalid request';

echo json_encode($response);
