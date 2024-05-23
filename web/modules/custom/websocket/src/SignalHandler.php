<?php

namespace Drupal\websocket;

/**
 * {@inheritdoc}
 */
class SignalHandler {

  /**
   * {@inheritdoc}
   */
  private $socketServer;

  /**
   * SignalHandler constructor.
   *
   * @param \Drupal\websocket\SocketServer $socketServer
   *   The socket server.
   */
  public function __construct(SocketServer $socketServer) {
    $this->socketServer = $socketServer;
  }

  /**
   * {@inheritdoc}
   */
  public function __invoke($signal) {
    switch ($signal) {
      case SIGINT:
        $this->handleSigInt();
        break;

      case SIGTERM:
        $this->handleSigTerm();
        break;

      default:
        $this->handleDefault($signal);
    }

  }

  /**
   * {@inheritdoc}
   */
  private function handleSigInt() {
    $this->socketServer->stop();
  }

  /**
   * {@inheritdoc}
   */
  private function handleSigTerm() {
    $this->handleSigInt();
  }

  /**
   * {@inheritdoc}
   */
  private function handleSigKill() {
    $this->handleSigInt();
  }

  /**
   * {@inheritdoc}
   */
  private function handleDefault(int $signal) {
    echo "Signal arrived: " . $signal . "\n";
  }

}
