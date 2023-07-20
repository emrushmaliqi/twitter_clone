<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js" integrity="sha384-geWF76RCwLtnZ8qwWowPQNguL3RmwHVBC9FhGdlKrxdiJJigb/j/68SIy3Te4Bkz" crossorigin="anonymous"></script>

<script>
   function like(e) {
      fetch(`/twitter_clone/api/like.php?id=${e.id}&is_liked=${e.getAttribute('data-is-liked')}`)
         .then(res => res.json())
         .then(data => {
            if (data.status == 'success') {
               $likesAmount = e.querySelector('small');
               $likesAmount.innerText = Number($likesAmount.innerText) + (e.getAttribute('data-is-liked') == "true" ? -1 : 1);
               if (e.getAttribute('data-is-liked') == "true") {
                  e.setAttribute('data-is-liked', "false");
               } else {
                  e.setAttribute('data-is-liked', "true");
               }
               e.classList.toggle('bi-heart');
               e.classList.toggle('bi-heart-fill');
            }
         })
         .catch(err => console.error(err))

   }
</script>
</body>

</html>