<?php

/**
 * @file
 * Post setup tasks.
 *
 * Any tasks that require all other settings and content to be defined.
 */

namespace OmbuCore\Task;

class PostSetup extends Task {
  /**
   * Implements parent::process().
   */
  public function process() {
    // Set proper workbench access control schemas.
    if (module_exists('workbench_access')) {
      $active = workbench_access_get_active_tree();
      foreach ($active['tree'] as $item) {
        $data = array_merge($active['access_scheme'], $item);
        workbench_access_section_save($data);
      }
    }
  }
}
