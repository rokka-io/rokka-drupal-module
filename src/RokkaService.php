<?php

namespace Drupal\rokka;

use Drupal\Core\Config\ConfigFactory;
use Drupal\Core\Entity\EntityStorageBase;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\rokka\RokkaAdapter\SourceImageMetadata;
use Psr\Log\LoggerInterface;
use Rokka\Client\Base;
use Rokka\Client\Factory;

/**
 * Defines a RokkaService service.
 */
class RokkaService implements RokkaServiceInterface {

  /**
   * @var LoggerInterface
   */
  private $logger;

  /**
   * @var ConfigFactory
   */
  private $configFactory;


  private $apiKey;

  private $organizationName;

  private $apiEndpoint;

  /**
   * @var EntityTypeManagerInterface
   */
  private $entityManager;

  /**
   * RokkaService constructor.
   *
   * @param EntityTypeManagerInterface $em
   * @param ConfigFactory $configFactory
   * @param LoggerInterface $logger
   *
   * @internal param string $apiKey
   * @internal param string $organizationName
   * @internal param string $apiEndpoint
   */
  public function __construct(EntityTypeManagerInterface $em, ConfigFactory $configFactory, LoggerInterface $logger) {
    $this->entityManager = $em;
    $this->logger = $logger;
    $this->configFactory = $configFactory;

    $config = $configFactory->get('rokka.settings');

    $this->apiKey = $config->get('api_key');
    $this->organizationName = $config->get('organization_name');
    $this->apiEndpoint = $config->get('api_endpoint') ?: Base::DEFAULT_API_BASE_URL;

  }

  /**
   * {@inheritdoc}
   */
  public function getRokkaImageClient() {
    return Factory::getImageClient($this->organizationName, $this->apiKey, '', $this->apiEndpoint);
  }

  /**
   * {@inheritdoc}
   */
  public function getRokkaUserClient() {
    return Factory::getUserClient($this->apiEndpoint);
  }

  /**
   * Returns the organization name.
   */
  public function getRokkaOrganizationName() {
    return $this->organizationName;
  }

  /**
   * @param string $uri
   *
   * @return \Drupal\Core\Entity\EntityInterface[]
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   */
  public function loadRokkaMetadataByUri($uri) {
    $rokka_metadata_storage = \Drupal::entityTypeManager()
      ->getStorage('rokka_metadata');

    return $rokka_metadata_storage->loadByProperties(['uri' => $uri]);
  }

  /**
   * Counts the number of images that share the same Hash.
   *
   * @param string $hash
   *
   * @return int
   */
  public function countImagesWithHash($hash) {
    // TODO: Implement loadRokkaMetadataByUri() method.
    // This is the old method used in D7:
    /*
    $q = new \EntityFieldQuery();
    $q->entityCondition('entity_type', 'rokka_metadata')
        ->propertyCondition('hash', $hash)
        ->range(null, 2);
    $metas = $q->execute();
    */
  }

  /**
   * Return the given setting from the Rokka module configuration.
   *
   * Examples:
   * - source_image_style (default: , 'rokka_source')
   * - use_hash_as_name (default: true)
   *
   * @param string $param
   *
   * @return mixed
   */
  public function getSettings($param) {
    // TODO: Implement getSettings() method.
    return FALSE;
  }

  /**
   * Returns the SEO compliant filename for the given image name.
   *
   * @param $filename
   * @return string
   */
  public static function cleanRokkaSeoFilename($filename)
  {
    // Rokka.io accepts SEO URL part as "[a-z0-9-]" only, remove not valid
    // characters and replace them with '-'
    return preg_replace('@[^a-z0-9-]@', '-', strtolower($filename));
  }
}
