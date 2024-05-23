<?php

namespace Drupal\eca_form\Plugin\Action;

use Drupal\Core\Form\FormStateInterface;

/**
 * Set the HTTP method to use when submitting the form.
 *
 * @Action(
 *   id = "eca_form_set_method",
 *   label = @Translation("Form: set method"),
 *   description = @Translation("Set the HTTP method to use when submitting the form."),
 *   eca_version_introduced = "1.1.0",
 *   type = "form"
 * )
 */
class FormSetMethod extends FormActionBase {

  /**
   * {@inheritdoc}
   */
  public function execute(): void {
    if (!($form = &$this->getCurrentForm())) {
      return;
    }
    $form['#method'] = $this->configuration['method'];
  }

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration(): array {
    return [
      'method' => 'post',
    ] + parent::defaultConfiguration();
  }

  /**
   * {@inheritdoc}
   */
  public function buildConfigurationForm(array $form, FormStateInterface $form_state): array {
    $form['method'] = [
      '#type' => 'select',
      '#title' => $this->t('Method'),
      '#description' => $this->t('The method of a form action <em>GET</em> or <em>POST</em>.'),
      '#options' => [
        'get' => $this->t('GET'),
        'post' => $this->t('POST'),
      ],
      '#weight' => -10,
      '#default_value' => $this->configuration['method'],
      '#required' => TRUE,
    ];
    return parent::buildConfigurationForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitConfigurationForm(array &$form, FormStateInterface $form_state): void {
    parent::submitConfigurationForm($form, $form_state);
    $this->configuration['method'] = $form_state->getValue('method');
  }

}
