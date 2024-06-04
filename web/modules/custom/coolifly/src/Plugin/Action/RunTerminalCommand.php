<?php

declare(strict_types=1);

namespace Drupal\coolifly\Plugin\Action;

use Drupal\Core\Access\AccessResultInterface;
use Drupal\Core\Action\ConfigurableActionBase;
use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Session\AccountInterface;
use Symfony\Component\Process\Process;

/**
 * Provides a Run Terminal Command action.
 *
 * @Action(
 *   id = "coolifly_run_terminal_command",
 *   label = @Translation("Run Terminal Command"),
 *   type = "node",
 *   category = @Translation("Terminal"),
 * )
 *
 * @DCG
 * For updating entity fields consider extending FieldUpdateActionBase.
 * @see \Drupal\Core\Field\FieldUpdateActionBase
 *
 * @DCG
 * In order to set up the action through admin interface the plugin has to be
 * configurable.
 * @see https://www.drupal.org/project/drupal/issues/2815301
 * @see https://www.drupal.org/project/drupal/issues/2815297
 *
 * @DCG
 * The whole action API is subject of change.
 * @see https://www.drupal.org/project/drupal/issues/2011038
 */
final class RunTerminalCommand extends ConfigurableActionBase {

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration(): array {
    return ['command-params' => 'ls'];
  }

  /**
   * {@inheritdoc}
   */
  public function buildConfigurationForm(array $form, FormStateInterface $form_state): array {
    $form['command-params'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Command Params'),
      '#default_value' => $this->configuration['command-params'],
    ];
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitConfigurationForm(array &$form, FormStateInterface $form_state): void {
    $this->configuration['command-params'] = $form_state->getValue('command-params');
  }

  /**
   * {@inheritdoc}
   */
  public function access($entity, AccountInterface $account = NULL, $return_as_object = FALSE): AccessResultInterface|bool {
    /** @var \Drupal\Core\Entity\ContentEntityInterface $entity */
    $access = $entity->access('update', $account, TRUE);
    // ->andIf($entity->get('field_example')->access('edit', $account, TRUE));
    return $return_as_object ? $access : $access->isAllowed();
  }

  /**
   * {@inheritdoc}
   */
  public function execute(ContentEntityInterface $entity = NULL): void {
    dpm($this->defaultConfiguration());
    $command = ['ls', '-lsa'];
    $command = ['composer', 'update', TRUE];
    // $result = shell_exec($this->configuration['example']);
    // dpm($result);
    $process = new Process($command);
    \Drupal::service('websocket_log.feed')->send('> ' . $command[0] . ' ' . $command[1] . '\n');

    if (!$command[2]) {
      $process->start();

      foreach ($process as $type => $data) {
        if ($process::OUT === $type) {
          // Echo "\nRead from stdout: " . $data;
          // $process::ERR === $type.
          $data_lines = explode(PHP_EOL, $data);
          // $data_new = '';
          foreach ($data_lines as $line) {
            // $data_new = $data_new . ' ' . trim($line);
            \Drupal::service('websocket_log.feed')->send(trim($line) . '\n');
          }
          // \Drupal::service('websocket_log.feed')->send($data_new);
        }
        else {
          // Echo "\nRead from stderr: " . $data;.
          dpm($data);
        }
      }

      return;
    }

    $process->run(function ($type, $buffer): void {
      if (Process::ERR === $type) {
        \Drupal::service('websocket_log.feed')->send(trim($buffer));
      }
      else {
        \Drupal::service('websocket_log.feed')->send(trim($buffer));
      }
    });

  }

}
