<?php
/**
 * @file
 * Setup taxonomy vocabulary.
 */

namespace BaseProfile\Task;

class Taxonomy extends Task {
  /**
   * Vocabulary and terms.
   *
   * Associative array of vocabularies and terms. If no vocabulary exists under
   * the given name, a new one will be created.  Vocabularies should be in the
   * form of:
   *
   * @code
   * $vocabularies['vocab_name'] = array(
   *   'name' => st('Vocab Name'),
   *   'description' => st('Vocab description'),
   *   'terms' => array(
   *     'Term 1',
   *     'Term 2',
   *   ),
   * );
   * @endcode
   *
   * @var array
   */
  protected $vocabularies;

  /**
   * Load up vocabularies.
   */
  public function settings() {
    $settings = $this->loadSettings('taxonomy');

    if (isset($settings['vocabularies'])) {
      $this->vocabularies = $settings['vocabularies'];
    }
  }

  /**
   * Create new vocabulary and terms.
   */
  public function process() {
    if ($this->vocabularies) {
      foreach ($this->vocabularies as $machine_name => $info) {
        // Check if vocabulary already exists.
        if (!($vocab = taxonomy_vocabulary_machine_name_load($machine_name))) {
          $vocab = (object) $info;
          $vocab->machine_name = $machine_name;
          taxonomy_vocabulary_save($vocab);
        }

        // Create new terms.
        if ($info['terms']) {
          foreach ($info['terms'] as $term_name) {
            $term = new stdClass();
            $term->vid = $vocab->vid;
            $term->name = $term_name;
            $term->description = $this->lorem();
            taxonomy_term_save($term);
          }
        }
      }
    }
  }
}
