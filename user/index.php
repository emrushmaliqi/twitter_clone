<?php
include '../utils.php';

if (!isLogged()) {
    header('Location: auth/login.php');
    die();
}
include '../layout/header.php';

$isLoggedUser = $_GET['username'] == $_SESSION['logged']['username'];

$user = null;

$tweets = [];
$followers = 0;
$following = 0;

if (isset($_GET['username'])) {
    $sql = "SELECT * FROM users WHERE username = ?";
    $stmt = $conn->prepare($sql);
    if ($stmt->execute([$_GET['username']])) {
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($user) {
            $tweets = getTweets($user['id'], true, false, $_GET['page'] ?? 1);
            $sql = "SELECT COUNT(*) FROM follows WHERE follower = ?";
            $followingStmt = $conn->prepare($sql);
            $followingStmt->execute([$user['id']]);
            $following = $followingStmt->fetchColumn();

            $sql = "SELECT COUNT(*) FROM follows WHERE following = ?";
            $followersStmt = $conn->prepare($sql);
            $followersStmt->execute([$user['id']]);
            $followers = $followersStmt->fetchColumn();
        }
    }
}

if (!$isLoggedUser && $user) {
    $sql = "SELECT count(*) FROM follows WHERE follower = ? AND following = ?";
    $stmt = $conn->prepare($sql);
    try {
        $stmt->execute([$_SESSION['logged']['id'], $user['id']]);
        $follow = $stmt->fetchColumn();
        if ($follow == 0)
            $user['is_followed'] = false;
        else
            $user['is_followed'] = true;
    } catch (PDOException $e) {
        print_r($e);
    }
}


?>


<?php if ($user) : ?>
    <style>
        #pic_preview {
            display: none;
        }

        #pic_preview[src] {
            display: block;
        }
    </style>

    <div class="container pt-5 d-flex flex-column align-items-center">
        <div class="d-flex justify-content-center align-items-center gap-4 w-75">
            <img src="../uploads/<?= $user['profile_image'] ?>" alt="<?= $user['username'] ?>" style="width:80px; height:80px; object-fit:cover;" class="img-fluid rounded-circle">
            <div class="w-50">
                <div>
                    <h4 class="mb-0"><?= $user['username'] ?></h4>
                </div>
                <div class="row align-items-center">
                    <span class="col"><?= count($tweets) ?> Tweets</span>
                    <span class="col" data-id="<?= $user['id'] ?>" <?= $followers > 0 ? 'onclick="showFollows(this)" role="button" data-follows-type="followers"' : "" ?>><span id="followersAmount"><?= $followers ?></span> Followers</span>
                    <span class="col" data-id="<?= $user['id'] ?>" <?= $following > 0 ? 'onclick="showFollows(this)" role="button" data-follows-type="followings"' : "" ?>><?= $following ?> Following</span>
                    <?php if ($isLoggedUser) : ?>
                        <button type="button" class="btn btn-secondary col" data-bs-toggle="modal" data-bs-target="#settingsModal">
                            <i role="button" class="bi bi-gear"></i>
                        </button>
                    <?php else : ?>
                        <button type="button" class="btn btn-secondary col" id="follow-btn" data-is-followed="<?= $user['is_followed'] ? '1' : '' ?>" data-id="<?= $user['id'] ?>">
                            <?= $user['is_followed'] ? 'Unfollow <i class="bi bi-person-x-fill"></i>' : 'Follow <i class="bi bi-person-add"></i>' ?>
                        </button>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <div class="mt-5">
            <?php foreach ($tweets as $tweet) : ?>
                <?php $isLiked = isTweetLiked($tweet['id']); ?>
                <div id="tweet-<?= $tweet['id'] ?>" class="px-auto mt-2 row align-items-center" style="min-width:400px;">
                    <div class="col-3 d-flex p-1 align-self-start justify-content-end">
                        <img src="../uploads/<?= $tweet['profile_image'] ?>" alt="<?php $tweet['username'] ?> picture" style="width: 50px; height:50px; object-fit:cover;" class="rounded-circle">
                    </div>
                    <div class="d-flex flex-column justify-content-start align-items-start col-6">
                        <div class="d-flex justify-content-start align-items-center gap-3">
                            <h5> <?= $tweet['username'] ?></h5>
                            <small><?= time_elapsed_string($tweet['created_at']); ?></small>
                        </div>
                        <p><?= $tweet['content'] ?></p>
                    </div>
                    <div class="col-2">
                        <div class="d-flex gap-3">
                            <div class="d-flex flex-column justify-content-center align-items-center">
                                <i role="button" onClick="like(this)" id="<?= $tweet['id'] ?>" data-is-liked="<?= $isLiked ? "true" : "false" ?>" class="text-danger d-flex flex-column bi bi-heart<?php $isLiked && print_r("-fill"); ?> ">
                                    <small class="text-black text-center"><?= $tweet['likes'] ?></small>
                                </i>
                            </div>
                            <?= $isLoggedUser ? '<button type="button" class="btn btn-danger" onclick="deleteTweet(this)" data-id="' . $tweet['id'] . '" ><i class="bi bi-trash"></i></button>' : '' ?>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
        <nav>
            <ul class="pagination mt-3">
                <li class="page-item">
                    <?php if (isset($_GET['page']) && $_GET['page'] > 1) : ?>
                        <a class="page-link" href="?page=<?= isset($_GET['page']) ? $_GET['page'] - 1 : 1; ?>&username=<?= $_GET['username'] ?>">Previous</a>
                    <?php else : ?>
                        <span class="page-link disabled">Previous</span>
                    <?php endif; ?>

                </li>
                <?= isset($_GET['page']) && $_GET['page'] > 1 ? "<li class='page-item'><a class='page-link' href='" . "?page=" . $_GET['page'] - 1 . "&username=" . $_GET['username'] . "'>" . $_GET['page'] - 1 . "</a></li>" : ""; ?>
                <li class="page-item active" aria-current="page">
                    <span class="page-link"><?= isset($_GET['page']) ? $_GET['page'] : 1; ?></span>
                </li>
                <?php if (count($tweets) == 10) : ?>
                    <li class="page-item" aria-current="page">
                        <a href="?page=<?= isset($_GET['page']) ? $_GET['page'] + 1 : 2; ?>&username=<?= $_GET['username'] ?>" class="page-link"><?= isset($_GET['page']) ? $_GET['page'] + 1 : 2; ?></a>
                    </li>
                    <li class="page-item">
                        <a class="page-link" href="?page=<?= isset($_GET['page']) ? $_GET['page'] + 1 : 2; ?>&username=<?= $_GET['username'] ?>">Next</a>
                    </li>
                <?php endif; ?>
            </ul>
        </nav>
    </div>

    <?php if (isset($_GET['success'])) : ?>
        <div style="bottom: 0; right: 10px;" class="position-fixed alert alert-success ?>" role="alert" id="anyAlert"><?= $_GET['success'] ?></div>
    <?php elseif (isset($_GET['error'])) : ?>
        <div style="bottom: 0; right: 10px;" class="position-fixed alert alert-warning ?>" role="alert" id="anyAlert"><?= $_GET['error'] ?></div>
    <?php endif; ?>
    <!-- Follows Modal  -->
    <div class="modal fade" tabindex="-1" id="followsModal">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title text-capitalize" id="followsModalLabel"></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body d-flex flex-column gap-3 pt-2 pb-4">
                </div>
            </div>
        </div>
    </div>
    <?php if ($isLoggedUser) : ?>

        <div class="modal fade" <?php if (isset($_GET['edit'])) echo 'data-bs-backdrop="static" data-bs-keyboard="false"' ?> id="settingsModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
            <?php if (isset($_GET['edit'])) : ?>
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h1 class="modal-title fs-5" id="exampleModalLabel">Edit <?= $_GET['edit'] ?></h1>
                        </div>
                        <form action="update.php" method="post" enctype="multipart/form-data">
                            <div class="modal-body d-flex flex-column gap-2 mb-2">
                                <?php if ($_GET['edit'] == 'picture') : ?>
                                    <div class="form-group">
                                        <label for="profile_image">Profile picture:</label>
                                        <input type="file" name="profile_image" id="profile_image" class="form-control" accept="image/*" onchange="loadFile(event)">
                                    </div>
                                    <div class="d-flex justify-content-center">
                                        <img id="pic_preview" class="w-75 rounded-circle" style="aspect-ratio: 1/1; object-fit:cover;" />
                                    </div>
                                    <script>
                                        const loadFile = function(event) {
                                            const output = document.getElementById('pic_preview');
                                            output.src = URL.createObjectURL(event.target.files[0]);
                                            output.onload = function() {
                                                URL.revokeObjectURL(output.src) // free memory
                                            }
                                        };
                                    </script>
                                <?php elseif ($_GET['edit'] == 'username') : ?>
                                    <div class="form-group">
                                        <label for="username">Username:</label>
                                        <input type="text" name="username" id="username" class="form-control" value="<?= $user['username'] ?>">
                                    </div>
                                <?php elseif ($_GET['edit'] == 'email') : ?>
                                    <div class="form-group">
                                        <label for="email">Email:</label>
                                        <input type="email" name="email" id="email" class="form-control" value="<?= $user['email'] ?>">
                                    </div>
                                <?php elseif ($_GET['edit'] == 'password') : ?>
                                    <div class="form-group">
                                        <label for="password">Current password:</label>
                                        <input type="password" name="password" id="password" class="form-control">
                                    </div>
                                    <div class="form-group">
                                        <label for="new_password">New password:</label>
                                        <input type="password" name="new_password" id="new_password" class="form-control">
                                    </div>
                                    <div class="form-group">
                                        <label for="current_password">Confirm password:</label>
                                        <input type="password" name="confirm_password" id="confirm_password" class="form-control">
                                    </div>
                                <?php endif; ?>
                            </div>
                            <div class="modal-footer">
                                <button type="submit" class="btn btn-primary" name='submit'>Save changes</button>
                                <a href="index.php?username=<?= $user['username'] ?>" class="btn btn-secondary">Close</a>
                            </div>
                        </form>
                    </div>
                </div>
        </div>
        <script>
            window.onload = () => {
                const myModal = new bootstrap.Modal('#settingsModal');
                myModal.show();
            }
        </script>
    <?php else : ?>
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h1 class="modal-title fs-5" id="exampleModalLabel">Edit</h1>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body d-flex flex-column justify-content-center gap-3">
                    <a href="/twitter_clone/user/index.php?username=<?= $user['username'] ?>&edit=picture" class="btn btn-primary">Edit profile picture</a>
                    <a href="/twitter_clone/user/index.php?username=<?= $user['username'] ?>&edit=username" class="btn btn-primary">Edit username</a>
                    <a href="/twitter_clone/user/index.php?username=<?= $user['username'] ?>&edit=email" class="btn btn-primary">Edit email</a>
                    <a href="/twitter_clone/user/index.php?username=<?= $user['username'] ?>&edit=password" class="btn btn-primary">Edit password</a>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>
<?php endif; ?>
<?php else : ?>
    <h1 class="mt-4">User not found</h1>
<?php endif; ?>

<script>
    const followBtn = document.getElementById('follow-btn');
    const followersSpan = document.getElementById('followersAmount');
    const alert = document.getElementById('anyAlert')



    if (alert)
        setTimeout(() => alert.remove(), 3000)

    if (followBtn) {
        followBtn.addEventListener('click', e => {
            fetch(`/twitter_clone/api/follow.php?id=${e.currentTarget.dataset.id}`)
                .then(res => res.json())
                .then(data => {
                    if (data.status == 'success') {
                        let isFollowed = followBtn.dataset.isFollowed == "1" ? "" : "1";
                        followBtn.dataset.isFollowed = isFollowed;
                        followBtn.innerHTML = isFollowed ? 'Unfollow <i class="bi bi-person-x-fill"></i>' : 'Follow <i class="bi bi-person-add"></i>';
                        followBtn.classList.remove(isFollowed ? 'btn-primary' : 'btn-secondary')
                        followBtn.classList.add(isFollowed ? 'btn-secondary' : 'btn-primary')
                        followersSpan.innerText = (isFollowed ? 1 : -1) + Number(followersSpan.innerText);
                    }
                }).catch(err => console.error(err))
        })
    }
    const followsModal = document.getElementById('followsModal')


    function showFollows(e) {
        const type = e.dataset.followsType;
        const userId = e.dataset.id
        fetch(`/twitter_clone/api/follows.php?id=${userId}&type=${type}`)
            .then(res => res.json())
            .then(res => {
                const {
                    status,
                    data
                } = res
                if (status != "success") {
                    alert("Something went wrong")
                    return;
                }
                followsModal.querySelector('.modal-title').innerText = `${type}`
                followsModal.querySelector('.modal-body').innerHTML = data.map(user => `<div class="row align-items-center" id='follow-${user.id}' style='height: 50px;'>
                        <a class="col-2 p-0 mx-2 rounded-circle" style="height: 50px; width: 50px" href="index.php?username=${user.username}">
                        <img src="../uploads/${user.profile_image}" class="img-fluid rounded-circle" />
                        </a>
                        <p class="col-8 m-0"><a href="index.php?username=${user.username}" class="text-decoration-none text-black" >${user.username}</a></p>
                        ${res.is_logged ? `<button role='button' class='col-2 btn btn-secondary' onclick='removeFollow(this)' data-id='${user.id}' >Remove</button>` :
                        user.is_followed != -1 ? 
                            `<button  role="button" onclick="follow(this)" class="btn ${user.is_followed ? 'btn-secondary' : 'btn-primary'} col-2" style="width: 90px" data-id="${user.id}" data-is-followed="${user.is_followed}" >
                                ${user.is_followed ? 'Unfollow' : 'Follow'}
                            </button>` : ""}
        
                </div>`).join('')
                new bootstrap.Modal(followsModal).show()
            })
            .catch(err => console.error(err))
    }

    function follow(e) {
        fetch(`/twitter_clone/api/follow.php?id=${e.dataset.id}`)
            .then(res => res.json())
            .then(data => {
                console.log(data)
                if (data.status == "success") {
                    const isFollowed = e.dataset.isFollowed == "1" ? "0" : "1";
                    e.dataset.isFollowed = isFollowed;
                    e.innerHTML = isFollowed == "1" ? 'Unfollow' : 'Follow';
                    e.classList.remove(isFollowed == "1" ? 'btn-primary' : 'btn-secondary');
                    e.classList.add(isFollowed == "1" ? 'btn-secondary' : 'btn-primary');

                }
            })
            .catch(err => console.error(err))
    }

    function removeFollow(e) {
        const id = e.dataset.id;
        if (confirm("Are you sure you want to remove this follower? \nThis action cannot be undone"))
            fetch(`/twitter_clone/api/remove-follow.php?id=${id}`)
            .then(res => res.json())
            .then(({
                status,
                message
            }) => {
                if (status == "success") {
                    document.getElementById(`follow-${id}`).remove();
                } else {
                    console.error(message);
                }
            })
    }

    function deleteTweet(e) {
        const id = e.dataset.id;
        console.log(id)
        if (confirm("Are you sure you want to delete this tweet? \nThis action cannot be undone"))
            fetch(`/twitter_clone/api/remove-tweet.php?id=${id}`)
            .then(res => res.json())
            .then(({
                status,
                message
            }) => {
                console.log(status, message);
                if (status == "success") {
                    document.getElementById(`tweet-${id}`).remove();
                } else console.error(message);
            })
            .catch(err => console.error(err))
    }
</script>


<?php include '../layout/footer.php'; ?>