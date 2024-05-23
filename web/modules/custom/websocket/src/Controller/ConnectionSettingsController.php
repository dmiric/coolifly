<?php

namespace Drupal\websocket\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\websocket\SocketServer;
use Drupal\websocket\WebsocketPermissions;
use Symfony\Component\HttpFoundation\JsonResponse;

/**
 * {@inheritdoc}
 */
class ConnectionSettingsController extends ControllerBase {

  /**
   * Returns service settings.
   *
   * @param string $serviceName
   *   The service name.
   *
   * @return \Symfony\Component\HttpFoundation\JsonResponse
   *   JSon response object.
   */
  public function settings(string $serviceName) {
    // Check whether service is ready.
    if (!SocketServer::isReady($serviceName)) {
      // Return service not available.
      return new JsonResponse(NULL, 502);
    }

    // Check permissions.
    if (!WebsocketPermissions::access($serviceName, \Drupal::currentUser())) {
      // Return snot allowed.
      return new JsonResponse(NULL, 403);
    }

    // Return service settings.
    $response = new JsonResponse([
      'name' => $serviceName,
      'debug' => (boolean) \Drupal::request()->get('debug'),
      'path' => SocketServer::getServiceUrl($serviceName, \Drupal::request()->getHttpHost()),
    ]);
    return $response;
  }

}
