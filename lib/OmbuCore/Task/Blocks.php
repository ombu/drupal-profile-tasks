<?php
/**
 * @file
 * Setup site blocks.
 */

namespace OmbuCore\Task;

class Blocks extends Task {
  /**
   * @param array
   * Blocks for the default theme
   */
  protected $default_blocks;

  /**
   * @param array
   * Blocks for the admin theme
   */
  protected $admin_blocks;

  /**
   * Assign some default blocks.
   */
  public function settings() {
    $settings = $this->loadSettings('blocks');
    $this->default_blocks = $settings['default_blocks'];
    $this->admin_blocks = $settings['admin_blocks'];
  }

  /**
   * Insert/update block locations.
   */
  public function process() {
    // Since this task runs after modules have been enabled, all blocks will be
    // setup to use the default theme.  So blocks in the default theme need to
    // be updated, while blocks for the admin theme need to be inserted.
    $default_theme = variable_get('theme_default', OMBUBASE_DEFAULT_THEME);
    foreach ($this->default_blocks as $record) {
      // Set some sane defaults.
      $record += array(
        'theme' => $default_theme,
        'status' => 1,
        'weight' => 0,
        'pages' => '',
        'cache' => -1,
      );

      $query = db_update('block');
      $query->fields($record);
      $query->condition('module', $record['module']);
      $query->condition('delta', $record['delta']);
      $query->execute();
    }

    $admin_theme = variable_get('admin_theme', 'seven');
    $query = db_insert('block')->fields(array('module', 'delta', 'theme', 'status', 'weight', 'region', 'pages', 'cache'));
    foreach ($this->admin_blocks as $record) {
      // Set some sane defaults.
      $record += array(
        'theme' => $admin_theme,
        'status' => 1,
        'weight' => 0,
        'pages' => '',
        'cache' => -1,
      );
      $query->values($record);
    }
    $query->execute();
  }
}
