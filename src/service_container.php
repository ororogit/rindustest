<?php

require_once '../vendor/autoload.php';
use Symfony\Component\DependencyInjection\ContainerBuilder;

//  Init service container
$containerBuilder = new ContainerBuilder();

//  Add Service into service container
$containerBuilder->register('check.service', 'App\Services\CheckWeatherService');



