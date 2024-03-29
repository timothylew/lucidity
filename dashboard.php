<?php
	session_start();
	if(!isset($_SESSION['current_user']) || empty($_SESSION['current_user'])) {
		header("Location: login.php");
	}
	else {
		require 'config/config.php';

		$mysqli = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

		// Checks to see if there is an error number and then prints out the error
		if($mysqli->connect_errno) {
			echo $mysqli->connect_error;
			exit();
		}

		// Set character encoding
		$mysqli->set_charset('utf8');

		$sql = "SELECT * FROM events 
				WHERE owner_id = '" . $_SESSION['current_user'] . "';"; 

		$results = $mysqli->query($sql);
		if(!$results) {
			echo $mysqli->error;
			exit();
		}
		$results2 = $mysqli->query($sql);
		$results3 = $mysqli->query($sql);

		// Close the connection.
		$mysqli->close();
	}
?>

<!DOCTYPE html>
<html>
<head>
	<link href="https://fonts.googleapis.com/css?family=Questrial" rel="stylesheet">
	<link rel="stylesheet" href="css/common.css">
	<link rel="stylesheet" href="css/dashboard.css">
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<title>Host Dashboard</title>
</head>
<body>
	<?php include 'nav.php'; ?>
	<div class="error"></div>
	<div class="container">
		<table>
			<tr>
				<td class="dashboard-left">
					<div class="dashboard-manage-events">
						<p style="font-size: 20px; padding-top: 20px; color: black;">Create Event Code</p>
						<form action="dashboard.php" method="POST">
							<div>
								<label for="event-id" class="text-paragraph">Event Code:</label>
								<div>
									<input type="text" id="event-id" name="event" placeholder="Event Code">
								</div>
							</div> 
						</form>
						<div>
							<button class="create-code">Submit</button>
						</div>
					</div>

					<div class="dashboard-manage-events">
						<p style="font-size: 20px; padding-top: 20px; color: black;">Update Event Code</p>
						<select class="select-option event-update">
							<option value="">Pick code to update.</option>
							<?php while($row = $results3->fetch_assoc()) : ?>
								<option value="<?php echo $row['event_code']; ?>">
									<?php echo $row['event_code']; ?>
								</option>
							<?php endwhile; ?>
						</select>

						<form action="dashboard.php" method="POST">
							<div>
								<label for="update-event-input"" class="text-paragraph">Event Code:</label>
								<div>
									<input type="text" id="update-event-input" name="event" placeholder="New event code">
								</div>
							</div> 
						</form>
						<div>
							<button class="update-code">Update</button>
						</div>
					</div>

					<div class="dashboard-manage-events">
						<p style="font-size: 20px; padding-top: 20px; color: black;">Delete Event Code</p>
						<select class="select-option event-delete">
							<option value="">Pick code to delete.</option>
							<?php while($row = $results2->fetch_assoc()) : ?>
								<option value="<?php echo $row['event_code']; ?>">
									<?php echo $row['event_code']; ?>
								</option>
							<?php endwhile; ?>
						</select>
						<div>
							<button class="delete-code">Delete</button>
						</div>
					</div>
				</td>

				<td class="dashboard-right">
					<div class="dashboard-manage-requests">
						<p style="font-size: 20px; padding-top: 20px; color: black;">Manage Requests</p>
						<div>
							<select class="select-option event-select">
								<option value="">--Select event code--</option>
								<?php while($row = $results->fetch_assoc()) : ?>
									<option value="<?php echo $row['event_code']; ?>">
										<?php echo $row['event_code']; ?>
									</option>
								<?php endwhile; ?>
							</select>
						</div>
						<div>
							<button class="request-refresh">Refresh</button>
						</div>
						<div class="request-display">No requests loaded.</div>
					</div>

					<div class="dashboard-support">
						<p style="font-size: 20px; padding-top: 20px; color: black;">Support</p>
						<p class="support-paragraph">Please feel free to contact us at lucidity@timothylew.com if you need anything! We will get back in touch with you as soon as possible.  If you experience any response errors or API errors during your time here, we recommend refreshing the page in case your access token has expired. The Lucidity team will also be notified immediately so we can further assist you.</p>
						<p class="support-paragraph">Graders: The <a href="about.php">about page</a> contains instructions and documentation on this project.</p>
					</div>
				</td>
			</tr>
		</table>
		<!-- </div> -->
		<div class="clear"></div>
	</div>

	<script type="text/javascript" src="util.js"></script>
	<script type="text/javascript">
		var eventCode = document.querySelector("#event-id");
		var updateEventCode = document.querySelector("#update-event-input");
		var createEvent = document.querySelector(".create-code");
		var eventSelect = document.querySelector(".event-select");
		var refreshButton = document.querySelector(".request-refresh");
		var requestIdArray = [];

		refreshToken(); 

		function clearErrors() {
			var errorDiv = document.querySelector(".error");
			while(errorDiv.hasChildNodes()) {
				errorDiv.removeChild(errorDiv.firstChild);
			}
		}

		eventCode.onkeydown = function(event) {
			console.log(eventCode.value.length);
			if(eventCode.value.length >= 5 && (event.keyCode != 8 && event.keyCode != 46)) {
				// Prevent the user from typing in more than 5 characters.
				return false;
			}
		}

		updateEventCode.onkeydown = function(event) {
			console.log(updateEventCode.value.length);
			if(updateEventCode.value.length >= 5 && (event.keyCode != 8 && event.keyCode != 46)) {
				// Prevent the user from typing in more than 5 characters.
				return false;
			}
		}

		createEvent.onclick = function() {
			clearErrors();
			console.log(eventCode.value);
			if(eventCode.value.length < 5) {
				createAlert("Code must be 5 characters long.", "red");
			}
			else {
				insertEvent(eventCode.value, updateDropdowns);
			}
			eventCode.value = "";
		}

		eventSelect.onchange = function() {
			console.log(eventSelect.value);
			clearErrors();
			refreshRequests(eventSelect.value);
		}

		refreshButton.onclick = function() {
			clearErrors();
			if(eventSelect.value != "" && eventSelect.value != undefined) {
				refreshRequests(eventSelect.value);
			}
			else {
				createAlert("Select an event to view requests.", "red");
			}
		}

		function refreshRequests(code) {
			if(code != "" && code != undefined) {
				document.querySelector(".request-display").innerHTML = ""; 
				getEventRequests(code, refreshRequestsCallback);
			}
			else {
				document.querySelector(".request-display").innerHTML = "No requests loaded."; 
			}
		}

		function refreshRequestsCallback(results) {
			console.log(results);
			requestIdArray = [];
			
			if(results.length > 0) {
				var lengthAdjustment = 0;
				for(var j = 0; j < (Math.floor((results.length/50) + 1)); j++) {
					var iterateLength = 50;
					if(j == Math.floor(results.length / 50)) {
						iterateLength = results.length % 50; 
					}
					console.log("Iterate length: " + iterateLength);
					var spotifyQuery = "";
					if(iterateLength > 0) {
						for(var i = 0; i < iterateLength; i++) {
							console.log(i + lengthAdjustment);
							console.log(results[i + lengthAdjustment]['song_id']);
							spotifyQuery += results[i + lengthAdjustment]['song_id'];
							if(i != iterateLength - 1) {
								spotifyQuery += ",";
							}
							requestIdArray.push(results[i + lengthAdjustment]['request_id']);
						}
						console.log(spotifyQuery);
						console.log(requestIdArray);
						retrieveSeveralTracks(spotifyQuery, retrieveTracksCallback, lengthAdjustment);
						if(iterateLength == 50) {
							lengthAdjustment += iterateLength;
						}
					}
				}
			}
			else {
				var noResults = document.createElement("p");
				noResults.innerHTML = "No requests at the moment.";
				document.querySelector(".request-display").appendChild(noResults);
			}
		}

		function retrieveTracksCallback(results, lengthAdjustment) {
			console.log(results);
			var resultsArray = results.tracks;
			if(resultsArray.length <= 0) {
				var noResults = document.createElement("p");
				noResults.innerHTML = "No requests at the moment.";
				document.querySelector(".request-display").appendChild(noResults);
			}
			for(var i = 0; i < resultsArray.length; i++) {
				var artistString = "";
				for(var j = 0; j < resultsArray[i].album.artists.length; j++) {
					artistString += resultsArray[i].album.artists[j].name;
					if(j != resultsArray[i].album.artists.length - 1) {
						artistString += ", ";
					}
				}
				console.log("Num " + (parseFloat(i) + parseFloat(lengthAdjustment)));
				createEventElement(resultsArray[i].name, artistString, resultsArray[i].album.name, resultsArray[i].album.images[0].url, "request", (parseFloat(i) + parseFloat(lengthAdjustment)));
			}
		}

		function createEventElement(song, artist, album, imageURL, type, request_id) {
			// for each result, we must call this function
			// for each result, we must call this function
			var container = document.createElement("div");
			var songName = document.createElement("p");
			var artistName = document.createElement("p");
			var albumName = document.createElement("p");
			var image = document.createElement("img");
			var dismiss = document.createElement("span");
			var table = document.createElement("table");
			var row = document.createElement("tr");
			var column1 = document.createElement("td");
			var column2 = document.createElement("td");
			var column3 = document.createElement("td");
			songName.innerHTML = song;
			artistName.innerHTML = artist;
			albumName.innerHTML = album;
			image.src = imageURL;
			image.classList.add("list-image");
			dismiss.innerHTML = "&times;";
			dismiss.classList.add("dismiss-button");

			var songInformation = document.createElement("div");
			songInformation.appendChild(songName);
			songInformation.appendChild(artistName);
			songInformation.appendChild(albumName);
			songInformation.classList.add("list-item");

			image.style.float = "left";
			songInformation.style.float = "left";
			image.classList.add("list-item");

			dismiss.onclick = function() {
				if(confirm("You are about to delete this request.  This action cannot be undone.")) {
					container.classList.add("remove-item");
					deleteRequest(requestIdArray[request_id]);
				}
			}

			column1.appendChild(image);
			column2.appendChild(songInformation);
			column3.appendChild(dismiss);
			column1.classList.add("column1");
			column2.classList.add("column2");
			column3.classList.add("column3");


			row.appendChild(column1);
			row.appendChild(column2);
			row.appendChild(column3);

			table.appendChild(row);

			container.appendChild(table);

			container.classList.add("list-object");

			if(type == "request") {
				document.querySelector(".request-display").appendChild(container);

			}
			else if(type == "playlist"){
				document.querySelector(".playlist-display").appendChild(container);
			}
		}

		function insertEvent(code, callback) {
			var request = new XMLHttpRequest();
			request.addEventListener("readystatechange", function() {
				if(request.readyState == XMLHttpRequest.DONE) {
					if(request.status == 200) {
						if(request.responseText == "successful_query") {
							createAlert("Code (" + code + ") successfully created.", "green");
							callback();
						}
						else {
							createAlert(request.responseText, "red");
						}
					}
					else {
						createAlert("AJAX Error " + request.status + ": " + request.statusText, "red");
					}
				}
			});
			request.open("GET", "api/createEvent.php?event_code=" + code);
			request.send();
		}

		document.querySelector(".delete-code").onclick = function() {
			clearErrors();
			var deleteSelectedItem = document.querySelector(".event-delete").value;
			if(deleteSelectedItem == "" || deleteSelectedItem == undefined) {
				createAlert("Select a code to delete.", "red");
			}
			else if(confirm("You are about to delete this event code and all of its requests.  This action cannot be undone.")) {
				deleteEvent(document.querySelector(".event-delete").value, updateDropdowns);
			}
		}

		document.querySelector(".update-code").onclick = function() {
			clearErrors();
			var newCode = document.querySelector("#update-event-input").value; // This is the input field.
			var oldCode = document.querySelector(".event-update").value; // This is the select.
			if(newCode.trim() == "") {
				createAlert("New code field cannot be empty.", "red");
			}
			else if (newCode.trim().length != 5) {
				createAlert("Code must be 5 characters.", "red");
			}
			else if(oldCode.trim() == "" || oldCode.trim() == undefined) {
				createAlert("Please select a code to update.", "red");
			}
			else {
				updateEvent(oldCode, newCode, updateDropdowns);
			}
			document.querySelector("#update-event-input").value = "";
		}

		function updateDropdowns() {
			getEvents(updateDropdownCallback);
		}

		function updateDropdownCallback(results) {
			console.log(results);
			var selectObjects = document.querySelectorAll("select");
			for(var i = 0; i < selectObjects.length; i++) {
				var currentItem = selectObjects[i];
				while(currentItem.hasChildNodes()) { 
					currentItem.removeChild(currentItem.firstChild);
				}
				var defaultOption = document.createElement("option");
			
				if(currentItem.classList.contains("event-delete")) {
					defaultOption.innerHTML = "Pick code to delete.";
				}
				else if(currentItem.classList.contains("event-update")) {
					defaultOption.innerHTML = "Pick code to update.";
				}
				else {
					defaultOption.innerHTML = "--Select event code--";
				}
				currentItem.appendChild(defaultOption);

				for(var j = 0; j < results.length; j++) {
					var option = document.createElement("option");
					option.innerHTML = results[j].event_code;
					option.value = results[j].event_code;
					currentItem.appendChild(option);
				}
			}
		}


	</script>

</body>
</html>