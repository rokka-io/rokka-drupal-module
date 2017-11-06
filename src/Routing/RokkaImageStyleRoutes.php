<?php

namespace Drupal\rokka\Routing;

use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\rokka\Controller\RokkaImageStyleDownloadController;
use Drupal\rokka\PathProcessor\RokkaPathProcessorImageStyles;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Routing\Route;

/**
 * Defines a route subscriber to register a url for serving image styles.
 */
class RokkaImageStyleRoutes implements ContainerInjectionInterface {

  /**
   * The module handler.
   *
   * @var \Drupal\Core\Extension\ModuleHandlerInterface
   */
  protected $moduleHandler;

  /**
   * Constructs a new RokkaImageStyleRoutes object.
   *
   * @param \Drupal\Core\Extension\ModuleHandlerInterface $module_handler
   *   The module handler.
   */
  public function __construct(ModuleHandlerInterface $module_handler) {
    $this->moduleHandler = $module_handler;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('module_handler')
    );
  }

  /**
   * Returns an array of route objects.
   *
   * @return \Symfony\Component\Routing\Route[]
   *   An array of route objects.
   */
  public function routes() {
    $routes = [];
    // Only add route for image styles if image module is enabled.
    if ($this->moduleHandler->moduleExists('image')) {
      $routes['rokka.image_styles'] = new Route(
        RokkaPathProcessorImageStyles::IMAGE_STYLE_PATH_PREFIX . '/{image_style}/{scheme}',
        [
          '_controller' => RokkaImageStyleDownloadController::class . '::deliver',
        ],
        [
          '_access' => 'TRUE',
        ]
      );
    }

    return $routes;
  }

}
