<?php

namespace Drupal\eca_language\Plugin\Action;

use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Language\LanguageDefault;
use Drupal\Core\StringTranslation\TranslationManager;
use Drupal\eca\Plugin\CleanupInterface;
use Drupal\eca_language\Event\LanguageNegotiateEvent;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Set the currently used language code.
 *
 * @Action(
 *   id = "eca_set_current_langcode",
 *   label = @Translation("Language: set code"),
 *   description = @Translation("Set the currently used or negotiated language code."),
 *   eca_version_introduced = "2.0.0"
 * )
 */
class SetCurrentLangcode extends LanguageActionBase implements CleanupInterface {

  /**
   * The default language object.
   *
   * @var \Drupal\Core\Language\LanguageDefault
   */
  protected LanguageDefault $languageDefault;

  /**
   * Stack of used languages.
   *
   * @var array
   */
  protected static array $languageStack = [];

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition): static {
    $instance = parent::create($container, $configuration, $plugin_id, $plugin_definition);
    $instance->languageDefault = $container->get('language.default');
    $instance->setStringTranslation($container->get('string_translation'));
    return $instance;
  }

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration(): array {
    return [
      'langcode' => '',
    ] + parent::defaultConfiguration();
  }

  /**
   * {@inheritdoc}
   */
  public function buildConfigurationForm(array $form, FormStateInterface $form_state): array {
    $form['langcode'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Language code'),
      '#description' => $this->t('Example: <em>en</em>.'),
      '#required' => TRUE,
      '#default_value' => $this->configuration['langcode'],
      '#eca_token_replacement' => TRUE,
    ];
    return parent::buildConfigurationForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitConfigurationForm(array &$form, FormStateInterface $form_state): void {
    parent::submitConfigurationForm($form, $form_state);
    $this->configuration['langcode'] = $form_state->getValue('langcode', '');
  }

  /**
   * {@inheritdoc}
   */
  public function execute(): void {
    $langcode = trim((string) $this->tokenService->replaceClear($this->configuration['langcode']));
    if ($langcode === '') {
      throw new \InvalidArgumentException("No language code specified.");
    }
    if (!($language = $this->languageManager->getLanguage($langcode))) {
      throw new \InvalidArgumentException(sprintf("No language found for langcode %s.", $langcode));
    }

    if (isset($this->event) && ($this->event instanceof LanguageNegotiateEvent)) {
      $this->event->langcode = $language->getId();
      return;
    }

    $langInfo = [
      'current' => $this->languageManager->getCurrentLanguage(),
      'default' => $this->languageDefault->get(),
      'override' => $this->languageManager->getConfigOverrideLanguage(),
    ];

    $this->languageManager->reset();

    $this->languageManager->setCurrentLangcode($language->getId());
    $this->languageManager->setConfigOverrideLanguage($language);
    $this->languageDefault->set($language);
    if ($this->stringTranslation instanceof TranslationManager) {
      $this->stringTranslation->setDefaultLangcode($language->getId());
    }
    self::$languageStack[] = $langInfo;
  }

  /**
   * {@inheritdoc}
   */
  public function cleanupAfterSuccessors(): void {
    if (isset($this->event) && ($this->event instanceof LanguageNegotiateEvent)) {
      return;
    }

    $langInfo = array_pop(self::$languageStack);
    // Instead of resetting language negotiation, the previous langcode is being
    // explicitly set here, to ensure that it does not potentially differ from
    // what was set before.
    $this->languageManager->setCurrentLangcode($langInfo['current']->getId());
    $this->languageManager->setConfigOverrideLanguage($langInfo['override']);
    $this->languageDefault->set($langInfo['default']);
    if ($this->stringTranslation instanceof TranslationManager) {
      $this->stringTranslation->setDefaultLangcode($langInfo['current']->getId());
    }
  }

}
