<?php

namespace Drupal\rokka;

use Drupal\rokka\RokkaAdapter\Image;
use GuzzleHttp\Client as GuzzleClient;

class Client {

  /** @var Configuration $organization */
  protected $configuration;
  /** @var \Rokka\Client\User $rokkaUserClient */
  protected $rokkaUserClient;

  /** @var \Rokka\Client\Image $rokkaImageClient */
  protected $rokkaImageClient;

  /**
   * @param Configuration $configuration
   * @return boolean
   */
  static function validateConfiguration(Configuration $configuration) {
    $client = static::buildUserClient($configuration);
    $client->getOrganization($configuration->getOrganizationName());

    // Let's return true, in case of errors an Exception is thrown.
    return true;
  }

  /**
   * @param Configuration $config
   */
  public function __construct(Configuration $config) {
    $this->configuration = $config;
    // $this->rokkaUserClient = static::initUserClient($config);
    // $this->rokkaImageClient = static::initImageClient($config);
  }

  /**
   * @param Configuration $configuration
   * @return \Rokka\Client\User
   */
  protected static function buildUserClient(Configuration $configuration) {
    $client = \Rokka\Client\Factory::getUserClient(
      $configuration->getBaseAPIUrl()
    );
    $client->setCredentials($configuration->getApiKey(), $configuration->getApiSecret());
    return $client;
  }

  /**
   * @param \Drupal\rokka\Configuration $configuration
   * @return \Rokka\Client\Image
   */
  protected static function buildImageClient(Configuration $configuration) {
    // Using Rokka Factory to build the standard Image client
    $client = \Rokka\Client\Factory::getImageClient(
      $configuration->getOrganizationName(),
      $configuration->getApiKey(),
      $configuration->getApiSecret(),
      $configuration->getBaseAPIUrl()
    );
    return $client;
  }

  /**
   * @return \Drupal\rokka\Configuration
   */
  public function getConfiguration() {
    return $this->configuration;
  }

  /**
   * @return \Rokka\Client\User
   */
  public function getUserClient() {
    if (empty($this->rokkaUserClient)) {
      $this->rokkaUserClient = $this->buildUserClient($this->configuration);
    }
    return $this->rokkaUserClient;
  }

  /**
   * @return \Rokka\Client\Image
   */
  public function getImageClient() {
    if (empty($this->rokkaImageClient)) {
      $this->rokkaImageClient = $this->buildImageClient($this->configuration);
    }
    return $this->rokkaImageClient;
  }

  /**
   * @param null $organization
   * @return \Rokka\Client\Core\Organization
   */
  public function getOrganization($organization = NULL) {
    return $this->getUserClient()->getOrganization($organization ?
      $organization : $this->configuration->getOrganizationName()
    );
  }

  /**
   * @return \Rokka\Client\Core\User
   * @throws \Exception
   */
  public function getUser() {
    throw new \Exception('Not Implemented');
  }

  /**
   * Returns an array of accepted formats, keyed by extension.
   *
   * @return array
   */
  public static function getFileFormats()
  {
    return array(
      'jpg' => 'JPEG Format',
      'png' => 'PNG Format',
      'gif' => 'GIF format',
    );
  }

  /**
   * Returns the SEO compliant filename for the given image name.
   *
   * @param $filename
   * @return string
   */
  public static function cleanRokkaSeoFileame($filename)
  {
    // Rokka.io accepts SEO URL part as "[a-z0-9-]" only, remove not valid
    // characters and replace them with '-'
    return preg_replace('@[^a-z0-9-]@', '-', strtolower($filename));
  }

}
