<?php

include '../utils.php';

if (isLogged())
    header('Location: ../index.php');


$err = '';

function logIn()
{
    $objDb = new Database;
    $conn = $objDb->connect();

    if (!isset($_POST['username_email']))
        throw new Exception('Username is not filled');
    if (!isset($_POST['password']) || strlen($_POST['password']) < 8)
        throw new Exception('Password is not valid');


    $user = null;
    // check if username exists
    $sql = "SELECT * FROM users where username = :input or email = :input";
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':input', $_POST['username_email']);
    if ($stmt->execute()) {
        global $user;
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$user)
            throw new Exception('Username or email does not exist');
    } else
        throw new Exception('Something went wrong, please try again');

    if (!password_verify($_POST['password'], $user['password']))
        throw new Exception('Password is not valid');

    if (isset($_POST['remember'])) {
        setcookie('username', $_POST['username'], time() + 3600 * 24 * 7);
        setcookie('password', $_POST['password'], time() + 3600 * 24 * 7);
    }

    header('Location: ../index.php');
    $_SESSION['logged'] = ['username' => $user['username'], 'id' => $user['id']];
}


if (isset($_POST['submit'])) {
    try {
        login();
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
    <title>Blog | Login</title>
</head>

<body>
    <form action="<?= $_SERVER['PHP_SELF'] ?>" method="post" class="container d-flex flex-column gap-2">
        <h1 class="text-center">Login</h1>
        <div class="form-group">
            <label for="username_email">Username or email</label>
            <input type="text" name="username_email" id="username_email" class="form-control">
        </div>
        <div class="form-group">
            <label for="password">Password</label>
            <input type="password" name="password" id="password" class="form-control">
        </div>
        <div class="form-group">
            <input type="checkbox" name="remember" id="remember" class="form-check-input    ">
            <label for="remember">Remember me</label>
        </div>
        <div class="row align-items-center">
            <button type="submit" name="submit" class="btn btn-primary mt-2 col-2">Login</button>
            <span class="col-4">
                Don't have an account?
                <a href="signup.php">Signup</a>
            </span>
        </div>

        <?= strlen($err) > 0 ? "<div style='width: max-content' class='alert alert-danger mt-2' role='alert'>$err</div>" : '' ?>
    </form>
</body>

</html>