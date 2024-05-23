<?php

namespace Drupal\websocket_chat;

use Ratchet\Wamp\WampServer;

/**
 * {@inheritdoc}
 */
class WampServerWrapper extends WampServer {

  /**
   * WampServerWrapper constructor.
   *
   * @param \Ratchet\Wamp\WampServerInterface $serviceName
   *   The service name parameter.
   */
  public function __construct($serviceName) {
    parent::__construct(new RealtimeComments($serviceName));
  }

}
