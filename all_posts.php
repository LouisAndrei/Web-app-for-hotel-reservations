<?php

// Includerea fișierului de conectare la baza de date
include 'componentsReview/connect.php';

?>

<!DOCTYPE html>
<html lang="en">
<head>

   <meta charset="UTF-8">
   <meta http-equiv="X-UA-Compatible" content="IE=edge">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>Review</title>
   <!-- css file link  -->
   <link rel="stylesheet" type="text/css" href="css/review_css.css">

</head>
<body>
   


<!-- inceputul sectiunii view all posts   -->

<section class="all-posts">

   <div class="heading"><h1>Camere <a href="index.php" class="inline-option-btn" style="margin-top: 0;">inapoi la pagina principala</a></h1></div>
   


   <div class="box-container">

   
   <?php
      // Selectăm toate postările din baza de date
      $select_posts = $conn->prepare("SELECT * FROM `posts`");
      $select_posts->execute();

      // Verificăm dacă există postări în rezultatul interogării
      if($select_posts->rowCount() > 0){
         // Parcurgem fiecare postare utilizând un ciclu while
         while($fetch_post = $select_posts->fetch(PDO::FETCH_ASSOC)){

         $post_id = $fetch_post['id'];

         // Numărăm și selectăm review-urile asociate postării curente
         $count_reviews = $conn->prepare("SELECT * FROM `reviews` WHERE post_id = ?");
         $count_reviews->execute([$post_id]);
         $total_reviews = $count_reviews->rowCount();
   ?>
   <div class="box">
      <!-- Afișăm imaginea postării -->
      <img src="uploaded_files/<?= $fetch_post['image']; ?>" alt="" class="image">
      <!-- Afișăm titlul postării -->
      <h3 class="title"><?= $fetch_post['title']; ?></h3>
      <!-- Afișăm numărul total de review-uri pentru postare -->
      <p class="total-reviews"><i class="fas fa-star"></i> <span><?= $total_reviews; ?></span></p>
      <!-- Adăugăm un link către pagina de vizualizare a postării -->
      <a href="view_post.php?get_id=<?= $post_id; ?>" class="inline-btn">Vezi camera</a>
   </div>
   <?php
      }
   }else{
      // Dacă nu există postări, afișăm un mesaj corespunzător
      echo '<p class="empty">no posts added yet!</p>';
   }
   ?>

   </div>

</section>

<!-- sfarsitul sectiunii view all posts  -->




<!-- sweetalert cdn link  -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/sweetalert/2.1.2/sweetalert.min.js"></script>

<!--  js file link  -->
<script src="jsReview/script.js"></script>

<?php include 'componentsReview/alers.php'; ?>

</body>
</html>