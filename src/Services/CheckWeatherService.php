<?php

namespace App\Services;

use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class CheckWeatherService{

    protected $params;
    protected $client;

    private $kolnData;
    private $cityData;

    public function __construct(HttpClientInterface $httpClient, ParameterBagInterface $params)
    {
        $this->params = $params;
        $this->client = $httpClient;
    }

    public function getWeather(string $city)
    {
        //  Defines result model
        $resultData = Array(
            "check"=>false,
            "criteria" => [
            "naming"=>false,
            "daytemp"=>false,
            "rival"=>false]
        );

        //  Obtain results from City and Köln
        $this->cityData = $this->makeApiCall( $city );
        $this->kolnData = $this->makeApiCall( "Köln" );

        $resultData['criteria']['naming'] = $this->isNaming($this->cityData['name']);
        $resultData['criteria']['daytemp'] = $this->isDayTemp();
        $resultData['criteria']['rival'] = $this->isRival();

        if($resultData['criteria']['naming'] &&  $resultData['criteria']['daytemp'] && $resultData['criteria']['rival'])
            $resultData['check'] = true;

        return $resultData;
    }

    /**
     * Return if place name have odd number of letters
     * @param string $value. Value to evaluate
     * @return bool Result of validation. True if have odd number of letters. False if don't have odd number of letters
     */
    private function isNaming($value): bool
    {
        return ( (strlen($value) % 2) == 0 ? true : false);
    }

    /**
     * Return if it is currently night (between sunset and sunrise) and the temperature is between 10 and 15 degrees Celcius. 
     * Or it is daytime and the temperature is between 17 and 25 degrees Celcius.
     * @param string $value. Value to evaluate
     * @return bool Result of validation. True if have odd number of letters. False if don't have odd number of letters
     */
    private function isDayTemp(): bool
    {
        $date = new \DateTime(null, new \DateTimeZone('Europe/Madrid'));
        $currentTime = ($date->getTimestamp() + $date->getOffset());

        //  Check if current time are night
        if($currentTime >= $this->cityData['sys']['sunset'] && $currentTime <= $this->cityData['sys']['sunrise'])
        {
            //  Night
            return ($this->cityData['main']['temp'] >= 10 && $this->cityData['main']['temp'] <= 15);
        }else{
            //  Daytime
            return ($this->cityData['main']['temp'] >= 17 && $this->cityData['main']['temp'] <= 25);
        }    

    }

    /**
     * It is currently warmer at the given place than in location Köln
     * @param float $temperature Temperature to compare with Köln (Cologne)
     * @return bool Indicates if is warmer than Köln
     */
    private function isRival(): bool
    {
        return ( $this->cityData['main']['temp'] > $this->kolnData['main']['temp'] );
    }

    /**
     * Construct weather url service to consult city
     * @param string $city Name of the city to search
     * @return string URL formed
     */
    private function constructWeatherQuery( $city )
    {
        return $this->params->get('endpoint')."$city&appid=".$this->params->get('api_key');
    }

    /** Convert Kelvin Temperature to Celsius Temperature 
     * @param float $temperature Temperature to convert. Format: 000.00
     * @return float Returns value converted
    */
    private function convertKelvinToCelsius( $temperature )
    {
        // 0K = -273,15C
        if(!is_numeric($temperature))
        {
           return false;
        }else{
           return $temperature - (273.15 * -1);
        }
    }

    /** Makes an api call to webservice 
     * @param string $cityName Name of the city to check
     * @return json|NotFoundHttpException if get value are success returns json with data otherwise returns notfoundhttpexception
    */
    private function makeApiCall( $cityName )
    {

        $response = $this->client->request('GET', $this->constructWeatherQuery( $cityName ));
        
        if($response->getStatusCode() == 404 || $response->getStatusCode() == 404)
            throw new \Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
        
        $data = json_decode($response->getContent(), true);

        //  Convert temperature to celsius
        if(isset($data['main']['temp']))
            $data['main']['temp'] = $this->convertKelvinToCelsius( $data['main']['temp'] );

        return $data;

    }

}