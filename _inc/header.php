<?php
//Force logging in
if (!$auth->logged_in && defined("MEMBERS_ONLY") && MEMBERS_ONLY === true) {
	header("Location: index.php");
}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
	<head>
		<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
		<title>hackme</title>
		<link href="_css/style.css" rel="stylesheet" type="text/css" media="screen" />
	</head>
<body>
	<div id="header">
		<div id="menu">
			<ul>
	        <?php if(!$auth->logged_in) : ?>
				<li><a href="index.php">Login</a></li>
				<li><a href="register.php">Register</a></li>
	        <?php else : ?>
	        	<li><a href="members.php">Main</a></li>
				<li><a href="post.php">Post</a></li>
	            <li><a href="logout.php">logout</a></li>
	        <?php endif; ?>
			</ul>
		</div>
		<!-- end #menu -->
	</div>
	<!-- end #header -->
	<div id="logo">
		<h1><a href="#">hackme </a></h1>
		<p><em>an information security bulletin board</em></p>
	</div>
	<hr />
	<!-- end #logo -->
	<div id="page">
		<div id="page-bgtop">
			<div id="page-bgbtm">
				<div id="content">
				<?php if ($auth->logged_in) : ?>
				<div class="post">
					<div class="post-bgtop">
						<div class="post-bgbtm">
				        	<h2 class = "title">hackme bulletin board</h2>
				        	<p>Logged in as <a href="#"><?php echo $_SESSION["username"]; ?></a></p>
				        </div>
				    </div>
				</div>
				<?php endif; ?>
