<?php

include '../utils.php';

if ($_SERVER['REQUEST_METHOD'] == 'GET') {
    header('Location: index.php');
    die();
} else if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (!isset($_SESSION['logged']['username'])) {
        header('HTTP/1.1 401 Unauthorized');
        die();
    }

    // update profile picture
    if (isset($_FILES['profile_image']) && !empty($_FILES['profile_image']['name'])) {
        $extensions = ['jpg', 'png', 'webp', 'jpeg'];
        $file_extension = pathinfo($_FILES['profile_image']['name'], PATHINFO_EXTENSION);
        if (!in_array($file_extension, $extensions)) {
            header('Location: ' . 'index.php?username=' . $_SESSION['logged']['username'] . '&error=Wrong file extension! "jpg, jpeg, png, webp are allowed."');
            die();
        } else {
            $imageName = $_SESSION['logged']['id'] . '.' . pathinfo($_FILES['profile_image']['name'], PATHINFO_EXTENSION);
            if (!move_uploaded_file($_FILES['profile_image']['tmp_name'], '../uploads/' . $imageName)) {
                header('Location: ' . 'index.php?username=' . $_SESSION['logged']['username'] . '&error=Something went wrong while uploading your image!');
                die();
            }
            $sql = "UPDATE users SET profile_image = ? WHERE id = ?";
            $stmt = $conn->prepare($sql);
            if (!$stmt->execute([$imageName, $_SESSION['logged']['id']])) {
                header('Location: ' . 'index.php?username=' . $_SESSION['logged']['username'] . '&error=Something went wrong while uploading your image!');
            } else {
                header('Location: ' . 'index.php?username=' . $_SESSION['logged']['username'] . '&success=Image uploaded');
            }
        }
    }
    // update email
    else if (isset($_POST['email'])) {
        $email = test_input($_POST['email']);
        if (strlen($email) > 3 && filter_var($email, FILTER_VALIDATE_EMAIL)) {
            if (isEmailAvailable($email)) {
                $sql = "UPDATE users SET email = ? WHERE id = ?";
                $stmt = $conn->prepare($sql);
                if ($stmt->execute([$email, $_SESSION['logged']['id']])) header('Location: ' . 'index.php?username=' . $_SESSION['logged']['username'] . '&success=Email updated');
                else header('Location: ' . 'index.php?username=' . $_SESSION['logged']['username'] . '&error=Something went wrong while updating your email!');
            } else {
                header('Location: ' . 'index.php?username=' . $_SESSION['logged']['username'] . '&error=Email already taken!');
                die();
            }
        } else {
            header('Location: ' . 'index.php?username=' . $_SESSION['logged']['username'] . '&error=Wrong email format!');
            die();
        }
    }
    // update username
    else if (isset($_POST['username'])) {
        $username = test_input($_POST['username']);
        if (strlen($username) >= 3 && strlen($username) <= 20) {
            if (isUserNameAvailable($username)) {
                $sql = "UPDATE users SET username = ? WHERE id = ?";
                $stmt = $conn->prepare($sql);
                if ($stmt->execute([$username, $_SESSION['logged']['id']])) {
                    $_SESSION['logged']['username'] = $username;
                    unset($_COOKIE['username']);
                    unset($_COOKIE['password']);
                    header('Location: ' . 'index.php?username=' . $username . '&success=Username updated');
                } else header('Location: ' . 'index.php?username=' . $_SESSION['logged']['username'] . '&error=Something went wrong while updating your username!');
            } else {
                header('Location: ' . 'index.php?username=' . $_SESSION['logged']['username'] . '&error=Username already taken!');
                die();
            }
        } else {
            header('Location: ' . 'index.php?username=' . $_SESSION['logged']['username'] . '&error=Username must be between 3 and 20 characters long!');
        }
    }
    // update password 
    else if (isset($_POST['password'], $_POST['new_password'], $_POST['confirm_password']) && !empty($_POST['password']) && !empty($_POST['new_password']) && !empty($_POST['confirm_password'])) {
        $password_errors = [];
        $user = null;

        $sql = "SELECT * FROM users WHERE id = ?";
        $stmt = $conn->prepare($sql);
        if ($stmt->execute([$_SESSION['logged']['id']])) {
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            if (!password_verify($_POST['password'], $user['password']))
                $password_errors[] = 'Wrong password!';
            if (strlen($_POST['new_password']) < 8)
                $password_errors[] = 'Password must be at least 8 characters long!';
            if ($_POST['new_password'] != $_POST['confirm_password'])
                $password_errors[] = 'Passwords do not match!';

            if (empty($password_errors)) {
                $password = password_hash($_POST['new_password'], PASSWORD_DEFAULT);
                $sql = "UPDATE users SET password = ? WHERE id = ?";
                $stmt = $conn->prepare($sql);
                if (!$stmt->execute([$password, $user['id']])) {
                    header('Location: ' . 'index.php?username=' . $_SESSION['logged']['username'] . '&error=Something went wrong while updating your password!');
                } else {
                    header('Location: ' . 'index.php?username=' . $_SESSION['logged']['username'] . '&success=Password updated');
                }
            } else {
                header('Location: ' . 'index.php?username=' . $_SESSION['logged']['username'] . '&error=' . $password_errors[0]);
                die();
            }
        }
    }
}
