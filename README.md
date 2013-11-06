JSON2SQL
========

A PHP class convert JSON to SQLite OR SQLite to JSON


The aim of this class is to use easy way convert from JSON to SQLite OR SQLite to JSON

[Usage]

<?php

// 1. Include class file


require "JSON2SQL.php";

// 2. Instance the class with database file name and table name
//    you can select other table using selectTable() function  


$test = new JSON2SQL("test.db", "person");

// 3. Open the debug mode if you need debug it


$test->debugMode(true);

// 4. OK, Let's create a new table in this database
//    the JSON key is the column name
// 	  the JSON value is the type of column
//	  you can use the JSON Object or JSON array 


$test->createTable('{"name":"text"}'); // for JSON Object


$test->createTable('[{"name":"text"},{"age":"integer"}]'); 

// for JSON array, I use this way to show you how to using, so this table columns contain 2 cloumn (name:text, age:integer)

// 5. Next, I will add a new record to the table
// As you see, you can add only one record with JSON Object, and you can add more record using JSON Array



$test->add('{"name":"aokihu","age":28}');
$test->add('[{"name":"bake","age":27},{"name":"cake","age":26}]');


// 6. Next, let me modify the record which the age is 27
// 
// First we use find() function to find the records we need
// the find() function param is SQL "WHERE ...", so you can using any SQL to find the record
// and will return all columns content
// 
// Second we use update function() to modify the record content, just pass it JSON Object
// 
// Third print the result, because the result was save in the instance varible $this->result, so we must clear the result set first, then find the result again. Otherwise you should never see the corect result



$test->find("age = 27")->update('{"name":"dog"}');



echo $test->clearResult()->find("age = 27")->toJSON();

// 7. Last, delete a record


$test->clearResult()->find("age = 26")->delete();

