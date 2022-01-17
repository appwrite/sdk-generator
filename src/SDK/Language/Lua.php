<?php

namespace Appwrite\SDK\Language;

use Appwrite\SDK\Language;

class Lua extends Language {

    /**
     * @var array
     */
    protected $params = [
      'packageName' => 'packageName',
  ];

  /**
   * @param string $name
   * @return $this
   */
  public function setPackageName($name)
  {
      $this->setParam('packageName', $name);

      return $this;
  }

  /**
   * @return string
   */
  public function getName()
  {
      return 'Lua';
  }

  /**
   * Get Language Keywords List
   *
   * @return array
   */
  public function getKeywords()
  {
      return [
        "local",
        "if",
        "elseif",
        "else"
        "end"
        "function",
        "pairs",
        "ipairs",
        "error",
        "assert",
        "is",
        "or",
        "and"
        "for",
        "print",
        "const",
        "close",
        "string",
        "number",
        "userdata",
        "thread",
        "table",
        "do"
      ];
  }

  /**
   * @return array
   */
  public function getIdentifierOverrides()
  {
      return ['Function' => 'Func'];
  }

  /**
   * @param $type
   * @return string
   */
  public function getTypeName($type)
  {
      switch ($type) {
          case self::TYPE_INTEGER:
              return 'number';
          break;
          case self::TYPE_STRING:
              return 'string';
          break;
          case self::TYPE_FILE:
              return 'http.MultipartFile';
          break;
          case self::TYPE_BOOLEAN:
              return 'boolean';
          break;
          case self::TYPE_ARRAY:
            return 'table';
        case self::TYPE_OBJECT:
          return 'table';
          case self::TYPE_NUMBER:
              return 'number';
          break;
      }

      return $type;
  }

  /**
   * @param array $param
   * @return string
   */
  public function getParamDefault(array $param)
  {
      $type       = $param['type'] ?? '';
      $default    = $param['default'] ?? '';
      $required   = $param['required'] ?? '';

      if($required) {
          return '';
      }

      $output = ' = ';

      if(empty($default) && $default !== 0 && $default !== false) {
          switch ($type) {
              case self::TYPE_OBJECT:
                  $output .= '{}';
                  break;
              case self::TYPE_NUMBER:
              case self::TYPE_INTEGER:
                  $output .= '0';
                  break;
              case self::TYPE_BOOLEAN:
                  $output .= 'false';
                  break;
              case self::TYPE_ARRAY:
                  $output .= '{}';
                  break;
              case self::TYPE_STRING:
                  $output .= "''";
                  break;
          }
      }
      else {
          switch ($type) {
              case self::TYPE_NUMBER:
              case self::TYPE_INTEGER:
              case self::TYPE_OBJECT:
              case self::TYPE_ARRAY:
                  $output .= $default;
                  break;
              case self::TYPE_BOOLEAN:
                  $output .= ($default) ? 'true' : 'false';
                  break;
              case self::TYPE_STRING:
                  $output .= "'{$default}'";
                  break;
          }
      }

      return $output;
  }

  /**
   * @param array $param
   * @return string
   */
  public function getParamExample(array $param)
  {
      $type       = $param['type'] ?? '';
      $example    = $param['example'] ?? '';

      $output = '';

      if(empty($example) && $example !== 0 && $example !== false) {
          switch ($type) {
              case self::TYPE_FILE:
                  $output .= 'await MultipartFile.fromPath(\''.$param['name'].'\', \'./path-to-files/image.jpg\', \'image.jpg\')';
                  break;
              case self::TYPE_NUMBER:
              case self::TYPE_INTEGER:
                  $output .= '0';
              break;
              case self::TYPE_BOOLEAN:
                  $output .= 'false';
                  break;
              case self::TYPE_STRING:
                  $output .= "''";
                  break;
              case self::TYPE_OBJECT:
                  $output .= '{}';
                  break;
              case self::TYPE_ARRAY:
                  $output .= '{}';
                  break;
          }
      }
      else {
          switch ($type) {
              case self::TYPE_OBJECT:
              case self::TYPE_FILE:
              case self::TYPE_NUMBER:
              case self::TYPE_INTEGER:
              case self::TYPE_ARRAY:
                  $output .= $example;
                  break;
              case self::TYPE_BOOLEAN:
                  $output .= ($example) ? 'true' : 'false';
                  break;
              case self::TYPE_STRING:
                  $output .= "'{$example}'";
                  break;
          }
      }

      return $output;
  }

  /**
   * @return array
   */
  public function getFiles()
  {
      return [
          [
              'scope'         => 'default',
              'destination'   => 'README.md',
              'template'      => 'dart/README.md.twig',
              'minify'        => false,
          ],
          [
              'scope'         => 'default',
              'destination'   => '/example/README.md',
              'template'      => 'dart/example/README.md.twig',
              'minify'        => false,
          ],
          [
              'scope'         => 'default',
              'destination'   => 'CHANGELOG.md',
              'template'      => 'dart/CHANGELOG.md.twig',
              'minify'        => false,
          ],
          [
              'scope'         => 'default',
              'destination'   => 'LICENSE',
              'template'      => 'dart/LICENSE.twig',
              'minify'        => false,
          ],
          [
              'scope'         => 'default',
              'destination'   => '/lib/src/client.dart',
              'template'      => 'dart/lib/src/client.dart.twig',
              'minify'        => false,
          ],
          [
              'scope'         => 'default',
              'destination'   => '/lib/src/client_base.dart',
              'template'      => 'dart/lib/src/client_base.dart.twig',
              'minify'        => false,
          ],
          [
              'scope'         => 'default',
              'destination'   => '/lib/src/client_browser.dart',
              'template'      => 'dart/lib/src/client_browser.dart.twig',
              'minify'        => false,
          ],
          [
              'scope'         => 'default',
              'destination'   => '/lib/src/client_io.dart',
              'template'      => 'dart/lib/src/client_io.dart.twig',
              'minify'        => false,
          ],
          [
              'scope'         => 'default',
              'destination'   => '/lib/src/client_mixin.dart',
              'template'      => 'dart/lib/src/client_mixin.dart.twig',
              'minify'        => false,
          ],
          [
              'scope'         => 'default',
              'destination'   => '/lib/src/client_stub.dart',
              'template'      => 'dart/lib/src/client_stub.dart.twig',
              'minify'        => false,
          ],
          [
              'scope'         => 'default',
              'destination'   => '/lib/src/exception.dart',
              'template'      => 'dart/lib/src/exception.dart.twig',
              'minify'        => false,
          ],
          [
              'scope'         => 'default',
              'destination'   => '/lib/src/response.dart',
              'template'      => 'dart/lib/src/response.dart.twig',
              'minify'        => false,
          ],
          [
              'scope'         => 'default',
              'destination'   => '/lib/query.dart',
              'template'      => 'dart/lib/query.dart.twig',
              'minify'        => false,
          ],
          [
              'scope'         => 'default',
              'destination'   => '/lib/{{ language.params.packageName }}.dart',
              'template'      => 'dart/lib/package.dart.twig',
              'minify'        => false,
          ],
          [
              'scope'         => 'default',
              'destination'   => '/pubspec.yaml',
              'template'      => 'dart/pubspec.yaml.twig',
              'minify'        => false,
          ],
          [
              'scope'         => 'default',
              'destination'   => '/lib/client_io.dart',
              'template'      => 'dart/lib/client_io.dart.twig',
              'minify'        => false,
          ],
          [
              'scope'         => 'default',
              'destination'   => '/lib/client_browser.dart',
              'template'      => 'dart/lib/client_browser.dart.twig',
              'minify'        => false,
          ],
          [
              'scope'         => 'default',
              'destination'   => '/lib/src/service.dart',
              'template'      => 'dart/lib/src/service.dart.twig',
              'minify'        => false,
          ],
          [
      'scope'         => 'default',
      'destination'   => '/lib/src/enums.dart',
      'template'      => 'dart/lib/src/enums.dart.twig',
      'minify'        => false,
    ],
          [
      'scope'         => 'default',
      'destination'   => '/lib/models.dart',
      'template'      => 'dart/lib/models.dart.twig',
      'minify'        => false,
    ],
          [
              'scope'         => 'service',
              'destination'   => '/lib/services/{{service.name | caseDash}}.dart',
              'template'      => 'dart/lib/services/service.dart.twig',
              'minify'        => false,
          ],
          [
              'scope'         => 'definition',
              'destination'   => '/lib/src/models/{{definition.name | caseSnake }}.dart',
              'template'      => 'dart/lib/src/models/model.dart.twig',
              'minify'        => false,
          ],
          [
              'scope'         => 'method',
              'destination'   => 'docs/examples/{{service.name | caseLower}}/{{method.name | caseDash}}.md',
              'template'      => 'dart/docs/example.md.twig',
              'minify'        => false,
          ],
          [
              'scope'         => 'default',
              'destination'   => '.travis.yml',
              'template'      => 'dart/.travis.yml.twig',
              'minify'        => false,
          ],
      ];
  }

}