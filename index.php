<?php
include 'utils.php';

if (!isLogged()) {
    header('Location: auth/login.php');
    die();
}
include 'layout/header.php';

$err = '';
$tweets = getTweets($_SESSION['logged']['id'], false, false, $_GET['page'] ?? 1);



function postTweet()
{
    global $conn;
    $tweet = test_input($_POST['tweet']);

    if (strlen($tweet) == 0)
        throw new Exception("Tweet cannot be empty");

    $sql = "INSERT INTO tweets (user_id, content) VALUES (?, ?)";
    $stmt = $conn->prepare($sql);
    if ($stmt->execute([$_SESSION['logged']['id'], $tweet]))
        header('Location: index.php');
    else
        throw new Exception("Error while posting tweet");
}

if (isset($_POST['submit'], $_POST['tweet'])) {
    try {
        postTweet();
    } catch (Exception $e) {
        $err = $e->getMessage();
    }
}






?>


<div class="container mt-3 d-flex flex-column align-items-center">

    <form action="<?= $_SERVER['PHP_SELF']; ?>" method="post" class="mt-4 mx-auto" style="width: 75%;">
        <textarea name="tweet" style="width:100%; height:40px; resize: none; margin: 0 auto;" class="form-control" placeholder="What's happening..." id="tweetText"></textarea>
        <button type="submit" name="submit" class="btn btn-primary d-block ms-auto mt-2">Tweet</button>
        <?php strlen($err) > 0 && print("<p class='text-danger'>$err</p>"); ?>
    </form>
    <div>
        <?php if (!empty($tweets)) : ?>
            <?php foreach ($tweets as $tweet) : ?>
                <?php $isLiked = isTweetLiked($tweet['id']); ?>
                <div class="px-auto mt-2 row align-items-center" style="min-width: 400px;">
                    <div class="col-3 d-flex p-1 align-self-start justify-content-end">
                        <img src="./uploads/<?= $tweet['profile_image'] ?>" alt="<?= $tweet['username'] ?> picture" class="rounded-circle" style="width: 50px; height: 50px; object-fit:cover;">
                    </div>
                    <div class="d-flex flex-column justify-content-start align-items-start col-6">
                        <div class="d-flex justify-content-start align-items-center gap-3">
                            <h5> <a href="/twitter_clone/user/?username=<?= $tweet['username'] ?>" class="text-decoration-none text-black"><?= $tweet['username'] ?></a></h5>
                            <small><?= time_elapsed_string($tweet['created_at']); ?></small>
                        </div>
                        <p><?= $tweet['content'] ?></p>
                    </div>
                    <div class="col-2">
                        <div class="d-flex flex-column justify-content-center align-items-start">
                            <i role="button" onClick="like(this)" id="<?= $tweet['id'] ?>" data-is-liked="<?= $isLiked ? "true" : "false" ?>" class="text-danger d-flex flex-column bi bi-heart<?php $isLiked && print_r("-fill"); ?> ">
                                <small class="text-black text-center"><?= $tweet['likes'] ?></small>
                            </i>
                        </div>
                    </div>
                </div>




            <?php endforeach; ?>
        <?php endif; ?>
    </div>
    <nav>
        <ul class="pagination mt-3">
            <li class="page-item">
                <?php if (isset($_GET['page']) && $_GET['page'] > 1) : ?>
                    <a class="page-link" href="?page=<?= isset($_GET['page']) ? $_GET['page'] - 1 : 1; ?>">Previous</a>
                <?php else : ?>
                    <span class="page-link disabled">Previous</span>
                <?php endif; ?>

            </li>
            <?= isset($_GET['page']) && $_GET['page'] > 1 ? "<li class='page-item'><a class='page-link' href='" . "?page=" . $_GET['page'] - 1 . "'>" . $_GET['page'] - 1 . "</a></li>" : ""; ?>
            <li class="page-item active" aria-current="page">
                <span class="page-link"><?= isset($_GET['page']) ? $_GET['page'] : 1; ?></span>
            </li>
            <li class="page-item" aria-current="page">
                <a href="?page=<?= isset($_GET['page']) ? $_GET['page'] + 1 : 2; ?>" class="page-link"><?= isset($_GET['page']) ? $_GET['page'] + 1 : 2; ?></a>
            </li>
            <li class="page-item">
                <a class="page-link" href="?page=<?= isset($_GET['page']) ? $_GET['page'] + 1 : 2; ?>">Next</a>
            </li>
        </ul>
    </nav>
</div>


<script>
    const tweetTxt = document.getElementById('tweetText');

    let size = 1;
    tweetTxt.addEventListener('keydown', e => {
        if (e.key == "Backspace") {
            if (e.target.value.length < (window.innerWidth / 11) * size + window.innerWidth / 11) {
                if (size > 1)
                    size--;
                e.target.style.height = `${10 + 30 * size}px`;

            }
        } else if (e.target.value.length >= (window.innerWidth / 11) * size) {
            size++;
            e.target.style.height = `${10 + 30 * size}px`;
        }

    })
</script>

<?php

include 'layout/footer.php';
