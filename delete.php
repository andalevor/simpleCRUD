<?php
require_once 'pdo.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    die('Not logged in');
}

if (isset($_POST['cancel'])) {
	header('Location: index.php');
	return;
}

if (isset($_POST['profile_id'])) {
	$statement = $pdo->prepare('DELETE from Profile WHERE profile_id = :id');
	$statement->execute([":id" => $_POST['profile_id']]);
	$statement = $pdo->prepare('DELETE from Position WHERE profile_id = :id');
	$statement->execute([":id" => $_POST['profile_id']]);
  $_SESSION['success'] = 'Profile deleted';
	header('Location: index.php');
	return;
}

if (!isset($_GET['profile_id'])) {
	$_SESSION['error'] = 'No profile_id parameter';
	header('Location: index.php');
	return;
}
$statement = $pdo->prepare('SELECT user_id, first_name, last_name from Profile WHERE profile_id = :id');
$statement->execute([":id" => $_GET['profile_id']]);
$prof = $statement->fetch(PDO::FETCH_ASSOC);
if ($prof === false) {
	$_SESSION['error'] = 'No such profile_id in database';
	header('Location: index.php');
	return;
}
if ($prof['user_id'] != $_SESSION['user_id']) {
  $_SESSION['error'] = 'You have no permition to delete this profile';
  header('Location: index.php');
  return;
}
?>
<!DOCTYPE html>
<html>
<head>
<title>Andrei Voronin</title>
</head>
<body>
<div class="container">
<?php
echo "<h1>Deleting Profile</h1>";
echo "<p>First Name: ".htmlentities($prof['first_name'])."</p>";
echo "<p>LastName: ".htmlentities($prof['last_name'])."</p>";
?>
<form method="post">
<input type="hidden" name="profile_id" value="<?= htmlentities($_GET['profile_id']) ?>">
<input type="submit" value="Delete">
<input type="submit" name="cancel" value="Cancel">
</form>
</div>
</body>
</html>

