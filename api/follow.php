<?php

include 'layout.php';


$response = ['status' => 'failed', 'message' => null];


if ($_SERVER['REQUEST_METHOD'] == 'GET') {
    try {
        if (isset($_GET['id']) && !empty($_GET['id'])) {
            $sql = "SELECT count(*) FROM follows WHERE follower = ? AND following = ?";
            $stmt = $conn->prepare($sql);
            $stmt->execute([$_SESSION['logged']['id'], $_GET['id']]);
            if ($stmt->fetchColumn() > 0) {
                // unfollow
                $sql = "DELETE FROM follows WHERE follower = ? AND following = ?";
                $stmt = $conn->prepare($sql);
                $stmt->execute([$_SESSION['logged']['id'], $_GET['id']]);
                $response['status'] = 'success';
                $response['message'] = 'unfollowed';
            } else {
                //follow
                $sql = "INSERT INTO follows (follower, following) VALUES (?, ?)";
                $stmt = $conn->prepare($sql);
                $stmt->execute([$_SESSION['logged']['id'], $_GET['id']]);
                $response['status'] = 'success';
                $response['message'] = 'followed';
            }
        } else {
            $response['message'] = 'invalid request';
            header('HTTP/1.1 400 Bad Request');
        }
    } catch (PDOException $e) {
        $response['message'] = $e->getMessage();
    }
}
echo json_encode($response);
