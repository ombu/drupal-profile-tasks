<?php

/**
 * @file
 * Provides a wrapper around entity_metadata_wrapper().
 *
 * I know, I know, a wrapper around a wrapper. But this actually provides some
 * useful abstraction around creating entities during build time.
 */

namespace OmbuCore\Content;

class Wrapper extends \EntityDrupalWrapper {
  /**
   * Array of bean entities that will be associated to this content on save.
   *
   * @param array
   */
  protected $beans;

  /**
   * Construct a new Wrapper object.
   *
   * @param string $type
   *   The type of the passed data.
   * @param array $data
   *   An array of values to set, keyed by property name. If the entity type has
   *   bundles the bundle key has to be specified.
   * @param array $info
   *   Optional. used internally to pass info about properties down the tree.
   */
  public function __construct($type, $data = array(), $info = array()) {
    // Make sure created entity is always assigned to a user.
    $entity = entity_create($type, $data + array('uid' => 1));
    parent::__construct($type, $entity, $info);
    $this->setup();
  }

  /**
   * Create a new bean associated to this content and return wrapper object.
   *
   * @param string $type
   *   The type of bean to create.
   * @param string $region
   *   The region to place bean in. Defaults to 'content'.
   * @param int $width
   *   The width of the bean for tiles. Defaults to 12.
   *
   * @return EntityDrupalWrapper
   *   Wrapper around bean object.
   */
  public function addBean($type, $region = 'content', $width = 12) {
    $bean = entity_create('bean', array('type' => $type));
    $this->beans[] = array(
      'bean' => $bean,
      'region' => $region,
      'width' => $width,
    );
    return entity_metadata_wrapper('bean', $bean);
  }

  /**
   * Either create a new file or use existing file with same name.
   *
   * @param string $filename
   *   The file name
   * @param string $path
   *   The path to the file to load up into Drupal.
   *
   * @return array
   *   Array containing fid of the file, either new or existing, appropriate for
   *   use with an EntityMetadataWrapper field.
   */
  public function addFile($filename, $path) {
    if (!file_exists($path . '/' . $filename)) {
      throw new WrapperException('File not found: ' . $path . '/' . $filename);
    }

    $destination = 'public://media';
    file_prepare_directory($destination, FILE_CREATE_DIRECTORY);
    $destination .= '/' . $filename;

    // If the file already exists in the database with the same name, then use
    // that file.
    $existing_files = file_load_multiple(array(), array('uri' => $destination));
    if (count($existing_files)) {
      $existing = reset($existing_files);
      $fid = $existing->fid;
    }
    else {
      // Save a new file, replacing existing files on the file system.
      if ($file = file_save_data($path . '/' . $filename, $destination, FILE_EXISTS_RENAME)) {
        $fid = $file->fid;
      }
      else {
        throw new WrapperException('Unable to save file ' . $filename);
      }
    }

    return array(
      'fid' => $fid,
    );
  }

  /**
   * Either create a new taxonomy term or load existing term.
   *
   * @param string $term_name
   *   The name of the term. Will be used to find existing once within
   *   $vocabulary.
   * @param string $vocabulary_name
   *   Valid vocabulary.
   *
   *
   * @return int
   *   The tid of the term, either new or existing, appropriate for
   *   use with an EntityMetadataWrapper field.
   */
  public function addTerm($term_name, $vocabulary_name) {
    if (!($vocabulary = taxonomy_vocabulary_machine_name_load($vocabulary_name))) {
      throw new WrapperException('Vocabulary not found: ' . $vocabulary_name);
    }

    $term = taxonomy_get_term_by_name($term_name, $vocabulary);
    if ($term) {
      $term = current($term);
    }
    else {
      $term = new \stdClass();
      $term->vid = $vocab->vid;
      $term->name = $term_name;
      $term->description = '';
      taxonomy_term_save($term);
    }

    return $term->tid;
  }

  /**
   * Return the uri for current entity.
   *
   * @return string
   *   Internal uri for entity.
   */
  public function uri() {
    return entity_uri($this->type(), $this->data);
  }

  /**
   * Save entity, blocks, and tile layout.
   *
   * @return Wrapper
   */
  public function save() {
    parent::save();

    // Save all of the beans associated with this content via tiles.
    if ($this->beans) {
      $blocks = array();
      $weight = 0;
      foreach ($this->beans as $info) {
        $info['bean']->save();

        $blocks[] = array(
          'module' => 'bean',
          'delta' => $info['bean']->delta,
          'region' => $info['region'],
          'width' => $info['width'],
          'weight' => $weight++
        );
      }

      // Generate new tiles context based on entity path and assign all new
      // blocks in order.
      $context = tiles_create_context($this->uri());
      tiles_assign_tiles($context, $blocks);
    }
  }
}
