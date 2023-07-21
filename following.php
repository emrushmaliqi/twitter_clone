<?php

include './utils.php';
include './layout/header.php';

if (!isLogged()) {
    header('Location: auth/login.php');
    die();
}

$tweets = getTweets($_SESSION['logged']['id'], false, true, $_GET['page'] ?? 1);

?>

<div class="container pt-5 d-flex flex-column align-items-center">
    <div>
        <?php foreach ($tweets as $tweet) : ?>
            <?php $isLiked = isTweetLiked($tweet['id']); ?>
            <div class="px-auto mt-2 row align-items-center" style="min-width: 400px;">
                <div class="col-3 d-flex p-1 align-self-start justify-content-end">
                    <img src="./uploads/<?= $tweet['profile_image'] ?>" alt="<?= $tweet['username'] ?> picture" class="rounded-circle" style="width: 50px; height: 50px; object-fit: cover;'">
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
    </div>
    <nav>
        <ul class="pagination mx-auto">
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
            <?php if (count($tweets) == 10) : ?>
                <li class="page-item" aria-current="page">
                    <a href="?page=<?= isset($_GET['page']) ? $_GET['page'] + 1 : 2; ?>" class="page-link"><?= isset($_GET['page']) ? $_GET['page'] + 1 : 2; ?></a>
                </li>
            <?php endif; ?>
            <li class="page-item">
                <a class="page-link <?= count($tweets) == 10 ? "" : "disabled" ?>" href=" ?page=<?= isset($_GET['page']) ? $_GET['page'] + 1 : 2; ?>">Next</a>
            </li>
        </ul>
    </nav>
</div>



<?php include './layout/footer.php>';
