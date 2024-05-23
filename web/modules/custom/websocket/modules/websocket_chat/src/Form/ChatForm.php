<?php

namespace Drupal\websocket_chat\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * The ChatForm form class.
 *
 * @package Drupal\websocket\Form
 */
class ChatForm extends FormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'chat_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $form['chat_message'] = [
      '#type' => 'textarea',
      '#title' => $this->t('Message'),
      '#disabled' => 'disabled',
      '#placeholder' => $this->t('Connecting...'),
    ];
    $form['actions']['#type'] = 'actions';
    $form['actions']['submit'] = [
      '#id' => 'chat-form-submit',
      '#type' => 'submit',
      '#value' => $this->t('Send'),
      '#button_type' => 'primary',
      '#disabled' => 'disabled',
    ];
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $this->messenger()->addStatus($this->t('Your message was submitted!'));
  }

}
