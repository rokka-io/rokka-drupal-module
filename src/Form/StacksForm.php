<?php

namespace Drupal\rokka\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Site\Settings;
use Drupal\Core\StreamWrapper\StreamWrapperInterface;
use Symfony\Component\HttpFoundation\Request;

/**
 * Defines an actions form.
 */
class StacksForm extends FormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'rokka_admin_stacks_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state, Request $request = NULL) {
    $form['validate_configuration'] = [
      '#type' => 'fieldset',
      '#description' => $this->t(
        "To validate current S3fs configuration include configuration inside settings.php file."
      ),
      '#title' => $this->t('Validate configuration'),
    ];

    $form['validate_configuration']['validate'] = [
      '#type' => 'submit',
      '#value' => $this->t('Validate'),
      '#validate' => [
        [$this, 'validateConfigValidateForm'],
      ],
      '#submit' => [
        [$this, 'validateConfigSubmitForm'],
      ],
    ];

    if (Settings::get('file_private_path')) {
      $form['copy_local']['private'] = [
        '#type' => 'submit',
        '#prefix' => '<br>',
        '#name' => 'private',
        '#value' => $this->t('Copy local private files to S3'),
        '#validate' => [
          [$this, 'copyLocalValidateForm'],
        ],
        '#submit' => [
          [$this, 'copyLocalSubmitForm'],
        ],
      ];
    }

    return $form;
  }

  /**
   * Validate current configuration.
   *
   * @param array $form
   *   Array that contains the structure of the form.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The current state of the form.
   */
  public function validateConfigValidateForm(array &$form, FormStateInterface $form_state) {
    $config = \Drupal::config('s3fs.settings')->get();
    if (!\Drupal::service('s3fs')->validate($config)) {
      $form_state->setError(
        $form,
        $this->t('Unable to validate your s3fs configuration settings. Please configure S3 File System from the admin/config/media/s3fs page or settings.php and try again.')
      );
    }
  }

  /**
   * Success message if configuration is correct.
   *
   * @param array $form
   *   Array that contains the structure of the form.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The current state of the form.
   */
  public function validateConfigSubmitForm(array &$form, FormStateInterface $form_state) {
    drupal_set_message('Your configuration works properly');
  }

  /**
   * Refreshes in form validation.
   *
   * @param array $form
   *   Array that contains the structure of the form.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The current state of the form.
   */
  public function refreshCacheValidateForm(array $form, FormStateInterface $form_state) {
    $config = \Drupal::config('s3fs.settings')->get();
    if (!\Drupal::service('s3fs')->validate($config)) {
      $form_state->setError(
        $form,
        $this->t('Unable to validate your s3fs configuration settings. Please configure S3 File System from the admin/config/media/s3fs page and try again.')
      );
    }

    // Use this values for submit step.
    $form_state->set('s3fs', ['config' => $config]);
  }

  /**
   * Validates in form submission.
   *
   * @param array $form
   *   Array that contains the structure of the form.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The current state of the form.
   */
  public function refreshCacheSubmitForm(array &$form, FormStateInterface $form_state) {
    $s3fs_storage = $form_state->get('s3fs');
    $config = $s3fs_storage['config'];
    \Drupal::service('s3fs')->refreshCache($config);
  }

  /**
   * Validates the form.
   *
   * @param array $form
   *   Array that contains the structure of the form.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The current state of the form.
   */
  public function copyLocalValidateForm(array $form, FormStateInterface $form_state) {
    $config = \Drupal::config('s3fs.settings')->get();
    if (!\Drupal::service('s3fs')->validate($config)) {
      $form_state->setError(
        $form,
        $this->t('Unable to validate your s3fs configuration settings. Please configure S3 File System from the admin/config/media/s3fs page and try again.')
      );
    }

    $local_normal_wrappers = \Drupal::service('stream_wrapper_manager')->getNames(StreamWrapperInterface::LOCAL_NORMAL);
    $triggering_element = $form_state->getTriggeringElement();
    $destination_scheme = $triggering_element['#name'];

    if (!empty($local_normal_wrappers[$destination_scheme])) {
      if ($destination_scheme == 'private' && !Settings::get('file_private_path')) {
        $form_state->setError(
          $form,
          $this->t("Private system is not properly configurated, check \$settings['file_private_path'] in your settings.php.")
        );
      }
    }
    else {
      $form_state->setError(
        $form,
        $this->t('Scheme @scheme is not supported.', ['@scheme' => $destination_scheme])
      );
    }

    // Use this values for submit step.
    $form_state->set('s3fs', [
      'config' => $config,
      'scheme' => $destination_scheme,
    ]);
  }

  /**
   * Submits the form.
   *
   * @param array $form
   *   Array that contains the structure of the form.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The current state of the form.
   */
  public function copyLocalSubmitForm(array &$form, FormStateInterface $form_state) {
    $s3fs_storage = $form_state->get('s3fs');
    $config = $s3fs_storage['config'];
    $scheme = $s3fs_storage['scheme'];
    \Drupal::service('s3fs')->copyFileSystemToS3($config, $scheme);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    // We use different submits instead default submit.
  }

}
