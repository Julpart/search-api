<?php
namespace Drupal\starwars\Plugin\rest\resource;

use Drupal\Core\Session\AccountProxyInterface;
use Drupal\rest\Plugin\ResourceBase;
use Drupal\rest\ResourceResponse;
use Drupal\user\UserInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * @RestResource(
 *   id = "starwars_rest",
 *   label = @Translation("StarWarsRest"),
 *   uri_paths = {
 *     "canonical" = "/path"
 *   }
 * )
 */
class StarWarsRest extends ResourceBase
{

  /**
   * A current user instance.
   *
   * @var \Drupal\Core\Session\AccountProxyInterface
   */
  protected $currentUser;

  /**
   * {@inheritdoc}
   */
  public function __construct(
    array                 $configuration,
                          $plugin_id,
                          $plugin_definition,
    array                 $serializer_formats,
    LoggerInterface       $logger,
    AccountProxyInterface $current_user)
  {
    parent::__construct($configuration, $plugin_id, $plugin_definition, $serializer_formats, $logger);

    $this->currentUser = $current_user;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition)
  {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->getParameter('serializer.formats'),
      $container->get('logger.factory')->get('dummy'),
      $container->get('current_user')
    );
  }

  public function get()
  {
    $query = \Drupal::request()->query;
    $response = [];
    if ($query->has('id')) {
      $queryNode = $this->entityManager->getStorage('node');
      $node = $query->loadByProperties(['type' => 'people', 'field_swapi_id' => $query->get('id')]);
      if (!empty($nod)) {
        $response['node'] = [
          'name' => $node->field_name,
        ];
        return new ResourceResponse($response);
      } else {
        $response['node'] = NULL;
        $response['message'] = 'Сharacter with id ' . $query->get('id') . ' is not found';
        return new ResourceResponse($response);
      }
    } else {
      return new ResourceResponse('Required parameter id is not set.', 400);
    }

  }
}
