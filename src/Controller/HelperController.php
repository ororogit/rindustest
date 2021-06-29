<?php

namespace App\Controller;

use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class HelperController extends AbstractController
{
    
    /** Return json Response serialized
     * @param $params array Contains params to serialize
     */
    public function returnJsonResponse($params, $statuscode, $errorMessage)
    {
        //  Initialize array for construct normalized response
        $output = [];

        //  Add status code for response
        $output['statuscode'] = $statuscode;

        //  Check if error are defined are has to be returned
        if(!empty($errorMessage) && !is_null($errorMessage))
            $output['error'] = true;

        //  Iterate params to get all params to json encode
        if(!empty($params))
        {
            foreach($params as $key=>$value)
            $output[$key] = $value;
        }
    
        return $this->json($output);
    }



}
