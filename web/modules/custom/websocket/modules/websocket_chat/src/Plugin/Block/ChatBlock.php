<?php

namespace Drupal\websocket_chat\Plugin\Block;

use Drupal\Core\Access\AccessResult;
use Drupal\Core\Block\BlockBase;
use Drupal\Core\Session\AccountInterface;
use Drupal\websocket\WebsocketPermissions;

/**
 * Chat block.
 *
 * @Block(
 *   id = "chat_block",
 *   admin_label = @Translation("Chat block"),
 * )
 */
class ChatBlock extends BlockBase {

  /**
   * {@inheritdoc}
   */
  public function build() {
    return [
      '#title' => 'Chat block',
      '#theme' => 'chat_block',
      '#form' => \Drupal::formBuilder()->getForm('\Drupal\websocket_chat\Form\ChatForm'),
      '#attached' => [
        'library' => [
          'websocket_chat/chat',
        ],
      ],
    ];
  }

  /**
   * {@inheritdoc}
   */
  protected function blockAccess(AccountInterface $account) {
    return AccessResult::allowedIf(WebsocketPermissions::access('chat', $account));
  }

}
