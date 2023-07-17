<?php

include 'componentsBookingCheckout/connect.php';

// Verifică dacă există un cookie "user_id" și îl atribuie variabilei $user_id
if(isset($_COOKIE['user_id'])){
   $user_id = $_COOKIE['user_id'];
}else{
   // Dacă nu există cookie "user_id", creează unul nou și îl setează pentru 30 de zile
   setcookie('user_id', create_unique_id(), time() + 60*60*24*30);
}

// Verifică dacă există o valoare "get_id" în parametrii GET și o atribuie variabilei $get_id
if(isset($_GET['get_id'])){
   $get_id = $_GET['get_id'];
}else{
   // Dacă nu există valoarea "get_id" în parametrii GET, setează $get_id ca fiind gol și redirecționează către "orders.php"
   $get_id = '';
   header('location:orders.php');
}

// Verifică dacă a fost trimisă o cerere de anulare prin metoda POST
if(isset($_POST['cancel'])){

   // Actualizează starea comenzii cu "canceled" în tabela "orders"
   $update_orders = $conn->prepare("UPDATE `orders` SET status = ? WHERE id = ?");
   $update_orders->execute(['canceled', $get_id]);
   header('location:orders.php');

}

?>

<!DOCTYPE html>
<html lang="en">
<head>

   <meta charset="UTF-8">
   <meta http-equiv="X-UA-Compatible" content="IE=edge">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>View Orders</title>
   <link rel="stylesheet" href="css/checkout_css.css">

</head>
<body>
   
<?php include 'componentsBookingCheckout/header.php'; ?>

<section class="order-details">

   <h1 class="heading">detaliile comenzii</h1>

   <div class="box-container">

   <?php
      $grand_total = 0;

      // Selectează comanda cu id-ul specificat din tabela "orders"
      $select_orders = $conn->prepare("SELECT * FROM `orders` WHERE id = ? LIMIT 1");
      $select_orders->execute([$get_id]);

      if($select_orders->rowCount() > 0){
         while($fetch_order = $select_orders->fetch(PDO::FETCH_ASSOC)){

            // Selectează produsul asociat comenzii din tabela "products"
            $select_product = $conn->prepare("SELECT * FROM `products` WHERE id = ? LIMIT 1");
            $select_product->execute([$fetch_order['product_id']]);

            if($select_product->rowCount() > 0){
               while($fetch_product = $select_product->fetch(PDO::FETCH_ASSOC)){

                  // Calculează subtotalul comenzii și adaugă-l la totalul general
                  $sub_total = ($fetch_order['price'] * $fetch_order['qty']);
                  $grand_total += $sub_total;
   ?>
   <div class="box">
      <div class="col">
         <p class="title"><i class="fas fa-calendar"></i><?= $fetch_order['date']; ?></p>
         <img src="uploaded_files/<?= $fetch_product['image']; ?>" class="image" alt="">
         <p class="price"><i class="fas fa-indian-rupee-sign"></i> <?= $fetch_order['price']; ?> x <?= $fetch_order['qty']; ?></p>
         <h3 class="name"><?= $fetch_product['name']; ?></h3>
         <p class="grand-total">total : <span><i class="fas fa-indian-rupee-sign"></i> <?= $grand_total; ?></span></p>
      </div>
      <div class="col">
         <p class="title">detalii</p>
         <p class="user"><i class="fas fa-user"></i><?= $fetch_order['name']; ?></p>
         <p class="user"><i class="fas fa-phone"></i><?= $fetch_order['number']; ?></p>
         <p class="user"><i class="fas fa-envelope"></i><?= $fetch_order['email']; ?></p>
         <p class="user"><i class="fas fa-map-marker-alt"></i><?= $fetch_order['address']; ?></p>
         <p class="title">status</p>
         <p class="status" style="color:<?php if($fetch_order['status'] == 'delivered'){echo 'green';}elseif($fetch_order['status'] == 'canceled'){echo 'red';}else{echo 'orange';}; ?>"><?= $fetch_order['status']; ?></p>
         <?php if($fetch_order['status'] == 'canceled'){ ?>
            <a href="checkout.php?get_id=<?= $fetch_product['id']; ?>" class="btn">comanda iar</a>
         <?php }else{ ?>
         <form action="" method="POST">
            <input type="submit" value="anuleaza comanda" name="cancel" class="delete-btn" onclick="return confirm('anuleaza aceasta comanda?');">
         </form>
         <?php } ?>
      </div>
   </div>
   <?php
            }
         }else{
            echo '<p class="empty">camera nu a fost gasita!</p>';
         }
      }
   }else{
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