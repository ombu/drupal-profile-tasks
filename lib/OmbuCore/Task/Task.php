<?php

/**
 * @file
 * Base class for profile tasks.
 */

namespace OmbuCore\Task;

use Symfony\Component\Yaml\Parser;
use Symfony\Component\Yaml\Exception\ParseException;

class Task implements TaskInterface {
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
   * determine file names.
   *
   * @param string $base_name
   *   The name of setting to load. E.g. if $base_name is 'role', then the
   *   role.yml file will be loaded and parsed. If the active profile has a
   *   role.yml file, that will be used instead.
   *
   * @return array
   *   The final settings for given $base_name.
   */
  protected function loadSettings($base_name) {
    // Check if current active profile has a file.
    $config_file = drupal_get_path('profile', $this->profile) . '/config/' . $base_name . '.yml';
    if (!file_exists($config_file)) {
      // Otherwise load up the default settings.
      $config_file = drupal_get_path('module', 'baseprofile') . '/config/' . $base_name . '.yml';

      if (!file_exists($config_file)) {
        // There's no settings file, return empty array.
        return array();
      }
    }

    try {
      $parser = new Parser();
      $settings = $parser->parse(file_get_contents($config_file));
      return $settings;
    }
    catch (ParseException $e) {
      throw new ProfileTaskException(st('Unable to parse YAML string for !file: !error', array(
        '!file' => $base_name . '.yml',
        '!error' => $e->getMessage(),
      )));
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
    $node = new \stdClass();
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
