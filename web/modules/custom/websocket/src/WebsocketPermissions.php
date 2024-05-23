<?php

namespace Drupal\websocket;

use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * {@inheritdoc}
 */
class WebsocketPermissions implements ContainerInjectionInterface {

  /**
   * The websocket service manager.
   *
   * @var \Drupal\websocket\WebsocketServiceManager
   */
  protected $serviceManager;

  /**
   * Constructs a WebsocketPermissions instance.
   *
   * @param \Drupal\websocket\WebsocketServiceManager $service_manager
   *   The entity manager.
   */
  public function __construct(WebsocketServiceManager $service_manager) {
    $this->serviceManager = $service_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static($container->get('plugin.manager.websocket'));
  }

  /**
   * Get permissions for Websocket services.
   *
   * @return array
   *   Permissions array.
   */
  public function permissions() {
    $permissions = [];

    $definitions = $this->serviceManager->getDefinitions();
    foreach ($definitions as $definition) {
      /**
       * @var \Drupal\websocket\WebsocketServiceDefinition $definition
       */
      $permissions['use websocket service ' . $definition->id()] = [
        'title' => t('Use Websocket service: %service', [
          '%service' => $definition->getLabel(),
        ]),
      ];
    }

    return $permissions;
  }

  /**
   * Check permission to use some websocket service.
   *
   * @param string $serviceName
   *   The service name.
   * @param \Drupal\Core\Session\AccountInterface $account
   *   The Account interface.
   *
   * @return bool
   *   Boolean access permission.
   */
  public static function access($serviceName, $account) {
    return $account->hasPermission('use websocket service ' . $serviceName);
  }

}
