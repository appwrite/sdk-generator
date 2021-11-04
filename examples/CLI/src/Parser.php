<?php

namespace Appwrite;

use jc21\CliTable;

class Parser {

    /**
     * List of colors supported by the library 
     *
     * @var array
     */
    protected const colors = array(
        'blue',
        'red',
        'green',
        'yellow',
        'magenta',
        'cyan',
        'white',
        'grey'
    );

    /**
     * Color for the table borders
     *
     * @var bool
     */
    protected const tableColor = self::colors[0];

    /**
     * Color for the table column headers
     *
     * @var string
     */
    protected const headerColor = self::colors[2];

    /**
     * Parse the response body from the server 
     *
     * @param $responseBody 
     * @return void
     */
    public function parseResponse($responseBody)
    {
        if(is_array($responseBody)) {
            foreach ($responseBody as $key => $value) {
                if (is_array($value) && count($value) !== 0 ) {
                    $this->drawKeyValue($key, '');
                    $this->drawTable($value, self::headerColor, self::tableColor);
                } 
                else {
                    $this->drawKeyValue($key, $value);
                }
            }
        } else {
            printf($responseBody);
        }
    }


    /**
     * Print a key value pair
     *
     * @param string $key
     * @param string $value 
     * @return void
     */
    protected function drawKeyValue($key, $value)
    {
        if(is_bool($value)) {
            $value = $value ? 'true' : 'false';
        }
        else if (is_array($value)) {
            $value = '{}';
        }

        printf("%s : %s\n", $key, $value);
    }

    /**
     * Get a column color based on the index
     *
     * @param int $index
     * @return string
     */
    protected function getColor($index = -1) : string {
        if ($index != -1) return self::colors[$index % count(self::colors) ];
        return self::colors[array_rand(self::colors)];
    }

    /**
     * Creates a table from the passed data
     *
     * @param array $data
     * @param string $headerColor
     * @param string $tableColor
     * @return void
     */
    protected function drawTable($data, $headerColor, $tableColor) {
        if (!is_array($data) || !is_array($data[0])) return;

        $keys = array_keys($data[0]);
        
        $table = new CliTable();
        $table->setTableColor($tableColor);
        $table->setHeaderColor($headerColor);
        
        foreach ($keys as $key => $value) {
            $table->addField(ucwords($value), $value, new Manipulators('truncate'), $this->getColor($key));
        }

        // Convert nested arrays to string representation
        $transformedData = array_map (function ($data) { 
            foreach ($data as $key => $value) {
                if (is_array($value)) {
                    if (count($value) == 0) {
                        $data[$key] = '{}';
                    } else {
                        $data[$key] = 'array('.count($value).')';
                    }
                } else if (is_object($value)) {
                    $data[$key] = 'Object';
                }
            }
            return $data;
        }, $data);

        $table->injectData($transformedData);
        $table->display();
    }


    /**
     * Formats an associative array of commands and descriptions using a 80 column mask
     *
     * @param array $arr
     * @return void
     */
    public function formatArray(array $arr) {
        $descriptionColumnLimit = 60;
        $commandNameColumnLimit = 20;
        $mask = "\t%-${commandNameColumnLimit}.${commandNameColumnLimit}s %-${descriptionColumnLimit}.${descriptionColumnLimit}s\n";
        
        array_walk($arr, function(&$key) use ($descriptionColumnLimit){
            $key = explode("\n", wordwrap($key, $descriptionColumnLimit));
        });
        
        foreach($arr as $key => $value) {
            foreach($value as $sentence) {
                 printf($mask, $key, $sentence);
                 $key = "";
            }
        }
    }
}