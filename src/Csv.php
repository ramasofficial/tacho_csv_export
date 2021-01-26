<?php

namespace Tacho;

use DateTime;

class Csv
{    
    /**
     * File path
     *
     * @var string
     */
    protected $file;

    /**
     * Worker ending line, detect when need to stop use previous worker
     *
     * @var string
     */
    public const WORKER_ENDING_LINE = 'Naktinės valandos';

    public function __construct($file)
    {
        $this->file = $file;
    }
    
    /**
     * readCsvData - This function read CSV data and make array
     *
     * @return array
     */
    public function readCsvData(): array
    {
        $handle = fopen($this->file, 'r');

        $csvData = [];
        while(($data = fgetcsv($handle)) !== FALSE) {
            for($c = 0; $c < count($data); $c++) {

                // Get tabNumber and fullName
                if(strpos($data[$c], '(')) {
                    preg_match('/\((.*?)\)/', $data[$c], $tabNo);
                    preg_match_all('/[A-ž]+/', $data[$c], $fullName);

                    // Push tabNumber and fullName to array
                    if(isset($tabNo[1])) {
                        if($tabNo[1] != 'UAB') {
                            if(strpos($tabNo[1], 'UAB')) {
                                $type = 'UAB';
                            } else {
                                $type = 'ATĮ';
                            }

                            $tabNo[1] = str_replace(' UAB', '', $tabNo[1]);

                            $csvData[$data[$c]] = [
                                'name' => $fullName[0][0].' '.$fullName[0][1],
                                'tabNo' => $tabNo[1],
                                'worker' => $data[$c],
                                'type' => $type,
                            ];

                            // Set worker
                            $worker = $data[$c];
                        }
                    }
                }

                // Search date
                $explodeString = explode(' ', trim($data[$c]));
                $checkIsDate = DateTime::createFromFormat('d.m.Y', $explodeString[0]);

                // Check is date
                if($checkIsDate !== false && isset($worker)) {
                    $formatDate = $checkIsDate->format('Y-m-d');

                    // Set row to zero
                    $row = 0;
                }

                // Check row and push needed data to worker array
                if(isset($row) && isset($worker)) {
                    // Push working to array
                    if($row == 11) {
                        $csvData[$worker]['data'][$formatDate]['working'] = str_replace('h', ':', $data[$c]);
                    }
                    // Push night to array
                    if($row == 12) {
                        $csvData[$worker]['data'][$formatDate]['night'] = str_replace('h', ':', $data[$c]);
                    }
                    // Push break to array
                    if($row == 15) {
                        $csvData[$worker]['data'][$formatDate]['break'] = str_replace('h', ':', $data[$c]);
                        ksort($csvData[$worker]['data']);
                    }

                    // If isset row add +1
                    $row++;
                }

                // Unset worker if now this row
                if(strpos($data[$c], self::WORKER_ENDING_LINE)) {
                    unset($worker);
                }
            }
        }

        return $csvData;
    }
}
