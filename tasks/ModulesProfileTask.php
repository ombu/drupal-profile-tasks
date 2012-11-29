<?php

/**
 * @file
 * Enables default modules for a site install.
 */

class ModulesProfileTask extends ProfileTask {
  /**
   * @var array
   * Array of modules to enable.
   */
  protected $modules;

  /**
   * Provide a list of default modules most sites need.
   */
  public function settings() {
    // Core dependencies.
    $this->modules[] = 'block';
    $this->modules[] = 'contextual';
    $this->modules[] = 'image';
    $this->modules[] = 'list';
    $this->modules[] = 'menu';
    $this->modules[] = 'number';
    $this->modules[] = 'options';
    $this->modules[] = 'path';
    $this->modules[] = 'taxonomy';
    $this->modules[] = 'dblog';
    $this->modules[] = 'search';
    $this->modules[] = 'overlay';
    $this->modules[] = 'field_ui';
    $this->modules[] = 'file';
    $this->modules[] = 'rdf';
    $this->modules[] = 'php';
    $this->modules[] = 'statistics';

    // Contrib dependencies.
    $this->modules[] = 'views';
    $this->modules[] = 'views_ui';
    $this->modules[] = 'views_bulk_operations';
    $this->modules[] = 'wysiwyg';
    $this->modules[] = 'pathauto';
    $this->modules[] = 'context';
    $this->modules[] = 'media';
    $this->modules[] = 'oembedcore';
    $this->modules[] = 'media_internet';
    $this->modules[] = 'media_oembed';

    // Custom dependencies.
    $this->modules[] = 'ombucleanup';
    $this->modules[] = 'ombudashboard';
    $this->modules[] = 'ombuseo';
  }

  /**
   * Enable all modules.
   */
  public function process() {
    if (!module_enable($this->modules)) {
      // If module enable fails, that means a module is missing.  Give a useful
      // error message to site installer.
      $module_data = system_rebuild_module_data();
      foreach ($this->modules as $module) {
        if (!isset($module_data[$module])) {
          throw new ProfileTaskException('Missing module: ' . $module);
        }
      }
    }
  }
}
