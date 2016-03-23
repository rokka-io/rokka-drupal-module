<?php

namespace Drupal\rokka;

use Drupal\rokka\DrupalAdapter\Bootstrap;
use Rokka\Client\Base;

class Configuration {
  use Bootstrap;

  protected $data;

  /**
   * @return array
   */
  protected static function defaults() {
    $defaults = array(
      'api_key' => NULL,
      'api_secret' => NULL,
      'organization_name' => NULL,
      'api_base_url' => Base::DEFAULT_API_BASE_URL,
    );
    return $defaults;
  }

  /**
   * @return array
   */
  protected static function required() {
    return array('api_key', 'api_secret', 'organization_name' );
  }

  /**
   * @param $data
   */
  protected function __construct(array $data = array()) {
    $this->data = $data;
  }

  /**
   * @param array $data
   * @return static
   */
  public static function fromConfig(array $data) {
    $required = static::required();

    if ($missing = array_diff($required, array_keys(array_filter($data, function($item) {
      return !is_null($item) && $item !== '';
    })))) {
      throw new \InvalidArgumentException('Config is missing the following keys: ' . implode(', ', $missing));
    }

    $data = array_merge(static::defaults(), $data);
    return new static($data);
  }

  /**
   * @return static
   */
  public static function fromDrupalVariables() {
    $data = static::defaults();
    $data['api_key'] = static::variable_get('rokka_api_key', NULL);
    $data['api_secret'] = static::variable_get('rokka_api_secret', NULL);
    $data['organization_name'] = static::variable_get('rokka_organization_name', NULL);
    $data['api_base_url'] = static::variable_get('rokka_api_base_url', Base::DEFAULT_API_BASE_URL);
    return static::fromConfig($data);
  }

  /**
   * @return string
   */
  public function getApiKey() {
    return $this->data['api_key'];
  }

  /**
   * @return string
   */
  public function getApiSecret() {
    return $this->data['api_secret'];
  }

  /**
   * @return string
   */
  public function getOrganizationName() {
    return $this->data['organization_name'];
  }

  /**
   * @return string
   */
  public function getBaseAPIUrl() {
    return $this->data['api_base_url'];
  }

}
