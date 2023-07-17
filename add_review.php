<?php

// Includerea fișierului de conectare la baza de date
include 'componentsReview/connect.php';

// Verificăm dacă există parametrul "get_id" în URL
if(isset($_GET['get_id'])){
   $get_id = $_GET['get_id'];
}else{
   // Dacă parametrul lipsește, setăm valoarea vidă pentru "get_id" și redirecționăm către pagina "all_posts.php"
   $get_id = '';
   header('location:all_posts.php');
}

// Verificăm dacă s-a trimis formularul
if(isset($_POST['submit'])){

   // Verificăm dacă utilizatorul este autentificat
   if($user_id != ''){

      // Generăm un ID unic pentru review
      $id = create_unique_id();

      // Obținem și filtrăm titlul review-ului
      $title = $_POST['title'];
      $title = filter_var($title, FILTER_SANITIZE_STRING);

      // Obținem și filtrăm descrierea review-ului
      $description = $_POST['description'];
      $description = filter_var($description, FILTER_SANITIZE_STRING);

      // Obținem și filtrăm rating-ul review-ului
      $rating = $_POST['rating'];
      $rating = filter_var($rating, FILTER_SANITIZE_STRING);

      // Verificăm dacă utilizatorul a mai adăugat deja un review pentru același post
      $verify_review = $conn->prepare("SELECT * FROM `reviews` WHERE post_id = ? AND user_id = ?");
      $verify_review->execute([$get_id, $user_id]);

      if($verify_review->rowCount() > 10){
         // Dacă există deja un review, adăugăm un mesaj de avertizare
         $warning_msg[] = 'Review-ul a fost dejat adaugat!';
      }else{
         // Înserăm review-ul în baza de date
         $add_review = $conn->prepare("INSERT INTO `reviews`(id, post_id, user_id, rating, title, description) VALUES(?,?,?,?,?,?)");
         $add_review->execute([$id, $get_id, $user_id, $rating, $title, $description]);
         $success_msg[] = 'Review-ul a fost adaugat cu succes! Multumim!';
      }

   }else{
      // Dacă utilizatorul nu este autentificat, adăugăm un mesaj de avertizare
      $warning_msg[] = 'Please login first!';
   }

}

?>

<!DOCTYPE html>
<html lang="en">
<head>

   <meta charset="UTF-8">
   <meta http-equiv="X-UA-Compatible" content="IE=edge">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>adauga review</title>
   <!--  css file link  -->
   <link rel="stylesheet" type="text/css" href="css/review_css.css">

</head>
<body>
   


<!-- inceputul sectiunii add review   -->

<section class="account-form">

   <form action="" method="post">
      <h3>adauga parerea ta</h3>
      <p class="placeholder">nume <span>*</span></p>
      <input type="text" name="title" required maxlength="50" placeholder="adauga numele" class="box">
      <p class="placeholder">parerea ta</p>
      <textarea name="description" class="box" placeholder="adauga parerea ta in legatura cu aceasta camera" maxlength="1000" cols="30" rows="10"></textarea>
      <p class="placeholder">review rating <span>*</span></p>
      <select name="rating" class="box" required>
         <option value="1">1</option>
         <option value="2">2</option>
         <option value="3">3</option>
         <option value="4">4</option>
         <option value="5">5</option>
      </select>
      <input type="submit" value="Trimite review" name="submit" class="btn">
      <a href="view_post.php?get_id=<?= $get_id; ?>" class="option-btn">Inapoi</a>
   </form>

</section>

<!-- sfarsitul sectiunii add review  -->




<!-- sweetalert cdn link  -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/sweetalert/2.1.2/sweetalert.min.js"></script>

<!-- js file link  -->
<script src="jsReview/script.js"></script>

<?php include 'componentsReview/alers.php'; ?>

</body>
</html>