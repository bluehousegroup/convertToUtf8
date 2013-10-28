ConvertToUtf8
-------------

ConvertToUtf8 is for database conversions where content encoding is getting garbled.  You may need to fine tune the actual replace commands depending on what encoding issues you are seeing.  This tool loops through the whole database changing the database and all tables to utf-8 and running a bunch of replace queries on every field to fix encoding

Example usage: "php convertToUtf8.php -h=localhost -d=db_name -u=root -p=root"