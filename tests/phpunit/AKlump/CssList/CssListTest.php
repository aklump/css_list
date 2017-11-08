<?php
/**
 * @file
 * PHPUnit tests for the CssListTest class
 */

namespace AKlump\CssList;

class CssListTest extends \PHPUnit_Framework_TestCase {

  public function testCompressedSortReturnsCorrectClasses() {
    $subject = '.menu--header.theme--dark .menu li{border-color:#000}.menu--header.theme--dark .menu>ul>li>ul>li{background-color:#2f2f2f;border-color:#090909}.menu--header.theme--dark .menu--header__close{color:#222;color:#fff;background-color:#D2222A}.menu--header.theme--dark .menu--header__toggle,.menu--header.theme--dark .menu--header__close{color:#fff;background-color:#222}.menu--header.theme--dark+.menu--header__close{color:#D2222A;background-color:#222}img{border:none}';
    list($classes) = CssList::getClasses($subject);
    $control = array(
      '.menu--header',
      '.menu',
      '.menu--header__close',
      '.menu--header__toggle',
      '.theme--dark',
    );
    $this->assertSame($control, $classes);
  }

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
      array('slice', array('slice', null, null)),
      array('slice__title', array('slice', null, 'title')),
      array('slice--big__title', array('slice', 'big', 'title')),
    );
  }

  /**
   * @dataProvider testClassNameProvider
   */
  public function testClassName($class, $parsed) {
    $this->assertSame($parsed, CssList::parseClassName($class));
  }

  /**
   * Provides data for testSort.
   */
  function DataForTestSortProvider() {
    $tests = array();
    $tests[] = array(
      explode(PHP_EOL,
        '.button
.button.layout--center
.button.theme--admin
.button.theme--black
.button.theme--caps
.button.theme--disabled
.button.theme--green
.button.theme--grey
.button.theme--larger
.button.theme--larger.theme--sharp
.button.theme--larger.theme--sharp-left
.button.theme--link
.button.theme--link.theme--disabled
.button.theme--sharp
.button.theme--sharp-left
.button-link'
      ),
      explode(PHP_EOL, '.button
.button.layout--center
.button.theme--admin
.button.theme--black
.button.theme--caps
.button.theme--disabled
.button.theme--green
.button.theme--grey
.button.theme--larger
.button.theme--link
.button.theme--sharp
.button.theme--sharp-left
.button.theme--larger.theme--sharp
.button.theme--larger.theme--sharp-left
.button.theme--link.theme--disabled
.button-link'),
    );

    $tests[] = array(
      array(
        '.slice.theme--white',
        '.slice',
        '.tile',
        '.slice__subtitle',
        '.som-tile__titling',
        '.som-tile.theme--inverted',
        '.som-tile__goto',
      ),
      array(
        '.slice',
        '.slice__subtitle',
        '.slice.theme--white',
        '.som-tile__goto',
        '.som-tile__titling',
        '.som-tile.theme--inverted',
        '.tile',
      ),
    );

    return $tests;
  }

  /**
   * @dataProvider DataForTestSortProvider
   */
  public function testSort($source, $control) {
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
      array(
        '.search-overlay__first',
        array('module' => array('.search-overlay__first')),
      ),
      array('.slide--big', array('submodule' => array('.slide--big'))),
      array(
        '.slide--big__title',
        array('submodule' => array('.slide--big__title')),
      ),
      array(
        '.search-overlay--big',
        array('submodule' => array('.search-overlay--big')),
      ),
      array(
        '.search-overlay--big__title',
        array('submodule' => array('.search-overlay--big__title')),
      ),

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


