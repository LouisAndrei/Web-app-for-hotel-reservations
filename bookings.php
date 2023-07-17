<?php

// Includerea fișierului de conectare la baza de date
include 'components/connect.php';

// Verificăm dacă există cookie-ul "user_id"
if(isset($_COOKIE['user_id'])){
   $user_id = $_COOKIE['user_id'];
}else{
   // Dacă cookie-ul nu există, generăm un ID unic și setăm cookie-ul pentru 30 de zile
   setcookie('user_id', create_unique_id(), time() + 60*60*24*30, '/');
   header('location:index.php');
}

// Verificăm dacă s-a trimis formularul de anulare a rezervării
if(isset($_POST['cancel'])){

   // Obținem și filtrăm ID-ul rezervării
   $booking_id = $_POST['booking_id'];
   $booking_id = filter_var($booking_id, FILTER_SANITIZE_STRING);

   // Verificăm dacă rezervarea există în baza de date
   $verify_booking = $conn->prepare("SELECT * FROM `bookings` WHERE booking_id = ?");
   $verify_booking->execute([$booking_id]);

   if($verify_booking->rowCount() > 0){
      // Dacă rezervarea există, o ștergem din baza de date
      $delete_booking = $conn->prepare("DELETE FROM `bookings` WHERE booking_id = ?");
      $delete_booking->execute([$booking_id]);
      $success_msg[] = 'rezervare anulata cu succes!';
   }else{
      // Dacă rezervarea nu există, afișăm un mesaj de avertizare
      $warning_msg[] = 'deja ati anulat aceasta rezervare!';
   }
   
}

?>

<!DOCTYPE html>
<html lang="en">
<head>

   <meta charset="UTF-8">
   <meta http-equiv="X-UA-Compatible" content="IE=edge">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>bookings</title>

   <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@8/swiper-bundle.min.css" />

   <!-- font awesome cdn link  -->
   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.2.0/css/all.min.css">

   <!-- custom css file link  -->
   <link rel="stylesheet" href="css/primapagina_css.css">

</head>
<body>

<?php include 'components/user_header.php'; ?>

<!-- inceputul sectiunii booking   -->

<section class="bookings">
   <h1 class="heading">rezervarile mele</h1>
   <div class="box-container">
   <?php
      // Selectăm rezervările utilizatorului curent din baza de date
      $select_bookings = $conn->prepare("SELECT * FROM `bookings` WHERE user_id = ?");
      $select_bookings->execute([$user_id]);

      // Verificăm dacă utilizatorul are rezervări
      if($select_bookings->rowCount() > 0){
         // Parcurgem fiecare rezervare utilizând un ciclu while
         while($fetch_booking = $select_bookings->fetch(PDO::FETCH_ASSOC)){
   ?>
   <div class="box">
      <!-- Afișăm informațiile rezervării -->
      <p>nume : <span><?= $fetch_booking['name']; ?></span></p>
      <p>email : <span><?= $fetch_booking['email']; ?></span></p>
      <p>telefon : <span><?= $fetch_booking['number']; ?></span></p>
      <p>check in : <span><?= $fetch_booking['check_in']; ?></span></p>
      <p>check out : <span><?= $fetch_booking['check_out']; ?></span></p>
      <p>camera : <span><?= $fetch_booking['rooms']; ?></span></p>
      <p>adulti : <span><?= $fetch_booking['adults']; ?></span></p>
      <p>copii : <span><?= $fetch_booking['childs']; ?></span></p>
      <!-- Formularul pentru anularea rezervării -->
      <form action="" method="POST">
         <input type="hidden" name="booking_id" value="<?= $fetch_booking['booking_id']; ?>">
         <input type="submit" value="anulează" name="cancel" class="btn" onclick="return confirm('anulati aceasta rezervare?');">
         <a href="view_products.php" class="btn">plată</a>
      </form>
      <!-- Alte informații aici ... -->
      <p>STATUS REZERVARE: 
         <?php
            if($fetch_booking['confirmed'] == 1){
               echo '<span style="color: green;">CONFIRMATA</span>';
            } else {
               echo '<span style="color: red;">NECONFIRMATA</span>';
            }
         ?>
      </p>
      
   </div>
   <?php
    }
   }else{
   ?>   
   <div class="box" style="text-align: center;">
      <p style="padding-bottom: .5rem; text-transform:capitalize;">nu au fost gasite rezervari!</p>
      <a href="index.php#reservation" class="btn">rezerva acum</a>
   </div>
   <?php
   }
   ?>
   </div>
</section>

<!-- sfarsitul sectiunii booking  -->





<?php include 'components/footer.php'; ?>

<script src="https://cdn.jsdelivr.net/npm/swiper@8/swiper-bundle.min.js"></script>

<script src="https://cdnjs.cloudflare.com/ajax/libs/sweetalert/2.1.2/sweetalert.min.js"></script>

<!--  js file link  -->
<script src="js/script.js"></script>

<?php include 'components/message.php'; ?>

</body>
</html>