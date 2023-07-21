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
    $sql = "SELECT users.*, count(tweets.id) as tweets_count FROM users
    LEFT JOIN tweets ON users.id = tweets.user_id
    WHERE users.username LIKE :q
    GROUP BY users.id";
    if ($category == 'tweets') {
        $sql = "SELECT tweets.*, users.username, users.profile_image, count(likes.tweet_id) as likes FROM tweets 
        JOIN users ON tweets.user_id = users.id 
        LEFT JOIN likes ON tweets.id = likes.tweet_id
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

<div class="container mt-4 d-flex flex-column align-items-center">

    <div>
        <form action="<?= $_SERVER['PHP_SELF'] ?>" method="GET" class="d-flex gap-3 justify-content-center">
            <button type="submit" name="category" value="users" <?= $category == 'users' ? 'disabled' : '' ?> class="btn btn-primary w-50">Users</button>
            <button type="submit" name="category" value="tweets" <?= $category == 'tweets' ? 'disabled' : '' ?> class="btn btn-primary w-50">Tweets</button>
            <input type="hidden" name="q" value="<?= $_GET['q'] ?>">
        </form>
    </div>
    <div class="my-5 <?= $category == 'users' ? "d-flex flex-wrap gap-4" : "" ?>">
        <?php if (count($results) > 0) : ?>
            <?php if ($category == 'users') :
                foreach ($results as $user) : ?>
                    <div class="card" style="width:12rem;">
                        <img src="/twitter_clone/uploads/<?= $user['profile_image'] ?>" alt="<?= $user['username'] ?> picture" style="aspect-ratio: 1/1;" class=" rounded-circle card-img-top">
                        <div class="p-2 div-body">
                            <h4 class="card-title"><?= $user['username'] ?></h4>
                            <p class="card-text"><?= $user['tweets_count'] ?> Tweets</p>
                            <a class="btn btn-primary" href="/twitter_clone/user/?username=<?= $user['username'] ?>">Go to profile</a>
                        </div>

                    </div>
                <?php endforeach; ?>
                <?php else :
                foreach ($results as $tweet) : ?>
                    <?php $isLiked = isTweetLiked($tweet['id']); ?>
                    <div class="px-auto mt-2 row align-items-center" style="min-width: 600px;">
                        <div class="col-3 d-flex p-1 align-self-start justify-content-end">
                            <img src="/twitter_clone/uploads/<?= $tweet['profile_image'] ?>" alt="<?php $tweet['username'] ?> picture" style="width: 50px; height: 50px; object-fit: cover;" class="rounded-circle">
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
<?php else : ?>
    <span>No results!</span>
<?php endif; ?>

</div>



<?php
include './layout/footer.php';
?>