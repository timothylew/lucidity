<?php
	session_start(); 

	if(isset($_POST['password']) && !empty($_POST['password']) && isset($_POST['username']) && !empty($_POST['username'])){
		$_SESSION['account_created'] = "set";
		$password_hash = hash('sha256', $_POST['password']);

		header("Location: login.php");
	}
	else if(isset($_SESSION['registration_submit']) && !empty($_SESSION['registration_submit'])) {
		$error = "There was an error processing your registration.  Please try again or contact an administrator.";
	}
?>

<!DOCTYPE html>
<html>
<head>
	<link href="https://fonts.googleapis.com/css?family=Questrial" rel="stylesheet">
	<link rel="stylesheet" href="css/common.css">
	<link rel="stylesheet" href="css/login.css">
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<title>Register</title>
</head>
<body>
	<?php include 'nav.php'; ?>

	<div class="error"></div>
	<div class="container">
		<h1>Want to facilitate better communication between your event-goers and live musicians?</h1>
		<p>Your free Lucidity host account will allow you to: </p>
		<ul>
			<li>Receive and manage music requests from your eventgoers.</li>
			<li>Facilitate communication between you and your attendees.</li>
			<li>Create a better event experience.</li>
		</ul>
	</div>
	<div class="container">

		<!-- <?php if(isset($error) && !empty($error)) : ?>
			<div class="error">
				<?php echo $error; ?>
			</div>
		<?php endif; ?> -->

		<form id="registration-form" action="register.php" method="POST">
			<div>
				<label for="username-id" class="text-paragraph">Username:</label>
				<div>
					<input type="text" id="username-id" name="username" placeholder="Username">
				</div>
			</div> 

			<div>
				<label for="email-id" class="text-paragraph">Email:</label>
				<div>
					<input type="email" id="email-id" name="email" placeholder="Email">
				</div>
			</div> 

			<div>
				<label for="password-id" class="text-paragraph">Password:</label>
				<div class="col-sm-9">
					<input type="password" id="password-id" name="password" placeholder="Password">
				</div>
			</div> 

			<div>
				<label for="password-confirm" class="text-paragraph">Confirm Password:</label>
				<div class="col-sm-9">
					<input type="password" id="password-confirm" name="password-confirm" placeholder="Confirm Password">
				</div>
			</div> 
			<div>
				<button type="submit">Register</button>
			</div> 
		</form>
	</div>

	<script type="text/javascript" src="util.js"></script>

	<script type="text/javascript">
		var processed = false;

		document.querySelector("#registration-form").onsubmit = function(event) {

			// Remove existing error notifications.
			var errorDiv = document.querySelector(".error");
			while(errorDiv.hasChildNodes()) { 
				errorDiv.removeChild(errorDiv.firstChild);
			}

			var red = "#f44336";
			var validRegistration = true;
			var username = document.querySelector("#username-id");
			var password = document.querySelector("#password-id");
			var email = document.querySelector("#email-id");
			var passwordConfirm = document.querySelector("#password-confirm");

			if(password.value != passwordConfirm.value) {
				validRegistration = false;
				createAlert("Your passwords do not match.", red);
			}
			if(username.value.trim().length == 0) {
				validRegistration = false;
				username.value = "";
				createAlert("Username must not be empty.", red);
				// Switch class here.
			}
			if(email.value.trim().length == 0) {
				validRegistration = false;
				createAlert("Email must not be empty.", red)
			}
			if(password.value.trim().length == 0) {
				validRegistration = false;
				createAlert("Password must not be empty.", red);
			}
			if(passwordConfirm.value.trim().length == 0) {
				validRegistration = false;
				createAlert("Confirm password must not be empty.", red)
			}

			if(validRegistration) {
				registerUser(username.value, email.value, password.value);
			}
			
			return false;
		}

		function registerUser(username_process, email_process, password_process) {
			var request = new XMLHttpRequest();
			request.addEventListener("readystatechange", function() {
				if(request.readyState == XMLHttpRequest.DONE) {
					if(request.status == 200) {
						console.log(request.responseText);
						if(request.responseText != "successful_query") {
							createAlert("Registration error.", "red");
						}
						else {
							window.location.href="login.php";
						}
					}
					else {
						createAlert("AJAX Error " + request.status + ": " + request.statusText, "red");
					}
				}
			});

			request.open("POST", "api/register.php");
			console.log("username=" + username_process + "&email=" + email_process + "&password=" + password_process);
			request.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
			request.send("username=" + username_process + "&email=" + email_process + "&password=" + password_process);
		}

	</script>

</body>
</html>