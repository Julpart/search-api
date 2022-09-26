<?php
namespace Drupal\starwars\Plugin\rest\resource;

use Drupal\Core\Session\AccountProxyInterface;
use Drupal\rest\Plugin\ResourceBase;
use Drupal\rest\ResourceResponse;
use Drupal\user\UserInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use \Drupal\node\Entity\Node;
use \Drupal\Core\Cache\CacheableMetadata;

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
  protected $entityManager;

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
    $instance = new static($configuration, $plugin_id, $plugin_definition,$container->getParameter('serializer.formats'),
      $container->get('logger.factory')->get('dummy'), $container->get('current_user'));
    $instance->entityManager = $container->get('entity_type.manager');
    return $instance;
  }
  public function permissions() {
    return [];
  }
  protected function loadNode($arr,$cache){
    $result = [];
    foreach ($arr as $item){
      $node = Node::load(current($item));
      $cache->addCacheableDependency($node);
      $result[] = [$item, $node->get('title')->getValue()];
    }
    return $result;
  }
  public function get()
  {
    $query = \Drupal::request()->query;
    $response = [];

    $cache = CacheableMetadata::createFromRenderArray([
      '#cache' => [
        'max-age' => 900,
      ],
    ]);

    if ($query->has('id')) {
      $queryNode = $this->entityManager->getStorage('node');
      $swapi_id = $query->get('id');
      $node = current($queryNode->loadByProperties(['type' => 'people', 'field_swapi_id' => (int)$swapi_id]));
      if (!empty($node)) {
        $response['node'] = [
          'name' => $node->get('field_name')->getValue(),
          'height' => $node->get('field_height')->getValue(),
          'mass' => $node->get('field_mass')->getValue(),
          'hair_color' => $node->get('field_hair_color')->getValue(),
          'skin_color' => $node->get('field_skin_color')->getValue(),
          'eye_color' => $node->get('field_eye_color')->getValue(),
          'birth_year' => $node->get('field_birth_year')->getValue(),
          'gender' => $node->get('field_gender')->getValue(),
          'homeworld' => $this->loadNode($node->get('field_homeworld')->getValue(),$cache),
          'films' => $this->loadNode($node->get('field_films')->getValue(),$cache),
          'species' => $this->loadNode($node->get('field_species')->getValue(),$cache),
          'vehicles' => $this->loadNode($node->get('field_vehicles')->getValue(),$cache),
          'starships' => $this->loadNode($node->get('field_starships')->getValue(),$cache),
          'created' => $node->get('field_created')->getValue(),
          'edited' => $node->get('field_edited')->getValue(),
        ];
        return new ResourceResponse($response);
      } else {
        $response['node'] = NULL;
        $response['message'] = 'Сharacter with id ' . $query->get('id') . ' is not found';
        return (new ResourceResponse($response))->addCacheableDependency($cache);
      }
    } else {
      return new ResourceResponse('Required parameter id is not set.', 400);
    }

  }
}
