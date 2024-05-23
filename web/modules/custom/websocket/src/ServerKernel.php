<?php

namespace Drupal\websocket;

use Drupal\Core\Database\Database;
use Drupal\Core\DrupalKernel;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class ServerKernel.
 *
 * Normal DrupalKernel cannot serve during whole service life time,
 * because database connection will die after wait_timeout. This makes
 * rebuilding kernel on connection loss necessary.
 *
 * @package Drupal\websocket
 */
class ServerKernel extends DrupalKernel {

  /**
   * Single-ton server kernel.
   *
   * @var \Drupal\websocket\ServerKernel
   */
  public static $kernel;

  /**
   * Lock kernel on rebuild.
   *
   * @var bool
   */
  private static $lock = FALSE;

  /**
   * Timeout seconds.
   *
   * @var int
   */
  private static $defaulTimeout = 300;

  /**
   * ServerKernel constructor.
   *
   * @param string $class_loader
   *   Drupal kernel class loader.
   */
  public function __construct($class_loader) {
    parent::__construct('prod', $class_loader, FALSE);
    self::bootEnvironment();
    $this->initializeSettings(new Request());
    $this->invalidateContainer();
    $this->boot();
  }

  /**
   * Ensure connection when it is dead.
   */
  public static function ensureConnection() {
    // Wait for rebuild.
    while (static::$lock) {
      sleep(1);
    }
    if (!static::isConnectionAvailable()) {
      static::rebuild(static::$kernel);
    }
  }

  /**
   * Rebuild a single-ton kernel.
   */
  public static function rebuild($kern) {
    // Lock kernel.
    static::$lock = TRUE;

    // Get class loader from old kernel.
    $class_loader = $kern->getContainer()->get('class_loader');
    /**
     * @var \Symfony\Component\HttpFoundation\RequestStack $req_stack
     */
    $req_stack = $kern->getContainer()->get('request_stack');

    // Delete old database connection from lists.
    Database::closeConnection();

    /**
     * Build new kernel to ensure new database connection for all services
     *
     * Unfortunately, it is not possible just to rebuildContainer(),
     * when database connection already dead, because discoverServiceProviders()
     * will use config storage (DatabaseStorage) to get list of modules from
     * 'core.extension' configuration
     */
    static::$kernel = new ServerKernel($class_loader);

    // Set old request stack, which was build by Drush.
    static::$kernel->getContainer()->set('request_stack', $req_stack);

    // Set long timeout.
    static::setConectionTimeout(static::$defaulTimeout);

    // Unlock kernel.
    static::$lock = FALSE;
  }

  /**
   * Set new connection timeout for database.
   *
   * @param int $seconds
   *   Connection timeout time.
   */
  private static function setConectionTimeout($seconds) {
    static::$kernel
      ->getContainer()
      ->get('database')
      ->query('set wait_timeout = ' . $seconds);
  }

  /**
   * {@inheritdoc}
   */
  private static function checkTimeout() {
    $stm = static::$kernel
      ->getContainer()
      ->get('database')
      ->query("show session variables like '%wait_timeout%'");
    $stm->execute();
    $res = $stm->fetchAll();
    var_dump($res);
  }

  /**
   * Check connectivity to database.
   *
   * @return bool
   *   Connection available boolean.
   */
  private static function isConnectionAvailable() {
    $ret = TRUE;
    try {
      // Send some meaningless query to database.
      static::$kernel->getContainer()->get('database')->query("SELECT 1;");
    }
    catch (\Exception $exception) {
      $ret = FALSE;
    }
    return $ret;
  }

}
