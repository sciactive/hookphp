<?php

class TestModel {
    public $test = 'right';

    public function testFunction($argument) {
        return $argument;
    }

    public function testFunctionFake($argument) {
        return false;
    }
}