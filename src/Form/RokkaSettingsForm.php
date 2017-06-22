<?php

namespace Drupal\rokka\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Configure Rokka settings for this site.
 */
class RokkaSettingsForm extends ConfigFormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'rokka_admin_settings_form';
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return ['rokka.settings'];
  }

  public function buildForm(array $form, FormStateInterface $form_state) {
//    $form = parent::buildForm($form, $form_state);

    $config = $this->config('rokka.settings');

    $form = array(
      'rokka_service_is_enabled' => array(
        '#title' => $this->t('Enable Rokka.io service'),
        '#description' => $this->t('Enable or disable the Rokka.io integration'),
        '#type' => 'checkbox',
        '#default_value' => $config->get('rokka_service_is_enabled'),
      ),
      'credentials' => array(
        '#type' => 'fieldset',
        '#title' => $this->t('API Credentials'),
        '#description' => $this->t('Enter your Rokka.io API credentials'),
        '#collapsible' => FALSE,

        'rokka_api_key' => array(
          '#title' => $this->t('API Key'),
          '#description' => $this->t('The API Key credential provided by the Rokka.io service'),
          '#type' => 'textfield',
          '#required' => TRUE,
          '#default_value' => $config->get('rokka_api_key'),
        ),
        'rokka_api_secret' => array(
          '#title' => $this->t('API Secret'),
          '#description' => $this->t('The API Secret credential provided by the Rokka.io service'),
          '#type' => 'textfield',
          '#required' => TRUE,
          '#default_value' => $config->get('rokka_api_secret'),
        ),
      ),
      'organization' => array(
        '#type' => 'fieldset',
        '#title' => $this->t('Organization Credentials'),
        '#description' => $this->t('Enter the Organization at Rokka.io'),
        '#collapsible' => FALSE,

        'rokka_organization_name' => array(
          '#title' => $this->t('Organization Name'),
          '#description' => $this->t('The Organization Name given from the Rokka.io service'),
          '#type' => 'textfield',
          '#required' => TRUE,
          '#default_value' => $config->get('rokka_organization_name'),
        )
      ),
    );

    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {

    $values = $form_state->getValues();
    $this->config('rokka.settings')
      ->set('rokka_service_is_enabled', $values['rokka_service_is_enabled'])
      ->set('rokka_api_key', $values['rokka_api_key'])
      ->set('rokka_api_secret', $values['rokka_api_secret'])
      ->set('rokka_organization_name', $values['rokka_organization_name'])
      ->save();

    parent::submitForm($form, $form_state);
  }

}
