<?php

if (!isset($_GET['term'])) {
  die('Missing required parameter');
}
// https://stackoverflow.com/questions/7803757/detect-if-session-cookie-set-properly-in-php
// Lets not start a session unless we already have one
if (!isset($_COOKIE[session_name()])) {
  die("Must be logged in");
}

session_start();
if ( ! isset($_SESSION['user_id']  )) {
  die('Not logged in');
}

require_once "pdo.php";

header("Content-type: application/json; charset=utf-8");
$sql = 'SELECT name FROM Institution';
$stmt = $pdo->prepare('SELECT name FROM Institution
  WHERE name LIKE :prefix');
$stmt->execute([':prefix' => $_GET['term']."%"]);
$schools = [];
while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
  $schools[] = $row['name'];
}

echo json_encode($schools);
?>
