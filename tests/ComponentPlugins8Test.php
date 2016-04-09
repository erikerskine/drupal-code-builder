<?php

/**
 * @file
 * Contains ComponentPlugins8Test.
 */

// Can't be bothered to figure out autoloading for tests.
require_once __DIR__ . '/DrupalCodeBuilderTestBase.php';

/**
 * Tests the Plugins generator class.
 *
 * Run with:
 * @code
 *   vendor/phpunit/phpunit/phpunit  tests/ComponentPlugins8Test.php
 * @endcode
 */
class ComponentPlugins8Test extends DrupalCodeBuilderTestBase {

  protected function setUp() {
    $this->setupDrupalCodeBuilder(8);
  }

  /**
   * Test Plugins component.
   */
  function testPluginsGenerationTests() {
    $permission_name = 'my permission name';

    // Create a module.
    $module_name = 'test_module';
    $module_data = array(
      'base' => 'module',
      'root_name' => $module_name,
      'readable_name' => 'Test module',
      'short_description' => 'Test Module description',
      'hooks' => array(
      ),
      'plugins' => array(
        'block',
      ),
      'requested_components' => array(
      ),
      'readme' => FALSE,
    );
    $files = $this->generateModuleFiles($module_data);
    $file_names = array_keys($files);

    $this->assertCount(2, $files, "Expected number of files is returned.");
    $this->assertContains("$module_name.info.yml", $file_names, "The files list has a .info.yml file.");
    $this->assertContains("src/Plugin/Block/TestModuleBlock.php", $file_names, "The files list has a plugin file.");

    // Check the plugin file.
    $plugin_file = $files["src/Plugin/Block/TestModuleBlock.php"];
    $this->assertNoTrailingWhitespace($plugin_file, "The plugin class file contains no trailing whitespace.");
    $this->assertNamespace($plugin_file, ['Drupal', $module_name, 'Plugin', 'Block'], "The plugin class file contains contains the expected namespace.");
    $this->assertClass($plugin_file, 'TestModuleBlock', "The plugin class file contains contains the expected class.");
  }

}