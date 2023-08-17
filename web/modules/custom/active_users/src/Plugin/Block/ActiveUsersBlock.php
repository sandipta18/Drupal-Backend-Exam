<?php

namespace Drupal\active_users\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Database\Connection;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Entity\Query\QueryFactoryInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Session\AccountInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides an active users block.
 *
 * @Block(
 *   id = "active_users_active_users",
 *   admin_label = @Translation("Active Users"),
 *   category = @Translation("Custom Block")
 * )
 */
class ActiveUsersBlock extends BlockBase implements ContainerFactoryPluginInterface {

  /**
   * The current user.
   *
   * @var \Drupal\Core\Session\AccountInterface
   */
  protected $account;

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * The entity.query.config service.
   *
   * @var \Drupal\Core\Entity\Query\QueryFactoryInterface
   */
  protected $entityQueryConfig;

  /**
   * The database connection.
   *
   * @var \Drupal\Core\Database\Connection
   */
  protected $database;

  /**
   * Constructs a new ActiveUsersBlock instance.
   *
   * @param array $configuration
   *   The plugin configuration, i.e. an array with configuration values keyed
   *   by configuration option name. The special key 'context' may be used to
   *   initialize the defined contexts by setting it to an array of context
   *   values keyed by context names.
   * @param string $plugin_id
   *   The plugin_id for the plugin instance.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\Core\Session\AccountInterface $account
   *   The current user.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager.
   * @param \Drupal\Core\Entity\Query\QueryFactoryInterface $entity_query_config
   *   The entity.query.config service.
   * @param \Drupal\Core\Database\Connection $database
   *   The database connection.
   */
  public function __construct(array $configuration,
  $plugin_id,
  $plugin_definition,
  AccountInterface $account,
  EntityTypeManagerInterface $entity_type_manager,
  QueryFactoryInterface $entity_query_config,
  Connection $database,
  ) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->account = $account;
    $this->entityTypeManager = $entity_type_manager;
    $this->entityQueryConfig = $entity_query_config;
    $this->database = $database;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('current_user'),
      $container->get('entity_type.manager'),
      $container->get('entity.query.config'),
      $container->get('database'),
    );
  }

  /**
   * {@inheritdoc}
   */
  public function build() {
    $user_names = [];
    $target_role = 'student';
    $one_hour_ago = \Drupal::time()->getCurrentTime() - 3600;
    $uids = $this->database
      ->query('SELECT uid FROM sessions WHERE uid != 0 AND `timestamp` >= :time', [':time' => $one_hour_ago])
      ->fetchCol();
    $users = $this->entityTypeManager->getStorage('user')->loadMultiple($uids);
    foreach ($users as $user) {
      if ($user->hasRole($target_role)) {
        $user_names[] = $user->getDisplayName();
      }
    }
    $user_names_string = implode(', ', $user_names);
    $build['content'] = [
      '#markup' => $user_names_string,
      '#cache' => [
    // Add cache tag.
        'tags' => ['user_list:' . $target_role],
      ],
    ];
    return $build;
  }

}
