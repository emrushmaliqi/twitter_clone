<?php
include './utils.php';
include './layout/header.php';

if (!isLogged()) {
    header("Location: auth/login.php");
    die();
}

$category = 'users';

$results;

if (isset($_GET['q']) && !empty($_GET['q'])) {
    if (isset($_GET['category']) && $_GET['category'] == 'tweets') {
        $category = 'tweets';
    }
    $sql = "SELECT * FROM users WHERE username LIKE :q";
    if ($category == 'tweets') {
        $sql = "SELECT tweets.*, users.username, users.profile_image, count(*) as likes FROM tweets 
        JOIN users ON tweets.user_id = users.id 
        JOIN likes ON tweets.id = likes.tweet_id
        WHERE content LIKE :q
        GROUP BY tweets.id
        ORDER BY tweets.created_at DESC";
    }
    $stmt = $conn->prepare($sql);
    $stmt->bindValue(':q', '%' . $_GET['q'] . '%', PDO::PARAM_STR);
    if ($stmt->execute()) {
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
} else {
    header("Location: index.php");
}

?>

<div class="container">

    <div>
        <form action="<?= $_SERVER['PHP_SELF'] ?>" method="GET" class="mx-auto mt-4 w-50 gap-3 justify-content-center d-flex">
            <button type="submit" name="category" value="users" <?= $category == 'users' ? 'disabled' : '' ?> class="btn btn-primary w-25">Users</button>
            <button type="submit" name="category" value="tweets" <?= $category == 'tweets' ? 'disabled' : '' ?> class="btn btn-primary w-25">Tweets</button>
            <input type="hidden" name="q" value="<?= $_GET['q'] ?>">
        </form>
    </div>

    <?php if (count($results) > 0) : ?>
        <?php if ($category == 'users') :
            foreach ($results as $user) : ?>
                <a href="/twitter_clone/user/?username=<?= $user['username'] ?>" class="text-decoration-none text-black">
                    <div class="d-flex gap-3">
                        <img src="/twitter_clone/uploads/<?= $user['profile_image'] ?>" alt="<?= $user['username'] ?> picture" style="width: 50px; height: 50px" class="rounded-circle">
                        <h4><?= $user['username'] ?></h4>
                    </div>
                </a>
            <?php endforeach; ?>
            <?php else :
            foreach ($results as $tweet) : ?>
                <?php $isLiked = isTweetLiked($tweet['id']); ?>
                <div>
                    <img src="/twitter_clone/uploads/<?= $tweet['profile_image'] ?>" alt="<?php $tweet['username'] ?> picture" style="width: 50px;" class="rounded-circle">
                    <div>
                        <div>
                            <h4> <?= $tweet['username'] ?></h4>
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
</div>
<?php endforeach; ?>
<?php endif; ?>
<?php else : ?>
    <span>No results!</span>
<?php endif; ?>

</div>



<?php
include './layout/footer.php';
?>