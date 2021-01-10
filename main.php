<?php
session_start(); 
include_once('appointmentsStorage.php');
$actualMonth = 1; 
$data=[];
$appointmentsStorage = new appointmentsStorage();
$appointments = $appointmentsStorage->findAll(); 
$alreadyHaveAppointment = false; 
$bookedApppointmentDetails = [];
$lastDeletedID; 
function redirect($page) {
  header("Location: ${page}");
  exit();
}
if(isset($_SESSION['actualMonth'])){
  $actualMonth = $_SESSION['actualMonth']; 
}
if(isset($_GET['actualMonth'])) {
  $actualMonth = $_GET['actualMonth'];
}
function checkIfAlreadyHaveAppointment($appointmentsStorage, &$alreadyHaveAppointment, &$bookedApppointmentDetails){
  $appointments = $appointmentsStorage->findAll(); 
  
  if(isset($_SESSION['user']))
  {
    $id = $_SESSION["user"]['id'];
    foreach($appointments as $k => $t){
      $tmpArray = $t['applicant'];
      //print_r($tmpArray);
      if(array_search($id, $tmpArray, true))
      {
        $alreadyHaveAppointment = true;
        $bookedApppointmentDetails['userId'] = $id;
        $bookedApppointmentDetails['appointmentID'] = $t['id'];
        $bookedApppointmentDetails['time'] = $t['time'];
      }
    }
  }
}
function isAdmin(){
  if(isset($_SESSION["user"]) && $_SESSION['user']['id'] === "admin"){
    return true;
  }
  else{
    return false; 
  }
}

checkIfAlreadyHaveAppointment($appointmentsStorage, $alreadyHaveAppointment, $bookedApppointmentDetails); 

function authorize() {
  return isset($_SESSION["user"]);
}
function deleteGivenIDFromAppointments($userId, $appointmentsID, $appointmentsStorage)
{
  $tmpappointments = $appointmentsStorage->findAll(); 
  foreach($tmpappointments as $k => $t){
    if($k === $appointmentsID){

      $tmpArray = $t['applicant'];
      foreach ($tmpArray as $k => $element){
        if($element === $userId){
          unset($tmpArray[$k]); 
          $tmpData['id'] = $t['id'];
          $tmpData['time'] = $t['time'];
          $tmpData['places'] = $t['places'];
          $tmpData['applicant'] = $tmpArray; 
          $appointmentsStorage->update($t['id'], $tmpData); 
        }
      }
      $t['applicant'] = $tmpArray; 
    }
  }
} 
if(isset($_POST['deletedID'])){
  $lastDeletedID=$bookedApppointmentDetails['userId'];
  $appointmentsID = $bookedApppointmentDetails['appointmentID'];
  deleteGivenIDFromAppointments($lastDeletedID, $appointmentsID, $appointmentsStorage); 
  redirect('main.php');
}
$arrayList = []; 
function MakeList($appointments, $actualMonth, &$arrayList)
{
  foreach($appointments as $k => $t){
    $date = $t['time'];
    $d = date_parse_from_format("Y-m-d", $date);
    if(intval($actualMonth) === $d["month"])
    {
     // print_r($t['time']); 
      array_push($arrayList, $t['time']);
    }
  }
  
}
MakeList($appointments, $actualMonth, $arrayList); 

function compareDates($a, $b)
{
    if ($a == $b) {
        return 0;
    }
    return ($a < $b) ? -1 : 1;
}
usort($arrayList, "compareDates");
//print_r($arrayList);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Nemzeti Koronavírus Depó</title>
  <style>
    a + #Disable{
      display:none;
    }

    #full{
      color:red;
    }
    #notFull{
      color:green;
    }
  </style>
</head>
<body>
<h1 align="center" >NemKoViD - Időpont foglalás</h1>
<br>
<?php if($alreadyHaveAppointment) : ?>
  <?php if(isset($_SESSION['user'])) : ?>
    VAN MÁR FOGLALÁSOD!
    Részletek:
    <?=$bookedApppointmentDetails['time']?>
    Ha leszeretnéd mondani foglalásodat, kattints ide: 
    <form action="main.php" method="post">
      <input hidden type="text" name="deletedID" value=<?$bookedApppointmentDetails['userId']?>>
      <button type="submit">Foglalás lemondása</button>
    </form>
  <?php endif ?>
<?php endif ?>


<h3>Az intézetünk azért jött létre, hogy a koronavírus elleni harcot minél hatékonyabban folytathassuk. Ebben elengedhetetlen, hogy mindenki megfelelő időpontban oltáshoz jusson. Kérjük, válasszon egy Önnek megfelelő szabad időpontot, és győzzük le együtt a betegség terjedését.</h3>

<?php if(!isset($_SESSION['user'])) : ?>
  <a href="login.php">Bejelentkezés</a>
  <a href="registration.php"> Regisztráció</a>
  
<?php endif ?>


<br>
Aktuális Hónap: 
<?php 
  echo date("F", mktime(null, null, null, $actualMonth));
?>
<br>
Az aktuális hónapban lévő időpontok:
<br>
<div id="list">
  <ul>
    <?php $i = 0;
    foreach($appointments as $k => $t) : ?>
      <?php
      $string = ""; 
      $date = $t['time'];
      $numberOfApplicants = count($t['applicant']);
      $numberOfPlaces = $t['places']; 
      //$maxOfApplicants = 5; 
      $class = "notFull";
      $class2 = "Able";
      $d = date_parse_from_format("Y-m-d", $date);?>
      <?php if(intval($actualMonth) === $d["month"]) : ?>
        <?php 
        if($numberOfApplicants >= $numberOfPlaces)
        {
          $class="full";
          $class2 = "Disable";
        }
        $string = $string . "<li " . "id=" . "'$class'>"; 
        $string = $string . strval($arrayList[$i]); 
        $string = $string . "   ";
        $string = $string . strval(count($t['applicant']));
        $string = $string .'/'. $numberOfPlaces . '</li>';?>
      <?php 
     
      ?>
    <?=$string?>
    <?php
        $normalDate = explode(" ", strval($arrayList[$i]));
        //print_r($normalDate);
        $i++;
    ?>
    <?php if(($numberOfApplicants < $numberOfPlaces && !$alreadyHaveAppointment) || isAdmin()) : ?>
         <?php if(isAdmin()) : ?>
          <?php $first = "<a id='$class2'" . "href=appointmentsDetails.php?date=" . $normalDate[0] ."&time=". $normalDate[1] ."> Jelentkezés</a>"?>
            <?=$first?>
        <?php elseif(authorize()) : ?>
            <?php $first = "<a id='$class2'" . "href=appointmentsDetails.php?date=" . $normalDate[0] ."&time=". $normalDate[1] ."> Jelentkezés</a>"?>
            <?=$first?>
          <?php else : ?>
            <?php $second = "<a id='$class2'" . "href=login.php" . "> Jelentkezés</a>"?>
            <?=$second?>
          <?php endif?>
        <?php endif?>
    <?php endif?>
    <?php endforeach ?>
  </ul>
</div>
<div id="buttons">
  <?php if($actualMonth > 1) : ?>
  <a href=main.php?actualMonth=<?=$actualMonth-1?>>Előző hónap</a>
  <?php endif ?>
  <?php if($actualMonth < 6) : ?>
  <a href=main.php?actualMonth=<?=$actualMonth+1?>>Következő hónap</a>
  <?php endif ?>
</div>

Click here to <a href="logout.php" tite="Logout">Logout.<a>
<br>

<?php if(isAdmin()) : ?>
  <a href=addDate.php >Új időpont meghirdetése</a>
<?php endif?>


</body>
</html>