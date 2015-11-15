<?php 
define("MEMBERS_ONLY", true);

require("_inc/functions.php");

//if the login form is submitted 
if (!isset($_GET['pid'])) {
	
	//Handle thread deletion
	if (isset($_GET['delpid'])) {
		$auth->query("DELETE FROM threads WHERE id = '%d' AND username = '%s'", array($_GET["delpid"], $_SESSION["username"]), true);
	}
	
	header("Location: members.php");
}

require("_inc/header.php");
?> 

<?php
	$threads = $auth->query("SELECT * FROM threads WHERE id = '%d' ORDER BY date DESC", array($_GET["pid"]), true);
	if (count($threads) === 0) echo "No thread found matching this ID"; 
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
				<div class="entry">
					<p><?php echo htmlspecialchars($thread->message); ?></p>
				</div>
				<?php if ($_SESSION["username"] === $thread->username) : ?>
			    	<p><a href="show.php?delpid=<?php echo htmlspecialchars($thread->id); ?>">DELETE</a></p>
				<?php endif; ?> 
			</div>
		</div>
	</div> 
<?php endforeach; ?>

<?php require("_inc/footer.php"); ?>