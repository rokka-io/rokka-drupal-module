<?php

/**
 * Rokka MassMover admin form.
 *
 * @param $form
 * @param $form_state
 *
*@return mixed
 */
function rokka_massmover_admin_settings_form($form, $form_state) {
    $form = array();

    $form['update_config'] = array(
        '#title' => t('Update field settings'),
        '#description' => t('Update the given fields configuration to use Rokka.io as the default storage'),
        '#type' => 'checkbox',
        '#default_value' => TRUE,
        '#required' => FALSE,
    );

    $form['move_files'] = array(
        '#title' => t('Move images to Rokka.io'),
        '#description' => t('Scan entities with the selected fields and move the available images to Rokka.io.').
            '<br >'.t('*WARNING* It is strongly suggested to create a database dump before proceeding!'),
        '#type' => 'checkbox',
        '#default_value' => FALSE,
        '#required' => FALSE,
    );

    $form['skip_failing'] = array(
        '#title' => t('Skip failing uploads'),
        '#description' => t('Skip failing uploads to Rokka.io instead of terminating the upload of all images. Logs of failed uploads will be kept.'),
        '#type' => 'checkbox',
        '#default_value' => TRUE,
        '#required' => FALSE,
        '#states' => array(
            // Show this element only when "move files" is checked
            'visible' => array(
                ':input[name="move_files"]' => array('checked' => TRUE),
            ),
        ),
    );

    $fields = array_keys(field_read_fields(array('type' => 'image')));
    $fields = array_combine($fields, $fields);

    $form['field_select'] = array(
        '#type' => 'fieldset',
        '#title' => t('Fields to be updated or moved'),
        '#description' => t('Select the fields to be handled by the MassMover operation'),
        '#collapsible' => TRUE,
        '#collapsed' => FALSE,
    );

    $form['field_select']['all_fields'] = array(
        '#title' => t('Select all fields'),
        '#description' => t('Select all fields'),
        '#type' => 'checkbox',
        '#default_value' => FALSE,
        '#required' => FALSE,
    );

    $form['field_select']['fields'] = array(
        '#title' => t('Fields (image)'),
        '#description' => t('Select the fields you want to handle durnig the process of updating/moving for Rokka. For field details, see: !link', array(
            '!link' => l(t('Fields list'), 'admin/reports/fields'),
        )),
        '#type' => 'checkboxes',
        '#options' => $fields,
        '#required' => FALSE,
        '#states' => array(
            // Disable the selection when "All fields" is checked
            'disabled' => array(
                ':input[name="all_fields"]' => array('checked' => TRUE),
            ),
        ),
    );

    $form['submit'] = array(
        '#type' => 'submit',
        '#value' => t('Submit'),
    );

    return $form;
}

/**
 * Validate callback for MassMover form.
 */
function rokka_massmover_admin_settings_form_validate($form, $form_state) {

    $fields = array_filter($form_state['values']['fields']);

    if (empty($fields) && !$form_state['values']['all_fields']) {
        form_set_error('', t('No fields selected: chose at least one field, or the "Move all fields" option.'));
    }
}

/**
 * Submit callback for MassMover form.
 */
function rokka_massmover_admin_settings_form_submit($form, $form_state)
{
    $values = $form_state['values'];
    $fields = array_filter($values['fields']);

    if (TRUE == $values['all_fields']) {
        $fields = array_keys(field_read_fields(['type' => 'image']));
    }

    $skipFailing = $values['skip_failing'];

    $move_operations = [];
    foreach ($fields as $field_name) {
        if (TRUE == $values['update_config']) {
            rokka_massmover_set_rokka_default_for_field($field_name);
        }

        if (TRUE == $values['move_files']) {
            $move_operations = array_merge($move_operations, rokka_massmover_get_batch_move_field_operations($field_name, $skipFailing));
        }
    }

    if (!empty($move_operations)) {
        $batch = [
            'operations' => $move_operations,
            'finished' => 'rokka_massmover_batch_finished',
            'title' => t('Moving images to Rokka.io'),
            'init_message' => t('Rokka MassMover is starting.'),
            'progress_message' => t('Processing fields for content types and bundles (completed @current out of @total), elapsed time: @elapsed.'),
        ];
        batch_set($batch);
    }
}
