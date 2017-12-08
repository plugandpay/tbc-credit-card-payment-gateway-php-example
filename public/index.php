<?php

require '../vendor/autoload.php';

use Pecee\SimpleRouter\SimpleRouter;

$dotenv = new Dotenv\Dotenv(__DIR__.'/../');
$dotenv->load();

SimpleRouter::setDefaultNamespace('\WeAreDe\TbcPay');

require_once '../helpers.php';
require_once '../routes.php';

SimpleRouter::start();
