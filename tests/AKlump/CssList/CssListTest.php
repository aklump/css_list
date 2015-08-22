<?php
/**
 * @file
 * PHPUnit tests for the CssListTest class
 */
namespace AKlump\CssList;

require_once dirname(__FILE__) . '/../../../vendor/autoload.php';

class CssListTest extends \PHPUnit_Framework_TestCase {

  /**
   * Provides data for testSplitCompoundClasses.
   *
   * @return 
   *   - 0: 
   */
  function testSplitCompoundClassesProvider() {
    return array(
      array('.do', array('.do')),
      array('.do.re', array('.do', '.re')),
      array('.do.re.me', array('.do', '.re', '.me')),
    );
  }
  
  /**
   * @dataProvider testSplitCompoundClassesProvider 
   */
  public function testSplitCompoundClasses($subject, $control) {
    $this->assertSame($control, CssList::splitCompoundClasses($subject));
  }

  /**
   * Provides data for testClassName.
   *
   * @return 
   *   - 0: 
   */
  function testClassNameProvider() {
    return array(
      array('slice', array('slice', NULL, NULL)),
      array('slice__title', array('slice', NULL, 'title')),
      array('slice--big__title', array('slice', 'big', 'title')),
    );
  }
  
  /**
   * @dataProvider testClassNameProvider 
   */
  public function testClassName($class, $parsed) {
    $this->assertSame($parsed, CssList::parseClassName($class));
  }

  public function testSort() {
    $source = array(
      '.slice.theme--white',
      '.slice',
      '.tile',
      '.slice__subtitle',
      '.som-tile__titling',
      '.som-tile.theme--inverted',
      '.som-tile__goto',
    );
    $control = array(
      '.slice',
      '.slice__subtitle',
      '.slice.theme--white',
      '.som-tile__goto',
      '.som-tile__titling',
      '.som-tile.theme--inverted',   
      '.tile',
    );

    CssList::sortClasses($source);
    $this->assertSame($control, $source);
  }
  

  /**
   * Provides data for testSmacss.
   *
   * @return 
   *   - 0: 
   */
  function testSmacssProvider() {
    return array(
      array('.is-hover', array('state' => array('.is-hover'))),
      array('.theme--blue', array('theme' => array('.theme--blue'))),
      array('.layout--left', array('layout' => array('.layout--left'))),
      array('.slide__first', array('module' => array('.slide__first'))),
      array('.search-overlay__first', array('module' => array('.search-overlay__first'))),
      array('.slide--big', array('submodule' => array('.slide--big'))),
      array('.slide--big__title', array('submodule' => array('.slide--big__title'))),
      array('.search-overlay--big', array('submodule' => array('.search-overlay--big'))),
      array('.search-overlay--big__title', array('submodule' => array('.search-overlay--big__title'))),

    );
  }
  
  /**
   * @dataProvider testSmacssProvider 
   */
  // public function testSmacss($search, $find) {
  //   list(, , $result) = CssList::getClasses($search);
  //   foreach ($find as $category => $values) {
  //     $this->assertCount(1, array_intersect($values, $result[$category]));
  //   }
    
  // }
  
  public function testId() {
    $source = '#page-header';
    
    list($ids) = CssList::getIds($source);

    $this->assertContains('#page-header', $ids);
  }

  public function testEndingDot() {
    $source = '.has-layout.';
    
    list($classes) = CssList::getClasses($source);

    $this->assertContains('.has-layout', $classes);
  }

  public function testCompoundClasses2() {
    $source = '.layout--group__item.tile--collection';
    
    list($classes, $compound) = CssList::getClasses($source);

    $this->assertContains('.tile--collection', $classes);
    $this->assertContains('.layout--group__item', $classes);

    $this->assertContains('.layout--group__item.tile--collection', $compound);
  }

  public function testCompoundClasses() {
    $source = '.some.big.classlist';
    
    list($classes) = CssList::getClasses($source);

    $this->assertContains('.some', $classes);
    $this->assertContains('.big', $classes);
    $this->assertContains('.classlist', $classes);

    $this->assertSame('.big', reset($classes));
    $this->assertSame('.some', end($classes));
  }
}


