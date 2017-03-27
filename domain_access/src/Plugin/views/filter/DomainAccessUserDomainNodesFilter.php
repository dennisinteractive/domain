<?php

namespace Drupal\domain_access\Plugin\views\filter;

use Drupal\views\Plugin\views\filter\BooleanOperator;
use Drupal\views\Plugin\views\display\DisplayPluginBase;
use Drupal\views\ViewExecutable;
use Drupal\Core\Session\AccountProxyInterface;
use Drupal\user\Entity\User;

/**
 * Handles matching of user domains.
 * @ingroup views_filter_handlers
 * @ViewsFilter("domain_access_user_domain_nodes_filter")
 */
class DomainAccessUserDomainNodesFilter extends BooleanOperator {

  /**
   * {@inheritdoc}
   */
  public function init(ViewExecutable $view, DisplayPluginBase $display, array &$options = NULL) {
    parent::init($view, $display, $options);
    $this->value_value = t('Available on user domains');
  }

  /**
   * {@inheritdoc}
   */
  public function getValueOptions() {
    $this->valueOptions = array(1 => $this->t('Yes'), 0 => $this->t('No'));
  }

  /**
   * {@inheritdoc}
   */
  protected function operators() {
    return array();
  }

  /**
   * {@inheritdoc}
   */
  public function query() {
    $table=$this->ensureMyTable();
//var_dump($this);
    $current_user = \Drupal\user\Entity\User::load(\Drupal::currentUser()->id());
    $current_user_id = $current_user->get('uid')->value;
    $condition = 'uda.entity_id = '.$current_user_id;
//    dpm($condition);

//    $query = $this->select('node__field_domain_access', 'nda');
//    $this->getFields('user__field_domain_access','uda');


//    $query = $this->select('user__field_domain_access', 'uda');

//    $this->leftJoin('user__field_domain_access', 'uda',$table.'.field_domain_access_target_id = uda.field_domain_access_target_id');

    $query = db_select('node__field_domain_access', 'n');
    $query->addField('n', 'entity_id');
    $query->leftJoin('user__field_domain_access', 'u', 'n.field_domain_access_target_id = u.field_domain_access_target_id');
//    $query->condition(
//      $query->andConditionGroup()
//        ->where( 'uda.entity_id = '.$current_user_id)
////        ->condition('sd.reindex', 0, '<>')
//    );


    $nids = $query->execute()->fetchCol();
    if (!$nids) {
      return;
    }


//    $account = $this->view->getUser()->id();
//    dpm($account);
//    $table = $this->ensureMyTable();
//    $this->leftJoin('user__field_domain_access', 'uda', 'node__field_domain_access.field_domain_access_target_id = uda.field_domain_access_target_id');

//    $this->addJoin('LEFT', 'user__field_domain_access', 'uda', '=', array('uda.field_domain_access_target_id',' nda.field_domain_access_target_id'));
//    $this->query->addTable('user__field_domain_access',NULL,NULL,'uda');
//    $this->query->addField('uda', 'field_domain_access_target_id');
//    $grants = db_or();
//    $grants->condition(db_and()
//      ->condition('user__field_domain_access.entity_id',$current_user_id, '=')
//      ->condition('user__field_domain_access.field_domain_access_target_id', 'node__field_domain_access.field_domain_access_target_id','=')
//    );

//    $this->query->addWhere('AND', $grants);



    //condition($field, $value = NULL, $operator = NULL)

//    if (!$account->hasPermission('bypass node access')) {
//      $table = $this->ensureMyTable();
//
//
//      $grants = db_or();
//
//
//      foreach (node_access_grants('view', $account) as $realm => $gids) {
//        foreach ($gids as $gid) {
//          $grants->condition(db_and()
//            ->condition($table . '.gid', $gid)
//            ->condition($table . '.realm', $realm)
//          );
//        }
//      }
//
//      $this->query->addWhere('AND', $grants);
//      $this->query->addWhere('AND', $table . '.grant_view', 1, '>=');
//    }


//    $this->addField('nda','field_domain_access_target_id');
//    $this->addField('user__field_domain_access','entity_id');
//    $this->addJoin('LEFT', 'user__field_domain_access', 'uda', '=', array('uda.field_domain_access_target_id',' nda.field_domain_access_target_id'));
//    $this->addWhere($this->options['group'],$condition);

  }

  /**
   * {@inheritdoc}
   */
  public function getCacheContexts() {
    $contexts = parent::getCacheContexts();

    $contexts[] = 'url.site';

    return $contexts;
  }

}
