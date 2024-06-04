<?php
require_once 'util.php';
require_once "pdo.php";
session_start();

if (!isset($_SESSION['user_id'])) {
    die('Not logged in');
}

if (isset($_POST['cancel'])) {
	header('Location: index.php');
	return;
}

if (isset($_POST['first_name']) && isset($_POST['last_name']) &&
  isset($_POST['email']) && isset($_POST['headline']) && isset($_POST['summary'])) {
  $pos_rank = validatePositions();
  $edu_rank = validateEducations();
  if (!validateProfile() || !$pos_rank || !$edu_rank) {
    header('Location: edit.php?profile_id='.$_POST['profile_id']);
    return;
  }
  $profile_id = $_POST['profile_id'];
  $sql = 'UPDATE Profile SET first_name = :fn, last_name = :ln, email = :em,
    headline = :hl, summary = :su WHERE profile_id = :id';
  $stmt = $pdo->prepare($sql);
  $stmt->execute([
    ':fn' => $_POST['first_name'],
    ':ln' => $_POST['last_name'],
    ':em' => $_POST['email'],
    ':hl' => $_POST['headline'],
    ':su' => $_POST['summary'],
    ':id' => $profile_id
  ]);
	$statement = $pdo->prepare('DELETE from Position WHERE profile_id = :id');
	$statement->execute([":id" => $_POST['profile_id']]);
  insertPositions($pdo, $pos_rank, $profile_id);
	$statement = $pdo->prepare('DELETE from Education WHERE profile_id = :id');
	$statement->execute([":id" => $_POST['profile_id']]);
  insertEducations($pdo, $edu_rank, $profile_id);
  $_SESSION['success'] = 'Profile successfully updated';
  header('Location: index.php');
  return;
}

if (!isset($_GET['profile_id'])) {
	$_SESSION['error'] = 'No profile_id parameter';
	header('Location: index.php');
	return;
}
$stmt = $pdo->prepare('SELECT user_id, first_name, last_name, email, headline,
  summary from Profile WHERE profile_id = :id');
$stmt->execute([":id" => $_GET['profile_id']]);
$prof = $stmt->fetch(PDO::FETCH_ASSOC);
if ($prof === false) {
	$_SESSION['error'] = 'No such profile_id in database';
	header('Location: index.php');
	return;
}
if ($prof['user_id'] != $_SESSION['user_id']) {
  $_SESSION['error'] = 'You have no permition to edit this profile';
  header('Location: index.php');
  return;
}
$sql = 'SELECT rank, year, description FROM Position WHERE profile_id = :id';
$stmt = $pdo->prepare($sql);
$stmt->execute([':id' => $_GET['profile_id']]);
$positions = $stmt->fetchAll(PDO::FETCH_ASSOC);
$sql = 'SELECT rank, year, name FROM Education JOIN Institution ON
 Education.institution_id = Institution.institution_id WHERE profile_id = :id';
$stmt = $pdo->prepare($sql);
$stmt->execute([':id' => $_GET['profile_id']]);
$educations = $stmt->fetchAll(PDO::FETCH_ASSOC);
$first_name = htmlentities($prof['first_name']);
$last_name = htmlentities($prof['last_name']);
$email = htmlentities($prof['email']);
$headline = htmlentities($prof['headline']);
$summary = htmlentities($prof['summary']);
?>
<!DOCTYPE html>
<html>
<head>
<title>Andrei Voronin</title>
<link rel="stylesheet" href="https://code.jquery.com/ui/1.12.1/themes/ui-lightness/jquery-ui.css"> 
<script src="https://code.jquery.com/jquery-3.2.1.js" integrity="sha256-DZAnKJ/6XZ9si04Hgrsxu/8s717jcIzLy3oi35EouyE=" crossorigin="anonymous"></script>
<script src="https://code.jquery.com/ui/1.12.1/jquery-ui.js" integrity="sha256-T0Vest3yCU7pafRw9r+settMBX6JkKN06dqBnpQ8d30=" crossorigin="anonymous"></script>
</head>
<body>
<div class="container">
<?php
echo "<h1>Editing Profile</h1>";
?>
<form method="post">
<p>First Name:
<input type="text" name="first_name" value="<?= $first_name ?>" size="60"/></p>
<p>Last Name:
<input type="text" name="last_name" value="<?= $last_name ?>" size="60"/></p>
<p>Email:
<input type="text" name="email" value="<?= $email ?>" size="30"/></p>
<p>Headline:<br/>
<input type="text" name="headline" value="<?= $headline ?>" size="80"/></p>
<p>Summary:<br/>
<textarea name="summary" rows="8" cols="80">
<?= $summary ?>
</textarea>
<p>
Educations: <input type="submit" id="addEdu" value="+">
<div id="edu_fields">
<?php
$eduNum = 0;
if ($educations) {
  $eduNum = sizeof($educations);
  $rank = array_column($educations, 'rank');
  array_multisort($rank, SORT_ASC, $educations);
  for ($i = 1; $i <= $eduNum; $i++) {
    echo '<div id="edu'.$i.'"><p>Year: <input type="text" name="edu_year'.$i.
      '" value="'.$educations[$i - 1]['year'].'" /><input type="button" value="-"
      onclick="$\'#edu'.$i.'\').remove();return false;"></p>
      <p>Schoold: <input type="text" size="80" name="edu_school'.$i.'"
      class="school" value="'.$educations[$i - 1]['name'].'"></p></div>';
  }
}
?>
</div>
</p>
<p>
Position: <input type="submit" id="addPos" value="+">
<div id="position_fields">
<?php
$posNum = 0;
if ($positions) {
  $posNum = sizeof($positions);
  $rank = array_column($positions, 'rank');
  array_multisort($rank, SORT_ASC, $positions);
  for ($i = 1; $i <= $posNum; $i++) {
    echo '<div id="position'.$i.'"><p>Year: <input type="text" name="year'.$i.
      '" value="'.$positions[$i - 1]['year'].'" /><input type="button" value="-"
      onclick="$\'#position'.$i.'\').remove();return false;"></p>
      <textarea name="desc'.$i.'" rows="8" cols="80">'.
      $positions[$i - 1]['description'].'</textarea></div>';
  }
}
?>
</div>
</p>
<p>
<input type="hidden" name="profile_id" value="<?= htmlentities($_GET['profile_id']) ?>">
<input type="submit" value="Save">
<input type="submit" name="cancel" value="Cancel">
</p>
</form>
<script>
countPos = <?= $posNum ?>;
countEdu = <?= $eduNum ?>;

// http://stackoverflow.com/questions/17650776/add-remove-html-inside-div-using-javascript
$(document).ready(function(){
    window.console && console.log('Document ready called');
    $('#addPos').click(function(event){
        // http://api.jquery.com/event.preventdefault/
        event.preventDefault();
        if ( countPos >= 9 ) {
            alert("Maximum of nine position entries exceeded");
            return;
        }
        countPos++;
        window.console && console.log("Adding position "+countPos);
        $('#position_fields').append(
            '<div id="position'+countPos+'"> \
            <p>Year: <input type="text" name="year'+countPos+'" value="" /> \
            <input type="button" value="-" \
                onclick="$(\'#position'+countPos+'\').remove();return false;"></p> \
            <textarea name="desc'+countPos+'" rows="8" cols="80"></textarea>\
            </div>');
    });
    $('#addEdu').click(function(event){
      event.preventDefault();
      if ( countEdu >= 9 ) {
        alert("Maximum of nine education entries exceeded");
        return;
      }
      countEdu++;
      window.console && console.log("Adding education "+countEdu);
      $('#edu_fields').append(
        '<div id="edu'+countEdu+'"> \
          <p>Year: <input type="text" name="edu_year'+countEdu+'" value="" /> \
            <input type="button" value="-" \
              onclick="$(\'#edu'+countEdu+'\').remove();return false;"><br>\
                <p>School: <input type="text" size="80"\
                  name="edu_school'+countEdu+'" class="school" value="" />\
                    </p></div>'
        );

        $('.school').autocomplete({
            source: "school.php"
        });
    });
});
</script>
</div>
</body>
</html>
