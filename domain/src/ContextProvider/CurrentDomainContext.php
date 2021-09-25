<?php

namespace Drupal\domain\ContextProvider;

use Drupal\domain\DomainNegotiatorInterface;
use Drupal\Core\Cache\CacheableMetadata;
use Drupal\Core\Plugin\Context\Context;
use Drupal\Core\Plugin\Context\ContextProviderInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\Core\Plugin\Context\EntityContextDefinition;


/**
 * Provides a context handler for the block system.
 */
class CurrentDomainContext implements ContextProviderInterface {

  use StringTranslationTrait;

  /**
   * The Domain negotiator.
   *
   * @var \Drupal\domain\DomainNegotiatorInterface
   */
  protected $negotiator;

  /**
   * Constructs a CurrentDomainContext object.
   *
   * @param \Drupal\domain\DomainNegotiatorInterface $negotiator
   *   The domain negotiator.
   */
  public function __construct(DomainNegotiatorInterface $negotiator) {
    $this->negotiator = $negotiator;
  }

  /**
   * {@inheritdoc}
   */
  public function getRuntimeContexts(array $unqualified_context_ids) {
    // Load the current domain.
    $current_domain = $this->negotiator->getActiveDomain();

    $context_definition = EntityContextDefinition::create('entity:domain')
      ->setRequired(FALSE)
      ->setLabel('Active domain');

    $context = new Context($context_definition, $current_domain);
    // Allow caching.
    $cacheability = new CacheableMetadata();
    $cacheability->setCacheContexts(['url.site']);
    $context->addCacheableDependency($cacheability);

    // Prepare the result.
    return [
      'entity:domain' => $context,
    ];

  }

  /**
   * {@inheritdoc}
   */
  public function getAvailableContexts() {
    return $this->getRuntimeContexts([]);
  }

}
