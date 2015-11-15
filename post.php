<?php 
define("MEMBERS_ONLY", true);

require("_inc/functions.php");

//if the login form is submitted 
if (isset($_POST['post_submit'])) {
	if(!isset($_POST['title']) || !isset($_POST['message'])) {
		die('<p>You did not fill in a required field.
		Please go back and try again!</p>');
	}
	
	$title = htmlspecialchars(trim($_POST["title"]));
	$message = htmlspecialchars(trim($_POST["message"]));

	$auth->query("INSERT INTO threads (username, title, message, date) VALUES('%s', '%s', '%s', '%d')", array(
		$_SESSION["username"],
		$title,
		$message,
		time()
	), true);
	
	header("Location: members.php");
}


require("_inc/header.php");
?> 

<div class="post">
	<div class="post-bgtop">
		<div class="post-bgbtm">            
            <h2 class="title">NEW POST</h2>
            <p class="meta">by <a href="#"><?php echo $_SESSION["username"]; ?> </a></p>
            <p> do not leave any fields blank... </p>
            
            <form method="post" action="post.php">
            Title: <input type="text" name="title" maxlength="50"/>
            <br />
            <br />
            Posting:
            <br />
            <br />
            <textarea name="message" cols="120" rows="10" id="message"></textarea>
            <br />
            <br />
            <input name="post_submit" type="submit" id="post_submit" value="POST" />
            </form>
        </div>
    </div>
</div>
<?php require("_inc/footer.php"); ?>