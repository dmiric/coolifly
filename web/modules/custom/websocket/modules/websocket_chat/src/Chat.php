<?php

namespace Drupal\websocket_chat;

use Drupal\websocket\DrupalAwareService;
use Drupal\websocket\DrupalSessionProvider;
use Ratchet\ConnectionInterface;
use Ratchet\MessageComponentInterface;

/**
 * {@inheritdoc}
 */
class Chat extends DrupalAwareService implements MessageComponentInterface {

  /**
   * The clients object.
   *
   * @var \SplObjectStorage
   */
  protected $clients;

  /**
   * Chat constructor.
   *
   * @param string $serviceName
   *   Service name parameter.
   */
  public function __construct($serviceName) {
    parent::__construct($serviceName);
    $this->clients = new \SplObjectStorage();
  }

  /**
   * {@inheritdoc}
   */
  public function onOpen(ConnectionInterface $conn) {

    // Get user.
    $user = DrupalSessionProvider::getUser($conn);

    // Check permission and close connection if not allowed.
    // if (!$this->access($user)) {
    //  $conn->close();
    //  return;
    // }
    $this->clients->attach($conn);
    echo "New connection {$conn->resourceId}\n";
  }

  /**
   * {@inheritdoc}
   */
  public function onMessage(ConnectionInterface $from, $msg) {
    $user = DrupalSessionProvider::getUser($from);
    foreach ($this->clients as $client) {
      $client->send($user->getDisplayName() . ': ' . $msg);
    }
  }

  /**
   * {@inheritdoc}
   */
  public function onClose(ConnectionInterface $conn) {
    $this->clients->detach($conn);

    echo "Connection {$conn->resourceId} has disconnected\n";
  }

  /**
   * {@inheritdoc}
   */
  public function onError(ConnectionInterface $conn, \Exception $e) {
    echo "An error occured {$e->getMessage()}\n";

    $conn->close();
  }

}
