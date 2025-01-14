<?php
require_once "pdo.php";
session_start();

if ( isset($_POST['cancel'] ) ) {
	header("Location: index.php");
	return;
}

// Check to see if we have some POST data, if we do process it
if ( isset($_POST['email']) && isset($_POST['pass']) ) {
	if ( strlen($_POST['email']) < 1 || strlen($_POST['pass']) < 1 ) {
		$_SESSION['error'] = "Email and password are required";
		header("Location: login.php");
		return;
	} else {
		if (strpos($_POST['email'], '@') === false) {
			$_SESSION['error'] = 'Email must have an at-sign (@)';
			header("Location: login.php");
			return;
		} else {
			$sql = 'SELECT user_id, name, password FROM users WHERE email = :em';
			$stmt = $pdo->prepare($sql);
			$stmt->execute([':em' => $_POST['email']]);
			$user = $stmt->fetch(PDO::FETCH_ASSOC);
			if (!$user) {
				$_SESSION['error'] = "No such user";
				header("Location: login.php");
				return;
			}
			$salt = 'XyZzy12*_';
			$check = hash('md5', $salt.$_POST['pass']);
			if ($check == $user['password']) {
				$_SESSION['user_id'] = $user['user_id'];
				$_SESSION['user_name'] = $user['name'];
				header("Location: index.php");
				return;
			} else {
				$_SESSION['error'] = "Incorrect password";
				header("Location: login.php");
				return;
			}
		}
	}
}
?>
<!DOCTYPE html>
<html>
<head>
<title>Andrei Voronin's Login Page</title>
</head>
<body>
<div class="container">
<h1>Please Log In</h1>
<?php
// Note triple not equals and think how badly double
// not equals would work here...
if (isset($_SESSION['error'])) {
	// Look closely at the use of single and double quotes
	echo('<p style="color: red;">'.$_SESSION['error']."</p>\n");
	unset($_SESSION['error']);
}
?>
<form method="POST">
<label for="email">Email</label>
<input type="text" name="email" id="email"><br/>
<label for="id_1723">Password</label>
<input type="password" name="pass" id="id_1723"><br/>
<input type="submit" onclick="return doValidate();" value="Log In">
<input type="submit" name="cancel" value="Cancel">
</form>
<p>
For a password hint, view source and find a password hint
in the HTML comments.
<!-- Hint: 
The account is umsi@umich.edu
The password is the three character name of the 
programming language used in this class (all lower case) 
followed by 123. -->
</p>
<script>
function doValidate() {
    console.log('Validating...');
    try {
        addr = document.getElementById('email').value;
        pw = document.getElementById('id_1723').value;
        console.log("Validating addr="+addr+" pw="+pw);
        if (addr == null || addr == "" || pw == null || pw == "") {
            alert("Both fields must be filled out");
            return false;
        }
        if ( addr.indexOf('@') == -1 ) {
            alert("Invalid email address");
            return false;
        }
        return true;
    } catch(e) {
        return false;
    }
    return false;
}
</script>
</div>
</body>
