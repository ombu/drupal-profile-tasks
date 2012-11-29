<?php

/**
 * @file
 * Sets up default theme settings.
 */

class ThemeProfileTask extends ProfileTask {
  /**
   * @var string
   * Default theme.
   */
  protected $default_theme;

  /**
   * @var string
   * Admin theme.
   */
  protected $admin_theme;

  /**
   * @var boolean
   * Use admin theme on node edit forms.
   */
  protected $node_admin_theme;

  /**
   * Setup theme defaults.
   */
  public function settings() {
    $this->default_theme = OMBUBASE_DEFAULT_THEME;
    $this->admin_theme = 'ombuadmin';
    $this->node_admin_theme = TRUE;
  }

  /**
   * Setup default and admin themes.
   */
  public function process() {
    // Enable the default theme.
    db_update('system')
      ->fields(array('status' => 1))
      ->condition('type', 'theme')
      ->condition('name', $this->default_theme)
      ->execute();
    variable_set('theme_default', $this->default_theme);

    // Enable the admin theme.
    db_update('system')
      ->fields(array('status' => 1))
      ->condition('type', 'theme')
      ->condition('name', $this->admin_theme)
      ->execute();
    variable_set('admin_theme', $this->admin_theme);

    // Set the admin theme for the node edit screen.
    variable_set('node_admin_theme', $this->node_admin_theme ? 1 : 0);
  }
}
