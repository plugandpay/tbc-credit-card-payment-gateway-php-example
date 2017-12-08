<?php

use Pecee\SimpleRouter\SimpleRouter;

SimpleRouter::get('/', 'TbcPayController@index');
SimpleRouter::post('/start', 'TbcPayController@start');
SimpleRouter::post('/ok', 'TbcPayController@ok');
SimpleRouter::post('/fail', 'TbcPayController@fail');
SimpleRouter::get('/orders/{status?}', 'TbcPayController@orders');
SimpleRouter::get('/close', 'TbcPayController@closeBusinessDay');

