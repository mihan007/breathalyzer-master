<?php
if (!isset($argv[1])) {
    echo "Input file required.\n";
    exit;
}

if (!file_exists($argv[1])) {
    echo "Wrong file name.\n";
    exit;
}

$input = explode(' ', trim(preg_replace('/\s+/', ' ', file_get_contents($argv[1]))));
$rawVocabulary = explode("\n", strtolower(str_replace("\r", '', trim(file_get_contents('vocabulary.txt')))));
$vocabulary = [];
foreach ($rawVocabulary as $vocabularyWord) { // Index by string length
    $vocabulary[strlen($vocabularyWord)][] = $vocabularyWord;
}

$treeKeys = array_keys($vocabulary);
sort($treeKeys);
$upperBound = end($treeKeys) + 1;
$rawVocabulary = array_flip($rawVocabulary);
$distanceSum = 0;
foreach ($input as $inputWord) {
    if (isset($rawVocabulary[$inputWord])) { // Full match
        continue;
    }

    $minimumDistance = -1;
    $inputWordLength = strlen($inputWord);
    $treeKey = 0;
    $vocabularyWordLength = 0;
    foreach ($treeKeys as $treeKey => $vocabularyWordLength) { // Find tree root
        if ($vocabularyWordLength >= $inputWordLength) {
            break;
        }
    }

    $keyStep = 0;
    $isUpperKey = true;
    do {
        if (isset($vocabulary[$vocabularyWordLength])) {
            foreach ($vocabulary[$vocabularyWordLength] as $vocabularyWord) {
                $newDistance = levenshtein($inputWord, $vocabularyWord);
                if ($minimumDistance < 0 || $newDistance < $minimumDistance) {
                    $minimumDistance = $newDistance;
                    if ($minimumDistance == 1) { // Shortest distance found
                        break 2;
                    }
                }
            }
        }

        ++$keyStep;
        if ($isUpperKey) {
            $treeKey += $keyStep;
            $vocabularyWordLength = isset($treeKeys[$treeKey]) ? $treeKeys[$treeKey] : $upperBound;
        } else {
            $treeKey -= $keyStep;
            $vocabularyWordLength = isset($treeKeys[$treeKey]) ? $treeKeys[$treeKey] : 0;
        }

        $isUpperKey = !$isUpperKey;
    } while ($minimumDistance > abs($inputWordLength - $vocabularyWordLength)
        && ($vocabularyWordLength > 0 || $vocabularyWordLength < $upperBound));

    $distanceSum += $minimumDistance;
}

echo $distanceSum . "\n";