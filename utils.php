<?php
session_start();
include 'db.php';

$objDb = new Database;
$conn = $objDb->connect();

function test_input($data)
{
  $data = trim($data);
  $data = stripslashes($data);
  $data = htmlspecialchars($data);
  return $data;
}

function isLogged()
{
  global $conn;
  if (isset($_SESSION['logged'])) {
    return true;
  }
  if (isset($_COOKIE['username'],  $_COOKIE['password'])) {

    $sql = "SELECT * FROM users WHERE username = ?";
    $stmt = $conn->prepare($sql);
    if ($stmt->execute($_COOKIE['username'])) {
      $user = $stmt->fetch(PDO::FETCH_ASSOC);
      if (!password_verify($_COOKIE['password'], $user['password'])) {
        return false;
      }
      if (isset($user)) {
        $_SESSION['logged'] = ['username' => $user['username'], 'id' => $user['id']];
        return true;
      }
    }
  }
  return false;
}

function getTweets($id = null, $user_tweets = false, $following_tweets = false)
{
  global $conn;
  $sql = "SELECT tweets.*, users.profile_image, users.username, count(likes.tweet_id) as likes FROM tweets 
  JOIN users ON users.id = tweets.user_id
  LEFT JOIN likes ON likes.tweet_id = tweets.id";
  $sql .= $user_tweets ? " WHERE tweets.user_id = :user" : " WHERE tweets.user_id != :user";

  if ($following_tweets)
    $sql .= " AND users.id IN (SELECT following FROM follows WHERE follower = :user_id)";
  $sql .= " GROUP BY tweets.id ORDER BY tweets.created_at DESC";
  $stmt = $conn->prepare($sql);
  if ($following_tweets)
    $stmt->bindParam(':user_id', $_SESSION['logged']['id']);
  $stmt->bindParam(':user', $id);
  if ($stmt->execute()) {
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
  } else {
    return [];
  }
}

function isTweetLiked($tweet_id)
{
  global $conn;
  $user_id = $_SESSION['logged']['id'];
  $sql = "SELECT count(*) FROM likes WHERE user_id = ? AND tweet_id = ?";
  $stmt = $conn->prepare($sql);
  $stmt->execute([$user_id, $tweet_id]);
  $count = $stmt->fetchColumn();

  if ($count > 0)
    return true;

  return false;
}

function time_elapsed_string($datetime, $full = false)
{
  $now = new DateTime;
  $ago = new DateTime($datetime);
  $diff = $now->diff($ago);

  $diff->w = floor($diff->d / 7);
  $diff->d -= $diff->w * 7;

  $string = array(
    'y' => 'year',
    'm' => 'month',
    'w' => 'week',
    'd' => 'day',
    'h' => 'hour',
    'i' => 'minute',
    's' => 'second',
  );
  foreach ($string as $k => &$v) {
    if ($diff->$k) {
      $v = $diff->$k . ' ' . $v . ($diff->$k > 1 ? 's' : '');
    } else {
      unset($string[$k]);
    }
  }

  if (!$full) $string = array_slice($string, 0, 1);
  return $string ? implode(', ', $string) . ' ago' : 'just now';
}

function isUserNameAvailable($username)
{
  global $conn;
  $sql = "SELECT username FROM users WHERE username = ?";
  $stmt = $conn->prepare($sql);
  if ($stmt->execute([$username])) {
    $user = $stmt->fetchAll(PDO::FETCH_ASSOC);
    if (count($user) == 0) {
      return true;
    } else {
      return false;
    }
  } else {
    return false;
  }
}

function isEmailAvailable($email)
{
  global $conn;
  $sql = "SELECT count(*) FROM users WHERE email = ?";
  $stmt = $conn->prepare($sql);
  if ($stmt->execute([$email])) {
    $user = $stmt->fetchColumn();
    if ($user == 0)
      return true;
    else
      return false;
  } else
    return false;
}
