<?php

namespace Drupal\websocket_chat;

use Drupal\websocket\SessionAwareService;
use Ratchet\ConnectionInterface;

/**
 * {@inheritdoc}
 */
class RealtimeComments extends SessionAwareService {

  /**
   * The clients object.
   *
   * @var \SplObjectStorage
   */
  protected $clients;

  /**
   * Chat constructor.
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
    $user = $this->getUser($conn);

    // Check permission and close connection if not allowed.
    if (!$this->access($user)) {
      $conn->close();
      return;
    }
    $this->clients->attach($conn);
    echo "New connection {$conn->resourceId}\n";
  }

  /**
   * {@inheritdoc}
   */
  public function onMessage(ConnectionInterface $from, $msg) {
    $user = $this->getUser($from);
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
