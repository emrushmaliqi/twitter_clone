<?php
include '../utils.php';

if (isLogged())
    header('Location: ../index.php');

$objDb = new Database;
$conn = $objDb->connect();

$err = '';

function signUp($username, $email, $password)
{
    global $conn, $err;
    $sql = "INSERT INTO users (username, email, password) VALUES (?, ?, ?)";
    $stmt = $conn->prepare($sql);
    if ($stmt->execute([$username, $email, $password])) {
        header("Location: /twitter_clone/auth/login.php");
    } else {
        $err = 'Sign up failed';
    }
}


if (isset($_POST['submit'])) {
    if ($_POST['password'] == $_POST['confirm-password']) {
        if (strlen($_POST['password']) >= 8) {
            if (strlen($_POST['username']) >= 3 && strlen($_POST['username']) <= 20) {
                if (isUserNameAvailable($_POST['username'])) {
                    $email = test_input($_POST['email']);
                    if (isset($_POST['email']) && filter_var($email, FILTER_VALIDATE_EMAIL)) {
                        if (isEmailAvailable($email)) {
                            $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
                            signUp($_POST['username'], $email, $password);
                        } else {
                            $err = 'Email is not available';
                        }
                    } else {
                        $err = "Invalid email";
                    }
                } else {
                    $err = 'Username is not available';
                }
            } else {
                $err = "Username is too short";
            }
        } else {
            $err = "Password must be at least 8 characters";
        }
    } else {
        $err = "Passwords don't match";
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