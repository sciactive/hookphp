<?php
namespace SciActive;

/**
 * Dynamic HookOverride class.
 *
 * @version 2.0.2
 * @license https://www.gnu.org/licenses/lgpl.html
 * @author Hunter Perrin <hperrin@gmail.com>
 * @copyright SciActive.com
 * @link http://requirephp.org
 */

/**
 * An object used to replace another object, so it can be hooked.
 *
 * This class is dynamically edited during the takeover of an object for
 * hooking.
 */
class HookOverride__NAMEHERE_ extends HookOverride implements \JsonSerializable {
  /**
   * Used to store the overridden class.
   * @var mixed $_hookObject
   */
  protected $_hookObject = null;
  /**
   * Used to store the prefix (the object's variable name).
   * @var string $_hookPrefix
   */
  protected $_hookPrefix = '';

  public function _hookObject() {
    return $this->_hookObject;
  }

  public function __construct(&$object, $prefix = '') {
    $this->_hookObject = $object;
    $this->_hookPrefix = $prefix;
  }

  public function &__get($name) {
    $val =& $this->_hookObject->$name;
    return $val;
  }

  public function __set($name, $value) {
    return $this->_hookObject->$name = $value;
  }

  public function __isset($name) {
    return isset($this->_hookObject->$name);
  }

  public function __unset($name) {
    unset($this->_hookObject->$name);
  }

  public function __toString() {
    return (string) $this->_hookObject;
  }

  public function __invoke() {
    if (method_exists($this->_hookObject, '__invoke')) {
      $args = func_get_args();
      return call_user_func_array(array($this->_hookObject, '__invoke'), $args);
    }
  }

  public function __set_state() {
    if (method_exists($this->_hookObject, '__set_state')) {
      $args = func_get_args();
      return call_user_func_array(array($this->_hookObject, '__set_state'), $args);
    }
  }

  public function __clone() {
    // TODO: Test this. Make sure cloning works properly.
    $newObject = clone $this->_hookObject;
    Hook::hookObject($newObject, get_class($newObject).'->', false);
    return $newObject;
  }

  public function jsonSerialize() {
    $_HOOK_arguments = func_get_args();
    $_HOOK_function = array($this->_hookObject, 'jsonSerialize');
    $_HOOK_data = array();
    \SciActive\Hook::runCallbacks($this->_hookPrefix.'jsonSerialize', $_HOOK_arguments, 'before', $this->_hookObject, $_HOOK_function, $_HOOK_data);
    if ($_HOOK_arguments !== false) {
      if (is_callable($this->_hookObject, 'jsonSerialize') && !empty($_HOOK_arguments)) {
        $_HOOK_return = array(call_user_func_array($_HOOK_function, $_HOOK_arguments));
      } else {
        $_HOOK_return = array($this->_hookObject);
      }
      \SciActive\Hook::runCallbacks($this->_hookPrefix.'jsonSerialize', $_HOOK_return, 'after', $this->_hookObject, $_HOOK_function, $_HOOK_data);
      if ((array) $_HOOK_return === $_HOOK_return) {
        return $_HOOK_return[0];
      }
    }
  }

//#CODEHERE#
}
