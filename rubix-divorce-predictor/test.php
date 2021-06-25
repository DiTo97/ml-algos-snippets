<?php

require_once __DIR__ . '/config.php';
include_once __DIR__ . '/vendor/autoload.php';

use Rubix\ML\Classifiers\KNearestNeighbors;
use Rubix\ML\Persisters\Filesystem;
use Rubix\ML\Serializers\RBX;
use Rubix\ML\Loggers\Screen;

$filesystem = new Filesystem(MODEL);
$encoding = $filesystem->load();

$serializer = new RBX();
$estimator = $serializer->deserialize($encoding);

if (!($estimator instanceof KNearestNeighbors))
    throw new UnexpectedValueException('K-nearest neighbors is supported only.');

$logger = new Screen();

$questionnaire = fopen(QUESTIONNAIRE, 'r');
$answers = array();

while(!feof($questionnaire)) {
    $answer = readline(fgets($questionnaire) . ' ');
    $answer = intval($answer);

    while ($answer < 0 || $answer > 4) {
        $logger->error('All responses must follow a 5-point scale i.e. '
            . '(0=Never, 1=Seldom, 2=Averagely, 3=Frequently, 4=Always)');
    
        $answer = readline(fgets($questionnaire) . ' ');
        $answer = intval($answer);
    }

    array_push($answers, $answer);
}

fclose($questionnaire);

$label = $estimator->predictSample($answers);
$confi = $estimator->probaSample($answers);

$logger->info("Test accuracy is: $label, "
    . 'with confidence: ' . $confi[$label]);
