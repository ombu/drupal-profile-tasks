<?php

/**
 * @file
 * Enables default modules for a site install.
 */

namespace OmbuCore\Task;

class Modules extends Task {
  /**
   * @var array
   * Array of modules to enable.
   */
  protected $modules;

  /**
   * Provide a list of default modules most sites need.
   */
  public function settings() {
    $this->modules = $this->loadSettings('modules');
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
