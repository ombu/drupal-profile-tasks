<?php
/**
 * @file
 * Setup site menus.
 */

class SetupMenusProfileTask extends ProfileTask {
  /**
   * New menus to create.
   *
   * Available keys that are required to create a new menu:
   *   - menu_name: The machine name of the new menu.
   *   - title: Display title
   *   - description: description of new menu.
   *
   * @var array
   */
  protected $menus = array();

  /**
   * Menu name of main menu.
   *
   * @var string
   */
  protected $main_menu;

  /**
   * Menu name of secondary menu.
   *
   * @var string
   */
  protected $secondary_menu;

  /**
   * Setup default menus.
   */
  public function settings() {
    // Add footer menu.
    $this->menus[] = array(
      'menu_name' => 'footer-menu',
      'title' => st('Footer Menu'),
      'description' => st('The footer menu displays links in the footer of the site.'),
    );

    // Set the main menu as the main menu.
    $this->main_menu = 'main-menu';

    // Set the footer menu as the secondary menu.
    $this->secondary_menu = 'footer-menu';
  }

}

function baseprofile_setup_menus($install_state) {
  foreach ($this->menus as $menu) {
    menu_save($menu);
  }

  // Update the menu router information.
  menu_rebuild();

  variable_set('menu_main_links_source', $this->main_menu);
  variable_set('menu_secondary_links_source', $this->secondary_menu);
}
