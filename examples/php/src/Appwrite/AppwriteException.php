<?php

namespace Appwrite;

use Exception;

class AppwriteException extends Exception {

  /**
   * @var mixed
   */
  private $response;

  /**
   * @param String $message
   * @param int $code
   * @param mixed $response
   */
  public function __construct($message = null, $code = 0, $response = null)
  {
      parent::__construct($message, $code);
      $this->response = $response;
  }
  
  /**
   * @return mixed
   */
  final public function getResponse()
  {
      return $this->response;
  }
}