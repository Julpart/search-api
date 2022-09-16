<?php
namespace Drupal\starwars\Services;
use GuzzleHttp\ClientInterface;

class APIService{
  protected $httpClient;
  protected $api = [
    'people' =>'https://swapi.dev/api/people',
    'planets' => 'https://swapi.dev/api/planets',
    'films' => 'https://swapi.dev/api/films',
    'vehicles' => 'https://swapi.dev/api/vehicles',
    'species' => 'https://swapi.dev/api/species',
    'starships' => 'https://swapi.dev/api/starships',
  ];
  /**
   * Constructor for MymoduleServiceExample.
   *
   * @param \GuzzleHttp\ClientInterface $http_client
   *   A Guzzle client object.
   */
  public function __construct(ClientInterface $http_client) {
    $this->httpClient = $http_client;
  }
//проверка на дату изменения  states

  public  function getUrl(){
    return $this->api;
  }

  /**
   * @return string[]
   */
  public function getApiByUrl($url)
  {
    $request = $this->httpClient->request('GET', $url);
    $requestContent = $request->getBody()->getContents();
    $requestArr = json_decode($requestContent);
    return $requestArr;
  }
  public function getAll()//сервис делает запросы очереди вызывают сервис
  {
    $result =[];
    foreach ($this->api as $key => $item){
      try {
        $request = $this->httpClient->request('GET', $item);//проверку статуса на 200
        $requestContent = $request->getBody()->getContents();
      }catch (ClientException $e){
        watchdog_exception('http_module', $e->getMessage());//drupal logger
      }
      $requestArr = json_decode($requestContent);

      $next = $requestArr->next;

      $arr = $requestArr->results;
      while (isset($next)) {
        try {
        $request = $this->httpClient->request('GET', $next);
        $requestContent = $request->getBody()->getContents();
        }catch (ClientException $e){
          watchdog_exception('http_module', $e->getMessage());
        }
        $requestArr = json_decode($requestContent);
        $next = $requestArr->next;
        $arr = array_merge($requestArr->results,$arr);
      }
      $result += [$key => $arr];
    }
    return $result;
  }




}
