<?php

namespace Drupal\rokka;

use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\rokka\RokkaAdapter\SourceImageMetadata;
use Rokka\Client\Base;
use Rokka\Client\Factory;

/**
 * Defines a RokkaService service.
 */
class RokkaService implements RokkaServiceInterface
{
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
     * @param string                     $apiKey
     * @param string                     $organizationName
     * @param string                     $apiEndpoint
     */
    public function __construct(EntityTypeManagerInterface $em, $apiKey = '', $organizationName = '', $apiEndpoint = null)
    {
        $this->apiKey = $apiKey;
        $this->organizationName = $organizationName;
        $this->apiEndpoint = $apiEndpoint ?: Base::DEFAULT_API_BASE_URL;
        $this->entityManager = $em;
    }

    /**
     * {@inheritdoc}
     */
    public function getRokkaImageClient()
    {
        return Factory::getImageClient($this->organizationName, $this->apiKey, '', $this->apiEndpoint);
    }

    /**
     * {@inheritdoc}
     */
    public function getRokkaUserClient()
    {
        return Factory::getUserClient($this->apiEndpoint);
    }

    /**
     * @param string $hash
     *
     * @return SourceImageMetadata
     */
    public function loadRokkaMetadataByHash($hash)
    {
        // TODO: Implement loadRokkaMetadataByHash() method.
    }

    /**
     * @param string $uri
     *
     * @return mixed
     */
    public function deleteRokkaMetadataByUri($uri)
    {
        // TODO: Implement deleteRokkaMetadataByUri() method.
    }
}
