<?php
/**
 * @file
 * Defines the CssList class.
 *
 * @ingroup name
 * @{
 */

namespace AKlump\CssList;

/**
 * Represents a CssList object class.
 *
 * @brief A utility class to analyze css files.
 */
class CssList {

  /**
   * determines the breakpoint of the class or id
   */
  const TOKEN = '[^\s,:{>+]*';

  public static function getIds($content) {
    preg_match_all('/\#[a-z]' . static::TOKEN . '/im', $content, $matches);
    $ids = $matches[0];
    static::reduceAndSortFiles($ids);
    static::filterOutFiles($ids);
    static::filterOutHex($ids);

    return array($ids);
  }

  public static function reduceAndSortFiles(&$array) {
    $array = array_unique($array);
    static::sortClasses($array);
  }

  public static function sortClasses(&$array) {
    uasort($array, function ($a, $b) {
      if ($a === $b) {
        return 0;
      }

      $cl = new CssList;

      $aSplit = $cl->splitCompoundClasses($a);
      $bSplit = $cl->splitCompoundClasses($b);

      $aCount = count($aSplit);
      $bCount = count($bSplit);

      $aParts = $cl->parseClassName($aSplit[0]);
      $bParts = $cl->parseClassName($bSplit[0]);

      $aHas = count(array_filter($aSplit)) > 1;
      $bHas = count(array_filter($bSplit)) > 1;

      // If base is not equal...
      if ($aParts[0] !== $bParts[0]) {
        return $aParts[0] < $bParts[0] ? -1 : 1;
      }

      // At this point the bases are equal.

      if ($aHas || $bHas) {
        if ($aHas xor $bHas) {
          // If only one has a compound then we send that
          return $bHas ? -1 : 1;
        }

        if ($aCount === $bCount) {
          return $a < $b ? -1 : 1;
        }

        return $aCount - $bCount;

      }

      // Compare on subcomponents
      if ($aParts[2] !== $bParts[2]) {
        return $aParts[2] < $bParts[2] ? -1 : 1;
      }

      // Compare if compound or not; compounds go bottom
      if ($aHas xor $bHas) {
        return $a < $b ? -1 : 1;
      }
    });

    $array = array_values($array);
  }

  public static function splitCompoundClasses($string) {
    preg_match_all('/(\.[^\.]+)/', $string, $matches);

    return count($matches[0]) > 1 ? $matches[0] : array($string);
  }

  /**
   * Parses a class into smacss parts.
   *
   * @param  string $class
   *
   * @return array
   *   0: module
   *   1: submodule if any
   *   2: subcomponent if any
   *
   * @code
   *   $class = 'panel--big__title';
   *   list($module, $submodule, $subcomponent) = $cs::parseClassName($class);
   * @endcode
   */
  public static function parseClassName($class) {
    $regex = array();
    $regex[] = '/(.+)\-\-|(.+)__|/';
    $regex[] = '/--(.+)__|--(.+)/';
    $regex[] = '/__(.+)/';

    $return = array(null, null, null);
    for ($i = 0; $i < count($regex); ++$i) {
      preg_match($regex[$i], $class, $matches);
      array_shift($matches);
      if (($matches = array_filter($matches)) && ($matches = reset($matches))) {
        $return[$i] = $matches;
      }
      else if ($i === 0) {
        $return[$i] = $class;
      }
    }

    return $return;
  }

  public static function filterOutFiles(&$array) {
    foreach ($array as $key => $value) {
      if (preg_match('/\);?$/', $value)) {
        unset($array[$key]);
      }
      if (preg_match('/\/|\.css\.map|\.scss/', $value)) {
        unset($array[$key]);
      }
    }
  }

  public static function filterOutHex(&$array) {
    foreach ($array as $key => $value) {
      if (preg_match('/^\#(?:[a-f0-9]{3}|[a-f0-9]{6})/i', $value)) {
        unset($array[$key]);
      }
    }
  }

  /**
   * Return classes and compound classes
   *
   * @param  string $content The css file to analyze.
   *
   * @return array
   *   0 The classes array
   *   1 The compound classes array
   */
  public static function getClasses($content) {
    $classes = static::_getClasses($content);
    static::reduceAndSortFiles($classes);
    static::filterOutFiles($classes);

    // Strip off ending . from classes
    array_walk($classes, function ($value, $key) use (&$classes) {
      $classes[$key] = rtrim($value, '.');
    });

    // Now review each line for compound classes and pull
    $compoundClasses = array();
    foreach ($classes as $value) {
      $compound = static::splitCompoundClasses($value);
      if (count($compound) > 1) {
        $compoundClasses[] = $value;
        $classes = array_merge($classes, $compound);
      }
    }
    // Pull out the removes
    $classes = array_diff($classes, $compoundClasses);

    static::reduceAndSortFiles($classes);

    $smacssCategories = static::splitPerSmacss($classes);

    return array($classes, $compoundClasses, $smacssCategories);
  }

  public static function splitPerSmacss($classesArray) {
    $smacss = array(
      'layout' => array(),
      'module' => array(),
      'submodule' => array(),
      'state' => array(),
      'theme' => array(),
    );

    foreach ($classesArray as $class) {
      if (preg_match('/^\.theme\-\-/i', $class)) {
        $smacss['theme'][] = $class;
      }
      else if (preg_match('/^\.layout\-\-/i', $class)) {
        $smacss['layout'][] = $class;
      }
      else if (preg_match('/^\.is\-/i', $class)) {
        $smacss['state'][] = $class;
      }
      else if (preg_match('/^[^\-]+\_\_/i', $class)) {
        $smacss['module'][] = $class;
      }
      else if (preg_match('/^[^\-]+\-\-/i', $class)) {
        $smacss['submodule'][] = $class;
      }
    }

    return $smacss;
  }

  protected static function _getClasses($content) {
    preg_match_all('/\.[a-z]' . static::TOKEN . '/im', $content, $matches);

    return $matches[0];
  }
}
