<?php
require "JSON2SQL.php";

$test = new JSON2SQL("test.db","person");
$test->debugMode(true);

// Unit 1
$test->dropTable()->createTable('[{"name":"text"},{"age":"integer"}]');

// Unit2
$test->add('{"name":"aokihu","age":28}');
$test->add('{"name":"aokihu","age":29}');

// Unit 3
$data = json_encode(array(
	array("name"=>"aokihu","age"=>"28"),
	array("name"=>"bit", "age"=>"19"),
	array("name"=>"cake", "age"=>23)
	)
);

$test->add($data)->add('{"name":"rainbow","age":28}');

// Unit 4
echo $test->find("age = 28")->toJSON();
echo "\n\n";

// Unit 5
echo "Update >>>\n";
echo $test->clearResult()
->find("age = 28")
->update('{"name":"yunyun"}')
->clearResult()
->find("age = 28")
->toJSON();
echo "\n\n";

// Unit 6
// Delete
echo "Delete >>> \n";
$test->clearResult()
->find("ID = 1")
->delete();

?>