<?php

/**
 * @file
 * Setup variables for site install.
 */

class SetupVarsProfileTask extends ProfileTask {
  /**
   * @var array
   * Variables array
   */
  protected $variables;

  /**
   * Setup variables.
   */
  public function settings() {
    $this->core_settings();
    $this->contrib_settings();
    $this->pathauto_settings();
    $this->cache_settings();
  }

  /**
   * Setup core module variables.
   */
  protected function core_settings() {
    $this->variables['menu_secondary_links_source'] = 'header-links';

    // Locale settings.
    $this->variables['site_default_country'] = 'US';
    $this->variables['date_first_day'] = 1;

    // 404 settings.
    $this->variables['site_404'] = 'baseprofile_404';

    // Date & Time settings.
    $this->variables['date_default_timezone_name'] = 'America/Los_Angeles';
    $this->variables['configurable_timezones'] = 0;

    // Error reporting:
    //  0 - Write errors to the log.
    //  1 - Write errors to the log and to the screen.
    $this->variables['error_level'] = 0;

    // Enable access logs.
    $this->variables['statistics_enable_access_log'] = TRUE;
  }

  /**
   * Setup user variables.
   */
  protected function user_settings() {
    // Set default contact status (gets added to the users data field)
    $this->variables['contact_default_status'] = 0;

    // Registration settings:
    //  0 - Only site admins can create new accounts.
    //  1 - Visitors can create accounts without admin approval.
    //  2 - Visitors can create accounts with admin approval.
    $this->variables['user_register'] = 0;

    // Require email varification when a visitor creates an account
    $this->variables['user_email_verification'] = 0;
  }

  /**
   * Setup non-core module variables.
   */
  protected function contrib_settings() {
    // OmbuSEO settings.
    $this->variables['ombuseo_node_page'] = 1;

    // oEmbed / media settings.
    $this->variables['default_oembedcore_provider'] = array(
      'youtube' => FALSE,
      'vimeo' => FALSE,
      'flickr' => FALSE,
      'qik' => FALSE,
      'revision3' => FALSE,
      'twitter' => FALSE,
    );
  }

  /**
   * Setup caching variables.
   */
  protected function cache_settings() {
    // Only setup caching if the environment isn't development.
    if (variable_get('environment', 'development') == 'development') {
      return;
    }

    // Caching mode:
    //  0 - Disabled
    //  1 - Normal
    //  2 - Aggressive
    $this->variables['cache'] = "1";

    // Minimum cache lifetime (seconds):
    //  0 - Disabled
    //  1 - Normal
    //  2 - Aggressive
    $this->variables['cache_lifetime'] = "60";

    // Page Compression (enabled/disabled.
    $this->variables['page_compression'] = '1';

    // Block Cache (enabled/disabled.
    $this->variables['block_cache'] = "1";

    // Aggregate CSS (enabled/disabled
    $this->variables['preprocess_css'] = "1";

    // Aggregate JS (enabled/disabled
    $this->variables['preprocess_js'] = "1";
  }

  /**
   * Setup pathauto variables.
   */
  protected function pathauto_settings() {
    // Update Action
    //  0 - Do nothing. Leave the old alias intact.
    //  1 - Create a new alias. Leave the existing alias functioning.
    //  2 - Create a new alias. Delete the old alias.
    $this->variables['pathauto_update_action'] = '2';

    // Reduce strings to letters and numbers from ASCII-96.
    $this->variables['pathauto_reduce_ascii'] = TRUE;
    $this->variables['pathauto_node_applytofeeds'] = '';

    // Default node pattern.
    $this->variables['pathauto_node_pattern'] = 'content/[node:title]';
  }

  /**
   * Save all the variables.
   */
  public function process() {
    foreach ($this->variables as $key => $value) {
      variable_set($key, $value);
    }
  }
}
