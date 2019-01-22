<?php
setlocale(LC_ALL, 'en_US.UTF8');
date_default_timezone_set('US/Pacific');

$stdin = fopen('php://stdin', 'r');
$stdout = fopen('php://stdout', 'w');
$stderr = fopen('php://stderr', 'w');

$lineNumber = 1;

while($inputCSV = fgetcsv($stdin, 0, ',')) {
    if ($lineNumber === 1) {
        $normalizedCSV = array_map(function ($item) { return strtoupper($item); }, $inputCSV);
    } else {
        try {
            $timeStamp = new DateTime($inputCSV[0]);
        } catch (Exception $ex) {
            fputs($stderr, 'Invalid timestamp - skipping row '.$lineNumber);
        }
        
        $fooDurationExploded = explode(':', $inputCSV[4]);
        $fooHour = $fooDurationExploded[0];
        $fooMinute = $fooDurationExploded[1];
        $fooSecondExploded = explode('.',$fooDurationExploded[2]);
        $fooSecond = $fooSecondExploded[0];
        $fooMillisecond = $fooSecondExploded[1];

        try {
            $fooInterval = new DateInterval('PT' . $fooHour . 'H' . $fooMinute . 'M' . $fooSecond . 'S');
            $fooFinal = $fooInterval->format('%s') . str_pad($fooMillisecond, 3, '0', STR_PAD_LEFT);
        } catch (Exception $ex) {
            fputs($stderr, 'Invalid DateInterval -- skipping row '.$lineNumber);
        }

        $barDurationExploded = explode(':', $inputCSV[5]);
        $barHour = $barDurationExploded[0];
        $barMinute = $barDurationExploded[1];
        $barSecondExploded = explode('.',$barDurationExploded[2]);
        $barSecond = $barSecondExploded[0];
        $barMillisecond = $barSecondExploded[1];

        try {
            $barInterval = new DateInterval('PT' . $barHour . 'H' . $barMinute . 'M' . $barSecond . 'S');
            $barFinal = $barInterval->format('%s') . str_pad($barMillisecond, 3, '0', STR_PAD_LEFT);
        } catch (Exception $ex) {
            fputs($stderr, 'Invalid DateInterval - skipping row '.$lineNumber);
        }

        $normalizedCSV = [
            $timeStamp->format(DateTimeInterface::ISO8601),
            $inputCSV[1],
            str_pad($inputCSV[2], 5, "0", STR_PAD_LEFT),
            strtoupper($inputCSV[3]),
            $fooFinal,
            $barFinal,
            floatval($fooFinal) + floatval($barFinal),
            $inputCSV[7]
        ];
    }

    fputcsv($stdout, $normalizedCSV);

    $lineNumber++;
}

fclose($stderr);
fclose($stdout);
fclose($stdin);
