<?php

module_load_include('inc', 'rokka');


/**
 * @param string $name
 * @return bool
 */
function rokka_crud_imagestack_delete($name) {
  if (empty($name) || !rokka_service_is_active()) {
    return FALSE;
  }

  $client = rokka_get_current_client();
  $imageClient = $client->getImageClient();
  try {
    return $imageClient->deleteStack($name);
  } catch (Exception $e) {
    // If the Stack is not found, that means that it is already deleted.
    if (404 === $e->getCode()) {
      return TRUE;
    }
    // If an exception is raised, no ImageStack could be deleted.
    watchdog('rokka', 'Exception while deleting ImageStack "%name" in "%function": error: "%error"', array(
      '%name' => $name,
      '%function' => __FUNCTION__,
      '%error' => $e->getMessage(),
    ));
  }
  return FALSE;
}

/**
 * @param string $name
 * @return bool|null|\Rokka\Client\Core\Stack
 */
function rokka_crud_imagestack_load($name) {
  if (empty($name) || !rokka_service_is_active()) {
    return NULL;
  }

  $client = rokka_get_current_client();
  $imageClient = $client->getImageClient();
  try {
    $stack = $imageClient->getStack($name);
    return $stack;
  } catch (\Exception $e) {
    // If an exception is raised, no ImageStack could be loaded.
    watchdog('rokka', 'Exception while loading ImageStack "%name" in "%function": error: "%error"', array(
      '%name' => $name,
      '%function' => __FUNCTION__,
      '%error' => $e->getMessage(),
    ));
  }
  return NULL;
}

/**
 * @param \Drupal\rokka\RokkaStack $rokkaStackEntity
 * @return bool|\Rokka\Client\Core\Stack
 */
function rokka_crud_imagestack_create(\Drupal\rokka\RokkaStack $rokkaStackEntity) {
  $name = $rokkaStackEntity->getImageStyle();

  $image_style = image_style_load($rokkaStackEntity->getImageStyle());
  $stackOperationCollection = \Drupal\rokka\ImageStyleHelper::buildStackOperationCollection($image_style['effects']);

  // We can not create a StackOperationCollection with no operations, but Drupal can!
  if ($stackOperationCollection) {
    try {
      $client = rokka_get_current_client();
      $stack = $client->getImageClient()->createStack(
        $rokkaStackEntity->getImageStyle(),
        $stackOperationCollection,
        NULL,
        $rokkaStackEntity->getStackOptions()
      );

      if (count($stackOperationCollection) < count($image_style['effects'])) {
        watchdog('rokka', 'Image style "%imagestyle" partially uploaded to Rokka.io"', array('%imagestyle' => $name), WATCHDOG_WARNING);
        drupal_set_message(t('Can not fully convert the style "%style" to Rokka.io: some effects are not available on Rokka.io!', array(
          '%style' => $name,
        )), 'warning');
        return $stack;
      }
      else {
        watchdog('rokka', 'Image style "%imagestyle" uploaded to Rokka.io"', array('%imagestyle' => $name));
        drupal_set_message(t('The style "!imagestyle" has been successfully saved to Rokka.', array(
          '!imagestyle' => l($name, ROKKA_BASE_ADMIN_MENU_URL . '/imagestack/' . $name)
        )));
        return TRUE;
      }
    } catch (\Exception $e) {
      watchdog('rokka', 'Exception while saving ImageStack in "%function": error: "%error"', array(
        '%function' => __FUNCTION__,
        '%error' => $e->getMessage(),
      ));
      drupal_set_message(t('Error while saving the image style "!imagestyle" to Rokka.io, please contact your site administrator.', array(
        '!imagestyle' => l($name, ROKKA_BASE_ADMIN_MENU_URL . '/imagestack/' . $name)
      )), 'error');
      return FALSE;
    }
  }
  if (!$stackOperationCollection && !empty($image_style['effects'])) {
    drupal_set_message(t('Can not convert the style "!style" to Rokka.io: all of the image-effects are not available on Rokka.io!', array(
      '!style' => l($name, 'admin/config/media/image-styles/edit/'. $name),
    )), 'error');
  }
  return FALSE;
}

/**
 * Helper function, avoid getting 404 Exceptions.
 * @param bool $raiseError Returns the underlying exception (default: true)
 * @return \Rokka\Client\Core\Stack[]
 */
function rokka_crud_imagestack_list($raiseError = TRUE) {
  $stacks = array();
  try {
    $rokkaClient = rokka_get_current_client();
    $imageClient = $rokkaClient->getImageClient();

    $stackCollection = $imageClient->listStacks();
    $stacks = $stackCollection->getStacks();
  } catch (\Exception $e) {
    // 404 returned when no ImageStacks are available (WTF!), thus it's not an error.
    if ($raiseError && 404 !== $e->getCode()) {
      return $e;
    }
  }
  return $stacks;
}

/**
 * Helper function to refresh all the active Image Styles to Rokka.io
 */
function rokka_crud_imagestack_rebuild_all() {
  if (!rokka_service_is_active()) {
    return;
  }

  $stacks = rokka_crud_imagestack_list(FALSE);
  foreach ($stacks as $stack) {
    rokka_crud_imagestack_delete($stack->getName());
  }

  $styles = image_styles();
  foreach ($styles as $style_name => $style) {

    /** @var \Drupal\rokka\RokkaStack $stack */
    $stack = entity_load_single('rokka_stack', $style_name);
    if (!$stack) {
      $stack = entity_create('rokka_stack', array());
      $stack->setImageStyle($style_name);
      $stack->save();
    }

    rokka_crud_imagestack_create($stack);
  }
}
