<!DOCTYPE html>
<html>
<body>

<?php
session_start();

// initializarea variabilelor
$username = "";
$email    = "";
$errors = array(); 

// conexiunea la baza de date
$db = mysqli_connect('localhost', 'root', '', 'project');

if ($db->connect_error) {
    die("Connection failed: " . $db->connect_error);
}

// Interogarea pentru a selecta informațiile despre utilizatori din baza de date
$sql = "SELECT id, username, email, img FROM users";
$result = $db->query($sql);

if ($result->num_rows > 0) {
    // Afisarea datelor pentru fiecare rând
    while($row = $result->fetch_assoc()) {
        // Afisarea id-ului, numelui și adresei de email a utilizatorului
        print "<br> id: ". $row["id"]. "<br> - Name: ". $row["username"]. "<br> - Email: " . $row["email"] . "<br>";
        // Afisarea imaginii utilizatorului
        print "<img src=\"".$row["img"]."\">";
    }
} else {
    // Afisarea unui mesaj în cazul în care nu există rezultate
    print "0 results";
}

$db->close();
?>



</body>
</html>