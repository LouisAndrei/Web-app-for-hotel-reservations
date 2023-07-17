<?php

include 'componentsReview/connect.php';

// Verifică dacă există o valoare "get_id" în parametrii GET și o atribuie variabilei $get_id
if(isset($_GET['get_id'])){
   $get_id = $_GET['get_id'];
}else{
   // Dacă nu există valoarea "get_id" în parametrii GET, setează $get_id ca fiind gol și redirecționează către "all_posts.php"
   $get_id = '';
   header('location:all_posts.php');
}

// Verifică dacă a fost trimisă o cerere de ștergere prin metoda POST
if(isset($_POST['delete_review'])){

   // Preia id-ul review-ului de șters din formularul trimis prin metoda POST și îl filtrează
   $delete_id = $_POST['delete_id'];
   $delete_id = filter_var($delete_id, FILTER_SANITIZE_STRING);

   // Verifică dacă review-ul există în tabela "reviews" pe baza id-ului specificat
   $verify_delete = $conn->prepare("SELECT * FROM `reviews` WHERE id = ?");
   $verify_delete->execute([$delete_id]);
   
   if($verify_delete->rowCount() > 0){
      // Dacă review-ul există, îl șterge din tabela "reviews"
      $delete_review = $conn->prepare("DELETE FROM `reviews` WHERE id = ?");
      $delete_review->execute([$delete_id]);
      $success_msg[] = 'Review-ul a fost sters!';
   }else{  
      $warning_msg[] = 'Review-ul a fost deja sters!';
   }

}

?>

<!DOCTYPE html>
<html lang="en">
<head>

   <meta charset="UTF-8">
   <meta http-equiv="X-UA-Compatible" content="IE=edge">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>Reviews</title>
   <!--  css file link  -->
   <link rel="stylesheet" type="text/css" href="css/review_css.css">

</head>
<body>
   


<!-- inceputul sectiunii view posts   -->

<section class="view-post">

   <div class="heading"><h1>Review-uri</h1> <a href="all_posts.php" class="inline-option-btn" style="margin-top: 0;">inapoi la camere</a></div>

   <?php
      // Selectează postarea cu id-ul specificat din tabela "posts"
      $select_post = $conn->prepare("SELECT * FROM `posts` WHERE id = ? LIMIT 1");
      $select_post->execute([$get_id]);

      if($select_post->rowCount() > 0){
         while($fetch_post = $select_post->fetch(PDO::FETCH_ASSOC)){

        $total_ratings = 0;
        $rating_1 = 0;
        $rating_2 = 0;
        $rating_3 = 0;
        $rating_4 = 0;
        $rating_5 = 0;

        // Selectează toate evaluările asociate postării din tabela "reviews"
        $select_ratings = $conn->prepare("SELECT * FROM `reviews` WHERE post_id = ?");
        $select_ratings->execute([$fetch_post['id']]);
        $total_reivews = $select_ratings->rowCount();

        // Calculează totalul evaluărilor și numărul de evaluări pentru fiecare rating
        while($fetch_rating = $select_ratings->fetch(PDO::FETCH_ASSOC)){
            $total_ratings += $fetch_rating['rating'];
            if($fetch_rating['rating'] == 1){
               $rating_1 += $fetch_rating['rating'];
            }
            if($fetch_rating['rating'] == 2){
               $rating_2 += $fetch_rating['rating'];
            }
            if($fetch_rating['rating'] == 3){
               $rating_3 += $fetch_rating['rating'];
            }
            if($fetch_rating['rating'] == 4){
               $rating_4 += $fetch_rating['rating'];
            }
            if($fetch_rating['rating'] == 5){
               $rating_5 += $fetch_rating['rating'];
            }
        }

        if($total_reivews != 0){
            // Calculează media rating-urilor și o rotunjește la 1 zecimală
            $average = round($total_ratings / $total_reivews, 1);
        }else{
            $average = 0;
        }
        
   ?>
   <div class="row">
      <div class="col">
         <img src="uploaded_files/<?= $fetch_post['image']; ?>" alt="" class="image">
         <h3 class="title"><?= $fetch_post['title']; ?></h3>
      </div>
      <div class="col">
         <div class="flex">
            <div class="total-reviews">
               <h3><?= $average; ?><i class="fas fa-star"></i></h3>
               <p><?= $total_reivews; ?> reviews</p>
            </div>
            <div class="total-ratings">
               <p>
                  <i class="fas fa-star"></i>
                  <i class="fas fa-star"></i>
                  <i class="fas fa-star"></i>
                  <i class="fas fa-star"></i>
                  <i class="fas fa-star"></i>
                  <span><?= $rating_5; ?></span>
               </p>
               <p>
                  <i class="fas fa-star"></i>
                  <i class="fas fa-star"></i>
                  <i class="fas fa-star"></i>
                  <i class="fas fa-star"></i>
                  <span><?= $rating_4; ?></span>
               </p>
               <p>
                  <i class="fas fa-star"></i>
                  <i class="fas fa-star"></i>
                  <i class="fas fa-star"></i>
                  <span><?= $rating_3; ?></span>
               </p>
               <p>
                  <i class="fas fa-star"></i>
                  <i class="fas fa-star"></i>
                  <span><?= $rating_2; ?></span>
               </p>
               <p>
                  <i class="fas fa-star"></i>
                  <span><?= $rating_1; ?></span>
               </p>
            </div>
         </div>
      </div>
   </div>
   <?php
         }
      }else{
         echo '<p class="empty">post is missing!</p>';
      }
   ?>

</section>

<!-- sfarsitul sectiunii view posts  -->

<!-- inceputul sectiunii reviews   -->

<section class="reviews-container">

   <div class="heading"><h1>Pararile altor persoane</h1> <a href="add_review.php?get_id=<?= $get_id; ?>" class="inline-btn" style="margin-top: 0;">adauga review</a></div>

   <div class="box-container">

   <?php
      // Selectăm toate review-urile care au post_id-ul egal cu valoarea din variabila $get_id
      $select_reviews = $conn->prepare("SELECT * FROM `reviews` WHERE post_id = ?");
      $select_reviews->execute([$get_id]);
      
      // Verificăm dacă există cel puțin un review în rezultatul interogării
      if($select_reviews->rowCount() > 0){
         
         // Parcurgem fiecare review folosind o buclă while
         while($fetch_review = $select_reviews->fetch(PDO::FETCH_ASSOC)){
    ?>
   <div class="box" <?php if($fetch_review['user_id'] == $user_id){echo 'style="order: -1;"';}; ?>>
   <?php
         // Selectăm utilizatorul din tabela 'users' care are id-ul egal cu valoarea din coloana 'user_id' din tabela 'reviews'
         $select_user = $conn->prepare("SELECT * FROM `users` WHERE id = ?");
         $select_user->execute([$fetch_review['user_id']]);
         
         // Parcurgem rezultatul interogării pentru a obține informațiile despre utilizator
         while($fetch_user = $select_user->fetch(PDO::FETCH_ASSOC)){
      ?>

      <div class="user">
         <?php if($fetch_user['image'] != ''){ ?>
            <img src="uploaded_files/<?= $fetch_user['image']; ?>" alt="">
         <?php }else{ ?>   
            <!-- Dacă nu există un utilizator în rezultatul interogării, afișăm un titlu alternativ -->
            <h3><?= substr($fetch_user['name'], 0, 1); ?> </h3> 
         <?php }; ?>   
         <div>
            <!-- Afișăm numele utilizatorului -->
            <p><?= $fetch_user['name']; ?></p>
            <!-- Afișăm data review-ului -->
            <span><?= $fetch_review['date']; ?></span>
         </div>
      </div>
      <?php }; ?>
      <div class="ratings">
         <?php if($fetch_review['rating'] == 1){ ?>
            <!-- Dacă ratingul este 1, afișăm un paragraf cu un singur stea și fundal roșu -->
            <p style="background:var(--red);"><i class="fas fa-star"></i> <span><?= $fetch_review['rating']; ?></span></p>
         <?php }; ?> 
         <?php if($fetch_review['rating'] == 2){ ?>
            <!-- Dacă ratingul este 2, afișăm un paragraf cu două stele și fundal portocaliu -->
            <p style="background:var(--orange);"><i class="fas fa-star"></i> <span><?= $fetch_review['rating']; ?></span></p>
         <?php }; ?>
         <?php if($fetch_review['rating'] == 3){ ?>
            <!-- Dacă ratingul este 3, afișăm un paragraf cu trei stele și fundal portocaliu -->
            <p style="background:var(--orange);"><i class="fas fa-star"></i> <span><?= $fetch_review['rating']; ?></span></p>
         <?php }; ?>   
         <?php if($fetch_review['rating'] == 4){ ?>
            <!-- Dacă ratingul este 4, afișăm un paragraf cu patru stele și fundal culoarea principală -->
            <p style="background:var(--main-color);"><i class="fas fa-star"></i> <span><?= $fetch_review['rating']; ?></span></p>
         <?php }; ?>
         <?php if($fetch_review['rating'] == 5){ ?>
            <!-- Dacă ratingul este 5, afișăm un paragraf cu cinci stele și fundal culoarea principală -->
            <p style="background:var(--main-color);"><i class="fas fa-star"></i> <span><?= $fetch_review['rating']; ?></span></p>
         <?php }; ?>
      </div>
      <h3 class="title"><?= $fetch_review['title']; ?></h3>
      <?php if($fetch_review['description'] != ''){ ?>
         <!-- Dacă descrierea review-ului nu este goală, afișăm un paragraf cu clasa 'description' -->
         <p class="description"><?= $fetch_review['description']; ?></p>
      <?php }; ?>  
      <?php if($fetch_review['user_id'] == $user_id){ ?>
         <!-- Dacă user_id-ul review-ului este egal cu user_id-ul curent, afișăm un formular pentru editarea și ștergerea review-ului -->
         <form action="" method="post" class="flex-btn">
            <input type="hidden" name="delete_id" value="<?= $fetch_review['id']; ?>">
            <a href="update_review.php?get_id=<?= $fetch_review['id']; ?>" class="inline-option-btn">editeaza review-ul</a>
            <input type="submit" value="sterge review-ul" class="inline-delete-btn" name="delete_review" onclick="return confirm('sterge acest review?');">
         </form>
      <?php }; ?>   
   </div>
   <?php
         }
      }else{
         // Dacă nu există niciun review, afișăm un paragraf cu clasa 'empty'
         echo '<p class="empty">nu au fost adăugate recenzii încă!</p>';
      }
   ?>

   </div>

</section>

<!-- sfarsitul sectiunii reviews  -->




<!-- sweetalert cdn link  -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/sweetalert/2.1.2/sweetalert.min.js"></script>

<!-- js file link  -->
<script src="jsReview/script.js"></script>

<?php include 'componentsReview/alers.php'; ?>

</body>
</html>