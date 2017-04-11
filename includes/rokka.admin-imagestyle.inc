<?php
/**
 * @file Helper functions for Drupal ImageStyle form handlers.
 */

function _rokka_add_imagestyle_form_elements(&$form, $form_state) {

  /** @var \Drupal\rokka\RokkaStack $stack */
  $stack = FALSE;
  if (isset($form_state['image_style'])) {
    $stack = entity_load_single('rokka_stack', $form_state['image_style']['name']);
  }

  if (!$stack) {
    $stack = entity_create('rokka_stack', array());
  }

  $form['rokka_stack_options'] = array(
    '#type' => 'fieldset',
    '#title' => 'Rokka Stack settings',
    '#description' => t('Settings for the Rokka Stack creation'),
    '#collapsible' => TRUE,
    '#collapsed' => FALSE,
    '#tree' => TRUE,
  );

  $form['rokka_stack_options']['rokka_jpg_quality'] = array(
    '#type' => 'textfield',
    '#title' => t('JPG quality'),
    '#description' => t('JPEG Quality: from 1 (high compression, low quality) to 100 (low compression, high quality)'),
    '#size' => 20,
    '#maxlength' => 3,
    '#required' => FALSE,
    '#default_value' => $stack->getJpgQuality(),
    '#element_validate' => array('element_validate_integer_positive'),
  );

  $form['rokka_stack_options']['rokka_png_compression_level'] = array(
    '#type' => 'textfield',
    '#title' => t('PNG Compression'),
    '#description' => t('PNG compression level: from 1 (low compression, fast) to 9 (high compression, slow). Image quality is the exact same, no matter which compression is taken.'),
    '#size' => 20,
    '#maxlength' => 1,
    '#required' => FALSE,
    '#default_value' => $stack->getPngCompressionLevel(),
    '#element_validate' => array('element_validate_integer_positive'),
  );
  
  $interlacing_modes = array(
    '' => t('Auto'),
    'none' => t('None'),
    'line' => t('Line'),
    'plane' => t('Plane'),
    'partition' => t('Partition')
  );
  $form['rokka_stack_options']['rokka_interlacing_mode'] = array(
    '#type' => 'select',
    '#title' => t('Interlacing Mode'),
    '#description' => t(''),
    '#options' => $interlacing_modes,
    '#required' => FALSE,
    '#default_value' => $stack->getInterlacingMode()
  );

  $form['#validate'][] ='rokka_image_style_form_validate';

  // Let our submit function be executed before the Image Style callbacks for
  // both "Submit" or "Override".
  array_unshift($form['#submit'], 'rokka_image_style_form_submit');

  // Handle the "override" form button, if available.
  if (isset($form['actions']['override']['#submit'])) {
    array_unshift($form['actions']['override']['#submit'], 'rokka_image_style_form_submit');
  }

  // Move the submit button to the bottom.
  $form['submit']['#weight'] = 10;
}

/**
 * Function callback for image_style_form validate.
 */
function rokka_image_style_form_validate(&$form, &$form_state) {
  $values = $form_state['values'];
  if (!empty($values['rokka_jpg_quality']) && ($values['rokka_jpg_quality'] > 100)) {
    form_set_error('rokka_jpg_quality', t('JPG quality must be an integer value between 0 and 100'));
  }
  if (!empty($values['rokka_png_compresion_level']) && ($values['rokka_png_compresion_level'] > 9)) {
    form_set_error('rokka_png_compresion_level', t('PNG compression level must be an integer value between 0 and 9'));
  }
}

/**
 * * Function callback for image_style_form submit.
 */
function rokka_image_style_form_submit($form, $form_state) {
  $values = $form_state['values'];
  $stack_name = $values['name'];

  /** @var \Drupal\rokka\RokkaStack $stack */
  $stack = entity_load_single('rokka_stack', $stack_name);
  if (!$stack) {
    $stack = entity_create('rokka_stack', array());
    $stack->setImageStyle($stack_name);
  }

  $stack->setJpgQuality($values['rokka_stack_options']['rokka_jpg_quality']);
  $stack->setPngCompressionLevel($values['rokka_stack_options']['rokka_png_compression_level']);
  $stack->setInterlacingMode($values['rokka_stack_options']['rokka_interlacing_mode']);
  $stack->save();
}
