<?php

namespace Drupal\websocket_log\Plugin\Block;

use Drupal\Core\Access\AccessResult;
use Drupal\Core\Block\BlockBase;
use Drupal\Core\Session\AccountInterface;
use Drupal\websocket\WebsocketPermissions;

/**
 * Log block.
 *
 * @Block(
 *   id = "log_block",
 *   admin_label = @Translation("Log block"),
 * )
 */
class LogBlock extends BlockBase {

  /**
   * {@inheritdoc}
   */
  public function build() {
    return [
      '#title' => 'Log block',
      '#theme' => 'log_block',
      '#markup' => 'Hello, World!',
      '#attached' => [
        'library' => [
          'websocket_log/websocket-log',
        ],
      ],
    ];
  }

  /**
   * {@inheritdoc}
   */
  protected function blockAccess(AccountInterface $account) {
    return AccessResult::allowedIf(WebsocketPermissions::access('log', $account));
  }

}
