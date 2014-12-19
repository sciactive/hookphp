<?php
/**
 * Dynamic hook_override class.
 *
 * @version 0.0.1alpha
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
class hook_override__NAMEHERE_ extends hook_override {
	/**
	 * Used to store the overridden class.
	 * @var mixed $_hook_object
	 */
	protected $_hook_object = null;
	/**
	 * Used to store the prefix (the object's variable name).
	 * @var string $_hook_prefix
	 */
	protected $_hook_prefix = '';

	public function _hook_object() {
		return $this->_hook_object;
	}

	public function __construct(&$object, $prefix = '') {
		$this->_hook_object = $object;
		$this->_hook_prefix = $prefix;
	}

	public function &__get($name) {
		return $val =& $this->_hook_object->$name;
	}

	public function __set($name, $value) {
		return $this->_hook_object->$name = $value;
	}

	public function __isset($name) {
		return isset($this->_hook_object->$name);
	}

	public function __unset($name) {
		unset($this->_hook_object->$name);
	}

	public function __toString() {
		return (string) $this->_hook_object;
	}

	public function __invoke() {
		if (method_exists($this->_hook_object, '__invoke')) {
			$args = func_get_args();
			return call_user_func_array(array($this->_hook_object, '__invoke'), $args);
		}
	}

	public function __set_state() {
		if (method_exists($this->_hook_object, '__set_state')) {
			$args = func_get_args();
			return call_user_func_array(array($this->_hook_object, '__set_state'), $args);
		}
	}

	public function __clone() {
		// TODO: Test this. Make sure cloning works properly.
		$newObject = clone $this->_hook_object;
		Hook::hookObject($newObject, get_class($newObject).'->', false);
		return $newObject;
	}

	public function jsonSerialize() {
		if (method_exists($this->_hook_object, 'jsonSerialize')) {
			$args = func_get_args();
			return call_user_func_array(array($this->_hook_object, 'jsonSerialize'), $args);
		} else {
			return json_decode(json_encode($this->_hook_object), true);
		}
	}

//#CODEHERE#
}