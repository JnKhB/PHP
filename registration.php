<?php
include_once('UsersStorage.php');
//print_r($_POST);
//print_r(isset($_POST['name']));
function validate($post, &$data, &$errors) {
    if(empty($_POST["name"])){
      $errors['name'] = 'A név megadása kötelező';
    }
    else{
      $data['name'] = $post['name'];
    }
    if(empty($post['taj'])){
      $errors['taj'] = 'A taj megadása kötelező';
    }
    else if(strlen((string)$post['taj']) != 9){
      $errors['taj'] = 'A tajszám 9 számból kell álljon';
    }
    else{
      $data['taj'] = $post['taj'];
    }
    if (empty($post['address'])) {
      $errors['address'] = 'A cím megadása kötelező';
    }
    else {
      $data['address'] = $post['address'];
    }
    if (!filter_var($post['email'], FILTER_VALIDATE_EMAIL)) {
      $errors['email'] = 'Az email cím nem megfelelő formátumú';
    }
    else{
      $data['email'] = $post['email'];
    }
    if(empty($post['password1'])){
      $errors['password1'] = 'A jelszó megadása kötelező';
    }
    elseif(empty($post['password2'])){
      $errors['password2'] = 'A jelszó megadása kötelező';
    }
    elseif($post['password2'] !== $post['password1']){
      $errors['password1'] = 'A jelszó nem egyezik';
      $errors['password2'] = 'A jelszó nem egyezik';
    }
    else{
      $data['password'] = $post['password1']; 
    }
    return count($errors) === 0;
}
function user_exists($usersStorage, $email) {
  $users = $usersStorage->findOne(['email' => $email]);
  return !is_null($users);
}
function redirect($page) {
  header("Location: ${page}");
  exit();
}
function add_user($usersStorage, $data) {
  $id = uniqid(); 
  $id = [
    'id' =>  $id,
    'taj' =>  $data['taj'],
    'name'  => (string)$data['name'],
    'address'  => (string)$data['address'],
    'password'  => password_hash($data['password'], PASSWORD_DEFAULT),
    'email'  => $data['email'],
  ];
  //print_r($id); 
  return $usersStorage->add($id);
}

$usersStorage = new UsersStorage();
$errors = [];
$data = [];

if (count($_POST) > 0) {
  if (validate($_POST, $data, $errors)) {
    if (user_exists($usersStorage, $data['email'])) {
      $errors['global'] = "Az e-mail címmel már történt regisztráció!";
    } else {
      add_user($usersStorage, $data);
      redirect('login.php');
    } 
  }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Nemzeti Koronavírus Depó</title>
  <style>
    form {
      border: 2px solid red;
      border-radius: 4px;
    }
  </style>
  </head>
  <body>
<!-- 
    login.php
-->
<h1 align="center" >Regisztráció</h1>
<form  action="" method="post" novalidate align="center">
    
    Teljes név: <input type="text" name="name" 
          value="<?= $_POST['name'] ?? '' ?>">
    <?php if (isset($errors['name'])) : ?>
      <small><?= $errors['name'] ?></small>
    <?php endif ?>
    <br>
    <br>
    
    Taj szám: <input type="number" name="taj"
         value="<?= $_POST['taj'] ?? '' ?>"> 
    <?php if (isset($errors['taj'])) : ?>
      <small><?= $errors['taj'] ?></small>
    <?php endif ?>
    <br>
    <br>
    Értesítési cím: <input type="text" name="address"
         value="<?= $_POST['address'] ?? '' ?>"> 
    <?php if (isset($errors['address'])) : ?>
      <small><?= $errors['address'] ?></small>
    <?php endif ?>
    <br>
    <br>
    E-mail: <input type="text" name="email"
         value="<?= $_POST['email'] ?? '' ?>"> 
    <?php if (isset($errors['email'])) : ?>
      <small><?= $errors['email'] ?></small>
    <?php endif ?>
    <br>
    <br>
    Jelszó: <input type="password" name="password1"
         value="<?= $_POST['password1'] ?? '' ?>"> 
    <?php if (isset($errors['password1'])) : ?>
      <small><?= $errors['password1'] ?></small>
    <?php endif ?>
    <br>
    <br>
    Jelszó: <input type="password" name="password2"
         value="<?= $_POST['password2'] ?? '' ?>"> 
    <?php if (isset($errors['password2'])) : ?>
      <small><?= $errors['password2'] ?></small>
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