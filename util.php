<?php

function validateProfile() {
  if (strlen($_POST['first_name']) < 1 || strlen($_POST['last_name']) < 1 ||
    strlen($_POST['email']) < 1 || strlen($_POST['headline']) < 1 || strlen($_POST['summary']) < 1) {
    $_SESSION['error'] = 'All profiles fields are required';
    return false;
  }
  if (strpos($_POST['email'], '@') === false) {
    $_SESSION['error'] = 'Email must have an at-sign (@)';
    return false;
  }
  return true;
}

function validatePositions() {
  $arr = [];
  for ($i = 1; $i <= 10; $i++) {
    if (isset($_POST['desc'.$i]) && isset($_POST['year'.$i])) {
      if (strlen($_POST['desc'.$i]) < 1 || strlen($_POST['year'.$i]) < 1) {
        $_SESSION['error'] = 'All positions fields are required';
        return false;
      }
      if (!is_numeric($_POST['year'.$i])) {
        $_SESSION['error'] = 'Position year must be a number.';
        return false;
      }
      $arr[] = $i;
    }
  }
  return $arr;
}

function validateEducations() {
  $arr = [];
  for ($i = 1; $i <= 10; $i++) {
    if (isset($_POST['edu_school'.$i]) && isset($_POST['edu_year'.$i])) {
      if (strlen($_POST['edu_school'.$i]) < 1 || strlen($_POST['edu_year'.$i]) < 1) {
        $_SESSION['error'] = 'All education fields are required';
        return false;
      }
      if (!is_numeric($_POST['edu_year'.$i])) {
        $_SESSION['error'] = 'Education year must be a number.';
        return false;
      }
      $arr[] = $i;
    }
  }
  return $arr;
}

function insertPositions($pdo, $pos_rank, $profile_id) {
  foreach ($pos_rank as $rank) {
    $sql = 'INSERT INTO Position (profile_id, rank, year, description)
      VALUES (:pid, :rk, :yr, :dn)';
    $stmt = $pdo->prepare($sql);
    $stmt->execute([
      ':pid' => $profile_id,
      ':rk' => $rank,
      ':yr' => $_POST['year'.$rank],
      ':dn' => $_POST['desc'.$rank]
    ]);
  }
}

function insertEducations($pdo, $edu_rank, $profile_id){
  foreach ($edu_rank as $rank) {
    $stmt = $pdo->prepare('SELECT institution_id FROM Institution WHERE name = :nm');
    $stmt->execute([':nm' => $_POST['edu_school'.$rank]]);
    $inst_id = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$inst_id) {
      $sql = 'INSERT INTO Institution (name) VALUES (:nm)';
      $stmt = $pdo->prepare($sql);
      $stmt->execute([':nm' => $_POST['edu_school'.$rank]]);
      $inst_id = $pdo->lastInsertId();
    } else {
      $inst_id = $inst_id['institution_id'];
    }
    $sql = 'INSERT INTO Education (profile_id, institution_id, rank, year)
      VALUES (:pid, :iid, :rk, :yr)';
    $stmt = $pdo->prepare($sql);
    $stmt->execute([
      ':pid' => $profile_id,
      ':iid' => $inst_id,
      ':rk' => $rank,
      ':yr' => $_POST['year'.$rank]
    ]);
  }
}
