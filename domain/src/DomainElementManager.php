<?php

namespace Drupal\domain;

use Drupal\Core\Form\FormStateInterface;

/**
 * Generic base class for handling hidden field options.
 *
 * Since domain options are restricted for various forms (users, nodes, source)
 * we have a base class for handling common use cases. The details of each
 * implementation are generally handled by a subclass and invoked within a
 * hook_form_alter().
 *
 * This class has some similarities to DomainAccessManager, but only cares
 * about form handling. It can be used as a base class by other modules that
 * show/hide domain options. See the DomainSourceElementManager for a non-default
 * implementation.
 */
class DomainElementManager implements DomainElementManagerInterface {

  /**
   * @var \Drupal\domain\DomainLoaderInterface
   */
  protected $loader;

  /**
   * Constructs a DomainElementManager object.
   *
   * @param \Drupal\domain\DomainLoaderInterface $loader
   *   The domain loader.
   * @param \Drupal\domain\DomainNegotiatorInterface $negotiator
   *   The domain negotiator.
   */
  public function __construct(DomainLoaderInterface $loader) {
    $this->loader = $loader;
  }

  /**
   * @inheritdoc
   */
  public function setFormOptions(array $form, FormStateInterface $form_state, $field_name, $hide_on_disallow = FALSE) {
    // There are cases, such as Entity Browser, where the form is partially
    // invoked, but without our fields.
    if (!isset($form[$field_name])) {
      return $form;
    }
    $fields = $this->fieldList($field_name);
    $disallowed = $this->disallowedOptions($form_state, $form[$field_name]);
    $empty = empty($form[$field_name]['widget']['#options']);

    // Check for domains the user cannot access or the absence of any options.
    if (!empty($disallowed) || $empty) {
      // @TODO: Potentially show this information to users with permission.
      $form[$field_name . '_disallowed'] = array(
        '#type' => 'value',
        '#value' => $disallowed,
      );
      $form['domain_hidden_fields'] = array(
        '#type' => 'value',
        '#value' => $fields,
      );
      if ($hide_on_disallow || $empty) {
        $form[$field_name]['#access'] = FALSE;
      }
      // Call our submit function to merge in values.
      // Account for all the submit buttons on the node form.
      $buttons = ['preview', 'delete'];
      $submit = $this->getSubmitHandler();
      foreach ($form['actions'] as $key => $action) {
        if (!in_array($key, $buttons) && is_array($action) && !in_array($submit, $form['actions'][$key]['#submit'])) {
          array_unshift($form['actions'][$key]['#submit'], $submit);
        }
      }
    }

    return $form;
  }

  /**
   * @inheritdoc
   */
  public static function submitEntityForm(array &$form, FormStateInterface $form_state) {
    $fields = $form_state->getValue('domain_hidden_fields');
    foreach ($fields as $field) {
      $values = $form_state->getValue($field . '_disallowed');
      if (!empty($values)) {
        $info = $form_state->getBuildInfo();
        $node = $form_state->getFormObject()->getEntity();
        $entity_values = $form_state->getValue($field);
      }
      foreach ($values as $value) {
        $entity_values[]['target_id'] = $value;
      }
      // Prevent a fatal error caused by passing a NULL value.
      // See https://www.drupal.org/node/2841962.
      if (!empty($entity_values)) {
        $form_state->setValue($field, $entity_values);
      }
    }
  }

  /**
   * @inheritdoc
   */
  public function disallowedOptions(FormStateInterface $form_state, $field) {
    $options = [];
    $info = $form_state->getBuildInfo();
    $entity = $form_state->getFormObject()->getEntity();
    $entity_values = $this->getFieldValues($entity, $field['widget']['#field_name']);
    if (isset($field['widget']['#options'])) {
      $options = array_diff_key($entity_values, $field['widget']['#options']);
    }
    return array_keys($options);
  }

  /**
   * @inheritdoc
   */
  public function fieldList($field_name) {
    static $fields = [];
    $fields[] = $field_name;
    return $fields;
  }

  /**
   * @inheritdoc
   */
  public function getFieldValues($entity, $field_name) {
    // @TODO: static cache.
    $list = array();
    // @TODO In tests, $entity is returning NULL.
    if (is_null($entity)) {
      return $list;
    }
    // Get the values of an entity.
    $values = $entity->get($field_name);
    // Must be at least one item.
    if (!empty($values)) {
      foreach ($values as $item) {
        if ($target = $item->getValue()) {
          if ($domain = $this->loader->load($target['target_id'])) {
            $list[$domain->id()] = $domain->getDomainId();
          }
        }
      }
    }
    return $list;
  }

  /**
   * @inheritdoc
   */
  public function getSubmitHandler() {
    return '\\Drupal\\domain\\DomainElementManager::submitEntityForm';
  }

}
