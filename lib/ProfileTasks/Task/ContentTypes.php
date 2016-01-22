<?php
/**
 * @file
 * Setup content types.
 */

namespace ProfileTasks\Task;

class ContentTypes extends Task {
  /**
   * @var boolean
   * TRUE to create basic page content type.
   */
  protected $create_page_type;

  /**
   * @var array
   * Node type settings. Should be keyed by node type.
   *
   * Valid node settings:
   *   - options (array): default options for new nodes of this type.
   *     Valid options (all boolean):
   *       - status: Published status.
   *       - promote: Promoted to front page.
   *       - sticky: Sticky at top of lists.
   *   - comments (int): How comments should be displayed. Valid options:
   *       - COMMENT_NODE_HIDDEN: Comments for this node are hidden.
   *       - COMMENT_NODE_CLOSED: Comments for this node are closed.
   *       - COMMENT_NODE_OPEN: Comments for this node are open.
   *   - submitted (boolean): Should submitted info be shown for nodes of this
   *     type.
   *   - menus (array): available menus for nodes of this type. Set to empty to
   *     hide menu options from node.
   *
   * An example definition:
   *
   * @code
   * $node_settings['page'] = array(
   *   'options' => array('status'),
   *   'comments' => COMMENT_NODE_HIDDEN,
   *   'submitted' => FALSE,
   *   'menus' => array('main-menu'),
   * );
   * @endcode
   */
  protected $node_settings = array();

  /**
   * Basic setings for page content type.
   */
  public function settings() {
    // Load settings.
    $settings = $this->loadSettings('content_types');

    $this->create_page_type = $settings['create_page_type'];
    $this->node_settings = $settings['node_settings'];
  }

  /**
   * Save page content type and node type settings.
   */
  public function process() {
    // Create Basic Page content type.
    if ($this->create_page_type) {
      // Insert default pre-defined node types into the database. For a complete
      // list of available node type attributes, refer to the node type API
      // documentation at:
      // http://api.drupal.org/api/HEAD/function/hook_node_info.
      $types = array(
        array(
          'type' => 'page',
          'name' => st('Basic page'),
          'base' => 'node_content',
          'description' => st("Use <em>basic pages</em> for your static content, such as an 'About us' page."),
          'custom' => 1,
          'modified' => 1,
          'locked' => 0,
        ),
      );

      foreach ($types as $type) {
        $type = node_type_set_defaults($type);
        node_type_save($type);
        node_add_body_field($type);
      }

      // Insert default pre-defined RDF mapping into the database.
      $rdf_mappings = array(
        array(
          'type' => 'node',
          'bundle' => 'page',
          'mapping' => array(
            'rdftype' => array('foaf:Document'),
          ),
        ),
      );
      foreach ($rdf_mappings as $rdf_mapping) {
        rdf_mapping_save($rdf_mapping);
      }
    }

    // If default settings have been set, use them as the base for all node
    // types.
    if (isset($this->node_settings['defaults'])) {
      $default_settings = $this->node_settings['defaults'];
      unset($this->node_settings['defaults']);
    }

    // Apply settings for each nodes.
    $types = node_type_get_types();
    foreach ($types as $type) {
      $type = $type->type;

      // If there's node specific settings, merge in with default settings.
      $settings = $default_settings;
      if (isset($this->node_settings[$type])) {
        $settings = $this->node_settings[$type] + $default_settings;
      }

      foreach ($settings as $key => $value) {
        switch ($key) {
          case 'options':
            $variable_key = 'node_options_' . $type;
            break;

          case 'comments':
            $variable_key = 'comment_' . $type;
            break;

          case 'submitted':
            $variable_key = 'node_submitted_' . $type;
            break;

          case 'menus':
            $variable_key = 'menu_options_' . $type;
            break;

          case 'base_content_fields':
            if ($value) {
              $this->createBaseContentFields($type);
            }
            break;

          default:
            // Default setting, handle any node type placement by replacing
            // [type] placeholder with node type.
            $variable_key = str_replace('[type]', $type, $key);
            break;
        }

        // Only set variable if it hasn't been set (e.g. in a contrib module
        // that provides it's own content type).
        if (!variable_get($variable_key, FALSE)) {
          variable_set($variable_key, $value);
        }
      }
    }
  }

  /**
   * Sets up base content fields for a content type.
   *
   * Currently the base content fields are:
   *   - Title (included by default with content types).
   *   - Summary
   *   - Banner image
   *   - Thumbnail
   */
  protected function createBaseContentFields($type) {
    // Summary field.
    if (!field_info_field('field_summary')) {
      $base = array(
        'field_name' => 'field_summary',
        'module' => 'text',
        'settings' => array(),
        'type' => 'text_long',
      );
      field_create_field($base);
    }
    if (!field_info_instance('node', 'field_summary', $type)) {
      $instance = array(
        'bundle' => $type,
        'display' => array(
          'default' => array(
            'label' => 'hidden',
            'module' => 'text',
            'settings' => array(),
            'type' => 'text_default',
            'weight' => 2,
          ),
        ),
        'entity_type' => 'node',
        'field_name' => 'field_summary',
        'label' => 'Summary',
        'settings' => array(
          'text_processing' => 0,
          'user_register_form' => FALSE,
        ),
        'widget' => array(
          'weight' => 1,
        ),
      );
      field_create_instance($instance);
    }

    // Banner image.
    if (!field_info_field('field_banner_image')) {
      $base = array(
        'field_name' => 'field_banner_image',
        'module' => 'ombumedia',
        'settings' => array(),
        'type' => 'ombumedia',
      );
      field_create_field($base);
    }
    if (!field_info_instance('node', 'field_banner_image', $type)) {
      $instance = array(
        'bundle' => $type,
        'display' => array(
          'default' => array(
            'label' => 'hidden',
            'module' => 'ombumedia',
            'settings' => array(),
            'type' => 'ombumedia_render',
            'weight' => 0,
          ),
        ),
        'entity_type' => 'node',
        'field_name' => 'field_banner_image',
        'label' => 'Banner image',
        'widget' => array(
          'active' => 1,
          'module' => 'ombumedia',
          'settings' => array(
            'allowed_schemes' => array(
              'public' => 'public',
            ),
            'allowed_types' => array(
              'audio' => 0,
              'document' => 0,
              'image' => 'image',
              'video' => 0,
            ),
            'allowed_view_modes' => array(
              'audio' => array(),
              'document' => array(),
              'image' => array(
                'default' => 'default',
              ),
              'video' => array(),
            ),
          ),
          'type' => 'ombumedia',
          'weight' => 2,
        ),
      );
      field_create_instance($instance);
    }

    // Thumbnail image.
    if (!field_info_field('field_thumbnail_image')) {
      $base = array(
        'field_name' => 'field_thumbnail_image',
        'module' => 'ombumedia',
        'settings' => array(),
        'type' => 'ombumedia',
      );
      field_create_field($base);
    }
    if (!field_info_instance('node', 'field_thumbnail_image', $type)) {
      $instance = array(
        'bundle' => $type,
        'display' => array(
          'default' => array(
            'label' => 'hidden',
            'module' => 'ombumedia',
            'settings' => array(),
            'type' => 'ombumedia_render',
            'weight' => 0,
          ),
        ),
        'entity_type' => 'node',
        'field_name' => 'field_thumbnail_image',
        'label' => 'Thumbnail image',
        'widget' => array(
          'active' => 1,
          'module' => 'ombumedia',
          'settings' => array(
            'allowed_schemes' => array(
              'public' => 'public',
            ),
            'allowed_types' => array(
              'audio' => 0,
              'document' => 0,
              'image' => 'image',
              'video' => 0,
            ),
            'allowed_view_modes' => array(
              'audio' => array(),
              'document' => array(),
              'image' => array(
                'default' => 'default',
              ),
              'video' => array(),
            ),
          ),
          'type' => 'ombumedia',
          'weight' => 3,
        ),
      );
      field_create_instance($instance);
    }

    // Change weight of body field.
    $field = field_info_instance('node', 'body', $type);
    $field['widget']['weight'] = 4;
    field_update_instance($field);

    // Create field group for base fields.
    $field_group = new \stdClass();
    $field_group->disabled = FALSE; /* Edit this to true to make a default field_group disabled initially */
    $field_group->api_version = 1;
    $field_group->identifier = 'group_base|node|' . $type . '|form';
    $field_group->group_name = 'group_base';
    $field_group->entity_type = 'node';
    $field_group->bundle = $type;
    $field_group->mode = 'form';
    $field_group->parent_name = '';
    $field_group->label = 'Basic Info';
    $field_group->weight = '0';
    $field_group->children = array(
      0 => 'title',
      1 => 'field_summary',
      2 => 'field_banner_image',
      3 => 'field_thumbnail_image',
      4 => 'body',
    );
    $field_group->format_type = 'tab';
    $field_group->format_settings = array(
      'formatter' => 'closed',
      'instance_settings' => array(
        'description' => '',
        'classes' => 'group-base field-group-tab',
        'required_fields' => 1,
      ),
    );
    if (!field_group_load_field_group($field_group->group_name, $field_group->entity_type, $field_group->bundle, $field_group->mode)) {
      field_group_group_save($field_group);
    }
  }
}
