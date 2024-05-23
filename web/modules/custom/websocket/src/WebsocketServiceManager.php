<?php

namespace Drupal\websocket;

use Drupal\Component\Plugin\Exception\PluginException;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Plugin\DefaultPluginManager;
use Drupal\Core\Plugin\Discovery\ContainerDerivativeDiscoveryDecorator;
use Drupal\Core\Plugin\Discovery\YamlDiscovery;

/**
 * Provides the default websocket manager.
 */
class WebsocketServiceManager extends DefaultPluginManager implements WebsocketServiceManagerInterface {

  /**
   * Provides default values for all websocket plugins.
   *
   * @var array
   */
  protected $defaults = [
    'id' => NULL,
    'enabled' => TRUE,
    'class' => NULL,
    'label' => NULL,
    'provider' => NULL,
  ];

  /**
   * Constructs a new WebsocketServiceManager object.
   *
   * @param \Drupal\Core\Extension\ModuleHandlerInterface $module_handler
   *   The module handler.
   */
  public function __construct(ModuleHandlerInterface $module_handler) {
    // Add more services as required.
    $this->moduleHandler = $module_handler;
  }

  /**
   * {@inheritdoc}
   */
  protected function getDiscovery() {
    if (!isset($this->discovery)) {
      $this->discovery = new YamlDiscovery('websocket', $this->moduleHandler->getModuleDirectories());
      $this->discovery->addTranslatableProperty('label', 'label_context');
      $this->discovery = new ContainerDerivativeDiscoveryDecorator($this->discovery);
    }
    return $this->discovery;
  }

  /**
   * {@inheritdoc}
   */
  public function processDefinition(&$definition, $plugin_id) {
    parent::processDefinition($definition, $plugin_id);

    // Transform definition from array to a class.
    $definition = new WebsocketServiceDefinition($definition);

    // Check class property definition.
    $class = $definition->getClass();
    if (empty($class)) {
      throw new PluginException(sprintf(
        'Websocket service (%s): Plugin property "class" is required. Provided by %s module.',
        $plugin_id,
        $definition->getProvider()
      ));
    }

    // Check class existence.
    if (!class_exists($class)) {
      throw new PluginException(sprintf(
        'Websocket service (%s): Defined class %s does not exist. Provided by %s module.',
        $plugin_id,
        $class,
        $definition->getProvider()
      ));
    }

    // Check label.
    if (empty($class)) {
      throw new PluginException(sprintf(
        'Websocket service (%s): Plugin property "label" is required. Provided by %s module.',
        $plugin_id,
        $definition->getProvider()
      ));
    }
  }

}
