<?php

namespace DrupalCodeBuilder\Test\Integration;

use Drupal\KernelTests\KernelTestBase;

/**
 * Integration tests test aspects that need a working Drupal site.
 *
 * These need to be run from Drupal's PHPUnit, rather than ours:
 * @code
 *  [drupal]/core $ ../vendor/bin/phpunit [DCB path]/Integration/CollectPluginInfoTest.php
 * @endcode
 *
 * @todo move these under /Test once the unit tests are moved into a subfolder.
 */
class CollectPluginInfoTest extends KernelTestBase {

  /**
   * The modules to enable.
   *
   * @var array
   */
  public static $modules = [];

  /**
   * {@inheritdoc}
   */
  protected function setUp() {
    // Drupal doesn't know about DCB, so won't have it in its autoloader, so
    // rely on the Factory file's autoloader.
    $dcb_root = dirname(dirname(__DIR__));
    require_once("$dcb_root/Factory.php");

    \DrupalCodeBuilder\Factory::setEnvironmentLocalClass('DrupalLibrary')
      ->setCoreVersionNumber(\Drupal::VERSION);

    parent::setUp();
  }

  /**
   * Tests collection of plugin type info
   */
  public function testPluginTypesInfoCollection() {
    $task_handler_collect = \DrupalCodeBuilder\Factory::getTask('Collect');

    // Hack the task handler so we can call the processing method with a subset
    // of plugin manager service IDs.
    $class = new \ReflectionObject($task_handler_collect);
    $method = $class->getMethod('gatherPluginTypeInfo');
    $method->setAccessible(TRUE);

    $test_plugin_types = [
      'plugin.manager.queue_worker',
      'plugin.manager.field.field_type',
    ];

    $plugin_types_info = $method->invoke($task_handler_collect, $test_plugin_types);

    $this->assertCount(2, $plugin_types_info);
    $this->assertArrayHasKey('queue_worker', $plugin_types_info, "The plugin types list has the queue_worker plugin type.");
    $this->assertArrayHasKey('field.field_type', $plugin_types_info, "The plugin types list has the field.field_type plugin type.");

    // Check the info for the queue worker plugin type.
    $queue_worker_type_info = $plugin_types_info['queue_worker'];
    $this->assertEquals('queue_worker', $queue_worker_type_info['type_id']);
    $this->assertEquals('plugin.manager.queue_worker', $queue_worker_type_info['service_id']);
    $this->assertEquals('Plugin/QueueWorker', $queue_worker_type_info['subdir']);
    $this->assertEquals('Drupal\Core\Queue\QueueWorkerInterface', $queue_worker_type_info['plugin_interface']);
    $this->assertEquals('Drupal\Core\Annotation\QueueWorker', $queue_worker_type_info['plugin_definition_annotation_name']);

    $this->assertArrayHasKey('plugin_interface_methods', $queue_worker_type_info);
    $plugin_interface_methods = $queue_worker_type_info['plugin_interface_methods'];
    $this->assertCount(3, $plugin_interface_methods);
    $this->assertArrayHasKey('processItem', $plugin_interface_methods);
    $this->assertArrayHasKey('getPluginId', $plugin_interface_methods);
    $this->assertArrayHasKey('getPluginDefinition', $plugin_interface_methods);

    $this->assertArrayHasKey('plugin_properties', $queue_worker_type_info);
    $plugin_properties = $queue_worker_type_info['plugin_properties'];
    $this->assertCount(3, $plugin_properties);
    $this->assertArrayHasKey('id', $plugin_properties);
    $this->assertArrayHasKey('title', $plugin_properties);
    $this->assertArrayHasKey('cron', $plugin_properties);

    // Check the info for the field type plugin type.
    $field_type_info = $plugin_types_info['field.field_type'];
    $this->assertEquals('field.field_type', $field_type_info['type_id']);
    $this->assertEquals('plugin.manager.field.field_type', $field_type_info['service_id']);
    $this->assertEquals('Plugin/Field/FieldType', $field_type_info['subdir']);
    $this->assertEquals('Drupal\Core\Field\FieldItemInterface', $field_type_info['plugin_interface']);
    $this->assertEquals('Drupal\Core\Field\Annotation\FieldType', $field_type_info['plugin_definition_annotation_name']);

    $this->assertArrayHasKey('plugin_interface_methods', $field_type_info);
    $plugin_interface_methods = $field_type_info['plugin_interface_methods'];
    //$this->assertCount(3, $plugin_interface_methods);
    $this->assertArrayHasKey('propertyDefinitions', $plugin_interface_methods);
    $this->assertArrayHasKey('mainPropertyName', $plugin_interface_methods);
    $this->assertArrayHasKey('schema', $plugin_interface_methods);
    // ... TODO loads more!

    $this->assertArrayHasKey('plugin_properties', $field_type_info);
    $plugin_properties = $field_type_info['plugin_properties'];
    //$this->assertCount(3, $plugin_properties);
    $this->assertArrayHasKey('id', $plugin_properties);
    $this->assertArrayHasKey('module', $plugin_properties);
    $this->assertArrayHasKey('label', $plugin_properties);
    $this->assertArrayHasKey('description', $plugin_properties);
    $this->assertArrayHasKey('category', $plugin_properties);
    $this->assertArrayHasKey('default_widget', $plugin_properties);
    $this->assertArrayHasKey('default_formatter', $plugin_properties);
    // ... TODO loads more!
  }

}