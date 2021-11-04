<?php

namespace Appwrite;

use jc21\CliTableManipulator;

class Manipulators extends CliTableManipulator {

    const TRUNCATE_THRESHOLD = 15;

    /**
     * Truncate values that are longer than self::TRUNCATE_THRESHOLD 
     *
     * @param string $value
     * @return string 
     */
    public function truncate($value): string {
      if (is_bool($value)) {
              $value = $value ? 'true' : 'false';
          }
      return strlen(empty($value) ? '' : $value) > self::TRUNCATE_THRESHOLD ? substr($value,0,self::TRUNCATE_THRESHOLD).'...' : $value;
    }    
}