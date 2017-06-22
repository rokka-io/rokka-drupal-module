<?php

namespace Drupal\rokka;

use Drupal\rokka\RokkaAdapter\SourceImageMetadata;

class RokkaMetadata extends Entity implements SourceImageMetadata {

  public $hash;
  public $created;
  public $filesize;
  public $uri;
  public $fid;

  public function __construct($values = array()) {
    parent::__construct($values, 'rokka_metadata');
  }

  /**
   * @return string
   */
  public function getHash() {
    return $this->hash;
  }

  /**
   * @return int
   */
  public function getCreatedTime() {
    return $this->created;
  }

  /**
   * @return int
   */
  public function getFilesize() {
    return $this->filesize;
  }

  /**
   * @return string
   */
  public function getUri() {
    return $this->uri;
  }

  /**
   * @return int
   */
  public function getFid() {
    return $this->fid;
  }
}
