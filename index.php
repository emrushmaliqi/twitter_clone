<?php
include 'utils.php';

if (!isLogged()) {
    header('Location: auth/login.php');
    die();
}

$err = '';
$tweets = getTweets($_SESSION['logged']['id'], false);



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





include 'layout/header.php';

?>


<div class="container">

    <form action="<?= $_SERVER['PHP_SELF']; ?>" method="post" class="mt-4 mx-auto" style="width: 75%;">
        <textarea name="tweet" style="width:100%; height:40px; resize: none; margin: 0 auto;" placeholder="What's happening..." id="tweetText"></textarea>
        <button type="submit" name="submit" class="btn btn-primary d-block ms-auto">Tweet</button>
        <?php strlen($err) > 0 && print("<p class='text-danger'>$err</p>"); ?>
    </form>
    <div class="d-flex flex-column gap-2">
        <?php if (!empty($tweets)) : ?>
            <?php foreach ($tweets as $tweet) : ?>
                <?php $isLiked = isTweetLiked($tweet['id']); ?>
                <div>
                    <img src="./uploads/<?= $tweet['profile_image'] ?>" alt="<?= $tweet['username'] ?> picture" class="rounded-circle" style="width: 50px;">
                    <div>
                        <div>
                            <h4> <a href="/twitter_clone/user/?username=<?= $tweet['username'] ?>" class="text-decoration-none text-black"><?= $tweet['username'] ?></a></h4>
                            <p><?= $tweet['content'] ?></p>
                        </div>
                        <div>
                            <div class="d-flex flex-column justify-content-center align-items-center">
                                <i role="button" onClick="like(this)" id="<?= $tweet['id'] ?>" data-is-liked="<?= $isLiked ? "true" : "false" ?>" class="text-danger bi bi-heart<?php $isLiked && print_r("-fill"); ?> ">
                                    <small class="text-black d-block ms-1"><?= $tweet['likes'] ?></small>
                                </i>
                            </div>
                            <small><?= time_elapsed_string($tweet['created_at']); ?></small>
                        </div>
                    </div>
                </div>




            <?php endforeach; ?>
        <?php endif; ?>
    </div>
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
