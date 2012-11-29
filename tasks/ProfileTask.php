<?php

/**
 * @file
 * Base class for profile tasks.
 */

class ProfileTask implements ProfileTaskInterface {
  /**
   * An array of information about the current install state.
   *
   * @param array
   */
  protected $install_state;

  /**
   * The current profile name.
   */
  protected $profile;

  /**
   * Constructor.
   *
   * Initializes task settings.
   *
   * @param array $install_state
   *   An array of information about the current installation state.
   */
  public function __construct($install_state) {
    $this->install_state = $install_state;

    $this->profile = $install_state['parameters']['profile'];

    $this->settings();
  }

  /**
   * Populates task settings.
   *
   * Each task can implement whatever it wants here.  This allows the profile to
   * override this method to either add or remove settings based on the current
   * site.
   */
  public function settings() {
  }

  /**
   * Processes this task.
   *
   * Any actual processing that needs to take place (e.g. enabling modules,
   * generating menus, etc) happens here.
   *
   * @return boolean
   *   TRUE if all processing is successful, FALSE otherwise.
   */
  public function process() {
  }

  /**
   * Helper Methods.
   */

  /**
   * Load settings from a file.
   *
   * Will load up default settings from baseprofile.module, and will also look
   * in the current active profile for additional settings. Uses base name to
   * determine file names and variable names within file.
   *
   * @param string $base_name
   *   The name of setting to load. E.g. if $base_name is 'role', then the
   *   baseprofile.role.inc file will be loaded (which should contain
   *   a $default_role variable definition). If the active profile has
   *   a $profile_name.role.inc file (which should contain a $role variable
   *   definition, that will also be loaded and merged with the $default_role
   *   variable.
   *
   * @return array
   *   The final settings for given $base_name.
   */
  protected function loadSettings($base_name) {
    // Loads up the default settings.
    $default_file = drupal_get_path('module', 'baseprofile') . '/baseprofile.' . $base_name . '.inc';
    if (file_exists($default_file)) {
      require $default_file;
      $default_base = "default_$base_name";
      $default_settings = $$default_base;

      // Check if current active profile has a file.
      $profile_file = drupal_get_path('profile', $this->profile) . $this->profile . '.' . $base_name . '.inc';
      if (file_exists($profile_file)) {
        // Will load up additional settings.
        require $profile_file;
        $settings = $$base_name;

        return array_merge_recursive($default_settings, $settings);
      }
      else {
        return $default_settings;
      }
    }
    else {
      // @todo: should throw exception.
    }
  }

  /**
   * Setup a new node object.
   *
   * @param string $type
   *   The type of node to create.
   *
   * @return object
   *   A new prepared node object.
   */
  protected function setupNode($type = 'page') {
    $node = new stdClass();
    $node->type = $type;
    node_object_prepare($node);
    $node->language = LANGUAGE_NONE;
    $node->uid = 1;

    return $node;
  }

  /**
   * Lorem ipsum generator.
   */
  protected function lorem() {
    return 'Urna dolor, dolor lectus porttitor cum? Scelerisque scelerisque rhoncus nec. Arcu proin. Nunc elit ultricies et tristique et mauris aliquet dolor ultrices cras eu lorem adipiscing? Sed cras, aenean sit eros a, pulvinar, placerat aenean ultrices nascetur nunc adipiscing porta! Platea velit. Odio augue, tempor cursus? Pellentesque eu, lorem sagittis, ut elementum sit tempor lorem natoque? Facilisis magna rhoncus turpis? Ut scelerisque mid porttitor dignissim. Vel! Massa scelerisque quis ultricies natoque magna, et odio elementum. Risus, urna proin dis parturient! Risus. Nunc vut tempor, arcu, natoque ac cras scelerisque duis. In lundium nunc turpis tempor odio scelerisque tempor, natoque vel, sagittis dignissim, ac odio. Dictumst in vel natoque, eros dictumst tincidunt aliquet? Sit velit, nunc dapibus porttitor vel porta porta.';
  }
}
