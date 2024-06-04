<?php

namespace Drupal\websocket_log;

use Drupal\Core\Session\AccountProxyInterface;
use ElephantIO\Client;

/**
 * Sends a server feed to the client based on admin configuration settings.
 */
class Feed {

  /**
   * Feed message.
   *
   * @var string
   */
  protected $message;

  /**
   * WebSocket server IP or name.
   *
   * @var string
   */
  protected $server;

  /**
   * WebSocket port number.
   *
   * @var string|null
   */
  protected $port;

  /**
   * WebSocket path.
   *
   * @var string|null
   */
  protected $path;

  /**
   * WebSocket protocol.
   *
   * @var string
   */
  protected $protocol;

  /**
   * Client.
   *
   * @var object
   */
  protected $client;

  /**
   * Current user.
   *
   * @var object
   */
  protected $currentUser;

  public function __construct(AccountProxyInterface $currentUser) {
    $this->currentUser = $currentUser;

    $this->server = 'localhost';
    $this->port = 3000;
    $this->protocol = 'http';
    $this->path = '';
  }

  /**
   * Sends a new item to the WebSocket server.
   *
   * @param string $message
   *   Feed message to send to the client.
   *
   * @see https://github.com/ratchetphp/Pawl
   */
  public function send($message) {
    $this->message = str_replace('\n', '\r\n', $message);
    $namespace = NULL;
    $event = 'private message';

    // Only enable polling transport.
    $this->setupClient($namespace, NULL, ['transports' => 'websocket']);

    $this->client->emit($event, ['content' => $this->message, 'to' => 'djuro']);

    // If ($retval = $this->client->wait($event)) {
    //  dpm($retval);
    // }
    // $this->client->emit($event, ['message' => 'b']);
    // if ($retval = $this->client->wait($event)) {
    //   dpm($retval);
    // }
    $this->client->disconnect();

    // $url = 'http://localhost:3000';
    // If client option is omitted then it will use latest client available,
    // aka. version 4.x.
    // $options = ['client' => Client::CLIENT_4X, 'transport' => 'websocket'];.
    // Emit an event to the server.
    // $data = ['username' => 'my-user'];
    // $client->emit('get-user-info', $data);.
    // Wait an event to arrive
    // beware when waiting for response from server, the script may be killed if
    // PHP max_execution_time is reached.
    /*
    if ($packet = $client->wait('user-info')) {
    // An event has been received, the result will be a \ElephantIO\Engine\Packet class
    // data property contains the first argument
    // args property contains array of arguments, [$data, ...].
    $data = $packet->data;
    dpm($data);
    $args = $packet->args;
    // Access data.
    $email = $data['email'];
    }
     */
  }

  /**
   * Create a socket client.
   *
   * @param string $namespace
   * @param array $options
   */
  public function setupClient($namespace, $options = []) {
    $url = $this->protocol . '://' . $this->server . ':' . $this->port;
    if (isset($options['url'])) {
      $url = $options['url'];
      unset($options['url']);
    }

    $this->client = Client::create($url, array_merge(['client' => $this->clientVersion()], $this->userCredentials()));
    $this->client->connect();
    if ($namespace) {
      $this->client->of(sprintf('/%s', $namespace));
    }
  }

  /**
   * Authenticate socket user.
   *
   * @return array
   *   Options array with auth credentials.
   */
  public function userCredentials() {
    $options = [
      'auth' => [
        'username' => $this->currentUser->getAccount()->name,
        // Add token at some point.
        // 'token' => $this->currentUser->uid,.
      ],
    ];

    return $options;
  }

  /**
   * Get or set client version to use.
   *
   * @param int $version
   *   Version to set.
   *
   * @return int
   *   Test.
   */
  private function clientVersion($version = NULL) {
    // Default client version.
    static $client = Client::CLIENT_4X;
    if (NULL !== $version) {
      $client = $version;
    }

    return $client;
  }

}
