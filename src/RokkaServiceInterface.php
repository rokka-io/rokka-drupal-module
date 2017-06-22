<?php

namespace Drupal\rokka;

use Drupal\rokka\RokkaAdapter\SourceImageMetadata;

/**
 * Rokka service interface.
 */
interface RokkaServiceInterface
{
    /**
     * @return \Rokka\Client\Image
     */
    public function getRokkaImageClient();

    /**
     * @return \Rokka\Client\User
     */
    public function getRokkaUserClient();

    /**
     * @param string $hash
     *
     * @return SourceImageMetadata
     */
    public function loadRokkaMetadataByHash($hash);

    /**
     * @param string $uri
     *
     * @return mixed
     */
    public function deleteRokkaMetadataByUri($uri);
}
