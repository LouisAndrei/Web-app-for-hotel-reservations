<?php

include 'componentsReview/connect.php'; // Include fișierul de conectare la baza de date

if(isset($_POST['submit'])){ // Verifică dacă s-a efectuat o trimitere a formularului

   $select_user = $conn->prepare("SELECT * FROM `users` WHERE id = ? LIMIT 1"); // Interogare pentru a selecta utilizatorul curent
   $select_user->execute([$user_id]);
   $fetch_user = $select_user->fetch(PDO::FETCH_ASSOC); // Obține rezultatul interogării

   $name = $_POST['name'];
   $name = filter_var($name, FILTER_SANITIZE_STRING); // Filtrare și validare nume

   $email = $_POST['email'];
   $email = filter_var($email, FILTER_SANITIZE_STRING); // Filtrare și validare email

   if(!empty($name)){ // Verifică dacă numele nu este gol
      $update_name = $conn->prepare("UPDATE `users` SET name = ? WHERE id = ?"); // Actualizează numele utilizatorului în baza de date
      $update_name->execute([$name, $user_id]);
      $success_msg[] = 'Username updated!'; // Mesaj de succes pentru actualizarea numelui
   }

   if(!empty($email)){ // Verifică dacă adresa de email nu este goală
      $verify_email = $conn->prepare("SELECT * FROM `users` WHERE email = ?"); // Verifică dacă adresa de email există deja în baza de date
      $verify_email->execute([$email]);
      if($verify_email->rowCount() > 0){
         $warning_msg[] = 'Email already taken!'; // Mesaj de avertizare dacă adresa de email este deja utilizată
      }else{
         $update_email = $conn->prepare("UPDATE `users` SET email = ? WHERE id = ?"); // Actualizează adresa de email în baza de date
         $update_email->execute([$email, $user_id]);
         $success_msg[] = 'Email updated!'; // Mesaj de succes pentru actualizarea adresei de email
      }
   }

   $image = $_FILES['image']['name'];
   $image = filter_var($image, FILTER_SANITIZE_STRING); // Filtrare și validare nume fișier imagine
   $ext = pathinfo($image, PATHINFO_EXTENSION); // Obține extensia fișierului imagine
   $rename = create_unique_id().'.'.$ext; // Generează un nume unic pentru fișierul imagine
   $image_size = $_FILES['image']['size']; // Obține mărimea fișierului imagine
   $image_tmp_name = $_FILES['image']['tmp_name']; // Obține numele temporar al fișierului imagine
   $image_folder = 'uploaded_files/'.$rename; // Setează calea către directorul în care se va salva fișierul imagine

  if(!empty($image)){ // Verifică dacă a fost selectată o imagine
   if($image_size > 2000000){ // Verifică dacă mărimea imaginii depășește limita
      $warning_msg[] = 'Image size is too large!'; // Mesaj de avertizare pentru mărimea prea mare a imaginii
   }else{
      $update_image = $conn->prepare("UPDATE `users` SET image = ? WHERE id = ?"); // Actualizează imaginea utilizatorului în baza de date
      $update_image->execute([$rename, $user_id]);
      move_uploaded_file($image_tmp_name, $image_folder); // Mută fișierul imagine în directorul specificat
      if($fetch_user['image'] != ''){
         unlink('uploaded_files/'.$fetch_user['image']); // Șterge vechea imagine din directorul specificat
      }
      $success_msg[] = 'Image updated!'; // Mesaj de succes pentru actualizarea imaginii
   }
  }

  $prev_pass = $fetch_user['password']; // Obține parola criptată a utilizatorului din baza de date

  $old_pass = password_hash($_POST['old_pass'], PASSWORD_DEFAULT);
  $old_pass = filter_var($old_pass, FILTER_SANITIZE_STRING); // Filtrare și validare parolă veche

  $empty_old = password_verify('', $old_pass); // Verifică dacă parola veche este goală

  $new_pass = password_hash($_POST['new_pass'], PASSWORD_DEFAULT);
  $new_pass = filter_var($new_pass, FILTER_SANITIZE_STRING); // Filtrare și validare parolă nouă

  $empty_new = password_verify('', $new_pass); // Verifică dacă parola nouă este goală

  $c_pass = password_verify($_POST['c_pass'], $new_pass);
  $c_pass = filter_var($c_pass, FILTER_SANITIZE_STRING); // Filtrare și validare parolă confirmată

  if($empty_old != 1){ // Verifică dacă parola veche nu este goală
      $verify_old_pass = password_verify($_POST['old_pass'], $prev_pass); // Verifică dacă parola veche introdusă este corectă
      if($verify_old_pass == 1){
         if($c_pass == 1){
            if($empty_new != 1){ // Verifică dacă parola nouă nu este goală
               $update_pass = $conn->prepare("UPDATE `users` SET password = ? WHERE id = ?"); // Actualizează parola utilizatorului în baza de date
               $update_pass->execute([$new_pass, $user_id]);
               $success_msg[] = 'Password updated!'; // Mesaj de succes pentru actualizarea parolei
            }else{
               $warning_msg[] = 'Please enter new password!'; // Mesaj de avertizare pentru lipsa parolei noi
            }
         }else{
            $warning_msg[] = 'Confirm password not matched!'; // Mesaj de avertizare pentru nepotrivirea parolei confirmate
         }
      }else{
         $warning_msg[] = 'Old password not matched!'; // Mesaj de avertizare pentru nepotrivirea parolei vechi
      }
  }
   
}

if(isset($_POST['delete_image'])){ // Verifică dacă s-a solicitat ștergerea imaginii de profil

   $select_old_pic = $conn->prepare("SELECT * FROM `users` WHERE id = ? LIMIT 1"); // Selectează utilizatorul curent pentru a verifica imaginea de profil existentă
   $select_old_pic->execute([$user_id]);
   $fetch_old_pic = $select_old_pic->fetch(PDO::FETCH_ASSOC);

   if($fetch_old_pic['image'] == ''){
      $warning_msg[] = 'Image already deleted!'; // Mesaj de avertizare dacă imaginea de profil a fost deja ștearsă
   }else{
      $update_old_pic = $conn->prepare("UPDATE `users` SET image = ? WHERE id = ?"); // Actualizează imaginea de profil cu un șir gol în baza de date
      $update_old_pic->execute(['', $user_id]);
      if($fetch_old_pic['image'] != ''){
         unlink('uploaded_files/'.$fetch_old_pic['image']); // Șterge imaginea de profil veche din directorul specificat
      }
      $success_msg[] = 'Image deleted!'; // Mesaj de succes pentru ștergerea imaginii de profil
   }

}

?>

<!DOCTYPE html>
<html lang="en">
<head>

   <meta charset="UTF-8">
   <meta http-equiv="X-UA-Compatible" content="IE=edge">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>update profile</title>
   <!--  css file link  -->
   <link rel="stylesheet" type="text/css" href="css/review_css.css">

</head>
<body>
   


<!-- inceputul sectiunii update   -->

<section class="account-form">

   <form action="" method="post" enctype="multipart/form-data">
      <h3>update your profile!</h3>
      <p class="placeholder">your name</p>
      <input type="text" name="name" maxlength="50" placeholder="<?= $fetch_profile['name']; ?>" class="box">
      <p class="placeholder">your email</p>
      <input type="email" name="email" maxlength="50" placeholder="<?= $fetch_profile['email']; ?>" class="box">
      <p class="placeholder">old password</p>
      <input type="password" name="old_pass" maxlength="50" placeholder="enter your old password" class="box">
      <p class="placeholder">new password</p>
      <input type="password" name="new_pass" maxlength="50" placeholder="enter your new password" class="box">
      <p class="placeholder">confirm password</p>
      <input type="password" name="c_pass" maxlength="50" placeholder="confirm your new password" class="box">
      <?php if($fetch_profile['image'] != ''){ ?>
         <img src="uploaded_files/<?= $fetch_profile['image']; ?>" alt="" class="image">
         <input type="submit" value="delete image" name="delete_image" class="delete-btn" onclick="return confirm('delete this image?');">
      <?php }; ?>
      <p class="placeholder">profile pic</p>
      <input type="file" name="image" class="box" accept="image/*">
      <input type="submit" value="update now" name="submit" class="btn">
   </form>

</section>

<!-- sfarsitul sectiunii update  -->




<!-- sweetalert cdn link  -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/sweetalert/2.1.2/sweetalert.min.js"></script>

<!-- js file link  -->
<script src="jsReview/script.js"></script>

<?php include 'componentsReview/alers.php'; ?>

</body>
</html>