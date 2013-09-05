<?php
/**
 * @file
 * Add site specific content.
 *
 * Creates node content structured as hierarchical menu
 */

namespace OmbuCore\Task;

class AddContent extends Task {
  /**
   * @param boolean
   *
   * If TRUE, a new blank homepage page node will be created.
   */
  protected $homepage_node;

  /**
   * @param array
   *
   * Structured array of nodes and/or links to be added to a menu.
   *
   * An example array looks like:
   *
   * @code
   *   $menu_nodes['main-menu'] = array(
   *     'About Us' => array(
   *       '#children' => array(
   *         'Our History' => array(),
   *         'Our Culture' => array(),
   *         'Our Team' => array(
   *           '#children' => array(
   *             'How We Work' => array(),
   *             'Our Departments' => array(
   *               '#link' => 'about-us/our-team/our-departments',
   *             ),
   *             'Work With Us' => array(),
   *           ),
   *         ),
   *       ),
   *     ),
   *   );
   * @endcode
   *
   * Some important notes about the structure of this array:
   *
   *   - The keys of the subarray become the node and/or menu title.
   *   - If a menu has children, the '#children' key can be used to contain an
   *     array of all children menu items.
   *   - If just a menu link should be created, instead of corresponding page
   *     node, add a '#link' key to the array definition for that menu item.
   */
  protected $menu_nodes;

  /**
   * Adds a simple way for adding a structure menu system.
   */
  public function settings() {
    $this->homepage_node = TRUE;
  }

  /**
   * Process creating structure content.
   */
  public function process() {
    if ($this->homepage_node) {
      $this->createHome();
    }

    if ($this->menu_nodes) {
      foreach ($this->menu_nodes as $menu => $nodes) {
        $this->buildMenu($menu, $nodes);
      }
    }
  }

  /**
   * Create homepage node and set it to the front page.
   */
  protected function createHome() {
    $node = $this->setupNode();
    $node->title = 'Home';
    $node->body[$node->language][0]['value'] = $this->lorem();
    node_save($node);
    variable_set('site_frontpage', 'node/' . $node->nid);
  }

  /**
   * Build structured nodes into a menu system.
   */
  protected function buildMenu($menu_name, $nodes, $parent = NULL) {
    static $weight = -50;

    foreach ($nodes as $title => $content) {
      // Check if a defined link exists
      if (isset($content['#link'])) {
        $menu_link = array(
          'menu_name' => $menu_name,
          'weight' => ++$weight,
          'link_title' => $title,
          'link_path' => $content['#link'],
        );
        if ($parent) {
          $menu_link['plid'] = $parent['mlid'];
        }
        menu_link_save($menu_link);
      }
      // Otherwise treat as a regular node with possible children.
      else {
        // Allow node type to be set.
        $type = isset($content['#type']) ? $content['#type'] : 'page';

        // Create a new node.
        $node = $this->setupNode($type);
        $node->title = $title;

        // Add lorem text to body.
        $node->body[$node->language][0]['value'] = $this->lorem();
        $node->body[$node->language][0]['format'] = 'default';

        // Make sure a menu item is created for this node.
        $node->menu = array(
          'menu_name' => $menu_name,
          'enabled' => TRUE,
          'link_title' => $node->title,
          'weight' => ++$weight,
        );

        if ($parent) {
          $node->menu['plid'] = $parent['mlid'];
          $menu_link = $node->menu;
        }

        node_save($node);
      }

      // If there's children, build them too.
      if (!empty($content['#children'])) {
        $this->buildMenu($content['#children'], $menu_name, $menu_link);
      }
    }
  }

  /**
   * Simple alternative method to `lorem()` for obtaining content during a
   * build.
   */
  protected function initialContent($name) {
    return file_get_contents(
      drupal_get_path('profile', $this->profile) . "/initial-content/${name}"
    );
  }

}
