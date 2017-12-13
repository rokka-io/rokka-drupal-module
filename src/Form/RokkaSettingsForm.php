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

  /**
   *
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $config = $this->config('rokka.settings');

    $form = [
      'is_enabled' => [
        '#title' => $this->t('Enable Rokka.io service'),
        '#description' => $this->t('Enable or disable the Rokka.io integration'),
        '#type' => 'checkbox',
        '#default_value' => $config->get('is_enabled'),
      ],
      'credentials' => [
        '#type' => 'fieldset',
        '#title' => $this->t('API Credentials'),
        '#description' => $this->t('Enter your Rokka.io API credentials'),
        '#collapsible' => FALSE,

        'api_key' => [
          '#title' => $this->t('API Key'),
          '#description' => $this->t('The API Key credential provided by the Rokka.io service'),
          '#type' => 'textfield',
          '#required' => TRUE,
          '#default_value' => $config->get('api_key'),
        ],
      ],
      'organization' => [
        '#type' => 'fieldset',
        '#title' => $this->t('Organization Credentials'),
        '#description' => $this->t('Enter the Organization at Rokka.io'),
        '#collapsible' => FALSE,

        'organization_name' => [
          '#title' => $this->t('Organization Name'),
          '#description' => $this->t('The Organization Name given from the Rokka.io service'),
          '#type' => 'textfield',
          '#required' => TRUE,
          '#default_value' => $config->get('organization_name'),
        ],
      ],
      'api_endpoint' => [
        '#title' => $this->t('API Endpoint'),
        '#description' => $this->t('The API endpoint'),
        '#type' => 'textfield',
        '#required' => TRUE,
        '#default_value' => $config->get('api_endpoint'),
      ],
      'stack' => [
        '#title' => $this->t('Stack Name Prefix'),
        '#description' => $this->t('Adds a prefix for newly created Rokka stacks. Helps preventing overwriting existing stacks created in the Rokka.io dashboard. '),
        '#type' => 'textfield',
        '#required' => FALSE,
        '#default_value' => $config->get('stack_prefix'),
      ],
    ];

    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $values = $form_state->getValues();
    $config = $this->config('rokka.settings');

    $config->set('is_enabled', $values['is_enabled']);
    $config->set('api_key', $values['api_key']);
    $config->set('api_endpoint', $values['api_endpoint']);
    $config->set('organization_name', $values['organization_name']);
    $config->save();

    parent::submitForm($form, $form_state);
  }

}
