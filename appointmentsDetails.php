<?php
session_start(); 
include_once('UsersStorage.php');
include_once('appointmentsStorage.php'); 
function authorize() {
  return isset($_SESSION["user"]);
}


$userID = $_SESSION["user"]['id']; 
if($userID !== "admin"){
  $name = $_SESSION["user"]['name']; 
  $address = $_SESSION["user"]['address']; 
  $taj = $_SESSION["user"]['taj']; 
  $userDetails = $_SESSION["user"]; 
}
$date = $_GET['date']; 
$time = $_GET['time']; 
$error = []; 
function redirect($page) {
  header("Location: ${page}");
  exit();
}
function isAdmin($userID){
  if ($userID === "admin"){
    return true;
  } 
  else{
    return false; 
  }
}
/*
  <li>Coffee</li>
  <li>Tea</li>
  <li>Milk</li>
</ul>  
*/

function listAllApplicants($date, $time, $appointmentsStorage, $userStorage){
  $string ="";
  $wholeTime = $date . " " . $time; 
  $appointments = $appointmentsStorage->findAll();
  $users = $userStorage->findAll(); 
  $string = "<ul>";
  foreach($appointments as $ids => $app){
    if($wholeTime === $app['time']){
      $array = $app['applicant'];
      foreach($array as $user1){
        //print_r($user1);
        foreach($users as $user2 => $item){
          if($user1 === $user2){
            $string =  $string ."<li>" . "Név: " . $item['name'] . " " . "TAJ: " . $item['taj'] . " " . "E-mail: " . $item['email'] . "</li>";
          }
              
        }
      }
    }
  }
  $string = $string . "<ul>"; 
  return $string;
}


function addUserToAppointments($date, $time, $appointmentsStorage, $userID) {
  $id = uniqid(); 
  $wholeTime = $date . " " . $time; 
  $appointments = $appointmentsStorage->findAll();
  //$isTimeIsSet
  foreach($appointments as $ids => $app){
      if($wholeTime === $app['time']){
        $tmpData = []; 
        $array = $app['applicant'];
        array_push($array, $userID); 
        $tmpData['id'] = $app['id'];
        $tmpData['time'] = $app['time'];
        $tmpData['places'] = $app['places']; 
        $tmpData['applicant'] = $array; 
        $appointmentsStorage->update($app['id'], $tmpData); 
        return; 
      }
  }
  return $appointmentsStorage->add($id);
}
if (count($_POST) > 0){
  if(!isset($_POST['accept'])){
    $error['accept'] = 'A mellékhatások tényét elfogadni kötelező!'; 
  }
}
if (count($_POST) > 0) {
  if(!isset($error['accept'])){
    $appointmentsStorage = new appointmentsStorage();
    //$appointments = $appointmentsStorage->findAll();

    addUserToAppointments($date, $time, $appointmentsStorage,$userID);
    redirect('final.php');
  }
}
$list = "";
if(isAdmin($userID)){
  $appointmentsStorage = new appointmentsStorage();
  $userStorage = new UsersStorage();
  $list = listAllApplicants($date, $time, $appointmentsStorage, $userStorage); 
  //print_r($list);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Nemzeti Koronavírus Depó - Jelentkezés részletei</title>
  <style>
    small{
      color:red; 
    }
  </style>
  </head>
  <body>
    <h1 align="center" >Időpont és jelentkező részletei</h1>
    Az Ön időpontjának részletei:<br>
    Dátum: <?=$date?>       <br>
    Idő: <?=$time?>       <br>
    <?php  if(!isAdmin($userID)) : ?>
    Név:  <?=$name?>       <br>
    Lakcím:    <?=$address?>           <br>
    TAJ szám:       <?=$taj?>      <br>
    <form action="" method="post" novalidate>
      <input type="checkbox" id="accept" name="accept" value="accept" >
      <input hidden type="text" name="hidden">
      <label for="accept"> Elfogadom, hogy az oltásnak rövidtávon mellékhatásai lehetnek!</label>
        <?php if (isset($error['accept'])) : ?>
          <small><?= $error['accept'] ?></small>
        <?php endif ?>
        <br>
      <button type="submit">Jelentkezés megerősítése</button>
    </form>
    <?php endif ?>
    Az adott időpontra a következő emberek jelentkeztek fel:
    <?php if(isAdmin($userID)) : ?>
      <?=$list?>
    <?php endif ?>
  </body>

</html>