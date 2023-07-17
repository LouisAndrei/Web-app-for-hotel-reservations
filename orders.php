<?php

// Includem fișierul de conectare la baza de date
include 'componentsBookingCheckout/connect.php';

// Verificăm dacă există un cookie pentru user_id
if(isset($_COOKIE['user_id'])){
   // Dacă există, preluăm valoarea cookie-ului în variabila $user_id
   $user_id = $_COOKIE['user_id'];
}else{
   // Altfel, generăm un id unic și setăm un cookie pentru user_id
   setcookie('user_id', create_unique_id(), time() + 60*60*24*30);
}

?>

<!DOCTYPE html>
<html lang="en">
<head>

   <meta charset="UTF-8">
   <meta http-equiv="X-UA-Compatible" content="IE=edge">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>Comenzile mele</title>
   <link rel="stylesheet" href="css/checkout_css.css">

</head>
<body>
   
<?php include 'componentsBookingCheckout/header.php'; ?>

<section class="orders">

   <h1 class="heading">Comenzile mele</h1>

   <div class="box-container">

   <?php
      // Selectăm comenzile utilizatorului curent și le ordonăm după data
      $select_orders = $conn->prepare("SELECT * FROM `orders` WHERE user_id = ? ORDER BY date DESC");
      $select_orders->execute([$user_id]);

      // Verificăm dacă există comenzi pentru utilizatorul curent
      if($select_orders->rowCount() > 0){
         // Parcurgem fiecare comandă
         while($fetch_order = $select_orders->fetch(PDO::FETCH_ASSOC)){
            // Selectăm produsul asociat comenzii
            $select_product = $conn->prepare("SELECT * FROM `products` WHERE id = ?");
            $select_product->execute([$fetch_order['product_id']]);

            // Verificăm dacă există produsul
            if($select_product->rowCount() > 0){
               // Parcurgem fiecare produs
               while($fetch_product = $select_product->fetch(PDO::FETCH_ASSOC)){
   ?>
   <div class="box" <?php if($fetch_order['status'] == 'canceled'){echo 'style="border:.2rem solid red";';}; ?>>
      <a href="view_order.php?get_id=<?= $fetch_order['id']; ?>">
         <p class="date"><i class="fa fa-calendar"></i><span><?= $fetch_order['date']; ?></span></p>
         <img src="uploaded_files/<?= $fetch_product['image']; ?>" class="image" alt="">
         <h3 class="name"><?= $fetch_product['name']; ?></h3>
         <p class="price"><i class="fas fa-indian-rupee-sign"></i> <?= $fetch_order['price']; ?> x <?= $fetch_order['qty']; ?></p>
         <p class="status" style="color:<?php if($fetch_order['status'] == 'delivered'){echo 'green';}elseif($fetch_order['status'] == 'canceled'){echo 'red';}else{echo 'orange';}; ?>"><?= $fetch_order['status']; ?></p>
      </a>
   </div>
   <?php
               }
            }
         }
      }else{
         // Afisam mesajul de comenzi negasite
         echo '<p class="empty">nu au fost gasite comenzi!</p>';
      }
   ?>

   </div>

</section>




<script src="https://cdnjs.cloudflare.com/ajax/libs/sweetalert/2.1.2/sweetalert.min.js"></script>

<script src="jsBookingCheckout/script.js"></script>

<?php include 'componentsBookingCheckout/alert.php'; ?>

</body>
</html>