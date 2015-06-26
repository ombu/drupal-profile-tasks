<?php
/**
 * @file
 * Setup media related configuration.
 */

namespace ProfileTasks\Task;

class Media extends Task {
  /**
   * Setup oembed related integration into files_entity.
   */
  public function process() {
    // Add oembed streams to video file types.
    $video = file_type_load('video');
    $video->mimetypes[] = 'video/oembed';
    $video->streams[] = 'oembed';
    file_type_save($video);

    // Oembed specific display settings for videos.
    $file_display = new \stdClass();
    $file_display->api_version = 1;
    $file_display->name = 'video__default__oembed';
    $file_display->weight = -10;
    $file_display->status = TRUE;
    $file_display->settings = array(
      'width' => '560',
      'height' => '340',
      'wmode' => '',
    );
    file_display_save($file_display);

    $file_display = new \stdClass();
    $file_display->api_version = 1;
    $file_display->name = 'video__default__oembed_thumbnail';
    $file_display->weight = -10;
    $file_display->status = TRUE;
    $file_display->settings = array(
      'width' => '180',
      'height' => '',
    );
    file_display_save($file_display);

    $file_display = new \stdClass();
    $file_display->api_version = 1;
    $file_display->name = 'video__preview__oembed_thumbnail';
    $file_display->weight = -10;
    $file_display->status = TRUE;
    $file_display->settings = array(
      'width' => '100',
      'height' => '75',
    );
    file_display_save($file_display);

    $file_display = new \stdClass();
    $file_display->api_version = 1;
    $file_display->name = 'video__teaser__oembed_thumbnail';
    $file_display->weight = -10;
    $file_display->status = TRUE;
    $file_display->settings = array(
      'width' => '100',
      'height' => '75',
    );
    file_display_save($file_display);

    // Default to "large" image style for default image file display.
    $displays = file_displays_load('image', 'default');
    if (!isset($displays['image__default__file_field_image'])) {
      $file_display = new \stdClass();
      $file_display->api_version = 1;
      $file_display->name = 'image__default__file_field_image';
      $file_display->weight = -10;
      $file_display->status = TRUE;
      $file_display->settings = array(
        'image_style' => variable_get('image_default_image_style', 'large'),
      );
      file_display_save($file_display);
    }
    elseif (empty($displays['image__default__file_field_image']->settings['image_style'])) {
      $displays['image__default__file_field_image']->settings['image_style'] = variable_get('image_default_image_style', 'large');
      file_display_save($displays['image__default__file_field_image']);
    }

    $this->addCaptionField();
  }

  /**
   * Adds a caption field to image file entities.
   */
  protected function addCaptionField() {
    $base = array(
      'active' => 1,
      'cardinality' => 1,
      'deleted' => 0,
      'entity_types' => array(),
      'field_name' => 'field_caption',
      'foreign keys' => array(
        'format' => array(
          'columns' => array(
            'format' => 'format',
          ),
          'table' => 'filter_format',
        ),
      ),
      'indexes' => array(
        'format' => array(
          0 => 'format',
        ),
      ),
      'locked' => 0,
      'module' => 'text',
      'settings' => array(),
      'translatable' => 0,
      'type' => 'text_long',
    );
    if (!field_info_field($base['field_name'])) {
      field_create_field($base);
    }

    $instance = array(
      'bundle' => 'image',
      'default_value' => NULL,
      'deleted' => 0,
      'description' => '',
      'display' => array(
        'default' => array(
          'label' => 'hidden',
          'module' => 'text',
          'settings' => array(),
          'type' => 'text_default',
          'weight' => 1,
        ),
        'preview' => array(
          'label' => 'above',
          'settings' => array(),
          'type' => 'hidden',
          'weight' => 0,
        ),
        'teaser' => array(
          'label' => 'above',
          'settings' => array(),
          'type' => 'hidden',
          'weight' => 0,
        ),
        'wysiwyg' => array(
          'label' => 'above',
          'settings' => array(),
          'type' => 'hidden',
          'weight' => 0,
        ),
      ),
      'entity_type' => 'file',
      'field_name' => 'field_caption',
      'label' => 'Caption',
      'required' => 0,
      'settings' => array(
        'text_processing' => 0,
        'user_register_form' => FALSE,
        'wysiwyg_override' => 1,
      ),
      'widget' => array(
        'active' => 1,
        'module' => 'text',
        'settings' => array(
          'rows' => 5,
        ),
        'type' => 'text_textarea',
        'weight' => 4,
      ),
      'workbench_access_field' => 0,
    );
    if (!field_info_instance($instance['entity_type'], $instance['field_name'], $instance['bundle'])) {
      field_create_instance($instance);
    }
  }
}
