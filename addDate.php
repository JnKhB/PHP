<?php 
session_start(); 
function redirect($page) {
    header("Location: ${page}");
    exit();
  }
if($_SESSION['user']['id'] !== "admin")
{
    redirect("main.php");
}
include_once('appointmentsStorage.php');
$errors = []; 
$data = []; 
function validate($post, &$data, &$errors) {
    if(empty($_POST["date"])){
      $errors['date'] = 'A dátum megadása kötelező';
    }
    else if(!validateDate($_POST["date"]))
    {
        $errors['date'] = 'A dátum nem megfelelő!';
    }
    else{
      $data['date'] = $post['date'];
    }
    if(empty($post['time'])){
      $errors['time'] = 'Az időpont megadása kötelező';
    }
    else if(!validateMilTime($_POST["time"])){
        $errors['time'] = 'Az idő nem megfelelő!';
    }
    else{
      $data['time'] = $post['time'];
    }
    if (empty($post['places'])) {
      $errors['places'] = 'A férőhelyek számának megadása kötelező';
    }
    else {
      $data['places'] = $post['places'];
    }
    return count($errors) === 0;
    
}
function validateDate($date, $format = 'Y-m-d'){
    $d = DateTime::createFromFormat($format, $date);
    return $d && $d->format($format) === $date;
}
function validateMilTime($time){
    $bits = explode(':', $time);
    if ($bits[0] > 24 || ($bits[0] == 24 && $bits[1] > 0) || count($bits) > 2){
    return false;
    }
    return true;
    }
function add_appointment($appointmentsStorage, $data) {
    $id = uniqid(); 
    $wholeTime = $data['date'] . " " . $data['time']; 
    $id = [
        'id' =>  $id,
        'time' =>  $wholeTime,
        'places'  => $data['places'], 
        'applicant' => []
    ];
    return $appointmentsStorage->add($id);
    }
$appointmentsStorage = new AppointmentsStorage();
if (count($_POST) > 0) {
  if (validate($_POST, $data, $errors)) {;
    add_appointment($appointmentsStorage, $data);
    redirect("main.php");
  }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Nemzeti Koronavírus Depó - Új időpont rögzítése</title>
  <style>
  </style>
  </head>
  <body>
  <h1 align="center" >Új időpont rögzítése</h1>
  <form  action="" method="post" novalidate align="center">
    
    Dátum: <input type="date" name="date" 
          value="<?= $_POST['date'] ?? '' ?>">
    <?php if (isset($errors['date'])) : ?>
      <small><?= $errors['date'] ?></small>
    <?php endif ?>
    <br>
    <br>
    
    Időpont: <input type="time" name="time"
         value="<?= $_POST['time'] ?? '' ?>"> 
    <?php if (isset($errors['time'])) : ?>
      <small><?= $errors['time'] ?></small>
    <?php endif ?>
    <br>
    <br>
    Helyek száma: <input type="number" name="places"
         value="<?= $_POST['places'] ?? '' ?>"> 
    <?php if (isset($errors['places'])) : ?>
      <small><?= $errors['places'] ?></small>
    <?php endif ?>
    <br>
    <br>
<button type="submit">Regisztráció</button>

<?php if (isset($errors['global'])) : ?>
    <small><?= $errors['global'] ?></small>
<?php endif ?>
</form>

</body>

</html>