<?php

// Setăm parametrii de conectare la baza de date
$db_name = 'mysql:host=localhost;dbname=hotel_db';
$db_user_name = 'root';
$db_user_pass = '';

// Creăm o nouă instanță PDO pentru conexiunea la baza de date
$conn = new PDO($db_name, $db_user_name, $db_user_pass);

// Funcție pentru generarea unui ID unic
function create_unique_id(){
   $str = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ1234567890';
   $rand = array();
   $length = strlen($str) - 1;

   for($i = 0; $i < 20; $i++){
      $n = mt_rand(0, $length);
      $rand[] = $str[$n];
   }
   return implode($rand);
}

?>
