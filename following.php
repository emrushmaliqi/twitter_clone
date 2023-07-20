<?php

include './utils.php';
include './layout/header.php';

if (!isLogged()) {
    header('Location: auth/login.php');
    die();
}

$tweets = getTweets($_SESSION['logged']['id'], false, true);

?>

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



<?php include './layout/footer.php>';
