<?php

namespace Drupal\rokka\StyleEffects;

use Rokka\Client\Core\StackOperation;

class EffectImageRotate implements InterfaceEffectImage {

  /**
   * @param $data
   * @return \Rokka\Client\Core\StackOperation;
   */
  public static function buildRokkaStackOperation($data) {
    $options = array(
      // Rokka.io throws 500 if we send an integer value for angle. :(
      'angle' => ''. static::normalizeAngle($data['degrees']),
      'background_color' => $data['bgcolor'],
      // => $data['random'],
    );
    return array(new StackOperation('rotate', $options));
  }

  /**
   * Returns the Angle value in [0-360] interval.
   * @param $angle
   * @return int
   */
  protected static function normalizeAngle($angle) {
    $angle = $angle % 360;
    if ($angle < 0) {
      $angle = 360 + $angle;
    }
    return $angle;
  }
}
