<?php

namespace App\Controller;

//  Weather Service reference
use App\Services\CheckWeatherService;


use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class Home Controller
 * @package App\Controller
 *
 * @Route(path="/api/v1")
 */
class HomeController extends HelperController
{

    /**
     * @Route("/check", methods={"GET"}, name="check")
     */
    public function checkAction(Request $request, CheckWeatherService $service): Response
    {
        //  Check if city query param are empty to return error
        if(empty($request->query->get('city')))
        {
            //  Return status code 400 (Bad request) and error set to true.
            $this->returnJsonResponse(null, 400, 'true');

            //  Note: If you want to throw Exception, uncomment next line :)
            //  throw new \Exception('Error', 400);
        }else{

            //  Get weather information by the city name and return response
            $data = $service->getWeather( $request->query->get('city') );

            //  Validate if has error
            if( !is_array($data) )
            {
                return $this->returnJsonResponse(null, 400, 'true');
            }else{
                //  Return jSON response
                return $this->returnJsonResponse($data, 200, null);
            }

        }

    }

}
