<?php

/**
 * @file
 * Setup content types.
 */

class SetupContentTypesProfileTask extends ProfileTask {
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
    $this->create_page_type = TRUE;
    $this->node_settings['page'] = array(
      'options' => array('status'),
      'comments' => COMMENT_NODE_HIDDEN,
      'submitted' => FALSE,
      'menus' => array('main-menu'),
    );
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

    foreach ($this->node_settings as $type => $settings) {
      foreach ($settings as $key => $value) {
        switch ($key) {
          case 'options':
            variable_set('node_options_' . $type, $value);
            break;

          case 'comments':
            variable_set('comment_' . $type, $value);
            break;

          case 'submitted':
            variable_set('node_submitted_' . $type, $value);
            break;

          case 'menus':
            variable_set('menu_options_' . $type, $value);
            break;
        }
      }
    }
  }
}
