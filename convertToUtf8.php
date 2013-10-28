<?php

/*
* ConvertToUtf8 is for database conversions where content encoding is getting garbled.  You may need to fine tune
* the actual replace commands depending on what encoding issues you are seeing.  This tool loops through the whole 
* database changing the database and all tables to utf-8 and running a bunch of replace queries on every field to 
* fix encoding
*
* Example usage: "php convertToUtf8.php -h=localhost -d=db_name -u=root -p=root"
*/

//get the options to use
$options = getopt("h:d:u:p::");
$db_name = null;
$host = "localhost";
$u_name = null;

if(isset($options['h'])) $host = $options['h'];

if(isset($options['d'])) $db_name = $options['d'];
else echo " You must specify a database ";

if(isset($options['u'])) $u_name = $options['u'];
else echo " You must specify a user ";

//call the main function
if($options['p'] && $db_name && $u_name) convertToUtf8($host, $db_name, $u_name, $options['p']);
else echo " You must specify a password ";

//this function theoretically would be moved into Dinkly core when completed
function convertToUtf8($host, $db_name, $u_name, $password)
{
	echo 'Started ';
	//Connect to database
	$db = new PDO("mysql:host=".$host.";dbname=".$db_name."", $u_name, $password);

	echo '.';
	//convert database to utf8
	$stmt = $db->prepare("ALTER DATABASE `".$db_name."` CHARACTER SET utf8 COLLATE utf8_general_ci;");
	$stmt->execute();

	//get all the tables
	$stmt = $db->prepare("SHOW TABLES");
	$stmt->execute();
	$table_names = $stmt->fetchAll();

	foreach($table_names as $table_array)
	{
		$table_name = $table_array[0];
		echo ".";
		//convert table to utf8
		$stmt = $db->prepare("ALTER TABLE `$table_name` CONVERT TO CHARACTER SET utf8");
		$stmt->execute();

		//get table columns
		$stmt = $db->prepare("SHOW COLUMNS FROM `" . $table_name . "`");
		$stmt->execute();	
		$table_schema = $stmt->fetchAll();

		foreach($table_schema as $row)
		{
			/*
			* Note: PDO wasn't playing nice with these replace commands, so we'll run them from the shell.
			* http://www.i18nqa.com/debug/utf8-debug.html -> UTF-8 Encoding Debugging Chart (add more as needed)
			*/

			echo '.';
			shell_exec('mysql -h "'.$host.'" -u "'.$u_name.'" "-p'.$password.'" -e "UPDATE '.$db_name.'.'.$table_name.' SET \`'.$row['Field'].'\` = REPLACE(\`'.$row['Field'].'\`, \"â€œ\", \"\“\");"');
			shell_exec('mysql -h "'.$host.'" -u "'.$u_name.'" "-p'.$password.'" -e "UPDATE '.$db_name.'.'.$table_name.' SET \`'.$row['Field'].'\` = REPLACE(\`'.$row['Field'].'\`, \"â€\", \"\”\");"');
			shell_exec('mysql -h "'.$host.'" -u "'.$u_name.'" "-p'.$password.'" -e "UPDATE '.$db_name.'.'.$table_name.' SET \`'.$row['Field'].'\` = REPLACE(\`'.$row['Field'].'\`, \"â€™\", \"’\");"');
			shell_exec('mysql -h "'.$host.'" -u "'.$u_name.'" "-p'.$password.'" -e "UPDATE '.$db_name.'.'.$table_name.' SET \`'.$row['Field'].'\` = REPLACE(\`'.$row['Field'].'\`, \"â€˜\", \"‘\");"');
			shell_exec('mysql -h "'.$host.'" -u "'.$u_name.'" "-p'.$password.'" -e "UPDATE '.$db_name.'.'.$table_name.' SET \`'.$row['Field'].'\` = REPLACE(\`'.$row['Field'].'\`, \"â€”\", \"–\");"');
			shell_exec('mysql -h "'.$host.'" -u "'.$u_name.'" "-p'.$password.'" -e "UPDATE '.$db_name.'.'.$table_name.' SET \`'.$row['Field'].'\` = REPLACE(\`'.$row['Field'].'\`, \"â€“\", \"—\");"');
			shell_exec('mysql -h "'.$host.'" -u "'.$u_name.'" "-p'.$password.'" -e "UPDATE '.$db_name.'.'.$table_name.' SET \`'.$row['Field'].'\` = REPLACE(\`'.$row['Field'].'\`, \"â€¢\", \"-\");"');
			shell_exec('mysql -h "'.$host.'" -u "'.$u_name.'" "-p'.$password.'" -e "UPDATE '.$db_name.'.'.$table_name.' SET \`'.$row['Field'].'\` = REPLACE(\`'.$row['Field'].'\`, \"â€¦\", \"…\");"');
			shell_exec('mysql -h "'.$host.'" -u "'.$u_name.'" "-p'.$password.'" -e "UPDATE '.$db_name.'.'.$table_name.' SET \`'.$row['Field'].'\` = REPLACE(\`'.$row['Field'].'\`, \'\"€œ\', \"\“\");"');
			shell_exec('mysql -h "'.$host.'" -u "'.$u_name.'" "-p'.$password.'" -e "UPDATE '.$db_name.'.'.$table_name.' SET \`'.$row['Field'].'\` = REPLACE(\`'.$row['Field'].'\`, \"–€“\", \"—\");"');
			shell_exec('mysql -h "'.$host.'" -u "'.$u_name.'" "-p'.$password.'" -e "UPDATE '.$db_name.'.'.$table_name.' SET \`'.$row['Field'].'\` = REPLACE(\`'.$row['Field'].'\`, \'\"€\', \"\”\");"');
			shell_exec('mysql -h "'.$host.'" -u "'.$u_name.'" "-p'.$password.'" -e "UPDATE '.$db_name.'.'.$table_name.' SET \`'.$row['Field'].'\` = REPLACE(\`'.$row['Field'].'\`, \"\€™\", \"\");"');
			shell_exec('mysql -h "'.$host.'" -u "'.$u_name.'" "-p'.$password.'" -e "UPDATE '.$db_name.'.'.$table_name.' SET \`'.$row['Field'].'\` = REPLACE(\`'.$row['Field'].'\`, \"â\", \"‘\");"');
		}
	}

	echo ' Done!';
	error_log('');
}
