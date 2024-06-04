<?php
require_once "pdo.php";
session_start();

if (!isset($_GET['profile_id'])) {
  $_SESSION['error'] = 'profile_id is missing';
  header('Location: index.php');
  return;
}

$sql = 'SELECT first_name, last_name, email, headline, summary
  FROM Profile WHERE profile_id = :id';
$stmt = $pdo->prepare($sql);
$stmt->execute([':id' => $_GET['profile_id']]);
$prof = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$prof) {
  $_SESSION['error'] = 'No such profile_id';
  header('Location: index.php');
  return;
}
$first_name = htmlentities($prof['first_name']);
$last_name = htmlentities($prof['last_name']);
$email = htmlentities($prof['email']);
$headline = htmlentities($prof['headline']);
$summary = htmlentities($prof['summary']);
$sql = 'SELECT rank, year, description FROM Position WHERE profile_id = :id';
$stmt = $pdo->prepare($sql);
$stmt->execute([':id' => $_GET['profile_id']]);
$positions = $stmt->fetchAll(PDO::FETCH_ASSOC);
$sql = 'SELECT rank, year, name FROM Education JOIN Institution
  ON Education.institution_id = Institution.institution_id WHERE profile_id = :id';
$stmt = $pdo->prepare($sql);
$stmt->execute([':id' => $_GET['profile_id']]);
$educations = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html>
<head>
<title>Andrei Voronin</title>
</head>
<body>
<div class="container">
<h1>Profile information</h1>
<?php
echo "<p>First Name: $first_name</p>";
echo "<p>Last Name: $last_name</p>";
echo "<p>Email: $email</p>";
echo "<p>Headline:<br>$headline</p>";
echo "<p>Summary:<br>$summary</p>";
if ($positions) {
  $rank = array_column($positions, 'rank');
  array_multisort($rank, SORT_ASC, $positions);
  echo '<p>Position</p><ul>';
  foreach ($positions as $pos) {
    echo '<li>'.$pos['year'].': '.$pos['description'].'</li>';
  }
  echo '</ul>';
}
if ($educations) {
  $rank = array_column($educations, 'rank');
  array_multisort($rank, SORT_ASC, $educations);
  echo '<p>Educations</p><ul>';
  foreach ($educations as $edu) {
    echo '<li>'.$edu['year'].': '.$edu['name'].'</li>';
  }
  echo '</ul>';
}
?>
<a href=index.php>Done</a>
</div>
</body>
</html>
