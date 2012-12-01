<?php
/**
 * @file
 * Setup default input formats.
 */

namespace BaseProfile\Task;

class InputFormats extends Task {
  /**
   * Format settings.
   *
   * @param array
   */
  protected $formats;

  /**
   * Wysiwyg settings.
   *
   * @param array
   */
  protected $wysiwyg;

  /**
   * Default format/wysiwyg settings.
   */
  public function settings() {
    $this->formats = $this->getFormatSettings();
    $this->wysiwyg = $this->getWysiwygSettings();
  }

  /**
   * Save format settings.
   */
  public function process() {
    // Save formats.
    foreach ($this->formats as $format_name => $format) {
      $format = (object) $format;
      filter_format_save($format);
    }

    // Save wysiwyg formats.
    foreach ($this->wysiwyg as $wysiwyg_name => $object) {
      db_insert('wysiwyg')
        ->fields(array(
          'format' => $object['format'],
          'editor' => $object['editor'],
          'settings' => serialize($object['settings']),
        ))
        ->execute();
    }
  }

  /**
   * Load up default format settings.
   */
  protected function getFormatSettings() {
    return $this->loadSettings('formats');
  }

  /**
   * Load up default wysiwyg settings.
   */
  protected function getWysiwygSettings() {
    return $this->loadSettings('wysiwyg');
  }
}
