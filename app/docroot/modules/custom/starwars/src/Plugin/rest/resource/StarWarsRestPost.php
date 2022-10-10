<?php

namespace Drupal\starwars\Plugin\rest\resource;

use Drupal\Core\Session\AccountProxyInterface;
use Drupal\rest\Plugin\ResourceBase;
use Drupal\rest\ResourceResponse;
use Drupal\user\UserInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use \Drupal\node\Entity\Node;
use \Drupal\starwars\Services\BatchService;
use Symfony\Component\HttpFoundation\Request;

/**
 * @RestResource(
 *   id = "starwars_rest_post",
 *   label = @Translation("StarWarsRestPost"),
 *   uri_paths = {
 *     "create" = "/create-node",
 *   }
 * )
 */
class StarWarsRestPost extends ResourceBase {
  /**
   * A current user instance.
   *
   * @var \Drupal\Core\Session\AccountProxyInterface
   */
  protected $currentUser;
  protected $entityManager;
  protected $nodeService;

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
    $instance->nodeService = $container->get('starwars.batch');
    return $instance;
  }

  protected function getTypeByUrl($url)
  {
    $substring = 'https://swapi.dev/api/';
    $type = str_replace($substring, "", $url);
    preg_match('#[a-z]+#',$type,$found);
    $type = current($found);
    return $type;
  }

  public function post(Request $request){
    $roles = $this->currentUser->getRoles();
    if (!in_array('authenticated',$roles)) {
      return new ResourceResponse('Ошибка авторизации', 400);
    }
    try {
      $data = json_decode($request->getContent());
      $type = $this->getTypeByUrl($data->url);
      $data->type = $type;
      $this->nodeService->createNode($data);
      return new ResourceResponse('succes',201);
    } catch (\Exception $e) {
      return new ResourceResponse('Something went wrong during entity creation. Check your data.', 400);
    }
  }

}
