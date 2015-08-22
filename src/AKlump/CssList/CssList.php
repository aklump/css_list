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

  public static function getIds($content) {
    preg_match_all('/\#[a-z][^\s,:]*/im', $content, $matches);
    $ids = $matches[0];
    static::reduceAndSortFiles($ids);
    static::filterOutFiles($ids);
    static::filterOutHex($ids);

    return $ids;
  }

  protected static function _getClasses($content) {
    preg_match_all('/\.[a-z][^\s,:]*/im', $content, $matches);
    
    return $matches[0];    
  }
  
  public static function getClasses($content) {
    $classes = static::_getClasses($content);
    static::reduceAndSortFiles($classes);
    static::filterOutFiles($classes);

    // Now review each line for compound classes and pull
    $removes = array();
    foreach ($classes as $value) {
      preg_match_all('/(\.[^\.]+)/', $value, $matches);
      if (count($matches[0]) > 1) {
        $removes[] = $value;
        $classes = array_merge($classes, $matches[0]);
      }
    }
    // Pull out the removes
    $classes = array_diff($classes, $removes);

    static::reduceAndSortFiles($classes);

    return $classes;
  }

  public static function filterOutHex(&$array) {
    foreach ($array as $key => $value) {
      if (preg_match('/^\#(?:[a-f0-9]{3}|[a-f0-9]{6})/i', $value)) {
        unset($array[$key]);
      }
    }
  }

  public static function filterOutFiles(&$array) {
    foreach ($array as $key => $value) {
      if (preg_match('/\);?$/', $value)) {
        unset($array[$key]);
      }
      if (preg_match('/\/|\.css\.map/', $value)) {
        unset($array[$key]);
      }
    }
  }

  public static function reduceAndSortFiles(&$array) {
    $array = array_unique($array);
    sort($array);
  }
  
  
}
