<?php

// Includerea fișierului de conexiune la baza de date
include 'componentsBookingCheckout/connect.php';

// Verificarea existenței user_id-ului în cookie sau generarea unuia nou
if(isset($_COOKIE['user_id'])){
   $user_id = $_COOKIE['user_id'];
}else{
   setcookie('user_id', create_unique_id(), time() + 60*60*24*30);
}

// Actualizarea cantității din coș
// Verificăm dacă există o cerere de actualizare a coșului prin POST.
if(isset($_POST['update_cart'])){

   // Obținem id-ul elementului din coșul de cumpărături din cerere și îl filtrăm pentru a preveni inserarea de date nedorite.
   $cart_id = $_POST['cart_id'];
   $cart_id = filter_var($cart_id, FILTER_SANITIZE_STRING);
   
   // Obținem cantitatea actualizată din cerere și o filtrăm pentru a preveni inserarea de date nedorite.
   $qty = $_POST['qty'];
   $qty = filter_var($qty, FILTER_SANITIZE_STRING);

   // Pregătim o interogare pentru a actualiza cantitatea elementului din tabelul "cart" folosind id-ul corespunzător.
   $update_qty = $conn->prepare("UPDATE `cart` SET qty = ? WHERE id = ?");
   $update_qty->execute([$qty, $cart_id]);

   // Adăugăm un mesaj de succes într-un vector de mesaje pentru a fi afișat utilizatorului.
   $success_msg[] = 'Coșul a fost actualizat!';

}

// Ștergerea unui produs din coș
// Verificăm dacă există o cerere de ștergere a unui element din coșul de cumpărături prin POST.
if(isset($_POST['delete_item'])){

   // Obținem id-ul elementului din coșul de cumpărături din cerere și îl filtrăm pentru a preveni inserarea de date nedorite.
   $cart_id = $_POST['cart_id'];
   $cart_id = filter_var($cart_id, FILTER_SANITIZE_STRING);
   
   // Pregătim o interogare pentru a verifica dacă elementul există în tabelul "cart".
   $verify_delete_item = $conn->prepare("SELECT * FROM `cart` WHERE id = ?");
   $verify_delete_item->execute([$cart_id]);

   // Verificăm dacă interogarea a returnat cel puțin un rezultat, adică elementul există în coșul de cumpărături.
   if($verify_delete_item->rowCount() > 0){
      // Dacă elementul există, pregătim o interogare pentru a șterge elementul din tabelul "cart".
      $delete_cart_id = $conn->prepare("DELETE FROM `cart` WHERE id = ?");
      $delete_cart_id->execute([$cart_id]);
      // Adăugăm un mesaj de succes într-un vector de mesaje pentru a fi afișat utilizatorului.
      $success_msg[] = 'Camera a fost ștearsă din coș!';
   }else{
      // Dacă elementul nu există, adăugăm un mesaj de avertizare într-un alt vector de mesaje pentru a fi afișat utilizatorului.
      $warning_msg[] = 'Camera a fost deja ștearsă din coș!';
   } 

}

// Golirea coșului
// Verificăm dacă există o cerere de golire a coșului prin POST.
if(isset($_POST['empty_cart'])){
   
   // Pregătim o interogare pentru a verifica dacă există elemente în coșul de cumpărături pentru utilizatorul curent.
   $verify_empty_cart = $conn->prepare("SELECT * FROM `cart` WHERE user_id = ?");
   $verify_empty_cart->execute([$user_id]);

   // Verificăm dacă interogarea a returnat cel puțin un rezultat, adică există elemente în coșul de cumpărături.
   if($verify_empty_cart->rowCount() > 0){
      // Dacă există elemente în coș, pregătim o interogare pentru a le șterge din tabelul "cart".
      $delete_cart_id = $conn->prepare("DELETE FROM `cart` WHERE user_id = ?");
      $delete_cart_id->execute([$user_id]);
      // Adăugăm un mesaj de succes într-un vector de mesaje pentru a fi afișat utilizatorului.
      $success_msg[] = 'Coșul a fost golit!';
   }else{
      // Dacă coșul este deja gol, adăugăm un mesaj de avertizare într-un alt vector de mesaje pentru a fi afișat utilizatorului.
      $warning_msg[] = 'Coșul a fost deja golit!';
   } 

}

?>

<!DOCTYPE html>
<html lang="en">
<head>

   <meta charset="UTF-8">
   <meta http-equiv="X-UA-Compatible" content="IE=edge">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>Cos de cumparaturi</title>
   <link rel="stylesheet" href="css/checkout_css.css">

</head>
<body>
   
<?php include 'componentsBookingCheckout/header.php'; ?>

<section class="products">

   <h1 class="heading">Cos de cumparaturi</h1>

   <div class="box-container">

   <?php
      $grand_total = 0;

      // Selectează toate produsele din coșul utilizatorului
      $select_cart = $conn->prepare("SELECT * FROM `cart` WHERE user_id = ?");
      $select_cart->execute([$user_id]);

      if($select_cart->rowCount() > 0){
         while($fetch_cart = $select_cart->fetch(PDO::FETCH_ASSOC)){

            // Selectează informațiile despre produsul din coș din tabela "products"
            $select_products = $conn->prepare("SELECT * FROM `products` WHERE id = ?");
            $select_products->execute([$fetch_cart['product_id']]);

            if($select_products->rowCount() > 0){
               $fetch_product = $select_products->fetch(PDO::FETCH_ASSOC);
      
   ?>
   <form action="" method="POST" class="box">
      <input type="hidden" name="cart_id" value="<?= $fetch_cart['id']; ?>">
      <img src="uploaded_files/<?= $fetch_product['image']; ?>" class="image" alt="">
      <h3 class="name"><?= $fetch_product['name']; ?></h3>
      <div class="flex">
         <p class="price"><i class="fas fa-indian-rupee-sign"></i> <?= $fetch_cart['price']; ?></p>
         <input type="number" name="qty" required min="1" value="<?= $fetch_cart['qty']; ?>" max="99" maxlength="2" class="qty">
         <button type="submit" name="update_cart" class="fas fa-edit">
         </button>
      </div>
      <p class="sub-total">total : <span><i class="fas fa-indian-rupee-sign"></i> <?= $sub_total = ($fetch_cart['qty'] * $fetch_cart['price']); ?></span></p>
      <input type="submit" value="sterge" name="delete_item" class="delete-btn" onclick="return confirm('stergeti aceasta camera?');">
   </form>
   <?php
      $grand_total += $sub_total; // Adaugă subtotalul la totalul general
      }else{
         echo '<p class="empty">nu a fost gasita camera in cos!</p>'; // Afisează un mesaj de eroare dacă produsul nu a fost găsit
      }
      }
   }else{
      echo '<p class="empty">cosul de cumparaturi este gol!</p>'; // Afisează un mesaj dacă coșul de cumpărături este gol
   }
   ?>

   </div>

   <?php if($grand_total != 0){ ?> <!-- Verifică dacă totalul general este diferit de 0 -->
      <div class="cart-total">
         <p>total : <span><i class="fas fa-indian-rupee-sign"></i> <?= $grand_total; ?></span></p> <!-- Afișează totalul general -->
         <form action="" method="POST">
          <input type="submit" value="goleste cosul" name="empty_cart" class="delete-btn" onclick="return confirm('goliți coșul de cumpărături?');"> <!-- Buton pentru golirea coșului de cumpărături cu confirmare -->
         </form>
         <a href="checkout.php" class="btn">finalizare plata</a> <!-- Buton pentru finalizarea plății -->
      </div>
   <?php } ?>

</section>




<script src="https://cdnjs.cloudflare.com/ajax/libs/sweetalert/2.1.2/sweetalert.min.js"></script>

<script src="jsBookingCheckout/script.js"></script>

<?php include 'componentsBookingCheckout/alert.php'; ?>

</body>
</html>