<?php

/**
 * @file
 * Administration page callbacks for the Rokka module.
 */


/**
 * Admin settings for Rokka.io credentials.
 */
function rokka_admin_settings_form($form, $form_state) {
  form_load_include($form_state, 'inc', 'rokka');

  $organization = NULL;
  if (variable_get('rokka_service_is_enabled', FALSE)) {
    $errorCode = rokka_validate_credentials();
    if (200 == $errorCode) {
      /** @var \Rokka\Client\Core\Organization $organization */
      $organization = rokka_get_current_client()->getOrganization();
    }
    elseif (empty($form_state['input'])) {
      drupal_set_message(t('Error validating your Rokka.io credentials [error #%errorcode]. Please verify the provided data.', array(
        '%errorcode' => $errorCode,
      )), 'error');
    }
  }

  $form = array(
    'rokka_service_is_enabled' => array(
      '#title' => t('Enable Rokka.io service'),
      '#description' => t('Enable or disable the Rokka.io integration'),
      '#type' => 'checkbox',
      '#default_value' => variable_get('rokka_service_is_enabled', FALSE),
    ),
    'credentials' => array(
      '#type' => 'fieldset',
      '#title' => t('API Credentials'),
      '#description' => t('Enter your Rokka.io API credentials'),
      '#collapsible' => FALSE,

      'rokka_api_key' => array(
        '#title' => t('API Key'),
        '#description' => t('The API Key credential provided by the Rokka.io service'),
        '#type' => 'textfield',
        '#required' => TRUE,
        '#default_value' => variable_get('rokka_api_key', NULL),
      ),
      'rokka_api_secret' => array(
        '#title' => t('API Secret'),
        '#description' => t('The API Secret credential provided by the Rokka.io service'),
        '#type' => 'textfield',
        '#required' => TRUE,
        '#default_value' => variable_get('rokka_api_secret', NULL),
      ),
    ),
    'organization' => array(
      '#type' => 'fieldset',
      '#title' => t('Organization Credentials'),
      '#description' => t('Enter the Organization at Rokka.io'),
      '#collapsible' => FALSE,

      'rokka_organization_name' => array(
        '#title' => t('Organization Name'),
        '#description' => t('The Organization Name given from the Rokka.io service'),
        '#type' => 'textfield',
        '#required' => TRUE,
        '#default_value' => variable_get('rokka_organization_name', NULL),
      )
    ),
  );

  $formats = \Drupal\rokka\Client::getFileFormats();
  foreach ($formats as &$format) {
    $format = t($format);
  }
  $form['image-settings'] = array(
    '#type' => 'fieldset',
    '#title' => t('Image Settings'),
    '#description' => t('Default image settings'),
    '#collapsible' => FALSE,

    'rokka_use_hash_as_name' => array(
      '#title' => t('Use Rokka HASH as filename on Rokka.io'),
      '#description' => t('Output Rokka.io URLs by using the HASH as filename. If not checked, the URI will also include the image filename.'),
      '#type' => 'checkbox',
      '#default_value' => variable_get('rokka_use_hash_as_name', TRUE),
    ),

    'rokka_default_image_format' => array(
      '#title' => t('Default image format'),
      '#description' => t('Default image format for rendering images'),
      '#type' => 'select',
      '#options' => $formats,
      '#default_value' => variable_get('rokka_default_image_format', 'jpg'),
    ),
  );
  if (!empty($organization)) {
    $form['organization']['display'] = array(
      '#markup' => t('Organization: %displayname', array('%displayname' => $organization->getDisplayName())) . '<br />',
    );
    $form['organization']['id'] = array(
      '#markup' => t('Organization id: %id', array('%id' => $organization->getId())) . '<br />',
    );
    $form['organization']['billing'] = array(
      '#markup' => t('Organization email: %email', array('%email' => $organization->getBillingEmail())) . '<br />',
    );
  }

  $styles = image_style_options(FALSE, PASS_THROUGH);
  $form['rokka_source_image_style'] = array(
    '#title' => t('Default Image Style'),
    '#description' => t('The image style requested to Rokka.io when loading the original uploaded image.'),
    '#type' => 'select',
    '#options' => $styles,
    '#default_value' => variable_get('rokka_source_image_style', 'rokka_source'),
  );

  $form['rokka_api_base_url'] = array(
    '#title' => t('Rokka.io API Uri'),
    '#description' => t('The Rokka.io API URL. Change this value at your own risk. Default value: %default', array('%default' => \Rokka\Client\Base::DEFAULT_API_BASE_URL)),
    '#type' => 'textfield',
    '#required' => TRUE,
    '#default_value' => variable_get('rokka_api_base_url', \Rokka\Client\Base::DEFAULT_API_BASE_URL),
  );

  $form['rokka_client_version'] = array(
    '#markup' => t('Rokka PHPClient API Version: %version', array('%version' => Rokka\Client\Base::DEFAULT_API_VERSION)),
  );

  $form = system_settings_form($form);

  $form['actions']['save_and_refresh_styles'] = array(
    '#type' => 'submit',
    '#value' => t('Save and refresh Image Styles'),
  );

  // Our submit must run *after* the system one to let variable_set() fill the values.
  $form['#submit'][] = 'rokka_admin_settings_form_submit';
  return $form;
}

/**
 * Validation of Rokka.io credentials.
 */
function rokka_admin_settings_form_validate($form, $form_state) {
  $values = $form_state['values'];
  $enabled = $values['rokka_service_is_enabled'];

  if (FALSE == $enabled) {
    return;
  }

  $data = array(
    'api_base_url'      => $values['rokka_api_base_url'],
    'organization_name' => $values['rokka_organization_name'],
    'api_key'           => $values['rokka_api_key'],
    'api_secret'        => $values['rokka_api_secret'],
  );

  // Skip this check, as it will be done by the system_settings validation.
  if (empty($data['organization_name']) || empty($data['api_key']) || empty($data['api_secret']) || empty($data['api_base_url'])) {
    return;
  }

  $returncode = rokka_validate_credentials($data);
  if (200 !== $returncode) {
    drupal_set_message(t('Error validating your Rokka.io credentials [error #%errorcode]. Please verify the provided data.', array(
      '%errorcode' => $returncode,
    )), 'error');

    switch($returncode) {
      case 403:
        form_set_error('rokka_api_key');
        form_set_error('rokka_api_secret');
        break;
      case 404:
        form_set_error('rokka_organization_name');
        break;
      default:
        form_set_error('rokka_api_base_url');
    }
  }
}

/**
 * @param $form
 * @param $form_state
 */
function rokka_admin_settings_form_submit($form, &$form_state){
  module_load_include('inc', 'rokka', 'includes/rokka.imagestack.crud');

  if ($form_state['clicked_button'] && $form_state['clicked_button']['#value'] == t('Save and refresh Image Styles')) {
    // refresh the cached client.
    rokka_get_current_client(TRUE);
    // Rebuild the ImageStacks based on the available Image-Styles.
    rokka_crud_imagestack_rebuild_all();
  }
}
