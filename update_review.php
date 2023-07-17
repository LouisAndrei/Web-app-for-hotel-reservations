<?php

include 'componentsReview/connect.php';

if(isset($_GET['get_id'])){ // Verifică dacă există un ID de revizuire în URL
   $get_id = $_GET['get_id']; // Obține ID-ul revizuirii din URL
}else{
   $get_id = '';
   header('location:all_posts.php'); // Redirecționează către pagina cu toate postările în cazul în care lipsește ID-ul de revizuire
}

if(isset($_POST['submit'])){ // Verifică dacă s-a apăsat butonul de trimitere a revizuirii

   $title = $_POST['title']; // Obține titlul revizuirii din formular
   $title = filter_var($title, FILTER_SANITIZE_STRING); // Filtrare și validare titlu
   $description = $_POST['description']; // Obține descrierea revizuirii din formular
   $description = filter_var($description, FILTER_SANITIZE_STRING); // Filtrare și validare descriere
   $rating = $_POST['rating']; // Obține ratingul revizuirii din formular
   $rating = filter_var($rating, FILTER_SANITIZE_STRING); // Filtrare și validare rating

   $update_review = $conn->prepare("UPDATE `reviews` SET rating = ?, title = ?, description = ? WHERE id = ?"); // Actualizează revizuirea în baza de date
   $update_review->execute([$rating, $title, $description, $get_id]); // Execută interogarea pregătită și actualizează revizuirea

   $success_msg[] = 'Review actualizat cu succes!'; // Mesaj de succes pentru actualizarea revizuirii

}

?>
<!DOCTYPE html>
<html lang="en">
<head>

   <meta charset="UTF-8">
   <meta http-equiv="X-UA-Compatible" content="IE=edge">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>update review</title>
   <!-- css file link  -->
   <link rel="stylesheet" type="text/css" href="css/review_css.css">

</head>
<body>
   


<!-- inceputul sectiunii update reviews   -->

<section class="account-form">

   <?php
      $select_review = $conn->prepare("SELECT * FROM `reviews` WHERE id = ? LIMIT 1");
      $select_review->execute([$get_id]);
      if($select_review->rowCount() > 0){
         while($fetch_review = $select_review->fetch(PDO::FETCH_ASSOC)){
   ?>
   <form action="" method="post">
      <h3>editeaza review-ul</h3>
      <p class="placeholder">nume <span>*</span></p>
      <input type="text" name="title" required maxlength="50" placeholder="adauga numele " class="box" value="<?= $fetch_review['title']; ?>">
      <p class="placeholder">parerea ta</p>
      <textarea name="description" class="box" placeholder="adauga pararea ta in legatura cu aceasta camera" maxlength="1000" cols="30" rows="10"><?= $fetch_review['description']; ?></textarea>
      <p class="placeholder">review rating <span>*</span></p>
      <select name="rating" class="box" required>
         <option value="<?= $fetch_review['rating']; ?>"><?= $fetch_review['rating']; ?></option>
         <option value="1">1</option>
         <option value="2">2</option>
         <option value="3">3</option>
         <option value="4">4</option>
         <option value="5">5</option>
      </select>
      <input type="submit" value="actualizeaza review" name="submit" class="btn">
      <a href="view_post.php?get_id=<?= $fetch_review['post_id']; ?>" class="option-btn">inapoi</a>
   </form>
   <?php
         }
      }else{
         echo '<p class="empty">ceva nu a functionat!</p>';
      }
   ?>

</section>

<!-- sfarsitul sectiunii update reviews  -->




<!-- sweetalert cdn link  -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/sweetalert/2.1.2/sweetalert.min.js"></script>

<!-- js file link  -->
<script src="jsReview/script.js"></script>

<?php include 'componentsReview/alers.php'; ?>

</body>
</html>