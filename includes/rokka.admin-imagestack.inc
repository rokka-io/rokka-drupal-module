<?php

use \Rokka\Client\Core\Stack;

/**
 * @return string|void
 */
function rokka_admin_imagestack_overview() {
  module_load_include('inc', 'rokka', 'includes/rokka.imagestack.crud');

  $output = '';

  if (!rokka_service_is_active()) {
    drupal_set_message(t('The Rokka.io module <a href="@rokka-configure">needs to be enabled</a> before accessing the Image-Stacks.', array(
      '@rokka-configure' => url(ROKKA_BASE_ADMIN_MENU_URL))));
    return $output;
  }
  if (200 !== rokka_validate_credentials()) {
    drupal_set_message(t('The Rokka.io module <a href="@rokka-configure">is not properly configured</a>.', array(
      '@rokka-configure' => url(ROKKA_BASE_ADMIN_MENU_URL))));
    return $output;
  }

  $header = array('Stack Name', 'Organization', 'Created', 'Stack Operations');
  $rows = array();

  $stacks = rokka_crud_imagestack_list();
  if (is_array(($stacks))) {
    foreach ($stacks as $stack) {
      $rows[] = array(
        // Stack name and edit link for Drupal
        l($stack->getName(), ROKKA_BASE_ADMIN_MENU_URL . '/imagestack/' . $stack->getName()) .
        ' (' . l(t('edit'), 'admin/config/media/image-styles/edit/' . $stack->getName()) . ')'
      ,
        $stack->getOrganization(),
        format_date($stack->getCreated()->getTimestamp(), 'long'),
        count($stack->getStackOperations()),
      );
    }
  }
  elseif ($stacks instanceof Exception) {
    drupal_set_message(t('Error while retrieving Rokka.io ImagesStack [%errorcode: "%errormessage"]', array(
      '%errorcode' => $stacks->getCode(),
      '%errormessage' => $stacks->getMessage(),
    )), 'error');
  }

  $output = theme('table', array(
    'header' => $header,
    'rows'   => $rows,
    'empty'  => t('No Image-Stack found on Rokka.io service.'),
  ));

  // Adding adminsitration form, for stack operations
  $form = drupal_get_form('rokka_admin_imagestack_operations_form');
  $output .= drupal_render($form);

  return $output;
}

/**
 * @param $form
 * @param $form_state
 * @return array
 */
function rokka_admin_imagestack_operations_form($form, $form_state) {
  $form = array();

  $form['rebuild'] = array(
    '#type' => 'submit',
    '#value' => t('Rebuild image stacks'),
  );

  return $form;
}

/**
 * @param $form
 * @param $form_state
 */
function rokka_admin_imagestack_operations_form_submit($form, $form_state) {
  switch($form_state['values']['op']) {
    case t('Rebuild image stacks'):
      module_load_include('inc', 'rokka', 'includes/rokka.imagestack.crud');
      rokka_crud_imagestack_rebuild_all();
      break;
  }
}

/**
 * @param Stack $stack
 * @return string
 */
function rokka_admin_imagestack_item(Stack $stack) {
  module_load_include('inc', 'rokka');

  $header = array('option', 'value');
  $rows = array();
  foreach($stack->getStackOptions() as $option => $value) {
    $rows[] = array(
      $option,
      $value
    );
  }
  $output = theme('table', array(
    'header' => $header,
    'rows'   => $rows,
    'empty'  => t('No Operations found on Rokka.io service for this ImageStack.'),
  ));


  $header = array('operation', 'options');
  $rows = array();
  foreach($stack->getStackOperations() as $operation) {
    $rows[] = array(
      ucfirst($operation->name),
      json_encode($operation->options),
    );
  }
  $output .= theme('table', array(
    'header' => $header,
    'rows'   => $rows,
    'empty'  => t('No Operations found on Rokka.io service for this ImageStack.'),
  ));

  return $output;
}
