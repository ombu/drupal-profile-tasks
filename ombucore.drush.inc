<?php

/**
 * @file
 * Drush integration for ombucore.
 */

/**
 * Implements hook_drush_command().
 */
function ombucore_drush_command() {
  $items['dump-roles-perms'] = array(
    'description' => dt('Dumps the current user roles and permissions into a file.'),
    'arguments' => array(),
  );
  $items['dump-formats'] = array(
    'description' => dt('Dumps the current filter formats.'),
    'arguments' => array(),
  );
  $items['dump-wysiwyg'] = array(
    'description' => dt('Dumps the current wysiwyg settings.'),
    'arguments' => array(),
  );
  $items['dump-variables'] = array(
    'description' => dt('Dumps variable settings.'),
    'arguments' => array(
      'name' => 'A string to filter the variables by. Variables whose name contains the string will be listed.',
    ),
    'examples' => array(
      'drush dump-variables' => 'Dump all variables and values.',
      'drush dump-variables user' => 'Dump all variables containing the string "user".',
      'drush dump-variables site_mail' => 'Dump the variable with the exact key "site_mail".',
    ),
  );
  return $items;
}

/**
 * Command callback. Dump roles and permissions.
 *
 *   drush dump-roles-perms
 */
function drush_ombucore_dump_roles_perms() {
  $install_profile = variable_get('install_profile', 'ombuprofile');

  $export_file_path = drupal_get_path('profile', $install_profile) . '/config/roles.yml';
  if (file_exists($export_file_path)) {
    if (!drush_confirm(dt("The file $export_file_path already exists.\nWould you like to overwrite it?"))) {
      drush_die('Aborting.');
    }
  }

  $content = array();
  $roles = user_roles();

  foreach ($roles as $rid => $role) {
    $perms = user_role_permissions(array($rid => $role));
    $content[$role] = array_keys($perms[$rid]);
  }

  drush_ombucore_write_yml($content, $export_file_path);
  drush_print('Users and Roles written to: ' . $export_file_path);
}

/**
 * Command callback. Dump input formats.
 *
 *   drush dump-formats
 */
function drush_ombucore_dump_formats() {
  $install_profile = variable_get('install_profile', 'ombuprofile');

  $export_file_path = drupal_get_path('profile', $install_profile) . '/config/formats.yml';
  if (file_exists($export_file_path)) {
    if (!drush_confirm(dt("The file $export_file_path already exists.\nWould you like to overwrite it?"))) {
      drush_die('Aborting.');
    }
  }

  $formats = array();
  foreach (filter_formats() as $format_name => $format) {
    // Get the filters.
    $format->filters = filter_list_format($format_name);

    // Turn the filter objects into arrays.
    foreach ($format->filters as $filter_name => $filter_obj) {
      $format->filters[$filter_name] = (array) $filter_obj;
    }
    $formats[$format_name] = (array) $format;
  }

  drush_ombucore_write_yml($formats, $export_file_path);
  drush_print('Formats written to: ' . $export_file_path);
}

/**
 * Command callback. Dump wysiwyg settings.
 *
 *   drush dump-wysiwyg
 */
function drush_ombucore_dump_wysiwyg() {
  $install_profile = variable_get('install_profile', 'ombuprofile');

  $export_file_path = drupal_get_path('profile', $install_profile) . '/config/wysiwyg.yml';
  if (file_exists($export_file_path)) {
    if (!drush_confirm(dt("The file $export_file_path already exists.\nWould you like to overwrite it?"))) {
      drush_die('Aborting.');
    }
  }

  foreach (wysiwyg_profile_load_all() as $profile_name => $profile) {
    $wysiwyg[$profile_name] = (array) $profile;
  }

  drush_ombucore_write_yml($wysiwyg, $export_file_path);
  drush_print('WYSIWYG settings written to: ' . $export_file_path);
}

/**
 * Command callback. Dump variable settings.
 *
 *   drush dump-settings
 */
function drush_ombucore_dump_variables() {
  $install_profile = variable_get('install_profile', 'ombuprofile');

  $export_file_path = drupal_get_path('profile', $install_profile) . '/config/variables.yml';
  if (file_exists($export_file_path)) {
    if (!drush_confirm(dt("The file $export_file_path already exists.\nWould you like to overwrite it?"))) {
      drush_die('Aborting.');
    }
  }

  // Retrieve variables from drush.
  $variables = call_user_func_array('drush_variable_get', func_get_args());

  // Retrieve variable from current yml file.
  $data = drush_ombucore_read_yml('variables');

  // Override and add new variables.
  $data = $variables + $data;

  drush_ombucore_write_yml($data, $export_file_path);
  drush_print('Variable settings written to: ' . $export_file_path);
}

/**
 * Reads a yml file from disk.
 *
 * @param string $type
 *   The type of yml file to read from (e.g. variables or modules).
 *
 * @return array
 *   Array representation of yml data.
 */
function drush_ombucore_read_yml($type) {
  $array = array();

  ombucore_autoload();

  // Create a dummy Task object. Task expects an array of the install_state,
  // which contains the current install profile, so a dummy install_state array
  // needs to be passed in. This could be better handled by splitting
  // loadSettings out into a separate object.
  $install_state = array(
    'parameters' => array(
      'profile' => variable_get('install_profile', 'ombuprofile'),
    ),
  );
  $task = new OmbuCore\Task\Task($install_state);
  $array = $task->loadSettings($type);

  return $array;
}

/**
 * Writes a yml file to disk.
 *
 * @param array $contents
 *   Data to write to the yml file.
 * @param string $file
 *   Path to the yml file relative to Drupal root.
 */
function drush_ombucore_write_yml($contents, $file) {
  ombucore_autoload();
  $dumper = new Symfony\Component\Yaml\Dumper();

  // Make sure directory exists and is writable.
  file_prepare_directory(dirname($file), FILE_CREATE_DIRECTORY);

  $yaml = $dumper->dump($contents, 5);
  file_put_contents($file, $yaml);
}
