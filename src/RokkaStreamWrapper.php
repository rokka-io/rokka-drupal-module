<?php

namespace Drupal\rokka;

use Drupal\rokka\RokkaAdapter\SourceImageMetadata;
use Drupal\rokka\RokkaAdapter\StreamWrapper;
use GuzzleHttp\Exception\GuzzleException;
use Rokka\Client\Core\SourceImage;

class RokkaStreamWrapper extends StreamWrapper implements \DrupalStreamWrapperInterface {
  /**
   * Configuration for this stream wrapper.
   *
   * @var \Drupal\rokka\Configuration
   */
  protected $config;

  /**
   * Construct a new stream wrapper.
   *
   * @param \Drupal\rokka\Configuration $config
   *   (optional) A specific configuration to use for this wrapper.
   */
  public function __construct(Configuration $config = NULL) {
    if (!$config) {
      // @codeCoverageIgnoreStart
      $config = Configuration::fromDrupalVariables();
      // @codeCoverageIgnoreEnd
    }

    $imageClient = (new Client($config))->getImageClient();
    parent::__construct($imageClient);
  }

  /**
   * @return \Rokka\Client\Image
   */
  public function getImageClient() {
    return static::$imageClient;
  }

  /**
   * Implements getTarget().
   *
   * The "target" is the portion of the URI to the right of the scheme.
   * So in rokka://test.txt, the target is 'example/test.txt'.
   */
  public function getTarget($uri = NULL) {
    if (!isset($uri)) {
      $uri = $this->uri;
    }

    list($scheme, $target) = explode('://', $uri, 2);

    // Remove erroneous leading or trailing, forward-slashes and backslashes.
    // In the rokka:// scheme, there is never a leading slash on the target.
    return trim($target, '\/');
  }

  /**
   * Implements getMimeType().
   * @todo
   */
  public static function getMimeType($uri, $mapping = NULL) {
    //*
    if (!isset($mapping)) {
      // The default file map, defined in file.mimetypes.inc is quite big.
      // We only load it when necessary.
      include_once DRUPAL_ROOT . '/includes/file.mimetypes.inc';
      $mapping = file_mimetype_mapping();
    }

    $extension = '';
    $file_parts = explode('.', basename($uri));

    // Remove the first part: a full filename should not match an extension.
    array_shift($file_parts);

    // Iterate over the file parts, trying to find a match.
    // For my.awesome.image.jpeg, we try:
    // - jpeg
    // - image.jpeg, and
    // - awesome.image.jpeg
    while ($additional_part = array_pop($file_parts)) {
      $extension = drupal_strtolower($additional_part . ($extension ? '.' . $extension : ''));
      if (isset($mapping['extensions'][$extension])) {
        return $mapping['mimetypes'][$mapping['extensions'][$extension]];
      }
    }
    //*/

    return 'application/octet-stream';
  }

  /**
   * Implements getDirectoryPath().
   *
   * In this case there is no directory string, so return an empty string.
   */
  public function getDirectoryPath() {
    return '';
  }

  /**
   * Returns a web accessible URL for the resource.
   *
   * This function should return a URL that can be embedded in a web page
   * and accessed from a browser. For example, the external URL of
   * "youtube://xIpLd0WQKCY" might be
   * "http://www.youtube.com/watch?v=xIpLd0WQKCY".
   *
   * @return string|null
   *   Returns a string containing a web accessible URL for the resource.
   */
  public function getExternalUrl() {
    $meta = entity_load_single('rokka_metadata', $this->uri);

    if ( !($meta instanceof RokkaMetadata)) {
      watchdog('rokka', 'Error getting getExternalUrl() for "@uri": RokkaMetadata not found!', array(
        '@uri' => $this->uri
      ));
      return NULL;
    }

    // @todo: remove the default style as soon as the getSourceImageUri
    // will support the 'empty' image style to get the original source image
    $defaultStyle = variable_get('rokka_source_image_style', 'rokka_source');
    $externalUri = self::$imageClient->getSourceImageUri($meta->getHash(), $defaultStyle, 'jpg');
    return $externalUri->__toString();
  }

  /**
   * Return the local filesystem path.
   *
   * @return string
   *   The local path.
   */
  protected function getLocalPath($uri = NULL) {
    if (!isset($uri)) {
      $uri = $this->uri;
    }

    $path = str_replace('rokka://', '', $uri);
    $path = trim($path, '/');
    return $path;
  }

  /**
   * Override register() to force using hook_stream_wrappers().
   * @param \Drupal\rokka\Configuration $config
   */
  public static function register(Configuration $config = NULL) {
    throw new \LogicException('Drupal handles registration of stream wrappers. Implement hook_stream_wrappers() instead.');
  }

  /**
   * Callback function invoked after the underlying Stream has been flushed to
   * Rokka.io, the callback receives the SourceImage returned by the
   * $client->uploadSourceimage() invocation.
   *
   * @param SourceImage $sourceImage
   * @return bool
   */
  protected function doPostSourceImageSaved(SourceImage $sourceImage) {
    // At this point the image has been uploaded to Rokka.io for the
    // "rokka://URI". Here we use our {rokka_metadata} table to store
    // the values returned by Rokka such as: hash, filesize, ...

    // First check if the URI is already tracked (i.e. the file has been overwritten).
    $meta =$this->doGetMetadataFromUri($this->uri);
    if ($meta) {
      watchdog('rokka', 'Image replaced on Rokka: ' . $this->uri . ' old-hash:' . $meta->getHash() .' new-hash:' . $sourceImage->hash);

      // If the two images are the same we're done, just return true.
      if ($meta->getHash() == $sourceImage->hash) {
        return true;
      }

      // Update the RokkaMetadata with the new data coming from the uploaded image.
      $meta->hash = $sourceImage->hash;
      $meta->created = $sourceImage->created->getTimestamp();
      $meta->filesize = $sourceImage->size;
    }
    else {
      watchdog('rokka', 'New Image uploaded to Rokka: ' . $this->uri . ' hash:' . $sourceImage->hash);
      // This is a new URI, track it in our RokkaMetadata entities.
      $meta = entity_create('rokka_metadata', array(
        'uri' => $this->uri,
        'hash' => $sourceImage->hash,
        'filesize' => $sourceImage->size,
        'created' => $sourceImage->created->getTimestamp()
      ));
    }

    return $meta->save();
  }

  /**
   * Callback function invoked by the underlying stream when the Rokka HASH is
   * needed instead of the standard URI (examples includes the deletion of an
   * image from Rokka.io or the uri_stat() function).
   *
   * @param $uri
   * @return SourceImageMetadata|null
   */
  protected function doGetMetadataFromUri($uri) {
    return entity_load_single('rokka_metadata', $uri);
  }

  /**
   * Callback function invoked after the underlying Stream has been unlinked and
   * the corresponding image deleted on Rokka.io
   * The callback receives the $hash used to remove the image.
   *
   * @param SourceImageMetadata $meta
   * @return bool
   */
  protected function doPostSourceImageDeleted(SourceImageMetadata $meta) {
    return entity_delete('rokka_metadata', $meta->getUri()) !== FALSE;
  }

  /**
   * Override the unlink() function. Instead of directly deleting the underlying
   * Rokka image, we must check if the same HASH has been assigned to another
   * file: this can happen when the user uploads multiple time the same image.
   * For each uploaded image Drupal will assign it a different FID/URI, but
   * Rokka is referencing to the same HASH computed on the image contents.
   *
   * @param string $uri
   *   A string containing the uri to the resource to delete.
   *
   * @return bool
   *   TRUE if resource was successfully deleted.
   *
   * @see http://php.net/manual/en/streamwrapper.unlink.php
   */
  public function unlink($uri) {
    $meta = $this->doGetMetadataFromUri($uri);
    $hash = $meta->getHash();

    $q = new \EntityFieldQuery();
    $q->entityCondition('entity_type', 'rokka_metadata')
      ->propertyCondition('hash', $hash)
      ->range(NULL, 2);
    $metas = $q->execute();

    // If the same HASH is used elsewhere for another file..
    if (!empty($metas['rokka_metadata']) && count($metas['rokka_metadata']) > 1) {
      // Remove the Drupal image and FID, but don't remove the Rokka's image.
      $this->doPostSourceImageDeleted($meta);
      watchdog('rokka', 'Image file "%uri" deleted, but kept in Rokka since HASH %hash is not unique.', array(
        '%uri' => $uri,
        '%hash' => $meta->getHash()
      ));
      return TRUE;
    }

    watchdog('rokka', 'Deleting image file "%uri".', array(
      '%uri' => $uri,
      '%hash' => $meta->getHash()
    ));

    // Else, go on and let's our parent delete the Rokka image instance.
    return parent::unlink($uri);
  }

  /**
   * Override the default exception handling, logging errors to Drupal messages
   * and Watchdog.
   *
   * @param \Exception[] $exceptions
   * @param mixed $flags
   * @return bool
   * @throws \Exception
   */
  protected function triggerException($exceptions, $flags = null) {
    $exceptions = is_array($exceptions) ? $exceptions : array($exceptions);

    /** @var \Exception $exception */
    foreach($exceptions as $exception) {
      // If we got a GuzzleException here, means that something happened during
      // the data transfer. We throw the exception to Drupal.
      if ($exception instanceof GuzzleException) {
        drupal_set_message(t('An error occurred while uploading your image to Rokka.io'), 'error');
        watchdog('rokka', 'Exception caught in triggerException(): %exception', array(
          '%exception' => $exception->getMessage()
        ));
        if (!($flags & STREAM_URL_STAT_QUIET)) {
          throw $exception;
        }
      }
    }

    return parent::triggerException($exceptions, $flags);
  }

  /**
   * @param string $uri
   * @param int $flags
   * @return array|bool
   */
  public function url_stat($uri, $flags) {
    if ($this->is_dir($uri)) {
      return $this->formatUrlStat();
    }

    $meta = $this->doGetMetadataFromUri($uri);
    if ($meta) {
      $data = [
        'timestamp' => $meta->getCreatedTime(),
        'filesize'  => $meta->getFilesize(),
      ];
      return $this->formatUrlStat($data);
    }

    return FALSE;
  }

  /**
   * @return bool
   *   FALSE, as this stream wrapper does not support realpath().
   */
  public function realpath() {
    return FALSE;
  }

  /**
   * Gets the name of the directory from a given path.
   * A trailing "/" is always appended to mark the resource as a Directory.
   *
   * @param string $uri
   *   A URI.
   *
   * @return string
   *   A string containing the directory name.
   *
   * @see drupal_dirname()
   * @todo
   */
  public function dirname($uri = NULL) {
    list($scheme, $target) = explode('://', $uri, 2);
    $target = $this->getTarget($uri);
    if (strpos($target, '/')) {
      // If we matched a directory here, let's append '/' in the end.
      $dirname = preg_replace('@/[^/]*$@', '', $target) . '/';
    }
    else {
      $dirname = '';
    }
    return $scheme . '://' . $dirname;
  }

  /**
   * Rokka has no support for mkdir(), thus we 'virtually' create them.
   *
   * @param string $uri
   *   A string containing the URI to the directory to create.
   * @param int $mode
   *   Permission flags - see mkdir().
   * @param int $options
   *   A bit mask of STREAM_REPORT_ERRORS and STREAM_MKDIR_RECURSIVE.
   *
   * @return bool
   *   TRUE if directory was successfully created.
   *
   * @see http://php.net/manual/en/streamwrapper.mkdir.php
   */
  public function mkdir($uri, $mode, $options) {
    // Returns TRUE only if we try to create a folder.
    return $this->is_dir($uri);
  }

  /**
   * @param string $uri
   * @return bool
   */
  protected function is_dir($uri) {
    list($scheme, $target) = explode('://', $uri, 2);

    // Check if it's the root directory.
    if (empty($target))
      return TRUE;

    // If not, check if the URI ends with '/' (eg: rokka://foldername/")
    return strrpos($target, '/') === (strlen($target) -1);
  }

  /**
   * Rokka.io has no support for rmdir().
   *
   * @param string $uri
   *   A string containing the URI to the directory to delete.
   * @param int $options
   *   A bit mask of STREAM_REPORT_ERRORS.
   *
   * @return bool
   *   TRUE if the directory was successfully deleted.
   *
   * Always return FALSE. (not supported)
   *
   * @see http://php.net/manual/en/streamwrapper.rmdir.php
   */
  public function rmdir($uri, $options) {
    return FALSE;
  }

  /**
   * Rokka.io has no support for rename().
   *
   * @param string $from_uri
   *   The uri to the file to rename.
   * @param string $to_uri
   *   The new uri for file.
   *
   * @return bool
   *   Always returns FALSE. (not supported)
   *
   * @see http://php.net/manual/en/streamwrapper.rename.php
   */
  public function rename($from_uri, $to_uri) {
    return FALSE;
  }

  /**
   * Rokka.io has no support for opendir().
   *
   * @param string $uri
   *   A string containing the URI to the directory to open.
   * @param int $options
   *   Whether or not to enforce safe_mode (0x04).
   *
   * @return bool
   *   TRUE on success.
   *
   * @see http://php.net/manual/en/streamwrapper.dir-opendir.php
   */
  public function dir_opendir($uri, $options) {
    return $this->is_dir($uri);
  }

  /**
   * Rokka.io has no support for readdir().
   *
   * @return string|bool
   *   The next filename, or FALSE if there are no more files in the directory.
   *
   * Always returns FALSE. (not supported)
   *
   * @see http://php.net/manual/en/streamwrapper.dir-readdir.php
   */
  public function dir_readdir() {
    return FALSE;
  }

  /**
   * Rokka.io has not support for rewinddir().
   *
   * @return bool
   *   TRUE on success.
   *
   * Always returns FALSE. (not supported)
   *
   * @see http://php.net/manual/en/streamwrapper.dir-rewinddir.php
   */
  public function dir_rewinddir() {
    return FALSE;
  }

  /**
   * Rokka.io has no support for closedir().
   *
   * @return bool
   *   TRUE on success.
   *
   * Always returns TRUE. (not supported)
   *
   * @see http://php.net/manual/en/streamwrapper.dir-closedir.php
   */
  public function dir_closedir() {
    return TRUE;
  }

}
