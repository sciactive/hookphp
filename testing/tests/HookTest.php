<?php
use SciActive\Hook as Hook;

class HookTest extends \PHPUnit\Framework\TestCase {
  public function testHooking() {
    $testModel = new TestModel;
    Hook::hookObject($testModel, 'TestModel->');
    $this->assertInstanceOf('\SciActive\HookOverride_TestModel', $testModel);

    return $testModel;
  }

  /**
   * @depends testHooking
   */
  public function testObjectAccess($testModel) {
    $that = $this;
    $ids = Hook::addCallback('TestModel->testFunction', -2, function (&$arguments, $name, &$object, &$function, &$data) use ($that) {
      $that->assertEquals('right', $object->test);
      $that->assertEquals('TestModel->testFunction', $name);
    });
    $this->assertTrue($testModel->testFunction(true));
    $this->assertEquals(1, Hook::delCallbackByID('TestModel->testFunction', $ids[0]));
  }

  /**
   * @depends testHooking
   */
  public function testPassByReference($testModel) {
    $that = $this;
    $ids = Hook::addCallback('TestModel->testReferenceFunction', -2, function (&$arguments, $name, &$object, &$function, &$data) use ($that) {
      $that->assertEquals('right', $object->test);
      $that->assertEquals('TestModel->testReferenceFunction', $name);
    });
    $arg = ['data' => true];
    $this->assertEquals($testModel->testReferenceFunction($arg), $arg);
    $this->assertTrue($arg['data']);
    $this->assertEquals($arg['test'], 12);
    $this->assertEquals(1, Hook::delCallbackByID('TestModel->testReferenceFunction', $ids[0]));
  }

  /**
   * @depends testHooking
   */
  public function testFunctionOverride($testModel) {
    $ids = Hook::addCallback('TestModel->testFunction', -1, function (&$arguments, $name, &$object, &$function, &$data) {
      $function = array($object, 'testFunctionFake');
    });
    $this->assertFalse($testModel->testFunction(true));
    $this->assertEquals(1, Hook::delCallbackByID('TestModel->testFunction', $ids[0]));
  }

  /**
   * @depends testHooking
   */
  public function testReturnValues($testModel) {
    $that = $this;
    $ids = Hook::addCallback('TestModel->testFunction', 2, function (&$return, $name, &$object, &$function, &$data) use ($that) {
      $that->assertEquals('success', $return[0]);
    });
    $testModel->testFunction('success');
    $this->assertEquals(1, Hook::delCallbackByID('TestModel->testFunction', $ids[0]));
  }

  /**
   * @depends testHooking
   */
  public function testVariadic($testModel) {
    $that = $this;
    $ids = Hook::addCallback('TestModel->testFunctionVariadic', 2, function (&$return, $name, &$object, &$function, &$data) use ($that) {
      $that->assertEquals(['success', [1, 2, 3]], $return[0]);
    });
    $testModel->testFunction('success', 1, 2, 3);
    $this->assertEquals(1, Hook::delCallbackByID('TestModel->testFunctionVariadic', $ids[0]));
  }

  /**
   * @depends testHooking
   */
  public function testArgumentModification($testModel) {
    $ids = Hook::addCallback('TestModel->testFunction', -1, function (&$arguments, $name, &$object, &$function, &$data) {
      $arguments[0] = 'success';
    });
    $this->assertEquals('success', $testModel->testFunction(true));
    $this->assertEquals(1, Hook::delCallbackByID('TestModel->testFunction', $ids[0]));
  }

  /**
   * @depends testHooking
   */
  public function testDataPassing($testModel) {
    $that = $this;
    Hook::addCallback('TestModel->testFunction', -1, function (&$arguments, $name, &$object, &$function, &$data) {
      $data['test'] = 1;
    });
    $ids = Hook::addCallback('TestModel->testFunction', 2, function (&$return, $name, &$object, &$function, &$data) use ($that) {
      $that->assertEquals(1, $data['test']);
    });
    $testModel->testFunction(true);
    $this->assertEquals(1, Hook::delCallbackByID('TestModel->testFunction', $ids[0]));
  }

  /**
   * Do this one last, cause it leaves its callbacks.
   *
   * @depends testHooking
   * @expectedException Exception
   * @expectedExceptionMessage Everything is good.
   */
  public function testTimeline($testModel) {
    $that = $this;
    Hook::addCallback('TestModel->testFunction', -1000, function (&$arguments, $name, &$object, &$function, &$data) {
      $data['timeline'] = 1;
    });
    Hook::addCallback('TestModel->testFunction', -100, function (&$return, $name, &$object, &$function, &$data) use ($that) {
      $that->assertEquals(1, $data['timeline']);
      $data['timeline']++;
    });
    Hook::addCallback('TestModel->testFunction', -90, function (&$return, $name, &$object, &$function, &$data) use ($that) {
      $that->assertEquals(2, $data['timeline']);
      $data['timeline']++;
    });
    Hook::addCallback('TestModel->testFunction', -70, function (&$return, $name, &$object, &$function, &$data) use ($that) {
      $that->assertEquals(3, $data['timeline']);
      $data['timeline']++;
    });
    Hook::addCallback('TestModel->testFunction', -30, function (&$return, $name, &$object, &$function, &$data) use ($that) {
      $that->assertEquals(4, $data['timeline']);
      $data['timeline']++;
    });
    Hook::addCallback('TestModel->testFunction', -10, function (&$return, $name, &$object, &$function, &$data) use ($that) {
      $that->assertEquals(5, $data['timeline']);
      $data['timeline']++;
    });
    Hook::addCallback('TestModel->testFunction', -9, function (&$return, $name, &$object, &$function, &$data) use ($that) {
      $that->assertEquals(6, $data['timeline']);
      $data['timeline']++;
    });
    Hook::addCallback('TestModel->testFunction', -8, function (&$return, $name, &$object, &$function, &$data) use ($that) {
      $that->assertEquals(7, $data['timeline']);
      $data['timeline']++;
    });
    Hook::addCallback('TestModel->testFunction', -7, function (&$return, $name, &$object, &$function, &$data) use ($that) {
      $that->assertEquals(8, $data['timeline']);
      $data['timeline']++;
    });
    Hook::addCallback('TestModel->testFunction', -6, function (&$return, $name, &$object, &$function, &$data) use ($that) {
      $that->assertEquals(9, $data['timeline']);
      $data['timeline']++;
    });
    Hook::addCallback('TestModel->testFunction', -5, function (&$return, $name, &$object, &$function, &$data) use ($that) {
      $that->assertEquals(10, $data['timeline']);
      $data['timeline']++;
    });
    Hook::addCallback('TestModel->testFunction', -4, function (&$return, $name, &$object, &$function, &$data) use ($that) {
      $that->assertEquals(11, $data['timeline']);
      $data['timeline']++;
    });
    Hook::addCallback('TestModel->testFunction', -3, function (&$return, $name, &$object, &$function, &$data) use ($that) {
      $that->assertEquals(12, $data['timeline']);
      $data['timeline']++;
    });
    Hook::addCallback('TestModel->testFunction', -2, function (&$return, $name, &$object, &$function, &$data) use ($that) {
      $that->assertEquals(13, $data['timeline']);
      $data['timeline']++;
    });
    Hook::addCallback('TestModel->testFunction', -1, function (&$return, $name, &$object, &$function, &$data) use ($that) {
      $that->assertEquals(14, $data['timeline']);
      $data['timeline']++;
    });
    Hook::addCallback('TestModel->testFunction', 1, function (&$return, $name, &$object, &$function, &$data) use ($that) {
      $that->assertEquals(15, $data['timeline']);
      $data['timeline']++;
    });
    Hook::addCallback('TestModel->testFunction', 2, function (&$return, $name, &$object, &$function, &$data) use ($that) {
      $that->assertEquals(16, $data['timeline']);
      $data['timeline']++;
    });
    Hook::addCallback('TestModel->testFunction', 3, function (&$return, $name, &$object, &$function, &$data) use ($that) {
      $that->assertEquals(17, $data['timeline']);
      $data['timeline']++;
    });
    Hook::addCallback('TestModel->testFunction', 4, function (&$return, $name, &$object, &$function, &$data) use ($that) {
      $that->assertEquals(18, $data['timeline']);
      $data['timeline']++;
    });
    Hook::addCallback('TestModel->testFunction', 5, function (&$return, $name, &$object, &$function, &$data) use ($that) {
      $that->assertEquals(19, $data['timeline']);
      $data['timeline']++;
    });
    Hook::addCallback('TestModel->testFunction', 6, function (&$return, $name, &$object, &$function, &$data) use ($that) {
      $that->assertEquals(20, $data['timeline']);
      $data['timeline']++;
    });
    Hook::addCallback('TestModel->testFunction', 7, function (&$return, $name, &$object, &$function, &$data) use ($that) {
      $that->assertEquals(21, $data['timeline']);
      $data['timeline']++;
    });
    Hook::addCallback('TestModel->testFunction', 8, function (&$return, $name, &$object, &$function, &$data) use ($that) {
      $that->assertEquals(22, $data['timeline']);
      $data['timeline']++;
    });
    Hook::addCallback('TestModel->testFunction', 9, function (&$return, $name, &$object, &$function, &$data) use ($that) {
      $that->assertEquals(23, $data['timeline']);
      $data['timeline']++;
    });
    Hook::addCallback('TestModel->testFunction', 10, function (&$return, $name, &$object, &$function, &$data) use ($that) {
      $that->assertEquals(24, $data['timeline']);
      $data['timeline']++;
    });
    Hook::addCallback('TestModel->testFunction', 1000, function (&$return, $name, &$object, &$function, &$data) use ($that) {
      $that->assertEquals(25, $data['timeline']);
      // Cool, it all worked.
      throw new Exception('Everything is good.');
    });
    $that->assertEquals(27, count(Hook::getCallbacks()['TestModel->testFunction']));
    $testModel->testFunction(true);
  }

}
