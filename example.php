<?php

use Xlr8\Sortable;
use Xlr8\Search;

require_once __DIR__ . '/vendor/autoload.php';

Search::setSources([
    'https://xlr8-interview-files.s3.eu-west-2.amazonaws.com/source_1.json',
    'https://xlr8-interview-files.s3.eu-west-2.amazonaws.com/source_2.json'
]);

$orderedHotels = Search::getNearbyHotels(-23.6814347,-46.9249416, Sortable::PRICE_PER_NIGHT);

var_dump($orderedHotels);