<?php
require_once 'pdo.php';
session_start();
?>
<!DOCTYPE html>
<html>
<head>
<title>Profile Database</title>
</head>
<body>
<div class="container">
<h1>Welcome to the Profile Database</h1>
<?php
if (isset($_SESSION['success'])) {
	echo '<p style="color: green;">'.$_SESSION['success'].'</p>';
	unset($_SESSION['success']);
}
if (isset($_SESSION['error'])) {
	echo '<p style="color:red">'.$_SESSION['error']."</p>\n";
	unset($_SESSION['error']);
}
if (isset($_SESSION['user_id'])) {
	echo '<p><a href="logout.php">Logout</a></p>';
} else {
	echo '<p><a href="login.php">Please Log In</a></p>';
}
$stmt = $pdo->query('SELECT profile_id, first_name, last_name, headline,
 	summary from Profile');
$profiles = $stmt->fetchAll(PDO::FETCH_ASSOC);
if ($profiles) {
	echo '<table border="1"><tr><th>Name</th><th>Headline</th>';
	if (isset($_SESSION['user_id'])) {
		echo '<th>Action</th>';
	}
  echo '</tr>';
  foreach ($profiles as $prof) {
    echo '<tr><td><a href=view.php?profile_id='.$prof['profile_id'].'>'.
      htmlentities($prof['first_name']).' '.htmlentities($prof['last_name']).'</a></td>
      <td>'.htmlentities($prof['headline']).'</td>';
    if (isset($_SESSION['user_id'])) {
      echo '<td><a href="edit.php?profile_id='.$prof['profile_id'].'">Edit</a>  
        <a href="delete.php?profile_id='.$prof['profile_id'].'">Delete</a></td>';
    }
    echo '</tr>';
  }
  echo '</table>';
}
if (isset($_SESSION['user_id'])) {
  echo '<p><a href=add.php>Add New Entry</a></p>';
}
?>
</div>
</body>

