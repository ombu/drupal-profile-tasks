<?php

/**
 * Profile installation support functions.
 */

/**
 * Defines a list of tasks for site install.
 */
function baseprofile_site_install_tasks() {
  return array(
    'Modules' => array(
      'display_name' => st('Setup Modules'),
    ),
    'Theme' => array(
      'display_name' => st('Setup Themes'),
    ),
    'ContentTypes' => array(
      'display_name' => st('Setup Content Types'),
    ),
    'Variables' => array(
      'display_name' => st('Setup Site Variables'),
    ),
    'Taxonomy' => array(
      'display_name' => st('Setup Taxonomy'),
    ),
    'Menus' => array(
      'display_name' => st('Setup Menus'),
    ),
    'Blocks' => array(
      'display_name' => st('Setup Blocks'),
    ),
    'InputFormats' => array(
      'display_name' => st('Setup Input Formats'),
    ),
    'Users' => array(
      'display_name' => st('Setup Roles & Users'),
    ),
    'AddContent' => array(
      'display_name' => st('Add Content'),
    ),
    'PostSetup' => array(
      'display_name' => st('Post Setup'),
    ),
  );
}

/**
 * Routes install task to appropriate class.
 *
 * @param string $task
 *   Current task to execute.
 * @param array $info
 *   Info about Current task.
 * @param array $install_state
 *   An array of information about the current installation state.
 *
 * @return boolean
 *   TRUE on success.
 */
function baseprofile_install_task_router($task, $info, $install_state) {
  // Allow active profile to extend default tasks to alter settings and
  // processing by having the same class namespaces by the profile.
  $task_class = $install_state['parameters']['profile'] . '\\Task\\' . $task;
  if (!class_exists($task_class)) {
    $task_class = 'BaseProfile\\Task\\' . $task;
  }

  if (class_exists($task_class)) {
    try {
      $task = new $task_class($install_state);

      // Log to drush.
      if (function_exists('drush_log')) {
        drush_log(st('Executing task: @task', array('@task' => $info['display_name'])), 'ok');
      }

      $task->process();

      return TRUE;
    }
    catch (Exception $e) {
      if (function_exists('drush_log')) {
        drush_log(st('Error executing task @task: @message', array(
          '@task' => $info['display_name'],
          '@message' => $e->getMessage(),
        )), 'error');
      }

      return FALSE;
    }
  }
  else {
    if (function_exists('drush_log')) {
      drush_log(st('Unable to find task @task', array('@task' => $task)), 'error');
    }
    return FALSE;
  }
}
