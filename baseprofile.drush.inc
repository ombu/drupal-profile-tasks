<?php

/**
 * @file
 * Drush integration for baseprofile.
 */

/**
 * Implements hook_drush_command().
 */
function baseprofile_drush_command() {
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
  return $items;
}

/**
 * Command callback. Dump roles and permissions.
 *
 *   drush dump-roles-perms
 */
function drush_baseprofile_dump_roles_perms() {
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

  drush_baseprofile_write_yml($content, $export_file_path);
  drush_print('Users and Roles written to: ' . $export_file_path);
}

/**
 * Command callback. Dump input formats.
 *
 *   drush dump-formats
 */
function drush_baseprofile_dump_formats() {
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

  drush_baseprofile_write_yml($formats, $export_file_path);
  drush_print('Formats written to: ' . $export_file_path);
}

/**
 * Command callback. Dump wysiwyg settings.
 *
 *   drush dump-wysiwyg
 */
function drush_baseprofile_dump_wysiwyg() {
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

  drush_baseprofile_write_yml($wysiwyg, $export_file_path);
  drush_print('WYSIWYG settings written to: ' . $export_file_path);
}

/**
 * Writes a yml file to disk.
 */
function drush_baseprofile_write_yml($contents, $file) {
  baseprofile_load_profile_includes();
  $dumper = new Symfony\Component\Yaml\Dumper();

  // Make sure directory exists and is writable.
  file_prepare_directory(dirname($file), FILE_CREATE_DIRECTORY);

  $yaml = $dumper->dump($contents, 5);
  file_put_contents($file, $yaml);
}
