<?php

namespace Drupal\rokka\Plugin\ImageToolkit;

use Drupal\system\Plugin\ImageToolkit\GDToolkit;

/**
 * Provides ImageMagick integration toolkit for image manipulation.
 *
 * @ImageToolkit(
 *   id = "rokka",
 *   title = @Translation("Rokka image toolkit")
 * )
 */
class RokkaToolkit extends GDToolkit {

  /**
   * {@inheritdoc}
   */
  public function isValid() {
    return ((bool) $this->getMimeType());
  }

}
