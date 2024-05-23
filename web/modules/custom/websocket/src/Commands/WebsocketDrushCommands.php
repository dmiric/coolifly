<?php

namespace Drupal\websocket\Commands;

use Drupal\websocket\ServerKernel;
use Drupal\websocket\SocketServer;
use Drush\Commands\DrushCommands;

/**
 * A Drush commandfile.
 *
 * In addition to this file, you need a drush.services.yml
 * in root of your module, and a composer.json file that provides the name
 * of the services file to use.
 */
class WebsocketDrushCommands extends DrushCommands {

  /**
   * The socket server.
   *
   * @var null
   */
  private $appliance = NULL;

  /**
   * Start ratchet socket server command.
   *
   * Drush command to start the ratchet test socket server.
   * This is not for production usage since database connection
   * will die after some time.
   *
   * @param string $port
   *   Port to run service on.
   * @param array $options
   *   The socket server options.
   *
   * @validate-module-enabled websocket
   * @command websocket:start-websocket-server
   * @aliases startsocket
   * @options port Port to run service on
   * @options arr An option that takes multiple values.
   * @options no-ssl Do not run over SSL (always when not running over reverse-proxy)
   * @options no-reverse-proxy Do not run over reverse proxy
   * @options just-print Do not start service, but print configuration
   * @usage websocket:start-websocket-server [hostname] [port] [--no-reverse-proxy|--no-ssl]
   *   Starts websocket server.
   */
  public function startWebsocketServer(
    $port = 3000,
    $options = [
      'no-ssl' => FALSE,
      'no-reverse-proxy' => FALSE,
      'just-print' => FALSE,
      ]
    ) {

    // Build new kernel on base of Drush's one to ensure long connectivity to
    // database during all the time.
    ServerKernel::rebuild(\Drupal::service('kernel'));

    $this->appliance = new SocketServer(
      'coolifly.ddev.site',
      $port,
      !$options['no-ssl'],
      !$options['no-reverse-proxy']
    );
    if ($options['just-print']) {
      $this->appliance::printConfig();
    }
    else {
      $this->appliance->run();
    }
  }

}
