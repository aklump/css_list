<?php
/**
 * @file
 * PHPUnit tests for the CssListTest class
 */
namespace AKlump\CssList;

require_once dirname(__FILE__) . '/../../../vendor/autoload.php';

class CssListTestTest extends \PHPUnit_Framework_TestCase {


  public function testCompoundClasses2() {
    $source = '.layout--group__item.tile--collection';
    
    $classes = CssList::getClasses($source);

    $this->assertContains('.tile--collection', $classes);
    $this->assertContains('.layout--group__item', $classes);
  }

  public function testCompoundClasses() {
    $source = '.some.big.classlist';
    
    $classes = CssList::getClasses($source);

    $this->assertContains('.some', $classes);
    $this->assertContains('.big', $classes);
    $this->assertContains('.classlist', $classes);

    $this->assertSame('.big', reset($classes));
    $this->assertSame('.some', end($classes));
  }
}


