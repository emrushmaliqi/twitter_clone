<?php

include './layout.php';

function findFollowers()
{
    global $conn;
    $sql = "SELECT users.username, users.profile_image, users.id, 
            CASE WHEN users.id != :logged_id THEN
            (SELECT COUNT(*) FROM follows WHERE follower = :logged_id AND following = users.id)
            ELSE -1 END AS is_followed
            FROM users 
            JOIN follows ON follows.follower = users.id
            WHERE follows.following = :user_id";
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':logged_id', $_SESSION['logged']['id']);
    $stmt->bindParam(':user_id', $_GET['id']);
    if ($stmt->execute()) {
        $followers = $stmt->fetchAll(PDO::FETCH_ASSOC);
        if (is_array($followers)) {
            if ($_GET['id'] == $_SESSION['logged']['id'])
                return ['status' => 'success', 'data' => $followers, 'is_logged' => true];
            return ['status' => 'success', 'data' => $followers];
        }
    }
    return ['status' => 'failed', 'data' => null, 'message' => 'Error while fetching followers'];
}

function findFollowing()
{
    global $conn;
    $sql = "SELECT users.username, users.profile_image, users.id,
            CASE WHEN users.id != :logged_id THEN
            (SELECT COUNT(*) FROM follows WHERE follower = :logged_id AND following = users.id)
            ELSE -1 END AS is_followed
            FROM users 
            JOIN follows ON follows.following = users.id
            WHERE follows.follower = :user_id";
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':logged_id', $_SESSION['logged']['id']);
    $stmt->bindParam(':user_id', $_GET['id']);
    if ($stmt->execute()) {
        $following = $stmt->fetchAll(PDO::FETCH_ASSOC);
        if (is_array($following)) return ['status' => 'success', 'data' => $following];
    }
    return ['status' => 'failed', 'data' => null, 'message' => 'Error while fetching followings'];
}

$response = ['status' => 'failed', 'data' => null];

if ($_SERVER['REQUEST_METHOD'] == 'GET' && isset($_GET['id']) && !empty($_GET['id']))
    $response = (isset($_GET['type']) && $_GET['type'] == 'followers') ? findFollowers() : findFollowing();
else
    $response['message'] = 'Invalid request method';




echo json_encode($response);
