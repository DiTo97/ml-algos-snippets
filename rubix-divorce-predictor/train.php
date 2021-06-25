<?php

require_once __DIR__ . '/config.php';
include_once __DIR__ . '/vendor/autoload.php';

use Rubix\ML\Loggers\Screen;
use Rubix\ML\Datasets\Labeled;
use Rubix\ML\Extractors\NDJSON;
use Rubix\ML\Classifiers\KNearestNeighbors;
use Rubix\ML\CrossValidation\Metrics\Accuracy;
use Rubix\ML\Serializers\RBX;
use Rubix\ML\Persisters\Filesystem;

$logger = new Screen();

$logger->info('Loading data into memory...');

$dataset = Labeled::fromIterator(new NDJSON(DATASET));

[$training, $testing] = $dataset->stratifiedSplit(0.8);

$estimator = new KNearestNeighbors(3, true);

$logger->info('Training...');

$estimator->train($training);

$serializer = new RBX();
$serializer->serialize($estimator)
           ->saveTo(new Filesystem(MODEL));

$logger->info('Making predictions...');

$predictions = $estimator->predict($testing);

$metric = new Accuracy();

$score = $metric->score($predictions, $testing->labels());

$logger->info("Validation accuracy is: $score");
