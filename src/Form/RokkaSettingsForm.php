<?php

namespace Drupal\rokka\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Configure Rokka settings for this site.
 */
class RokkaSettingsForm extends ConfigFormBase
{
    /**
     * {@inheritdoc}
     */
    public function getFormId()
    {
        return 'rokka_admin_settings_form';
    }

    /**
     * {@inheritdoc}
     */
    protected function getEditableConfigNames()
    {
        return ['rokka.settings'];
    }

    public function buildForm(array $form, FormStateInterface $form_state)
    {
        $config = $this->config('rokka.settings');

        $form = array(
            'is_enabled' => array(
                '#title' => $this->t('Enable Rokka.io service'),
                '#description' => $this->t('Enable or disable the Rokka.io integration'),
                '#type' => 'checkbox',
                '#default_value' => $config->get('is_enabled'),
            ),
            'credentials' => array(
                '#type' => 'fieldset',
                '#title' => $this->t('API Credentials'),
                '#description' => $this->t('Enter your Rokka.io API credentials'),
                '#collapsible' => false,

                'api_key' => array(
                    '#title' => $this->t('API Key'),
                    '#description' => $this->t('The API Key credential provided by the Rokka.io service'),
                    '#type' => 'textfield',
                    '#required' => true,
                    '#default_value' => $config->get('api_key'),
                ),
            ),
            'organization' => array(
                '#type' => 'fieldset',
                '#title' => $this->t('Organization Credentials'),
                '#description' => $this->t('Enter the Organization at Rokka.io'),
                '#collapsible' => false,

                'organization_name' => array(
                    '#title' => $this->t('Organization Name'),
                    '#description' => $this->t('The Organization Name given from the Rokka.io service'),
                    '#type' => 'textfield',
                    '#required' => true,
                    '#default_value' => $config->get('organization_name'),
                ),
            ),
            'api_endpoint' => array(
                '#title' => $this->t('API Endpoint'),
                '#description' => $this->t('The API endpoint'),
                '#type' => 'textfield',
                '#required' => true,
                '#default_value' => $config->get('api_endpoint'),
            ),
        );

        return parent::buildForm($form, $form_state);
    }

    /**
     * {@inheritdoc}
     */
    public function submitForm(array &$form, FormStateInterface $form_state)
    {
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
