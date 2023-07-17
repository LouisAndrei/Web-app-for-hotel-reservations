<?php
  session_start();

  

  // Verificăm dacă utilizatorul dorește să se deconecteze
  if (isset($_GET['logout'])) {
    // Distrugem sesiunea și eliminăm variabila de sesiune pentru utilizator
    session_destroy();
    unset($_SESSION['username']);
    header("location: index.php");
  }
?>

<?php

// Includem fișierul de conectare la baza de date
include 'components/connect.php';

// Verificăm dacă există un cookie pentru user_id
if(isset($_COOKIE['user_id'])){
   // Dacă există, atribuim valoarea acestuia variabilei $user_id
   $user_id = $_COOKIE['user_id'];
}else{
   // Dacă nu există, generăm un user_id unic și creăm un cookie pentru acesta
   setcookie('user_id', create_unique_id(), time() + 60*60*24*30, '/');
   // Redirecționăm utilizatorul către pagina index.php
   header('location:index.php');
}

// Verificăm dacă s-a apăsat butonul "check"
if(isset($_POST['check'])){

   // Preluăm valoarea din câmpul check_in și o filtrăm pentru a preveni injectarea de cod
   $check_in = $_POST['check_in'];
   $check_in = filter_var($check_in, FILTER_SANITIZE_STRING);

   $total_rooms = 0;

   // Verificăm dacă există rezervări pentru data specificată în câmpul check_in
   $check_bookings = $conn->prepare("SELECT * FROM `bookings` WHERE check_in = ?");
   $check_bookings->execute([$check_in]);

   // Iterăm prin rezervările găsite
   while($fetch_bookings = $check_bookings->fetch(PDO::FETCH_ASSOC)){
      // Adunăm numărul total de camere ocupate
      $total_rooms += $fetch_bookings['rooms'];
   }

   // Verificăm dacă numărul total de camere ocupate depășește 30
   if($total_rooms >= 30){
      // Dacă da, adăugăm un mesaj de avertizare în array-ul $warning_msg
      $warning_msg[] = 'nu au fost gasite camere disponibile';
   }else{
      // Altfel, adăugăm un mesaj de succes în array-ul $success_msg
      $success_msg[] = 'camera este disponibila';
   }

}

if(isset($_POST['book'])){ // Verifică dacă a fost trimisă o cerere de rezervare
   // Generarea unui ID unic pentru rezervare
   $booking_id = create_unique_id();
   // Preia valorile din formularul de rezervare 
   $name = $_POST['name'];
   $name = filter_var($name, FILTER_SANITIZE_STRING);
   $email = $_POST['email'];
   $email = filter_var($email, FILTER_SANITIZE_STRING);
   $number = $_POST['number'];
   $number = filter_var($number, FILTER_SANITIZE_STRING);
   $rooms = $_POST['rooms'];
   $rooms = filter_var($rooms, FILTER_SANITIZE_STRING);
   $check_in = $_POST['check_in'];
   $check_in = filter_var($check_in, FILTER_SANITIZE_STRING);
   $check_out = $_POST['check_out'];
   $check_out = filter_var($check_out, FILTER_SANITIZE_STRING);
   $adults = $_POST['adults'];
   $adults = filter_var($adults, FILTER_SANITIZE_STRING);
   $childs = $_POST['childs'];
   $childs = filter_var($childs, FILTER_SANITIZE_STRING);
   $total_rooms = 0;
   // Verifică dacă există rezervări pentru data de check-in specificată
   $check_bookings = $conn->prepare("SELECT * FROM `bookings` WHERE check_in = ?");
   $check_bookings->execute([$check_in]);
   while($fetch_bookings = $check_bookings->fetch(PDO::FETCH_ASSOC)){
      $total_rooms += $fetch_bookings['rooms'];
   }
   if($total_rooms >= 30){
      $warning_msg[] = 'nu au fost gasite camere disponibile'; // Adaugă un mesaj de avertizare dacă nu mai sunt camere disponibile pentru data de check-in
   }else{
     // Verifică dacă există deja o rezervare identică în baza de date
      $verify_bookings = $conn->prepare("SELECT * FROM `bookings` WHERE user_id = ? AND name = ? AND email = ? AND number = ? AND rooms = ? AND check_in = ? AND check_out = ? AND adults = ? AND childs = ?");
      $verify_bookings->execute([$user_id, $name, $email, $number, $rooms, $check_in, $check_out, $adults, $childs]);

      if($verify_bookings->rowCount() > 0){
         $warning_msg[] = 'oops! exista deja o rezervare identica'; // Adaugă un mesaj de avertizare dacă o rezervare identică există deja în baza de date
      }else{
         // Inserează rezervarea în baza de date
         $book_room = $conn->prepare("INSERT INTO `bookings`(booking_id, user_id, name, email, number, rooms, check_in, check_out, adults, childs) VALUES(?,?,?,?,?,?,?,?,?,?)");
         $book_room->execute([$booking_id, $user_id, $name, $email, $number, $rooms, $check_in, $check_out, $adults, $childs]);
         $success_msg[] = 'felicitari! ati rezervat cu succes camera'; // Adaugă un mesaj de succes dacă rezervarea a fost efectuată cu succes
      }

   }

}

if(isset($_POST['send'])){

   // Generăm un id unic pentru mesaj
   $id = create_unique_id();

   // Preluăm și filtrăm valorile introduse în câmpurile formularului
   $name = $_POST['name'];
   $name = filter_var($name, FILTER_SANITIZE_STRING);
   $email = $_POST['email'];
   $email = filter_var($email, FILTER_SANITIZE_STRING);
   $number = $_POST['number'];
   $number = filter_var($number, FILTER_SANITIZE_STRING);
   $message = $_POST['message'];
   $message = filter_var($message, FILTER_SANITIZE_STRING);

   // Verificăm dacă există deja un mesaj identic
   $verify_message = $conn->prepare("SELECT * FROM `messages` WHERE name = ? AND email = ? AND number = ? AND message = ?");
   $verify_message->execute([$name, $email, $number, $message]);

   if($verify_message->rowCount() > 0){
      // Dacă există, adăugăm un mesaj de avertizare în array-ul $warning_msg
      $warning_msg[] = 'oops! ati trimis deja acest mesaj';
   }else{
      // Altfel, inserăm mesajul în baza de date și adăugăm un mesaj de succes în array-ul $success_msg
      $insert_message = $conn->prepare("INSERT INTO `messages`(id, name, email, number, message) VALUES(?,?,?,?,?)");
      $insert_message->execute([$id, $name, $email, $number, $message]);
      $success_msg[] = 'mesajul a fost trimis cu succes!';
   }

}

?>
<!DOCTYPE html>
<html>
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<head>
	<title>Aplicatie web pentru rezervari hoteliere</title>
	<link rel="stylesheet" type="text/css" href="css/login_css.css">
</head>
<body>

<div class="header1">
	<h6>CONT</h6>
</div>
	
<div class="content1">
  	<!-- mesaj de notificare -->
  	<?php if (isset($_SESSION['success'])) : ?>
      <div class="error success" >
      	<h3>
          <?php 
          	echo "Felicitari! Te-ai logat cu succes."; 
          	unset($_SESSION['success']);
          ?>
      	</h3>
      </div>
  	<?php endif ?>
     
   
    
    
     <!-- informații despre utilizatorul autentificat -->
     <div style="text-align: center;">
         <?php  // Verifică dacă există o sesiune activă pentru utilizator
         if(isset($_SESSION['username'])) : ?> 
           <p style="color: lime;">Ne bucurăm să te vedem, <strong><?php echo $_SESSION['username']; ?></strong></p>
           <p> <a href="index.php?logout='1'" style="color: lime;">LOG OUT</a> </p>
         <?php else : ?>
           <p style="color: red;">Nu ești autentificat.</p>
           <p> <a href="login.php" style="color: lime;">LOG IN</a> </p>
         <?php endif; ?>
      </div>
</div>






<head>

   <meta charset="UTF-8">
   <meta http-equiv="X-UA-Compatible" content="IE=edge">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>home</title>

   <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@8/swiper-bundle.min.css" />

   <!-- font awesome cdn link  -->
   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.2.0/css/all.min.css">

   <!-- custom css file link  -->
   <link rel="stylesheet" type="text/css" href="css/primapagina_css.css">

</head>
<body>

<?php include 'components/user_header.php'; ?>




<!-- inceputul sectiunii home  -->
<section class="home" id="home">
   

<h6 style="font-family: 'Arial', sans-serif; font-weight: bold; font-size: 24px; text-transform: uppercase; text-align: center; color: #38df0e; background-color: #777; padding: 10px; border-radius: 5px; box-shadow: 0 0 10px rgba(0, 0, 0, 0.3); text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.5);">Hotelul Refugiul Fermecat</h6>


    <div class="box-container">

        <div class="box1">
            <span class="discount">-70%</span>
            <div class="image">
                <img src="images/camera standard.jpg" alt="">
                
            </div>
            <div class="content">
                <h3>Camera standard</h3>
                <div class="price"> o cameră simplă cu un pat, potrivită pentru o singură <br> persoană sau un cuplu. <br> <br>
                  
                
                100 LEI/noapte <span>150 LEI</span> </div>
            </div>
        </div>

        <div class="box1">
            <span class="discount">-55%</span>
            <div class="image">
                <img src="images/camera family.jpg" alt="">
                
            </div>
            <div class="content">
                <h3>Camera family</h3>
                <div class="price"> o cameră  mare, cu o capacitate de două <br> sau mai multe paturi,  pentru familii sau grupuri mari. <br><br>
                  
                180 LEI/noapte <span>220 LEI</span> </div>
            </div>
        </div>

        <div class="box1">
            <span class="discount">-85%</span>
            <div class="image">
                <img src="images/camera deluxe.jpg" alt="">
                
            </div>
            <div class="content">
                <h3>Camera deluxe</h3>
                <div class="price"> o cameră mare și confortabilă, <br> cu facilități suplimentare <br><br>
                  
               250 LEI/noapte <span>300 LEI</span> </div>
            </div>
        </div>

        <div class="box1">
            <span class="discount">-30%</span>
            <div class="image">
                <img src="images/camera suite.jpg" alt="">
                
            </div>
            <div class="content">
                <h3>Camera suite</h3>
                <div class="price"> este o cameră mare și luxoasă, de obicei cu un <br> living separat și cu facilități de divertisment suplimentare. <br><br>
                  
                350 LEI/noapte <span>450 LEI</span> </div>
            </div>
        </div>

        <div class="box1">
            <span class="discount">-80%</span>
            <div class="image">
                <img src="images/camera afaceri.jpg" alt="">
                
            </div>
            <div class="content">
                <h3>Camera executive</h3>
                <div class="price"> este o cameră de dimensiuni mari, cu facilități pentru <br> afaceri, cum ar fi un birou și o imprimantă. <br> <br>
                  
                400 LEI/noapte <span>500 LEI</span> </div>
            </div>
        </div>

        <div class="box1">
            <span class="discount">-65%</span>
            <div class="image">
                <img src="images/camera pers diz.jpg" alt="">
                
            </div>
            <div class="content">
                <h3>Camera speciala</h3>
                <div class="price"> camera pentru persoanele cu dizabilitati, cu facilități precum <br> băi adaptate si uși mai largi.<br><br>
                  
                150 LEI/noapte <span>200 LEI</span> </div>
            </div>
        </div>
  </section>
<!-- sfarsitul sectiunii home -->
 



<!-- inceputul sectiunii availability   -->
<section class="availability" id="availability">
<div class="parallax">
    <form action="" method="post">
    <h3>Verifica disponibilitatea</h3>
       <div class="flex">
          <div class="box">
             <p>check in <span>*</span></p>
             <input type="date" name="check_in" class="input" required>
          </div>
          <div class="box">
             <p>check out <span>*</span></p>
             <input type="date" name="check_out" class="input" required>
          </div>
          <div class="box">
             <p>adulti <span>*</span></p>
             <select name="adults" class="input" required>
                <option value="1">1 adult</option>
                <option value="2">2 adulti</option>
                <option value="3">3 adulti</option>
                <option value="4">4 adulti</option>
                <option value="5">5 adulti</option>
                <option value="6">6 adulti</option>
             </select>
          </div>
          <div class="box">
             <p>copii <span>*</span></p>
             <select name="childs" class="input" required>
                <option value="-">0 copii</option>
                <option value="1">1 copil</option>
                <option value="2">2 copii</option>
                <option value="3">3 copii</option>
                <option value="4">4 copii</option>
                <option value="5">5 copii</option>
                <option value="6">6 copii</option>
             </select>
          </div>
          <div class="box">
             <p>camere <span>*</span></p>
             <select name="rooms" class="input" required>
                <option value="1">Camera standard</option>
                <option value="2">Camera family</option>
                <option value="3">Camera deluxe</option>
                <option value="4">Camera suite</option>
                <option value="5">Camera executive</option>
                <option value="6">Camera speciala</option>
             </select>
          </div>
       </div>
       <input type="submit" value="verifica" name="check" class="btn">
    </form>
    </div>
 </section>
 <!-- sfarsitul sectiunii availability  -->


 
 
 <!-- inceputul sectiunii about  -->
<section class="about" id="about">

    <div class="row">
       <div class="image">
          <img src="images/personal.png" alt="">
       </div>
       <div class="content">
          <h3>Personal de calitate</h3>
          <p>Personalul hotelului este esențial pentru a oferi oaspeților o experiență memorabilă și pentru a face ca șederea lor 
            să fie cât mai confortabilă și plăcută posibil; la hotelul nostru, suntem mândri să avem un personal profesionist și dedicat, 
            care se străduiește să ofere servicii excelente, de la check-in până la check-out, și care este mereu disponibil să ofere asistență în 
            cazul în care oaspeții noștri au nevoie de ajutor cu privire la orice aspect al șederii lor. Personalul nostru este format din oameni 
            pasionați de industria ospitalității și care își aduc contribuția la crearea unei atmosfere prietenoase și relaxante, care îi face pe oaspeți să se simtă ca acasă.</p>
          <a href="#reservation" class="btn">rezervă o cameră</a>
       </div>
    </div>
 
    <div class="row revers">
       <div class="image">
          <img src="images/mancare.jpg" alt="">
       </div>
       <div class="content">
          <h3>Cea mai buna mancare</h3>
          <p>Mâncarea servită la un hotel este o componentă esențială a experienței de cazare, iar la hotelul nostru ne asigurăm că preparatele 
            noastre sunt pregătite cu cele mai bune ingrediente, astfel încât să vă oferim o varietate de arome și gusturi delicioase, pornind de la preparate 
            tradiționale locale până la cele internaționale, pentru a satisface cele mai exigente gusturi ale oaspeților noștri; de asemenea, ne preocupăm să 
            avem o ofertă echilibrată și sănătoasă, cu opțiuni vegetariene, vegane sau fără gluten, pentru a satisface nevoile alimentare ale tuturor oaspeților noștri.</p>
          <a href="#contact" class="btn">contact</a>
       </div>
    </div>
 
    <div class="row">
       <div class="image">
          <img src="images/piscina.jpg" alt="">
       </div>
       <div class="content">
          <h3>Piscina</h3>
          <p>Descoperă paradisul acvatic al hotelului nostru! Piscina interioară reprezinta răsfăț și relaxare într-un singur loc. Rezervă acum și lasă-te învăluit de confort și lux.
             Bucură-te de apă cristalină și de o atmosferă plăcută. Te așteptăm cu brațele deschise!</p>
          <a href="#availability" class="btn">verifica disponibilitatea</a>
       </div>
    </div>
 
 </section>
 <!-- sfarsitul sectiunii about -->




 <!-- inceptul sectiunii services -->
<section class="services">

<div class="box-container">

   <div class="box">
      <img src="images/mancare.png" alt="">
      <h3>Mancare si bauturi</h3>
      <p>Mâncarea si bauturile de la hotelul nostru sunt pregătite cu ingrediente proaspete și de calitate, astfel încât să vă puteți bucura de o varietate de arome și gusturi delicioase în fiecare zi.</p>
   </div>

   <div class="box">
      <img src="images/terasa.jpg" alt="">
      <h3>Terasa</h3>
      <p>Terasa de la hotelul nostru este un loc minunat pentru a admira priveliști impresionante, în timp ce vă relaxați și vă bucurați de o băutură 
         răcoritoare sau o gustare delicioasă, într-un mediu confortabil și plăcut.</p>
   </div>



   <div class="box">
      <img src="images/decoratiune.png" alt="">
      <h3>Decoratiuni deosebite</h3>
      <p>Decoratiunile de la hotelul nostru sunt alese cu mare atenție și grijă pentru a crea o atmosferă plăcută și primitoare pentru oaspeții noștri, prin combinarea 
         elementelor tradiționale și moderne, pentru a oferi un cadru unic și rafinat.</p>
   </div>

   <div class="box">
      <img src="images/piscina.png" alt="">
      <h3>Piscina</h3>
      <p>Piscina hotelului nostru este o oază de răcoare și de relaxare, perfectă pentru a petrece timpul într-un mediu plăcut și confortabil.</p>
   </div>

</div>

</section>
<!-- sfarsitul sectiunii services -->


 

<!-- inceputul sectiunii de rezervari  -->
<section class="reservation" id="reservation">
<div class="parallax2">

    <form action="" method="post">
       <h3>Dorești să faci o rezervare?</h3>
       <div class="flex">
          <div class="box">
             <p>nume <span>*</span></p>
             <input type="text" name="name" maxlength="50" required placeholder="tasteaza numele" class="input">
          </div>
          <div class="box">
             <p>e-mail <span>*</span></p>
             <input type="email" name="email" maxlength="50" required placeholder="tasteaza e-mail-ul" class="input">
          </div>
          <div class="box">
             <p>telefon <span>*</span></p>
             <input type="number" name="number" maxlength="10" min="0" max="9999999999" required placeholder="tasteaza numele de telefon" class="input">
          </div>
          <div class="box">
             <p>camere <span>*</span></p>
             <select name="rooms" class="input" required>
                <option value="1" selected>Camera standard</option>
                <option value="2">Camera family</option>
                <option value="3">Camera deluxe</option>
                <option value="4">Camera suite</option>
                <option value="5">Camera executive</option>
                <option value="6">Camera speciala</option>
             </select>
          </div>
          <div class="box">
             <p>check in <span>*</span></p>
             <input type="date" name="check_in" class="input" required>
          </div>
          <div class="box">
             <p>check out <span>*</span></p>
             <input type="date" name="check_out" class="input" required>
          </div>
          <div class="box">
             <p>adulti <span>*</span></p>
             <select name="adults" class="input" required>
                <option value="1" selected>1 adult</option>
                <option value="2">2 adulti</option>
                <option value="3">3 adulti</option>
                <option value="4">4 adulti</option>
                <option value="5">5 adulti</option>
                <option value="6">6 adulti</option>
             </select>
          </div>
          <div class="box">
             <p>copii <span>*</span></p>
             <select name="childs" class="input" required>
                <option value="0" selected>0 copii</option>
                <option value="1">1 copil</option>
                <option value="2">2 copii</option>
                <option value="3">3 copii</option>
                <option value="4">4 copii</option>
                <option value="5">5 copii</option>
                <option value="6">6 copii</option>
             </select>
          </div>
       </div>
       <input type="submit" value="rezervă acum" name="book" class="btn">
    </form>
    </div>
 </section>
 <!-- sfarsitul sectiunii de rezervari -->


 
 
 <!-- inceputul sectiunii gallery  -->
<section class="gallery" id="gallery">

    <div class="swiper gallery-slider">
       <div class="swiper-wrapper">
          <img src="images/camera standard.jpg" class="swiper-slide" alt="">
          <img src="images/camera family.jpg" class="swiper-slide" alt="">
          <img src="images/camera deluxe.jpg" class="swiper-slide" alt="">
          <img src="images/camera afaceri.jpg" class="swiper-slide" alt="">
          <img src="images/camera pers diz.jpg" class="swiper-slide" alt="">
          <img src="images/camera suite.jpg" class="swiper-slide" alt="">
          <img src="images/mancare.jpg" class="swiper-slide" alt="">
          <img src="images/personal.png" class="swiper-slide" alt="">
          <img src="images/piscina.jpg" class="swiper-slide" alt="">
       </div>
       <div class="swiper-pagination"></div>
    </div>
 
 </section>
 <!-- sfarsitul sectiunii gallery -->


 
 
 
 <!-- inceputul sectiunii contact -->
<section class="contact" id="contact">

    <div class="row">
 
       <form action="" method="post">
          <h3>Trimite-ne un mesaj</h3>
          <style>
             .input-text {
                width: 300px;
                height: 50px;
               }
          </style>
          
          <input type="text" class="input-text" name="name" required maxlength="50" placeholder="nume" class="box">
          <input type="email" class="input-text" name="email" required maxlength="50" placeholder="e-mail" class="box">
          <input type="number" class="input-text" name="number" required maxlength="10" min="0" max="9999999999" placeholder="telefon" class="box">
          <textarea name="message" class="input-text" class="box" required maxlength="1000" placeholder="mesaj" cols="30" rows="10"></textarea>
          <input type="submit" class="input-text" value="trimite" name="send" class="btn">
       </form>
       
 
       
 
 </section>
 <!-- sfarsitul sectiunii contact -->




 <!-- inceputul sectiunii reviews   -->
<section class="reviews" id="reviews">
   <br><br><br><br><br><br><br><br><br><br><br><br><br><br>
<div class="parallax3">
  <h2 class="reviews__title">Vrei sa vezi parerea altor clienti sau sa lasi chiar tu un review?</h2>
  <div class="reviews__container">
    <a href="all_posts.php" class="reviews__button-link">
      <button class="reviews__button">Lasa un review</button>
    </a>
  </div>
 </div>
</section>
<!-- sfarsitul sectiunii reviews  -->





<?php include 'components/footer.php'; ?>

<script src="https://cdn.jsdelivr.net/npm/swiper@8/swiper-bundle.min.js"></script>

<script src="https://cdnjs.cloudflare.com/ajax/libs/sweetalert/2.1.2/sweetalert.min.js"></script>

<!--  js file link  -->
<script src="js/script.js"></script>

<?php include 'components/message.php'; ?>


</body>
</html>