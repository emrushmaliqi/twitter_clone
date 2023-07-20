<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.2/font/bootstrap-icons.css" integrity="sha384-b6lVK+yci+bfDmaY1u0zE8YYJt0TZxLEAFyYSLHId4xoVvsrQu3INevFKo+Xir8e" crossorigin="anonymous">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-9ndCyUaIbzAi2FUVXJi0CjmCapSmO7SnpJef0486qhLnuZ2cdeRhO02iuK6FUUVM" crossorigin="anonymous">
  <title>Home</title>
</head>

<body>
  <nav class="navbar navbar-expand-lg bg-body-tertiary px-5 ">
    <div class="container mx-5">
      <a href="/twitter_clone/index.php" class="text-black text-decoration-none">Explore</a>
      <a href="/twitter_clone/following.php" class="text-black text-decoration-none">Following</a>
      <form class="d-flex w-50" role="search" action="/twitter_clone/search.php" method="GET">
        <input class="form-control me-2" name="q" type="search" placeholder="Search" aria-label="Search">
        <button class="btn btn-outline-success" type="submit">Search</button>
      </form>
      <div class="dropdown">
        <button class="btn btn-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
          <i class="bi bi-person-fill"></i>
        </button>
        <ul class="dropdown-menu">
          <li><a class="dropdown-item" href="/twitter_clone/user?username=<?php echo $_SESSION['logged']['username'] ?>">Go to profile</a></li>
          <li><a class="dropdown-item" href="/twitter_clone/logout.php">Logout</a></li>
        </ul>
      </div>
    </div>
  </nav>