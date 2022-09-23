<?php
namespace Drupal\starwars\Services;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\RequestException;

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
  protected $types= [
    'people',
    'planets',
    'films',
    'vehicles',
    'species',
    'starships',
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


  public  function getUrl(){
    return $this->api;
  }

  /**
   * @return string[]
   */
  public function getApiByUrl($url)
  {
    try {
      $request = $this->httpClient->request('GET', $url);
      $requestContent = $request->getBody()->getContents();
      $requestArr = json_decode($requestContent);
      return $requestArr;
    }
    catch (RequestException $e){
      \Drupal::logger('starwars')->error($e->getMessage());
    }
  }

  public function getTypeByUrl($url)
  {
    $substring = 'https://swapi.dev/api/';
    $type = str_replace($substring, "", $url);
    return $type;
  }
  public function nextTypeLink($type)
  {
    $check = false;
    foreach ($this->api as $key => $value){
      if($check){
        return ['link' => $value, 'type' =>$key];
      }
      if($key == $type){
        $check = true;
      }
    }
    return false;
  }
  public function getType($url){
    $parsed_url = parse_url($url);
    $path = [
      'people',
      'planets',
      'films',
      'vehicles',
      'species',
      'starships',
    ];
    foreach ($path as $item){
      if(strripos($parsed_url['path'],$item)){
        return $item;
      }
    }
  }





}
