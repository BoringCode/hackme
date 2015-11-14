<link href="style.css" rel="stylesheet" type="text/css" media="screen" />
</head>
<body>
<div id="header">
	<div id="menu">
		<ul>
        <?php
			if(!isset($_COOKIE['hackme'])){ ?>
				<li><a href="index.php">Login</a></li>
				<li><a href="register.php">Register</a></li>
        <?php
			}else{
		?>
        		<li><a href="members.php">Main</a></li>
				<li><a href="post.php">Post</a></li>
                <li><a href="logout.php">logout</a></li>
        <?php }?>
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
