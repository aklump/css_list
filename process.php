<?php
/**
 * @file
 * Processes a file or directory to index the css classes and ids.
 *
 * @defgroup css_list CSS List
 */
use \AKlump\CssList\CssList;
use \AKlump\LoftDataGrids\ExportData;

require_once dirname(__FILE__) . '/vendor/autoload.php';

try {
  $source = array();
  if (!empty($argv[1])) {
    if ($argv[1] === '.' || empty($argv[1])) {
      $source = getcwd();
    }
    elseif (substr($argv[1], 0, 1) === '/') {
      $source = $argv;
    }
    else {
      $source = getcwd() . '/' . $argv[1];
    }
     
    if (is_file($source)) {
      $source = array($source);
    }
    elseif (is_dir($source)) {
      $source = scandir($source);
    }
  }

  // Reduce to .css files only
  foreach ($source as $key => $file) {
    if (substr($file, -4) !== '.css') {
      unset($source[$key]);
    }
  }

  if (empty($source)) {
    throw new \InvalidArgumentException("The first argument must be a .css file or folder with at least one .css file.");
  }

  $results = array();
  $data = new ExportData;
  foreach ($source as $file) {
    $contents = file_get_contents($file);
    list($ids) = CssList::getIds($contents);

    $data->setPage('Ids');
    foreach ($ids as $id) {
      $data->add('id', $id)->next();
    }

    list($classes, $compoundClasses, $smaCss) = CssList::getClasses($contents);

    $classes = array_merge($classes, $compoundClasses);
    CssList::sortClasses($classes);

    $data->setPage('Classes');
    foreach ($classes as $class) {
      $data->add('class', $class)->next();
    }

    // $data->setPage('Compound Classes');
    // foreach ($compoundClasses as $class) {
    //   $data->add('class', $class)->next();
    // }

    // $data->setPage('SmaCSS Categories');
    // foreach ($smaCss as $category => $classList) {
    //   // foreach ($classList as $classes) {
    //     $data->add('category', $category)
    //     ->add('classes', implode(', ', $classList))
    //     ->next();
    //   // }
    // }
  }
    
} catch (Exception $e) {
  print $e->getMessage() . PHP_EOL;
}

$exporter = new \AKlump\LoftDataGrids\ValuesOnlyExporter($data);
print $exporter->export();
