<?php
include '../utils.php';

if (isLogged())
    header('Location: ../index.php');

$err = '';



function sign_up()
{
    global $conn;

    if (strlen($_POST['password']) < 8)
        throw new Exception('Password must be at least 8 characters long');
    if ($_POST['password'] != $_POST['confirm-password'])
        throw new Exception('Passwords do not match');
    if (strlen($_POST['username']) < 3 || strlen($_POST['username']) > 20)
        throw new Exception('Username must be between 3 and 20 characters long');
    if (!isUserNameAvailable($_POST['username']))
        throw new Exception('Username is not available');
    $email = test_input($_POST['email']);
    if (!isset($_POST['email']) || !filter_var($email, FILTER_VALIDATE_EMAIL))
        throw new Exception('Invalid email');
    if (!isEmailAvailable($email))
        throw new Exception('Email is not available');

    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);

    $sql = "INSERT INTO users (username, email, password) VALUES (?, ?, ?)";
    $stmt = $conn->prepare($sql);
    if (!$stmt->execute([$_POST['username'], $email, $password]))
        throw new Exception('Sign up failed');
    header("Location: /twitter_clone/auth/login.php");
}

if (isset($_POST['submit'])) {
    try {
        sign_up();
    } catch (Exception $e) {
        $err = $e->getMessage();
    }
}

?>


<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-9ndCyUaIbzAi2FUVXJi0CjmCapSmO7SnpJef0486qhLnuZ2cdeRhO02iuK6FUUVM" crossorigin="anonymous">
    <title>Blog | Signup</title>
</head>

<body>
    <form action="<?= $_SERVER['PHP_SELF'] ?>" method="post" class="container">
        <h1 class="text-center">Signup</h1>
        <div class="form-group">
            <label for="username">Username</label>
            <input type="text" name="username" id="username" class="form-control">
        </div>
        <div class="form-group">
            <label for="email">Email</label>
            <input type="email" name="email" id="email" class="form-control">
        </div>
        <div class="form-group">
            <label for="password">Password</label>
            <input type="password" name="password" id="password" class="form-control">
        </div>
        <div class="form-group">
            <label for="confirm-password">Confirm Password</label>
            <input type="password" name="confirm-password" id="confirm-password" class="form-control">
        </div>
        <button type="submit" name="submit" class="btn btn-primary mt-2">Signup</button>
        <span>
            Already have an account?
            <a href="login.php">Login</a>
        </span>
        <?= strlen($err) > 0 ? "<div style='width: max-content' class='alert alert-danger mt-2' role='alert'>$err</div>" : '' ?>
    </form>
</body>

</html>