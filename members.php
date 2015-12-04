<?php
define("MEMBERS_ONLY", true);

require("_inc/functions.php");

//if the login form is submitted 
if (isset($_POST['submit']) && isset($_POST["password"]) && isset($_POST["username"]) && isset($_POST["nonce"])) {
	$nonce = new Nonce("login_action");
	if (!$nonce->verify($_POST["nonce"])) {
		die("CSRF detected, knock it off you punk");
	}
	$auth->login($_POST["username"], $_POST["password"]);
}

require("_inc/header.php");

$threads = $auth->query("SELECT * FROM threads ORDER BY date DESC", array(), true);
foreach($threads as $thread) :
?>
	<div class="post">
		<div class="post-bgtop">
			<div class="post-bgbtm">
				<h2 class="title">
					<a href="show.php?pid=<?php echo htmlspecialchars($thread->id); ?>"><?php echo htmlspecialchars($thread->title); ?>
					</a>
				</h2>
				<p class="meta">
					<span class="date"><?php echo date('l, d F, Y', htmlspecialchars($thread->date)); ?></span> - Posted by <a href="#"><?php echo htmlspecialchars($thread->username); ?></a>
				</p>
			</div>
		</div>
	</div> 
<?php endforeach; ?>
<?php include('_inc/footer.php'); ?>
