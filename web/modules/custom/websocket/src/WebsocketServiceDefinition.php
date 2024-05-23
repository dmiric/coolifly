<?php

namespace Drupal\websocket;

use Drupal\Component\Plugin\Definition\PluginDefinition;

/**
 * Provides the default websocket service plugin definition.
 */
class WebsocketServiceDefinition extends PluginDefinition {

  /**
   * The enabled boolean attribute.
   *
   * @var bool
   */
  private $enabled;

  /**
   * The label attribute.
   *
   * @var string
   */
  private $label;

  /**
   * WebsocketServiceDefinition constructor.
   *
   * @param array $array_definition
   *   The plugin definition.
   */
  public function __construct(array $array_definition) {
    $this->id = $array_definition['id'];
    $this->enabled = (bool) $array_definition['enabled'];
    $this->provider = $array_definition['provider'];
    $this->label = $array_definition['label'];
    $this->setClass($array_definition['class']);
  }

  /**
   * Enabled attribute getter.
   *
   * @return bool
   *   Enabled attribute value.
   */
  public function isEnabled() {
    return $this->enabled;
  }

  /**
   * Label attribute getter.
   *
   * @return null|string
   *   Label attribute value.
   */
  public function getLabel() {
    return $this->label;
  }

}
