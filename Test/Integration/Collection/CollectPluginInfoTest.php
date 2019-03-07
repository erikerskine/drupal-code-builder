<?php

namespace DrupalCodeBuilder\Test\Integration\Collection;

/**
 * Integration tests test aspects that need a working Drupal site.
 *
 * These need to be run from Drupal's PHPUnit, rather than ours:
 * @code
 *  [drupal]/core $ ../vendor/bin/phpunit ../vendor/drupal-code-builder/drupal-code-builder/Test/Integration/Collection/CollectPluginInfoTest.php
 * @endcode
 */
class CollectPluginInfoTest extends CollectTestBase {

  /**
   * The modules to enable.
   *
   * @var array
   */
  public static $modules = [
    // Modules for the help section service.
    'help',
    'tour',
    // Provides a queue worker plugin.
    'aggregator',
  ];

  /**
   * Tests collection of plugin type info
   */
  public function testPluginTypesInfoCollection() {
    $plugin_types_collector = new \DrupalCodeBuilder\Task\Collect\PluginTypesCollector(
      \DrupalCodeBuilder\Factory::getEnvironment(),
      new \DrupalCodeBuilder\Task\Collect\ContainerBuilderGetter,
      new \DrupalCodeBuilder\Task\Collect\MethodCollector,
      new \DrupalCodeBuilder\Task\Collect\CodeAnalyser($this->environment)
    );

    // Hack the task handler so we can call the processing method with a subset
    // of plugin manager service IDs.
    $class = new \ReflectionObject($plugin_types_collector);
    $method = $class->getMethod('gatherPluginTypeInfo');
    $method->setAccessible(TRUE);

    $test_plugin_jobs = [
      // In Core, and other modules provide plugins.
      [
        'service_id' => 'plugin.manager.queue_worker',
        'type_id' => 'queue_worker',
      ],
      // In Core, and our name doesn't match Plugin module's name.
      [
        'service_id' => 'plugin.manager.field.field_type',
        'type_id' => 'field.field_type',
      ],
      // In a module, and other modules provide plugins.
      [
        'service_id' => 'plugin.manager.help_section',
        'type_id' => 'help_section',
      ]
    ];

    $plugin_types_info = $method->invoke($plugin_types_collector, $test_plugin_jobs);

    $this->assertCount(3, $plugin_types_info);
    $this->assertArrayHasKey('queue_worker', $plugin_types_info, "The plugin types list has the queue_worker plugin type.");
    $this->assertArrayHasKey('field.field_type', $plugin_types_info, "The plugin types list has the field.field_type plugin type.");
    $this->assertArrayHasKey('help_section', $plugin_types_info, "The plugin types list has the help_section plugin type.");

    // Check the info for the queue worker plugin type.
    $queue_worker_type_info = $plugin_types_info['queue_worker'];
    $this->assertEquals('queue_worker', $queue_worker_type_info['type_id']);
    $this->assertEquals('queue_worker', $queue_worker_type_info['type_label']);
    $this->assertEquals('plugin.manager.queue_worker', $queue_worker_type_info['service_id']);
    $this->assertEquals('Plugin/QueueWorker', $queue_worker_type_info['subdir']);
    $this->assertEquals('Drupal\Core\Queue\QueueWorkerInterface', $queue_worker_type_info['plugin_interface']);
    $this->assertEquals('Drupal\Core\Annotation\QueueWorker', $queue_worker_type_info['plugin_definition_annotation_name']);
    $this->assertEquals('Drupal\Core\Queue\QueueWorkerBase', $queue_worker_type_info['base_class']);

    $this->assertArrayHasKey('plugin_interface_methods', $queue_worker_type_info);
    $plugin_interface_methods = $queue_worker_type_info['plugin_interface_methods'];
    $this->assertCount(1, $plugin_interface_methods);
    $this->assertArrayHasKey('processItem', $plugin_interface_methods);

    $this->assertArrayHasKey('plugin_properties', $queue_worker_type_info);
    $plugin_properties = $queue_worker_type_info['plugin_properties'];
    $this->assertCount(3, $plugin_properties);
    $this->assertArrayHasKey('id', $plugin_properties);
    $this->assertArrayHasKey('title', $plugin_properties);
    $this->assertArrayHasKey('cron', $plugin_properties);

    // Check the info for the field type plugin type.
    $field_type_info = $plugin_types_info['field.field_type'];
    $this->assertEquals('field.field_type', $field_type_info['type_id']);
    $this->assertEquals('field.field_type', $field_type_info['type_label']);
    $this->assertEquals('plugin.manager.field.field_type', $field_type_info['service_id']);
    $this->assertEquals('Plugin/Field/FieldType', $field_type_info['subdir']);
    $this->assertEquals('Drupal\Core\Field\FieldItemInterface', $field_type_info['plugin_interface']);
    $this->assertEquals('Drupal\Core\Field\Annotation\FieldType', $field_type_info['plugin_definition_annotation_name']);
    $this->assertEquals('Drupal\Core\Field\FieldItemBase', $field_type_info['base_class']);

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

    // Check the info for the help section type plugin type.
    $help_section_type_info = $plugin_types_info['help_section'];
    $this->assertEquals('help_section', $help_section_type_info['type_id']);
    $this->assertEquals('help_section', $help_section_type_info['type_label']);
    $this->assertEquals('plugin.manager.help_section', $help_section_type_info['service_id']);
    $this->assertEquals('Plugin/HelpSection', $help_section_type_info['subdir']);
    $this->assertEquals('Drupal\help\HelpSectionPluginInterface', $help_section_type_info['plugin_interface']);
    $this->assertEquals('Drupal\help\Annotation\HelpSection', $help_section_type_info['plugin_definition_annotation_name']);
    $this->assertEquals('Drupal\help\Plugin\HelpSection\HelpSectionPluginBase', $help_section_type_info['base_class']);
  }

}