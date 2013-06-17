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
          throw new TaskException('Missing module: ' . $module);
        }
      }
    }

    // Disable update module. The Drupal installer will run cron after
    // installation is complete, which will in turn run update_cron(), which
    // sends a mail to the admin with available updates. This breaks deployments
    // on servers with sendmail disabled, and we don't want to expose update
    // warnings to client sites anyway.
    module_disable(array('update'));
  }
}
