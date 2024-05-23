<?php

namespace Drupal\websocket;

use Ratchet\App;
use Ratchet\WebSocket\WsServer;

/**
 * The Socket server class.
 *
 * @package Drupal\websocket
 */
class SocketServer extends App {

  /**
   * True if plugin discovery found at least one websocket service definition.
   *
   * @var bool
   */
  private $atLeastOneService = FALSE;

  /**
   * {@inheritdoc}
   */
  public function __construct($hostname, $port = 0, $ssl = TRUE, $reverse = TRUE) {
    // Set default port number value.
    $port_number = 3000;
    // Update port number on valid value.
    if ($port >= 1024 && $port <= 65535) {
      $port_number = $port;
    }
    $host = 'localhost';
    if (!empty($hostname)) {
      $host = $hostname;
    }
    $address = '127.0.0.1';
    if (!$reverse) {
      $address = '0.0.0.0';
    }
    parent::__construct($host, $port_number, $address);

    // Save hostname and port.
    static::setHost($host);
    static::setPort($port_number);

    // Set ssl and reverse proxy.
    static::setSsl($ssl);
    static::setReverseProxy($reverse);

    /**
     * @var \Drupal\websocket\WebsocketServiceManager $manager
     */
    $manager = \Drupal::service('plugin.manager.websocket');
    $definitions = $manager->getDefinitions();
    foreach ($definitions as $definition) {
      /**
       * @var \Drupal\websocket\WebsocketServiceDefinition $definition
       */
      // Check whether service enabled.
      if (!$definition->isEnabled()) {
        echo sprintf("Websocket service (%s): Is not enabled. Skipping...\n", $definition->id());
        continue;
      }

      // Set application as controller.
      $class = $definition->getClass();
      $controller = new DrupalSessionProvider(new WsServer(new $class($definition->id())));

      // Found at least one websocket service definition.
      $this->atLeastOneService = TRUE;

      // Add all necessary routes.
      echo sprintf("Websocket service (%s): Registered at %s\n",
        $definition->id(),
        static::getServiceUrl($definition->id()));
      $this->route(static::getServicePathInfo($definition->id()), $controller, ['*'], $host);
      if ($host != 'localhost') {
        $this->route(static::getServicePathInfo($definition->id()), $controller, ['*'], 'localhost');
      }

      // Set ready state.
      $this->setReady($definition->id(), TRUE);

      // Add signal handler.
      $sig_handler = new SignalHandler($this);
      $this->_server->loop->addSignal(SIGTERM, $sig_handler);
      $this->_server->loop->addSignal(SIGINT, $sig_handler);
    }
  }

  /**
   * {@inheritdoc}
   */
  public function run() {
    // Do not run if no services.
    if (!$this->atLeastOneService) {
      echo "No websocket services to run. Stopping...\n";
      return;
    }
    echo "Websocket services: Starting...\n";

    // Close FlashPolicy socket, since it is not necessary.
    $this->flashServer->socket->close();

    // Run.
    parent::run();
  }

  /**
   * {@inheritdoc}
   */
  private static function getScheme() {
    $scheme = "ws";
    if (static::usesSsl()) {
      $scheme = $scheme . "s";
    }
    return $scheme;
  }

  /**
   * Get URL where to connect to service websocket.
   *
   * @param string $serviceName
   *   The service name.
   * @param string $domain
   *   The domain string.
   *
   * @return string
   *   The service url.
   */
  public static function getServiceUrl($serviceName = NULL, $domain = NULL) {
    $scheme = static::getScheme();
    $host = $domain;
    if (empty($host)) {
      $host = static::getHost();
    }
    $port = static::getSocketPort();
    $path = static::getPathInfo();
    if (!empty($serviceName)) {
      $path = static::getServicePathInfo($serviceName);
    }
    return $scheme . "://" . $host . ":" . $port . $path;
  }

  /**
   * {@inheritdoc}
   */
  public function getServiceProxyUrl($serviceName = NULL) {
    if (!static::usesReverseProxy()) {
      return static::getServiceUrl($serviceName);
    }

    $scheme = 'ws';
    $host = static::getHost();
    $port = static::getPort();
    $path = static::getPathInfo();
    if (!empty($serviceName)) {
      $path = static::getServicePathInfo($serviceName);
    }
    return $scheme . "://" . $host . ":" . $port . $path;
  }

  /**
   * {@inheritdoc}
   */
  private static function getServicePathInfo($serviceName) {
    return static::getPathInfo() . '/' . $serviceName;
  }

  /**
   * {@inheritdoc}
   */
  private static function getPathInfo() {
    $prefix = '';
    // Using reverse proxy add a prefix.
    if (static::usesReverseProxy()) {
      $prefix = '/websocket';
    }
    return $prefix;
  }

  /**
   * Sets some service state variable, e.g. ready = TRUE.
   *
   * @param string $serviceName
   *   The service name.
   * @param string $var
   *   The var string.
   * @param mixed $value
   *   The value string.
   */
  private static function setServiceStateVar($serviceName, $var, $value) {
    \Drupal::state()->set('websocket.' . $serviceName . '.' . $var, $value);
  }

  /**
   * Gets service state variable, e.g. ready.
   *
   * @param string $serviceName
   *   The service name.
   * @param string $var
   *   The var.
   *
   * @return mixed
   *   The websocket.
   */
  public static function getServiceStateVar($serviceName, $var) {
    return \Drupal::state()->get('websocket.' . $serviceName . '.' . $var);
  }

  /**
   * Set service readyness.
   *
   * @param string $serviceName
   *   The service name.
   * @param bool $state
   *   The state boolean.
   */
  private static function setReady($serviceName, $state) {
    static::setServiceStateVar($serviceName, 'ready', $state);
  }

  /**
   * {@inheritdoc}
   */
  private static function setHost($host) {
    static::setServiceStateVar('core', 'host', $host);
  }

  /**
   * {@inheritdoc}
   */
  private static function setPort($port) {
    static::setServiceStateVar('core', 'port', $port);
  }

  /**
   * {@inheritdoc}
   */
  private static function setSsl($useSsl) {
    static::setServiceStateVar('core', 'ssl', $useSsl);
  }

  /**
   * {@inheritdoc}
   */
  private static function setReverseProxy($useReverseProxy) {
    static::setServiceStateVar('core', 'reverseProxy', $useReverseProxy);

    // If not reverse then reset ssl.
    if (!$useReverseProxy) {
      static::setSsl(FALSE);
    }
  }

  /**
   * Checks whether service ready.
   *
   * @param string $serviceName
   *   The service name.
   *
   * @return bool
   *   Service ready boolean.
   */
  public static function isReady($serviceName) {
    return (bool) static::getServiceStateVar($serviceName, 'ready');
  }

  /**
   * Get the host number.
   *
   * @return int
   *   The host number.
   */
  private static function getHost() {
    return (string) static::getServiceStateVar('core', 'host');
  }

  /**
   * Returns port number for external socket connection.
   *
   * @return int
   *   The socket port number.
   */
  private static function getSocketPort() {
    // When using reverse proxy then 80 or 443.
    if (static::usesReverseProxy()) {
      if (static::usesSsl()) {
        return 443;
      }
      else {
        return 80;
      }
    }
    return static::getPort();
  }

  /**
   * Returns port number.
   *
   * @return int
   *   The port number.
   */
  private static function getPort() {
    return (int) static::getServiceStateVar('core', 'port');
  }

  /**
   * {@inheritdoc}
   */
  public static function usesSsl() {
    return (bool) static::getServiceStateVar('core', 'ssl');
  }

  /**
   * {@inheritdoc}
   */
  public static function usesReverseProxy() {
    return (bool) static::getServiceStateVar('core', 'reverseProxy');
  }

  /**
   * Stop server.
   */
  public function stop() {
    ServerKernel::ensureConnection();
    echo "\nStopping websocket server...\n";

    /**
     * Set unready state for all services
     *
     * @var \Drupal\websocket\WebsocketServiceManager $manager
     */
    $manager = \Drupal::service('plugin.manager.websocket');
    $definitions = $manager->getDefinitions();
    foreach ($definitions as $definition) {
      /**
       * @var \Drupal\websocket\WebsocketServiceDefinition $definition
       */
      $this->setReady($definition->id(), FALSE);
    }

    $this->_server->loop->stop();
  }

  /**
   * Print configuration of started service.
   */
  public static function printConfig() {
    if (PHP_SAPI == 'cli') {
      echo "\nGeneral server socket configuration:\n
         - URL: " . static::getServiceUrl() . "\n
         - Uses reverse proxy: " . (static::usesReverseProxy() ? 'Yes' : 'No') . "\n
         - Uses SSL: " . (static::usesSsl() ? 'Yes' : 'No') . "\n\n";

      if (static::usesReverseProxy()) {
        static::printNginxConfig();
        static::printApacheConfig();
      }
    }
  }

  /**
   * {@inheritdoc}
   */
  public static function printNginxConfig() {
    if (PHP_SAPI == 'cli') {
      echo "Adapt your Nginx virtual host configuration to use reverse proxy\n
        in /etc/nginx/sites-enabled/" . static::getHost() . ".conf:\n
        \n
        upstream websocket {\n
          server localhost:" . static::getPort() . ";\n
        }\n
        server {\n
          listen " . static::getSocketPort() . (static::usesSsl() ? ' ssl' : '') . ";\n
          ...\n
          location /websocket {\n
            proxy_pass http://websocket;\n
            proxy_http_version 1.1;\n
            proxy_set_header Upgrade websocket;\n
            proxy_set_header Connection upgrade;\n
            proxy_set_header Host localhost;\n
          }\n
          ...\n
        }\n
        \n";
    }
  }

  /**
   * {@inheritdoc}
   */
  public static function printApacheConfig() {
    if (PHP_SAPI == 'cli') {

      echo "Or adapt your Apache2 virtual host configuration to use reverse proxy\n
        in /etc/apache2/sites-enabled/" . static::getHost() . ".conf:\n
        \n
        <Proxy ws://localhost:" . static::getPort() . "/*>\n
          Allow from all\n
        </Proxy>\n
        <VirtualHost *:" . static::getSocketPort() . ">\n
          ...\n
          <LocationMatch \"" . static::getPathInfo() . "\">\n
            ProxyPass ws://localhost:" . static::getPort() . static::getPathInfo() . "\n
            ProxyPassReverse ws://localhost:" . static::getPort() . static::getPathInfo() . "\n
          </LocationMatch>\n
          ...\n
        </VirtualHost>\n
        \n";

      echo "Be sure to enable all Apache2 modules:\n
          a2enmod proxy\n
          a2enmod proxy_http\n
          a2enmod proxy_wstunnel\n";
    }
  }

}
