<div>
	<ul class="navbar">
		<li class="nav-element"><a href="index.php"><img src="img/lucidity_hd.png" height="50px"></a></li>
		<?php if(isset($_SESSION['current_user']) && !empty($_SESSION['current_user'])) : ?>
			<li class="nav-element nav-element-link" style="float:right;"><a href="logout.php">Logout</a></li>
			<li class="nav-element nav-element-link" style="float:right;"><a href="dashboard.php">Manage</a></li>
		<?php else : ?>
			<li class="nav-element nav-element-link" style="float:right;"><a href="login.php">Login</a></li>
			<li class="nav-element nav-element-link" style="float:right;"><a href="event.php">Request</a></li>
		<?php endif; ?>
		<li class="nav-element nav-element-link" style="float:right;"><a href="about.php">About</a></li>
	</ul>
</div>

<div class="clear"></div>