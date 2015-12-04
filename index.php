<?php 
require("_inc/functions.php");

//Redirect to members page if logged in
if ($auth->logged_in) {
	header("Location: members.php");
}

$nonce = new Nonce("login_action");

require("_inc/header.php");
?>

<div class="post">
	<div class="post-bgtop">
		<div class="post-bgbtm">
			<h2 class="title"><a href="#">Welcome to hackme </a></h2>
			<div class="entry">
				<?php if (!$auth->logged_in) : ?>
	           	<form method="post" action="members.php">
					<h2>LOGIN</h2>
					<table>
						<tr> <td> Username </td> <td> <input type="text" name="username" /> </td> </tr>
						<tr> <td> Password </td> <td> <input type="password" name="password" /> </td>  
	                    <td> <input type="submit" name = "submit" value="Login" /> </td></tr>
					</table>
					<input type="hidden" name="nonce" value="<?php echo $nonce->get(); ?>">
				</form>					
				<hr style=\"color:#000033\" />					
				<p></p><p>If you are not a member yet, please click <a href="register.php">here</a> to register.</p>
           		<?php endif; ?>
			</div>
		</div>
	</div>
</div>
<!-- end #sidebar -->
<?php require('_inc/footer.php'); ?>
