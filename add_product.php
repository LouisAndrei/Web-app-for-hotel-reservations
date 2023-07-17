<?php

// Includerea fișierului de conectare la baza de date
include 'componentsBookingCheckout/connect.php';

// Verificăm dacă există cookie-ul "user_id"
if(isset($_COOKIE['user_id'])){
   $user_id = $_COOKIE['user_id'];
}else{
   // Dacă cookie-ul nu există, generăm un ID unic și setăm cookie-ul pentru 30 de zile
   setcookie('user_id', create_unique_id(), time() + 60*60*24*30);
}

// Verificăm dacă s-a trimis formularul
if(isset($_POST['add'])){

   // Generăm un ID unic pentru produs
   $id = create_unique_id();

   // Obținem și filtrăm numele produsului
   $name = $_POST['name'];
   $name = filter_var($name, FILTER_SANITIZE_STRING);

   // Obținem și filtrăm prețul produsului
   $price = $_POST['price'];
   $price = filter_var($price, FILTER_SANITIZE_STRING);

   // Obținem numele și extensia fișierului de imagine
   $image = $_FILES['image']['name'];
   $image = filter_var($image, FILTER_SANITIZE_STRING);
   $ext = pathinfo($image, PATHINFO_EXTENSION);

   // Redenumim fișierul de imagine utilizând un ID unic pentru a evita conflictul de nume
   $rename = create_unique_id().'.'.$ext;

   // Obținem calea și mărimea fișierului temporar de imagine
   $image_tmp_name = $_FILES['image']['tmp_name'];
   $image_size = $_FILES['image']['size'];

   // Stabilim directorul în care vom salva fișierul de imagine
   $image_folder = 'uploaded_files/'.$rename;

   // Verificăm mărimea fișierului de imagine
   if($image_size > 2000000){
      // Dacă fișierul este prea mare, adăugăm un mesaj de avertizare
      $warning_msg[] = 'Image size is too large!';
   }else{
      // Înserăm produsul în baza de date și mutăm fișierul de imagine în directorul destinatar
      $add_product = $conn->prepare("INSERT INTO `products`(id, name, price, image) VALUES(?,?,?,?)");
      $add_product->execute([$id, $name, $price, $rename]);
      move_uploaded_file($image_tmp_name, $image_folder);
      $success_msg[] = 'Product added!';
   }

}

?>

<!DOCTYPE html>
<html lang="en">

<head>

   <meta charset="UTF-8">
   <meta http-equiv="X-UA-Compatible" content="IE=edge">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>Adauga camera</title>
   <link rel="stylesheet" href="css/checkout_css.css">

</head>
<body>
   
<?php include 'componentsBookingCheckout/header.php'; ?>

<section class="product-form">

   <form action="" method="POST" enctype="multipart/form-data">
      <h3>Informatii camera</h3>
      <p>nume <span>*</span></p>
      <input type="text" name="name" placeholder="introduceti numele camerei" required maxlength="50" class="box">
      <p>pret <span>*</span></p>
      <input type="number" name="price" placeholder="introduceti pretul camerei" required min="0" max="9999999999" maxlength="10" class="box">
      <p>poza <span>*</span></p>
      <input type="file" name="image" required accept="image/*" class="box">
      <input type="submit" class="btn" name="add" value="adauga camera">
   </form>

</section>




<script src="https://cdnjs.cloudflare.com/ajax/libs/sweetalert/2.1.2/sweetalert.min.js"></script>

<script src="jsBookingCheckout/script.js"></script>

<?php include 'componentsBookingCheckout/alert.php'; ?>

</body>
</html>