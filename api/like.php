<?php
include 'layout.php';



$response = ['status' => 'failed', 'message' => null];

if ($_SERVER['REQUEST_METHOD'] == 'GET') {
    if (isset($_GET['id'], $_GET['is_liked'])) {

        $sql = "INSERT INTO LIKES (user_id, tweet_id) VALUES (?, ?)";

        if ($_GET['is_liked'] == "true") {
            $sql = "DELETE FROM LIKES WHERE user_id = ? AND tweet_id = ?";
        }
        $stmt = $conn->prepare($sql);
        try {
            if ($stmt->execute([$_SESSION['logged']['id'], $_GET['id']])) {
                $response['status'] = 'success';
                $response['message'] = 'Tweet liked';
            } else {
                $response['message'] = 'Something went wrong';
            }
        } catch (Exception $e) {
            $response['message'] = $e->getMessage();
        }
    } else {
        $response['message'] = 'Missing parameters';
    }
} else {
    $response['message'] = 'Wrong Request method';
}
echo json_encode($response);
