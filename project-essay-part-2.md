# Project Writeup (Part 2)

1. show.php interprets user input from the GET url to display a post based upon a given ID. I can exploit this to force it to display a list of usernames and passwords from the database using this string:

 ' UNION DISTINCT SELECT null, pass as username, username, null, null FROM users WHERE 1=1 OR username='
