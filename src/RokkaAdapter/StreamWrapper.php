<?php
/**
 * @class RokkaStreamWrapper
 *
 * This is a low-level stream implementation for the "rokka://" wrapper handler,
 * only stream read/write functions are defined here, thus leaving all the
 * folder and file stat functions to be implemented in child classes.
 *
 * Further notices:
 *  1. Since no concept of directories exists, all the directory related functions
 *   are defined as 'abstract', thus requiring children classes to be implement
 *   a filesystem virtualization.
 *
 * 2. This wrapper's support for uri_stat() function, stream_read() and
 *   stream_seek() is dependent to the availability of the Rokka.io file HASH.
 *   To fully support such feature this class must extended and the following
 *   function implemented: "buildHashFromUri($uri)".
 *
 * To further support the management of Rokka.io HASHes, the following functions
 * must be implemented to keep track of the URI/HASH mappings:
 *
 *  - doGetMetadataFromUri($uri)
 *      Used to retrieve, from a given uri, the related SourceImageMetadata object
 *  - doPostSourceImageDeleted(SourceImageMetadata $meta)
 *      This method is invoked after a Rokka image file get removed, useful to
 *      keep track of successfully removed images
 *  - doPostSourceImageSaved(ImageSource $source)
 *      This method is invoked afer a Rokka image file is successfully uploaded
 *      to Rokka, the $source objects contains the associated SourceImageMedadata
 */

namespace Drupal\rokka\RokkaAdapter;

use GuzzleHttp\Psr7\CachingStream;
use GuzzleHttp\Psr7\Stream;
use Rokka\Client\Core\SourceImage;
use Rokka\Client\Image;

abstract class StreamWrapper
{

  /** @var Stream $body */
  protected $body;

  /** @var Image */
  protected static $imageClient;

  /** @var string */
  protected $uri;

  /** @var string */
  protected $mode;

    public static $supportedModes = array('w', 'r');

  /**
   * @param \Rokka\Client\Image $imageClient
   */
  public function __construct(Image $imageClient)
  {
      static::$imageClient = $imageClient;
  }

  /**
   * @param SourceImage $sourceImage
   * @return bool
   */
  abstract protected function doPostSourceImageSaved(SourceImage $sourceImage);

  /**
   * @param SourceImageMetadata $meta
   * @return bool
   */
  abstract protected function doPostSourceImageDeleted(SourceImageMetadata $meta);

  /**
   * @param $uri
   * @return SourceImageMetadata
   */
  abstract protected function doGetMetadataFromUri($uri);

  /**
   * Support for stat().
   *
   * This important function goes back to the Unix way of doing things.
   * In this example almost the entire stat array is irrelevant, but the
   * mode is very important. It tells PHP whether we have a file or a
   * directory and what the permissions are. All that is packed up in a
   * bitmask. This is not normal PHP fodder.
   *
   * @param string $uri
   *   A string containing the URI to get information about.
   * @param int $flags
   *   A bit mask of STREAM_URL_STAT_LINK and STREAM_URL_STAT_QUIET.
   *
   * @return array|bool
   *   An array with file status, or FALSE in case of an error - see fstat()
   *   for a description of this array.
   *
   * Use the formatUrlStat() function as an helper for return values.
   *
   * @see http://php.net/manual/en/streamwrapper.url-stat.php
   */
  abstract public function url_stat($uri, $flags);

  /**
   * Implements setUri().
   */
  public function setUri($uri)
  {
      $this->uri = $uri;
  }

  /**
   * Implements getUri().
   */
  public function getUri()
  {
      return $this->uri;
  }

  /**
   * Close the stream
   */
  public function stream_close()
  {
      if ($this->body) {
          $this->body->close();
          $this->body = null;
          return true;
      }

      return false;
  }

  /**
   * @param string $path
   * @param string $mode
   * @param array $options
   * @param string $opened_path
   *
   * @return bool
   */
  public function stream_open($path, $mode, $options, &$opened_path)
  {
      $this->uri = $path;

    // We don't care about the binary flag
    $this->mode = rtrim($mode, 'bt');

    // $this->params = $params = $this->getParams($path);
    $exceptions = array();
      if (strpos($this->mode, '+')) {
          $exceptions[] =  new \LogicException('The RokkaStreamWrapper does not support simultaneous reading and writing (mode: {'.$this->mode.'}).');
      }
      if (!in_array($this->mode, static::$supportedModes)) {
          $exceptions[] = new \LogicException('Mode not supported: {'.$this->mode.'}. Use one "r", "w".', 400);
      }

      $ret = null;
      if (empty($exceptions)) {
          // This stream is Write-Only since the stream is not reversible for Read
      // and Write operations from the same filename: to read from a previously
      // written filename, the HASH must be provided.
      if ('w' == $this->mode) {
          $ret = $this->openWriteStream($options, $exceptions);
      }

          if ('r' == $this->mode) {
              $ret = $this->openReadStream($options, $exceptions);
          }
      }

      if (!empty($exceptions)) {
          return $this->triggerException($exceptions);
      }

      return $ret;
  }

  /**
   * Write data the to the stream
   *
   * @param string $data
   *
   * @return int Returns the number of bytes written to the stream
   */
  public function stream_write($data)
  {
      return $this->body->write($data);
  }

  /**
   * @return bool
   */
  public function stream_eof()
  {
      return $this->body->eof();
  }

  /**
   * Support for ftell().
   *
   * @return int
   *   The current offset in bytes from the beginning of file.
   *
   * @see http://php.net/manual/en/streamwrapper.stream-tell.php
   */
  public function stream_tell()
  {
      return $this->body->tell();
  }

  /**
   * Support for fflush().
   *
   * @return bool
   *   TRUE if data was successfully stored (or there was no data to store).
   */
  public function stream_flush()
  {
      if ('r' == $this->mode) {
          // Read only Streams can not be flushed, just return true.
      return true;
      }
      $this->body->rewind();
      try {
          $imageCollection = static::$imageClient->uploadSourceImage(
        $this->body->getContents(),
        basename($this->uri)
      );

          if (1 !== $imageCollection->count()) {
              $exception = new \LogicException('RokkaStreamWrapper: No SourceImage data returned after invoking uploadSourceImage()!', 404);
              return $this->triggerException($exception);
          }

      /** @var SourceImage $image */
      $image = reset($imageCollection->getSourceImages());
          $image->size = $this->body->getSize();

      // Invoking Post-Save callback
      return $this->doPostSourceImageSaved($image);
      } catch (\Exception $e) {
          $this->body = null;
          return $this->triggerException($e);
      }
  }

  /**
   * Support for fstat().
   *
   * @return array
   *   An array with file status, or FALSE in case of an error - see fstat()
   *   for a description of this array.
   *
   * @see http://php.net/manual/en/streamwrapper.stream-stat.php
   */
  public function stream_stat()
  {
      return array(
      'size' => $this->body->getSize(),
    );
  }

  /**
   * Initialize the stream wrapper for a write only stream.
   *
   * @param array $params Operation parameters
   * @param array $errors Any encountered errors to append to
   *
   * @return bool
   */
  protected function openWriteStream($params, &$errors)
  {
      // We must check HERE if the underlying connection to Rokka is working fine
    // instead of returning FALSE during stream_flush() and stream_close() if
    // Rokka service is not available.
    // Reason: The PHP core, in the "_php_stream_copy_to_stream_ex()" function, is
    // not checking if the stream contents got successfully written after the
    // source and destination streams have been opened.
    try {
        // @todo: Using listStack() invocation to check if Rokka is still alive,
      // but we must use a better API invocation here!
      self::$imageClient->listStacks(1);
        $this->body = new Stream(fopen('php://temp', 'r+'));
        return true;
    } catch (\Exception $e) {
        $errors[] = $e;
        return $this->triggerException($errors);
    }
  }

  /**
   * Initialize the stream wrapper for a read only stream.
   *
   * @param array $params Operation parameters
   * @param array $errors Any encountered errors to append to
   *
   * @return bool
   */
  protected function openReadStream($params, &$errors)
  {
      $meta = $this->doGetMetadataFromUri($this->uri);
      if (empty($meta)) {
          $errors[] = new \LogicException('Unable to determine the Rokka.io HASH for the current URI.', 404);
          return $this->triggerException($errors);
      }

      try {
          // Reading the original source image contents and saving it in a memory-temp
      // file to be able to create a Stream from it (the source image file can't
      // be accessed directly via a specific URL).
      $sourceStream = fopen('php://temp', 'r+');
          fwrite($sourceStream, self::$imageClient->getSourceImageContents($meta->getHash()));
          rewind($sourceStream);

          $this->body = new Stream($sourceStream, 'rb');

      // Wrap the body in a caching entity body if seeking is allowed
      if (!$this->body->isSeekable()) {
          $this->body = new CachingStream($this->body);
      }
      } catch (\Exception $e) {
          $errors[] = $e;
          return $this->triggerException($errors);
      }
      return true;
  }

  /**
   * Support for unlink().
   *
   * @param string $uri
   *   A string containing the uri to the resource to delete.
   *
   * @return bool
   *   TRUE if resource was successfully deleted.
   *
   * @see http://php.net/manual/en/streamwrapper.unlink.php
   */
  public function unlink($uri)
  {
      $meta = $this->doGetMetadataFromUri($uri);

      if (!$meta || empty($meta->getHash())) {
          $exception = new \LogicException('Unable to determine the Rokka.io HASH for the current URI.', 404);
          return $this->triggerException($exception);
      }
      try {
          return self::$imageClient->deleteSourceImage($meta->getHash())
        && $this->doPostSourceImageDeleted($meta);
      } catch (\Exception $e) {
          return $this->triggerException($e, STREAM_URL_STAT_QUIET);
      }
  }

  /**
   * Support for flock().
   *
   * The Rokka.io service has no locking capability, so return TRUE.
   *
   * @return bool
   *   Always returns TRUE at the present time. (not supported)
   */
  public function stream_lock($operation)
  {
      return true;
  }

  /**
   * Read data from the underlying stream.
   *
   * @param int $count Amount of bytes to read
   *
   * @return string
   *   Always returns FALSE. (not supported)
   */
  public function stream_read($count)
  {
      if ('r' == $this->mode) {
          return $this->body->read($count);
      }
      return false;
  }

  /**
   * Seek to a specific byte in the stream
   *
   * @param int $offset Seek offset
   * @param int $whence Whence (SEEK_SET, SEEK_CUR, SEEK_END)
   *
   * @return bool
   *  Always returns FALSE. (not supported)
   */
  public function stream_seek($offset, $whence = SEEK_SET)
  {
      if ($this->body->isSeekable()) {
          $this->body->seek($offset, $whence);
          return true;
      }
      return false;
  }

  /**
   * Rokka.io has no support for chmod().
   *
   * @param string $mode
   *   A string containing the new mode for the resource.
   *
   * @return bool
   *   TRUE if resource permissions were successfully modified.
   *
   * Always returns TRUE. (not supported)
   *
   * @see http://php.net/manual/en/streamwrapper.chmod.php
   */
  public function chmod($mode)
  {
      return true;
  }

  /**
   * Trigger one or more errors
   *
   * @param \Exception|\Exception[] $exceptions
   * @param mixed                   $flags  If set to STREAM_URL_STAT_QUIET, then no error or exception occurs
   * @return bool
   */
  protected function triggerException($exceptions, $flags = null)
  {
      if ($flags & STREAM_URL_STAT_QUIET) {
          // This is triggered with things like file_exists()
      if ($flags & STREAM_URL_STAT_LINK) {
          // This is triggered for things like is_link()
        // return $this->formatUrlStat(false);
      }
          return false;
      }

      $exceptions = is_array($exceptions) ? $exceptions : array($exceptions);
      $messages = array();
    /** @var \Exception $exception */
    foreach ($exceptions as $exception) {
        $messages[] = $exception->getMessage();
    }

      trigger_error(implode("\n", $messages), E_USER_WARNING);
      return false;
  }

  /**
   * Helper function to prepare a url_stat result array.
   * All files and folders will be returned with 0777 permission.
   *
   * @param string|array $result Data to add
   *  - Null or String for Folders
   *  - Array for Files with the following keyed values:
   *    - 'timestamp': the creation/modification timestamp
   *    - 'filesize': the file dimensions
   *
   * @return array Returns the modified url_stat result
   */
  protected function formatUrlStat($result = null)
  {
      static $statTemplate = array(
      0  => 0,  'dev'     => 0,
      1  => 0,  'ino'     => 0,
      2  => 0,  'mode'    => 0,
      3  => 0,  'nlink'   => 0,
      4  => 0,  'uid'     => 0,
      5  => 0,  'gid'     => 0,
      6  => -1, 'rdev'    => -1,
      7  => 0,  'size'    => 0,
      8  => 0,  'atime'   => 0,
      9  => 0,  'mtime'   => 0,
      10 => 0,  'ctime'   => 0,
      11 => -1, 'blksize' => -1,
      12 => -1, 'blocks'  => -1,
    );
      $stat = $statTemplate;
      $type = gettype($result);
    // Determine what type of data is being cached
    if ($type == 'NULL' || $type == 'string') {
        // Directory with 0777 access - see "man 2 stat".
      $stat['mode'] = $stat[2] = 0040777;
    } elseif ($type == 'array' && isset($result['timestamp'])) {
        // ListObjects or HeadObject result
      $stat['mtime'] = $stat[9] = $stat['ctime'] = $stat[10] = $result['timestamp'];
      // $stat['atime'] = $stat[8] = $result['timestamp'];
      $stat['size'] = $stat[7] = $result['filesize'];
      // Regular file with 0777 access - see "man 2 stat".
      $stat['mode'] = $stat[2] = 0100777;
    }
      return $stat;
  }
}
