<?php
namespace SciActive;

/**
 * HookPHP
 *
 * An object method hooking system.
 *
 * Hooks are used to call a callback when a method is called and optionally
 * manipulate the arguments/function call/return value.
 *
 * @version 2.0.2
 * @license https://www.gnu.org/licenses/lgpl.html
 * @author Hunter Perrin <hperrin@gmail.com>
 * @copyright SciActive.com
 * @link http://requirephp.org
 */

if (!class_exists('\SciActive\HookOverride')) {
  include_once(__DIR__.DIRECTORY_SEPARATOR.'HookOverride.php');
}

class Hook {
  /**
   * An array of the callbacks for each hook.
   * @var array
   */
  protected static $hooks = array();
  /**
   * A copy of the HookOverride_extend file.
   * @var string
   */
  private static $hookFile;

  /**
   * Add a callback.
   *
   * A callback is called either before a method runs or after. The callback
   * is passed an array of arguments or return value which it can freely
   * manipulate. If the callback runs before the method and sets the arguments
   * array to false (or causes an error), the method will not be run.
   * Callbacks before a method are passed the arguments given when the method
   * was called, while callbacks after a method are passed the return value
   * (in an array) of that method.
   *
   * The callback can receive up to 5 arguments, in this order:
   *
   * - &$arguments - An array of either arguments or a return value.
   * - $name - The name of the hook.
   * - &$object - The object on which the hook caught a method call.
   * - &$function - A callback for the method call which was caught. Altering
   *   this will cause a different function/method to run.
   * - &$data - An array in which callbacks can store data to communicate with
   *   later callbacks.
   *
   * A hook is the name of whatever method it should catch. A hook can also
   * have an arbitrary name, but be wary that it may already exist and it may
   * result in your callback being falsely called. In order to reduce the
   * chance of this, always use a plus sign (+) and your component's name to
   * begin arbitrary hook names. E.g. "+com_games_player_bonus".
   *
   * If the hook is called explicitly, callbacks defined to run before the
   * hook will run immediately followed by callbacks defined to run after.
   *
   * A negative $order value means the callback will be run before the method,
   * while a positive value means it will be run after. The smaller the order
   * number, the sooner the callback will be run. You can think of the order
   * value as a timeline of callbacks, zero (0) being the actual method being
   * hooked.
   *
   * Additional identical callbacks can be added in order to have a callback
   * called multiple times for one hook.
   *
   * The hook "all" is a pseudo hook which will run regardless of what was
   * actually caught. Callbacks attached to the "all" hook will run before
   * callbacks attached to the actual hook.
   *
   * Note: Be careful to avoid recursive callbacks, as they may result in an
   * infinite loop. All methods under $_ are automatically hooked.
   *
   * @param string $hook The name of the hook to catch.
   * @param int $order The order can be negative, which will run before the method, or positive, which will run after the method. It cannot be zero.
   * @param callback The callback.
   * @return array An array containing the IDs of the new callback and all matching callbacks.
   * @uses \SciActive\Hook::sortCallbacks() To resort the callback array in the correct order.
   */
  public static function addCallback($hook, $order, $function) {
    $callback = array($order, $function);
    if (!isset(Hook::$hooks[$hook])) {
      Hook::$hooks[$hook] = array();
    }
    Hook::$hooks[$hook][] = $callback;
    uasort(Hook::$hooks[$hook], '\\SciActive\\Hook::sortCallbacks');
    return array_keys(Hook::$hooks[$hook], $callback);
  }

  /**
   * Delete a callback by its ID.
   *
   * @param string $hook The name of the callback's hook.
   * @param int $id The ID of the callback.
   * @return int 1 if the callback was deleted, 2 if it didn't exist.
   */
  public static function delCallbackByID($hook, $id) {
    if (!isset(Hook::$hooks[$hook][$id])) {
      return 2;
    }
    unset(Hook::$hooks[$hook][$id]);
    return 1;
  }

  /**
   * Get the array of callbacks.
   *
   * Callbacks are stored in arrays inside this array. The keys of this array
   * are the name of the hook whose callbacks are contained in its value as an
   * array. Each array contains the values $order, $function, in that order.
   *
   * @return array An array of callbacks.
   */
  public static function getCallbacks() {
    return Hook::$hooks;
  }

  /**
   * Hook an object.
   *
   * This hooks all (public) methods defined in the given object.
   *
   * @param object &$object The object to hook.
   * @param string $prefix The prefix used to call the object's methods. Usually something like "$object->".
   * @param bool $recursive Whether to hook objects recursively.
   * @return bool True on success, false on failure.
   */
  public static function hookObject(&$object, $prefix = '', $recursive = true) {
    if ((object) $object === $object) {
      $isString = false;
    } else {
      $isString = true;
    }

    // Make sure we don't take over the hook object, or we'll end up
    // recursively calling ourself. Some system classes shouldn't be hooked.
    $className = str_replace('\\', '_', $isString ? $object : get_class($object));
    global $_;
    if (isset($_) && in_array($className, array('\SciActive\Hook', 'depend', 'config', 'info'))) {
      return false;
    }

    if ($recursive && !$isString) {
      foreach ($object as $curName => &$curProperty) {
        if ((object) $curProperty === $curProperty) {
          Hook::hookObject($curProperty, $prefix.$curName.'->');
        }
      }
    }

    if (!class_exists("\SciActive\HookOverride_$className")) {
      if ($isString) {
        $reflection = new \ReflectionClass($object);
      } else {
        $reflection = new \ReflectionObject($object);
      }
      $methods = $reflection->getMethods(\ReflectionMethod::IS_PUBLIC);

      $code = '';
      foreach ($methods as &$curMethod) {
        $fname = $curMethod->getName();
        if (in_array($fname, array('__construct', '__destruct', '__get', '__set', '__isset', '__unset', '__toString', '__invoke', '__set_state', '__clone', '__sleep', 'jsonSerialize'))) {
          continue;
        }

        //$fprefix = $curMethod->isFinal() ? 'final ' : '';
        $fprefix = $curMethod->isStatic() ? 'static ' : '';
        $params = $curMethod->getParameters();
        $paramArray = $paramNameArray = array();
        foreach ($params as &$curParam) {
          $paramName = $curParam->getName();
          $paramPrefix = $curParam->isVariadic() ? '...' : '';
          $paramPrefix .= $curParam->isPassedByReference() ? '&' : '';
          if ($curParam->isDefaultValueAvailable()) {
            $paramSuffix = ' = '.var_export($curParam->getDefaultValue(), true);
          } else {
            $paramSuffix = '';
          }
          $paramArray[] = "{$paramPrefix}\${$paramName}{$paramSuffix}";
          $paramNameArray[] = "{$paramPrefix}\${$paramName}";
        }
        unset($curParam);
        $code .= $fprefix."function $fname(".implode(', ', $paramArray).") {\n"
        .(defined('HHVM_VERSION') ?
          (
            // There is bad behavior in HHVM where debug_backtrace
            // won't return arguments, but calling func_get_args
            // somewhere in the function changes that behavior to be
            // consistent with official PHP. However, it also
            // returns arguments by value, instead of by reference.
            // So, we must use a more direct method.
            "  \$_HOOK_arguments = array();\n"
            .(count($paramNameArray) > 0 ?
              "  \$_HOOK_arguments[] = ".implode('; $_HOOK_arguments[] = ', $paramNameArray).";\n" :
              ''
            )
            ."  \$_HOOK_real_arg_count = func_num_args();\n"
            ."  \$_HOOK_arg_count = count(\$_HOOK_arguments);\n"
            ."  if (\$_HOOK_real_arg_count > \$_HOOK_arg_count) {\n"
            ."    for (\$i = \$_HOOK_arg_count; \$i < \$_HOOK_real_arg_count; \$i++)\n"
            ."      \$_HOOK_arguments[] = func_get_arg(\$i);\n"
            ."  }\n"
          ) : (
            // We must use a debug_backtrace, because that's the
            // best way to get all the passed arguments, by
            // reference. 5.4 and up lets us limit it to 1 frame.
            (version_compare(PHP_VERSION, '5.4.0') >= 0 ?
              "  \$_HOOK_arguments = debug_backtrace(false, 1);\n" :
              "  \$_HOOK_arguments = debug_backtrace(false);\n"
            )
            ."  \$_HOOK_arguments = \$_HOOK_arguments[0]['args'];\n"
          )
        )
        ."  \$_HOOK_function = array(\$this->_hookObject, '$fname');\n"
        ."  \$_HOOK_data = array();\n"
        ."  \\SciActive\\Hook::runCallbacks(\$this->_hookPrefix.'$fname', \$_HOOK_arguments, 'before', \$this->_hookObject, \$_HOOK_function, \$_HOOK_data);\n"
        ."  if (\$_HOOK_arguments !== false) {\n"
        ."    \$_HOOK_return = call_user_func_array(\$_HOOK_function, \$_HOOK_arguments);\n"
        ."    if ((object) \$_HOOK_return === \$_HOOK_return && get_class(\$_HOOK_return) === '$className')\n"
        ."      \\SciActive\\Hook::hookObject(\$_HOOK_return, '$prefix', false);\n"
        ."    \$_HOOK_return = array(\$_HOOK_return);\n"
        ."    \\SciActive\\Hook::runCallbacks(\$this->_hookPrefix.'$fname', \$_HOOK_return, 'after', \$this->_hookObject, \$_HOOK_function, \$_HOOK_data);\n"
        ."    if ((array) \$_HOOK_return === \$_HOOK_return)\n"
        ."      return \$_HOOK_return[0];\n"
        ."  }\n"
        ."}\n\n";
      }
      unset($curMethod);
      // Build a HookOverride class.
      $include = str_replace(array('_NAMEHERE_', '//#CODEHERE#', '<?php', '?>'), array($className, $code, '', ''), Hook::$hookFile);
      eval($include);
    }

    eval('$object = new \SciActive\HookOverride_'.$className.' ($object, $prefix);');
    return true;
  }

  /**
   * Run the callbacks for a given hook.
   *
   * Each callback is run and passed the array of arguments, and the name of
   * the given hook. If any callback changes $arguments to FALSE, the
   * following callbacks will not be called, and FALSE will be returned.
   *
   * @param string $name The name of the hook.
   * @param array &$arguments An array of arguments to be passed to the callbacks.
   * @param string $type The type of callbacks to run. 'before', 'after', or 'all'.
   * @param mixed &$object The object on which the hook was called.
   * @param callback &$function The function which is called at "0". You can change this in the "before" callbacks to effectively takeover a function.
   * @param array &$data A data array for callback communication.
   */
  public static function runCallbacks($name, &$arguments = array(), $type = 'all', &$object = null, &$function = null, &$data = array()) {
    if (isset(Hook::$hooks['all'])) {
      foreach (Hook::$hooks['all'] as $curCallback) {
        if (($type == 'all' && $curCallback[0] != 0) || ($type == 'before' && $curCallback[0] < 0) || ($type == 'after' && $curCallback[0] > 0)) {
          call_user_func_array($curCallback[1], array(&$arguments, $name, &$object, &$function, &$data));
          if ($arguments === false) {
            return;
          }
        }
      }
    }
    if (isset(Hook::$hooks[$name])) {
      foreach (Hook::$hooks[$name] as $curCallback) {
        if (($type == 'all' && $curCallback[0] != 0) || ($type == 'before' && $curCallback[0] < 0) || ($type == 'after' && $curCallback[0] > 0)) {
          call_user_func_array($curCallback[1], array(&$arguments, $name, &$object, &$function, &$data));
          if ($arguments === false) {
            return;
          }
        }
      }
    }
  }

  /**
   * Sort function for callback sorting.
   *
   * This assures that callbacks are executed in the correct order. Callback
   * IDs are preserved as long as uasort() is used.
   *
   * @param array $a The first callback in the comparison.
   * @param array $b The second callback in the comparison.
   * @return int 0 for equal, -1 for less than, 1 for greater than.
   * @access private
   */
  private static function sortCallbacks($a, $b) {
    if ($a[0] == $b[0]) {
      return 0;
    }
    return ($a[0] < $b[0]) ? -1 : 1;
  }

  public static function getHookFile() {
    Hook::$hookFile = file_get_contents(__DIR__.DIRECTORY_SEPARATOR.'HookOverride_extend.php');
  }
}

Hook::getHookFile();
