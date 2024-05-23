<?php

namespace Drupal\eca_base\Plugin\ECA\Event;

use Drupal\Core\Form\FormStateInterface;
use Drupal\eca\Entity\Eca;
use Drupal\eca\Entity\Objects\EcaEvent;
use Drupal\eca\Event\Tag;
use Drupal\eca\Plugin\ECA\Event\EventBase;
use Drupal\eca\Plugin\PluginUsageInterface;
use Drupal\eca_base\BaseEvents;
use Drupal\eca_base\Event\CronEvent;
use Drupal\eca_base\Event\CustomEvent;
use Symfony\Contracts\EventDispatcher\Event;

/**
 * Plugin implementation of the ECA base Events.
 *
 * @EcaEvent(
 *   id = "eca_base",
 *   deriver = "Drupal\eca_base\Plugin\ECA\Event\BaseEventDeriver",
 *   eca_version_introduced = "1.0.0"
 * )
 */
class BaseEvent extends EventBase implements PluginUsageInterface {

  /**
   * {@inheritdoc}
   */
  public static function definitions(): array {
    $actions = [];
    $actions['eca_cron'] = [
      'label' => 'ECA cron event',
      'event_name' => BaseEvents::CRON,
      'event_class' => CronEvent::class,
      'tags' => Tag::RUNTIME | Tag::PERSISTENT | Tag::EPHEMERAL,
    ];
    $actions['eca_custom'] = [
      'label' => 'ECA custom event',
      'event_name' => BaseEvents::CUSTOM,
      'event_class' => CustomEvent::class,
      'tags' => Tag::RUNTIME,
    ];
    return $actions;
  }

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration(): array {
    if ($this->eventClass() === CronEvent::class) {
      $values = [
        'frequency' => '* * * * *',
      ];
    }
    elseif ($this->eventClass() === CustomEvent::class) {
      $values = [
        'event_id' => '',
      ];
    }
    else {
      $values = [];
    }
    return $values + parent::defaultConfiguration();
  }

  /**
   * {@inheritdoc}
   */
  public function buildConfigurationForm(array $form, FormStateInterface $form_state): array {
    if ($this->eventClass() === CronEvent::class) {
      $form['frequency'] = [
        '#type' => 'textfield',
        '#title' => $this->t('Frequency'),
        '#default_value' => $this->configuration['frequency'],
        '#description' => $this->t('The frequency of a cron job is defined by a cron spcific notation which is best explained at https://en.wikipedia.org/wiki/Cron. Note: date and time need to be provided in UTC timezone.'),
      ];
    }
    elseif ($this->eventClass() === CustomEvent::class) {
      $form['event_id'] = [
        '#type' => 'textfield',
        '#title' => $this->t('Event ID'),
        '#default_value' => $this->configuration['event_id'],
        '#description' => $this->t('The custom event ID. Leave empty to trigger all custom events.'),
      ];
    }
    return parent::buildConfigurationForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitConfigurationForm(array &$form, FormStateInterface $form_state): void {
    parent::submitConfigurationForm($form, $form_state);
    if ($this->eventClass() === CronEvent::class) {
      $this->configuration['frequency'] = $form_state->getValue('frequency');
    }
    elseif ($this->eventClass() === CustomEvent::class) {
      $this->configuration['event_id'] = $form_state->getValue('event_id');
    }
  }

  /**
   * {@inheritdoc}
   */
  public function generateWildcard(string $eca_config_id, EcaEvent $ecaEvent): string {
    switch ($this->getDerivativeId()) {

      case 'eca_custom':
        $configuration = $ecaEvent->getConfiguration();
        return isset($configuration['event_id']) ? trim($configuration['event_id']) : '';

      case 'eca_cron':
        return $ecaEvent->getId() . '::' . $ecaEvent->getConfiguration()['frequency'];

      default:
        return parent::generateWildcard($eca_config_id, $ecaEvent);

    }
  }

  /**
   * {@inheritdoc}
   *
   * Verifies if this event is due for the next execution.
   *
   * This event stores the last execution time for each modeller event
   * identified by $id and determines with the given frequency, if and when
   * this same event triggered cron should be executed.
   */
  public static function appliesForWildcard(Event $event, string $event_name, string $wildcard): bool {
    if ($event instanceof CronEvent) {
      [$id, $frequency] = explode('::', $wildcard, 2);
      if ($event->isDue($id, $frequency)) {
        $event->storeTimestamp($id);
        return TRUE;
      }
    }
    elseif ($event instanceof CustomEvent) {
      return ($event->getEventId() === $wildcard) || ($wildcard === '');
    }
    return FALSE;
  }

  /**
   * {@inheritdoc}
   */
  public function pluginUsed(Eca $eca, string $id): void {
    if ($this->eventClass() === CronEvent::class) {
      // Verify that this cron event has been executed in the past. If not, we
      // store the current timestamp as if the cron event was executed just now.
      // This makes sure that when the cron event really will be executed in
      // the future, the correct next due date can be calculated.
      $lastRun = $this->state->getTimestamp('cron-' . $id);
      if (!$lastRun) {
        $this->state->setTimestamp('cron-' . $id, $this->state->getCurrentTimestamp() - 60);
      }
    }
  }

}
