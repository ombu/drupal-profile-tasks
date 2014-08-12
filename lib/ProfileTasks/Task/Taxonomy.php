<?php
/**
 * @file
 * Setup taxonomy vocabulary.
 */

namespace ProfileTasks\Task;

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

    if (!empty($settings['vocabularies'])) {
      $this->vocabularies = $settings['vocabularies'];
    }
  }

  /**
   * Create new vocabulary and terms.
   */
  public function process() {
    if ($this->vocabularies) {
      foreach ($this->vocabularies as $machine_name => $info) {
        $info = $info + array(
          'machine_name' => $machine_name
        );
        $vocab = $this->createVocabulary($info);

        // Create new terms.
        if ($info['terms']) {
          $this->processTerms($info['terms'], $vocab);
        }
      }
    }
  }

  /**
   * Create a new vocabulary.
   *
   * @param array $info
   *   Keyed array expected by taxonomy_vocabulary_save().
   *
   * @return object
   *   Saved vocabulary.
   */
  protected function createVocabulary($info) {
    // Check if vocabulary already exists.
    if (!($vocab = taxonomy_vocabulary_machine_name_load($info['machine_name']))) {
      $vocab = (object) $info;
      taxonomy_vocabulary_save($vocab);
    }

    return $vocab;
  }

  /**
   * Process terms array.
   *
   * @param array $terms
   *   Array of terms to save
   * @param object $vocab
   *   Vocabulary object
   * @param object $parent_term
   *   Parent term, if creating sub terms.
   */
  protected function processTerms($terms, $vocab, $parent = NULL) {
    foreach ($terms as $key => $term_name) {
      // If term is an array, then recursively create terms associated to
      // correct parent.
      $subterms = array();
      if (is_array($term_name)) {
        $subterms = $term_name;
        $term_name = $key;
      }
      elseif (is_null($term_name)) {
        $term_name = $key;
      }

      $term = $this->createTerm($term_name, $vocab->vid, $parent);
      $this->saveTerm($term);

      if ($subterms) {
        $this->processTerms($subterms, $vocab, $term);
      }
    }
  }

  /**
   * Creates a new taxonomy term object.
   *
   * @param string $term_name
   *   Term name
   * @param int $vid
   *   Vocabulary id
   * @param object $parent_term
   *   Parent term, if creating sub terms.
   *
   * @return object
   *   Fully loaded taxonomy term object.
   */
  protected function createTerm($term_name, $vid, $parent = NULL) {
    static $weight = 0;

    $term = new \stdClass();
    $term->vid = $vid;
    $term->name = $term_name;
    $term->description = $this->lorem();
    $term->format = 'default';
    $term->weight = $weight++;

    // Assign parent term if present.
    if ($parent) {
      $term->parent = $parent->tid;
    }

    return $term;
  }

  /**
   * Saves a taxonomy term object.
   *
   * @param object $term
   *   The taxonomy term object.
   */
  protected function saveTerm($term) {
    return taxonomy_term_save($term);
  }
}
