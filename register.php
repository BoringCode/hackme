<?php
require("_inc/functions.php");

//Redirect to members page if logged in
if ($auth->logged_in) {
    header("Location: members.php");
}

$nonce = new Nonce("register_action");

if (isset($_POST['submit'])) {

    if (!isset($_POST["nonce"]) || !$nonce->verify($_POST["nonce"])) {
        die("CSRF detected, knock it off you punk");
    }
    
    if(!isset($_POST['uname']) || !isset($_POST['password']) || !isset($_POST['fname']) || !isset($_POST['lname'])) {
        die('<p>You did not fill in a required field.
        Please go back and try again!</p>');
    }
    
    if (!$auth->createUser($_POST["uname"], $_POST["password"], $_POST["fname"], $_POST["lname"])) {
        die("Sorry, can't create user");
    } else {
        $userCreated = true;
    }
}

require("_inc/header.php");
?>
<div class="post">
	<div class="post-bgtop">
		<div class="post-bgbtm">
        <h2 class = "title">hackme Registration</h2>
        <?php if (isset($userCreated)) : ?>
            <h3>Registration Successful!</h3> <p>Welcome <?php echo $_POST['fname']; ?>! Please log in...</p>
        <?php else : ?>
        	<form method="post" action="register.php">
            <table>
                <tr>
                    <td> Username </td> 
                    <td> <input type="text" name="uname" maxlength="20"/> </td>
                    <td> <em>choose a login id</em> </td>
                </tr>
                <tr>
                    <td> Password </td>
                    <td> <input type="password" name="password" maxlength="40" /> </td>
                    <td>
                        <em>Password must match requirements</em>
                        <ul>
                            <li>Must be at least 8 characters</li>
                            <li>Must not be a dictionary word</li>
                            <li>Password cannot be primarily letters, numbers, or special characters (must be a combination of them)</li>
                        </ul>
                    </td>
                </tr>
                <tr>
                    <td> First Name </td>
                    <td> <input type="text" name="fname" maxlength="25"/> </td>
                </tr>
                 <tr>
                    <td> Last Name </td>
                    <td> <input type="text" name="lname" maxlength="25"/> </td>
                </tr>
                <tr>
                    <td> <input type="submit" name="submit" value="Register" /> </td>
                </tr>
            </table>
            <input type="hidden" name="nonce" value="<?php echo $nonce->get(); ?>">
            </form>
        <?php endif; ?>
        </div>
    </div>
</div>

<?php require("_inc/footer.php"); ?>