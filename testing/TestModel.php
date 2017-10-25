<?php

class TestModel {
  public $test = 'right';

  public function testFunction($argument) {
    return $argument;
  }

  public function testFunctionFake($argument) {
    return false;
  }

  public function testFunctionVariadic($argument, ...$rest) {
    return [$argument, $rest];
  }

  public function testReferenceFunction(&$data) {
    $data['test'] = 12;
    return $data;
  }
}
