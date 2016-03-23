<?php


/**
 * @return string|void
 */
function rokka_admin_sourceimages_overview() {
  module_load_include('inc', 'rokka');

  $output = '';

  if (!rokka_service_is_active()) {
    drupal_set_message(t('The Rokka.io module <a href="@rokka-configure">needs to be enabled</a> before accessing the Images list.', array(
      '@rokka-configure' => url(ROKKA_BASE_ADMIN_MENU_URL))));
    return $output;
  }
  if (200 !== rokka_validate_credentials()) {
    drupal_set_message(t('The Rokka.io module <a href="@rokka-configure">is not properly configured</a>.', array(
      '@rokka-configure' => url(ROKKA_BASE_ADMIN_MENU_URL))));
    return $output;
  }

  $rokkaClient = rokka_get_current_client();
  $imageClient = $rokkaClient->getImageClient();
  $defaultStyle = variable_get('rokka_source_image_style', 'rokka_source');

  $header = array('hash', 'organization', 'created', 'link');
  $rows = array();

  try {
    $images = $imageClient->listSourceImages();
    foreach($images->getSourceImages() as $image) {
      $rows[] = array(
        // @todo: remove the default style as soon as the getSourceImageUri
        // will support the 'empty' image style to get the original source image
        l($image->hash, $imageClient->getSourceImageUri($image->hash, $defaultStyle, 'jpg')->__toString()),
        $image->organization,
        format_date($image->created->getTimestamp(), 'long'),
        $image->link,
      );
    }

  } catch (Exception $e) {
    // 404 returned when no SourceImages are available (?), thus it's not an error.
    if (404 !== $e->getCode()) {
      drupal_set_message(t('Error while retrieving Rokka.io SourceImages [%errorcode: "%errormessage"]', array(
        '%errorcode' => $e->getCode(),
        '%errormessage' => $e->getMessage(),
      )), 'error');
    }
  }

  $output = theme('table', array(
    'header' => $header,
    'rows'   => $rows,
    'empty'  => t('No SourceImages found on Rokka.io service.'),
  ));

  return $output;
}
