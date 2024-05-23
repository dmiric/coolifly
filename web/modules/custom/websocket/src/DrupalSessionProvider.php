<?php

namespace Drupal\websocket;

use Drupal\Core\Session\AnonymousUserSession;
use GuzzleHttp\Psr7\ServerRequest;
use Psr\Http\Message\RequestInterface;
use Ratchet\ConnectionInterface;
use Ratchet\Http\HttpServerInterface;
use Ratchet\Session\SessionProvider;
use Symfony\Bridge\PsrHttpMessage\Factory\HttpFoundationFactory;

/**
 * {@inheritdoc}
 */
class DrupalSessionProvider extends SessionProvider {

  /**
   * The SessionConfiguration attribute.
   *
   * @var \Drupal\Core\Session\SessionConfiguration
   */
  private $sessionConfig;

  /**
   * {@inheritdoc}
   */
  public function __construct(HttpServerInterface $app) {
    /**
     * Get Drupal session configuration and handler
     *
     * @var \Drupal\Core\Session\SessionHandler $session_handler
     */
    $this->rebuildDependencies();
    parent::__construct($app, $this->_handler);
  }

  /**
   * {@inheritdoc}
   */
  protected function rebuildDependencies() {
    $this->sessionConfig = \Drupal::service('session_configuration');
    $this->_handler = \Drupal::service('session_handler');
  }

  /**
   * {@inheritdoc}
   */
  protected function ensureConnection() {
    ServerKernel::ensureConnection();
    $this->rebuildDependencies();
  }

  /**
   * {@inheritdoc}
   */
  public function onOpen(ConnectionInterface $conn, RequestInterface $request = NULL) {
    $this->ensureConnection();

    /**
     * If there is an origin header, then replace hostname and scheme by
     * origin's.
     * That will happen when using Websocket services via a reverse proxy.
     *
     * @var \GuzzleHttp\Psr7\Uri $uri
     */
    $headers = $request->getHeaders();
    $uri = $request->getUri();
    if (isset($headers['Origin'][0])) {
      $origin_pieces = parse_url($headers['Origin'][0]);
      $uri = $uri
        ->withScheme($origin_pieces['scheme'])
        ->withHost($origin_pieces['host']);
    }
    $request = $request->withUri($uri);

    /**
     * Get Drupal's session configuration and set its options on current
     *
     * @var \Drupal\Core\Session\SessionConfiguration $sessionConfig
     */
    $factory = new HttpFoundationFactory();
    // Build server request from Psr7-Request.
    $server_request = new ServerRequest(
      $request->getMethod(),
      $uri,
      $request->getHeaders(),
      $request->getBody(),
      $request->getProtocolVersion()
    );
    // Build Symfony request from Psr-7 server request.
    $symfony_request = $factory->createRequest($server_request);

    // Get session options from Drupal's session configuration.
    $options = $this->sessionConfig->getOptions($symfony_request);
    // Set option.
    $this->setOptions($options);

    return parent::onOpen($conn, $request);
  }

  /**
   * {@inheritdoc}
   */
  public function onMessage(ConnectionInterface $from, $msg) {
    $this->ensureConnection();
    return parent::onMessage($from, $msg);
  }

  /**
   * {@inheritdoc}
   */
  public function onError(ConnectionInterface $conn, \Exception $e) {
    var_dump($e);
    $this->ensureConnection();
    return parent::onError($conn, $e);
  }

  /**
   * {@inheritdoc}
   */
  public function onClose(ConnectionInterface $conn) {
    $this->ensureConnection();
    return parent::onClose($conn);
  }

  /**
   * Get's Drupal's user from connection.
   *
   * @param \Ratchet\ConnectionInterface $conn
   *   The Connection interface.
   *
   * @return \Drupal\Core\Session\AccountInterface
   *   The Account interface.
   */
  public static function getUser(ConnectionInterface $conn) {
    ServerKernel::ensureConnection();

    /**
     * @var \Symfony\Component\HttpFoundation\Session\Attribute\AttributeBag $attr
     */
    $attr = static::getSession($conn)->getBag('attributes');
    $uid = $attr->get('uid');

    // Return anonymous if no UID in session.
    $anonymous = new AnonymousUserSession();
    if (empty($uid)) {
      return $anonymous;
    }

    /**
     * Load user by UID
     *
     * @var \Drupal\user\Entity\User $user
     */
    $user = \Drupal::entityTypeManager()->getStorage('user')->load($uid);
    if (empty($user)) {
      return $anonymous;
    }

    return $user;
  }

  /**
   * Get Drupal's session object from connection.
   *
   * @param \Ratchet\ConnectionInterface $conn
   *   The Connection interface.
   *
   * @return \Symfony\Component\HttpFoundation\Session\Session
   *   The session object.
   */
  public static function getSession(ConnectionInterface $conn) {
    return $conn->Session;
  }

}
