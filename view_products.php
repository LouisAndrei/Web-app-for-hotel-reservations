<?php

// Includem fișierul de conexiune la baza de date
include 'componentsBookingCheckout/connect.php';

// Verificăm dacă există un cookie pentru user_id
if(isset($_COOKIE['user_id'])){
   $user_id = $_COOKIE['user_id'];
}else{
   // Dacă nu există, generăm un user_id unic și creăm cookie-ul pentru acesta
   setcookie('user_id', create_unique_id(), time() + 60*60*24*30);
}

// Verificăm dacă a fost trimis formularul pentru adăugarea în coș
if(isset($_POST['add_to_cart'])){

   // Generăm un id unic pentru înregistrarea din coș
   $id = create_unique_id();

   // Obținem id-ul produsului și cantitatea din formular
   $product_id = $_POST['product_id'];
   $product_id = filter_var($product_id, FILTER_SANITIZE_STRING);
   $qty = $_POST['qty'];
   $qty = filter_var($qty, FILTER_SANITIZE_STRING);
   
   // Verificăm dacă există deja un produs cu același user_id și product_id în coș
   $verify_cart = $conn->prepare("SELECT * FROM `cart` WHERE user_id = ? AND product_id = ?");   
   $verify_cart->execute([$user_id, $product_id]);

   // Verificăm numărul maxim de înregistrări în coș pentru user_id-ul curent
   $max_cart_items = $conn->prepare("SELECT * FROM `cart` WHERE user_id = ?");
   $max_cart_items->execute([$user_id]);

   // Verificăm rezultatele verificărilor și efectuăm acțiunile corespunzătoare
   if($verify_cart->rowCount() > 0){
      $warning_msg[] = 'ati adaugat deja camera in cos!';
   }elseif($max_cart_items->rowCount() == 10){
      $warning_msg[] = 'cosul este plin';
   }else{

      // Obținem prețul produsului din baza de date
      $select_price = $conn->prepare("SELECT * FROM `products` WHERE id = ? LIMIT 1");
      $select_price->execute([$product_id]);
      $fetch_price = $select_price->fetch(PDO::FETCH_ASSOC);

      // Inserăm înregistrarea în coș
      $insert_cart = $conn->prepare("INSERT INTO `cart`(id, user_id, product_id, price, qty) VALUES(?,?,?,?,?)");
      $insert_cart->execute([$id, $user_id, $product_id, $fetch_price['price'], $qty]);
      $success_msg[] = 'camera a fost adaugata in cos!';
   }

}

?>

<!DOCTYPE html>
<html lang="en">
<head>

   <meta charset="UTF-8">
   <meta http-equiv="X-UA-Compatible" content="IE=edge">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>Camere</title>
   <link rel="stylesheet" href="css/checkout_css.css">

</head>
<body>
   
<?php include 'componentsBookingCheckout/header.php'; ?>

<section class="products">

   <h1 class="heading">Camere</h1>

   <div class="box-container">

   <?php 
      // Selectăm toate produsele din tabelul "products"
      $select_products = $conn->prepare("SELECT * FROM `products`");
      $select_products->execute();
      if($select_products->rowCount() > 0){
         while($fetch_prodcut = $select_products->fetch(PDO::FETCH_ASSOC)){
   ?>
   <form action="" method="POST" class="box">
      <img src="uploaded_files/<?= $fetch_prodcut['image']; ?>" class="image" alt="">
      <h3 class="name"><?= $fetch_prodcut['name'] ?></h3>
      <input type="hidden" name="product_id" value="<?= $fetch_prodcut['id']; ?>">
      <div class="flex">
         <p class="price"><i class="fas fa-indian-rupee-sign"></i><?= $fetch_prodcut['price'] ?></p>
         <input type="number" name="qty" required min="1" value="1" max="99" maxlength="2" class="qty">
      </div>
      <input type="submit" name="add_to_cart" value="Adauga in cos" class="btn">
      <a href="checkout.php?get_id=<?= $fetch_prodcut['id']; ?>" class="delete-btn">Rezerva acum</a>
   </form>
   <?php
      }
   }else{
      // Dacă nu există produse, afișăm un mesaj corespunzător
      echo '<p class="empty">no products found!</p>';
   }
   ?>

   </div>

</section>




<script src="https://cdnjs.cloudflare.com/ajax/libs/sweetalert/2.1.2/sweetalert.min.js"></script>

<script src="jsBookingCheckout/script.js"></script>

<?php include 'componentsBookingCheckout/alert.php'; ?>

</body>
</html>