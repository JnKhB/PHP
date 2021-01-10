<?php 
include_once('UsersStorage.php');

$data = []; 
$errors = []; 
$data = [];
$isADMIN = false; 
function validate($post, &$data, &$errors) {
    if (!isset($post['password'])) {
      $errors['password'] = 'Jelszó megadása kötelező';
    }
    else if (trim($post['password']) === '') {
      $errors['password'] = 'Jelszó megadása kötelező';
    }
    else {
      $data['password'] = $post['password'];
    }
    if (!isset($post['email'])) {
        $errors['email'] = 'Az e-mail cím megadása kötelező';
      }
      else if (trim($post['email']) === '') {
        $errors['email'] = 'Az e-mail cím megadása kötelező';
      }
      else {
        $data['email'] = $post['email'];
      }
    return count($errors) === 0;
}

function redirect($page) {
  header("Location: ${page}");
  exit();
}
function logout() {
  unset($_SESSION["user"]);
}
function check_user(&$isADMIN, $user_storage, $email, $password) {

  if($email === "admin@nemkovid.hu" && $password === "admin")
  {
    $users = $user_storage->findMany(function ($user) use ($email, $password) {
      return $user["email"] === $email && $user["password"] === $password;
    }); 
    $isADMIN = true;
    return count($users) === 1 ? array_shift($users) : NULL;
  }
  $users = $user_storage->findMany(function ($user) use ($email, $password) {
    return $user["email"] === $email && 
           password_verify($password, $user["password"]);
  }); 
  return count($users) === 1 ? array_shift($users) : NULL;
}
function login($user) {
  $_SESSION["user"] = $user;
}
session_start();
$userStorage = new UsersStorage();
$errors = [];
if ($_POST) {
  if (validate($_POST, $data, $errors)) {
    $logged_in_user = check_user($isADMIN, $userStorage, $data['email'], $data['password']);
    if($isADMIN)
    {
      login($logged_in_user);
      redirect('main.php');
    }
    if (!$logged_in_user) {
      $errors['global'] = "Login error";
    } 
    else {
      login($logged_in_user);
      redirect('main.php');
    }
  }
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Nemzeti Koronavírus Depó - Belépés</title>
  <style>
  </style>
  </head>
  <body>
  <h1 align="center" >Bejelentkezés</h1>
<?php if (isset($errors['global'])) : ?>
  <p><span class="error"><?= $errors['global'] ?></span></p>
<?php endif ?>

<form action="" method="post">
  <div>
    <label for="email">E-mail cím: </label><br>
    <input type="text" name="email" id="email" value="<?= $_POST['email'] ?? "" ?>">
    <?php if (isset($errors['email'])) : ?>
      <span class="error"><?= $errors['email'] ?></span>
    <?php endif; ?>
  </div>
  <div>
    <label for="password">Jelszó: </label><br>
    <input type="password" name="password" id="password">
    <?php if (isset($errors['password'])) : ?>
      <span class="error"><?= $errors['password'] ?></span>
    <?php endif; ?>
  </div>
  <div>
    <button type="submit">Bejelentkezés</button>
  </div>
</form>

</body>

</html>