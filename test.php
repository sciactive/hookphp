<?php

error_reporting(E_ALL);
require 'hook.php';

echo 'Testing...<br>';

class Test {
	public function testFunction($argument) {
		echo "Output: $argument<br>";
		return 2;
	}
}

$obj = new Test;
Hook::hookObject($obj, '$obj->');
Hook::addCallback('$obj->testFunction', -2, function(&$arguments, $name, &$object, &$function, &$data){
	$arguments[0] = 'Still Failure!';
});
Hook::addCallback('$obj->testFunction', -1, function(&$arguments, $name, &$object, &$function, &$data){
	$arguments[0] = 'Success!';
	$data['test'] = 1;
});
Hook::addCallback('$obj->testFunction', 1, function(&$return, $name, &$object, &$function, &$data){
	if ($data['test'] !== 1)
		echo 'Data check failed.<br>';
	else
		echo 'Data check passed.<br>';
	if ($return[0] !== 2)
		echo 'Return value check failed.<br>';
	else
		echo 'Return value check passed.<br>';
});

$obj->testFunction('Failure!');
