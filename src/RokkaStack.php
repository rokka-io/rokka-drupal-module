<?php

namespace Drupal\rokka;

use Drupal\rokka\RokkaAdapter\RokkaStackInterface;

class RokkaStack extends \Entity implements RokkaStackInterface {

  public $options;
  public $created;
  public $image_style;

  public function __construct($values = array()) {
    parent::__construct($values, 'rokka_stack');
  }

  /**
   * @return mixed
   */
  public function save() {
    if (empty($this->getCreatedTime())) {
      $this->created = time();
    }

    return parent::save();
  }

  /**
   * @return array The Stack options
   */
  public function getStackOptions() {
    // Ensure that the options is an array, Drupal is failing in initializing it!
    if (!is_array($this->options)) {
      $this->options = array();
    }
    return $this->options;
  }

  /**
   * @param $option
   * @param $value
   */
  protected function setStackOption($option, $value) {
    // Ensure that the options is an array, Drupal is failing in initializing it!
    if (!is_array($this->options)) {
      $this->options = array();
    }

    if (!empty($value)) {
      $this->options[$option] = $value;
    }
    else if (array_key_exists($option, $this->options)) {
      unset($this->options[$option]);
    }
  }

  /**
   * @param $option
   * @return null
   */
  protected function getStackOption($option) {
    // Ensure that the options is an array, Drupal is failing in initializing it!
    if (!is_array($this->options)) {
      $this->options = array();
      return NULL;
    }

    if (array_key_exists($option, $this->options)) {
      return $this->options[$option];
    }
    return NULL;
  }

  /**
   * @param $value
   */
  public function setJpgQuality($value) {
    $this->setStackOption('jpg.quality', (int) $value);
  }

  /**
   * @return integer
   */
  public function getJpgQuality() {
    return $this->getStackOption('jpg.quality');
  }

  /**
   * @param $value
   */
  public function setPngCompressionLevel($value) {
    $this->setStackOption('png.compression_level', (int) $value);
  }

  /**
   * @return integer
   */
  public function getPngCompressionLevel() {
    return $this->getStackOption('png.compression_level');
  }

  /**
   * @param $value
   */
  public function setInterlacingMode($value) {
    $this->setStackOption('interlacing.mode', $value);
  }

  /**
   * @return string
   */
  public function getInterlacingMode() {
    return $this->getStackOption('interlacing.mode');
  }

  /**
   * @return array The Image Style label
   */
  public function getImageStyle() {
    return $this->image_style;
  }

  /**
   * @param string $value The Image Style label
   */
  public function setImageStyle($value) {
    $this->image_style = $value;
  }

  /**
   * @return string The Stack creation time.
   */
  public function getCreatedTime() {
    return $this->created;
  }
}
