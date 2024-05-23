<?php

namespace Drupal\websocket;

/**
 * {@inheritdoc}
 */
class DrupalAwareService {

  /**
   * The service name string.
   *
   * @var string
   */
  private $serviceName;

  /**
   * DrupalAwareService constructor.
   *
   * @param string $serviceName
   *   The service name.
   */
  public function __construct($serviceName) {
    $this->serviceName = $serviceName;
  }

  /**
   * Get service name, e.g. chat.
   *
   * @return string
   *   The service name.
   */
  public function getServiceName() {
    return $this->serviceName;
  }

  /**
   * Check's user's permission to use that service.
   *
   * @param \Drupal\Core\Session\AccountInterface $account
   *   The Account interface.
   *
   * @return bool
   *   The access permission boolean.
   */
  protected function access($account) {
    return WebsocketPermissions::access($this->getServiceName(), $account);
  }

}
