<?php

namespace Appwrite\SDK\Language;

use Twig\TwigFilter;

class Web extends JS
{
    /**
     * @return string
     */
    public function getName(): string
    {
        return 'Web';
    }

    public function getStaticAccessOperator(): string
    {
        return '.';
    }

    public function getStringQuote(): string
    {
        return "'";
    }

    public function getArrayOf(string $elements): string
    {
        return '[' . $elements . ']';
    }

    /**
     * @return array
     */
    public function getFiles(): array
    {
        return [
            [
                'scope'         => 'default',
                'destination'   => 'src/index.ts',
                'template'      => 'web/src/index.ts.twig',
            ],
            [
                'scope'         => 'default',
                'destination'   => 'src/client.ts',
                'template'      => 'web/src/client.ts.twig',
            ],
            [
                'scope'         => 'default',
                'destination'   => 'src/service.ts',
                'template'      => 'web/src/service.ts.twig',
            ],
            [
                'scope'         => 'service',
                'destination'   => 'src/services/{{service.name | caseKebab}}.ts',
                'template'      => 'web/src/services/template.ts.twig',
            ],
            [
                'scope'         => 'default',
                'destination'   => 'src/services/realtime.ts',
                'template'      => 'web/src/services/realtime.ts.twig',
            ],
            [
                'scope'         => 'default',
                'destination'   => 'src/models.ts',
                'template'      => 'web/src/models.ts.twig',
            ],
            [
                'scope'         => 'default',
                'destination'   => 'src/permission.ts',
                'template'      => 'web/src/permission.ts.twig',
            ],
            [
                'scope'         => 'default',
                'destination'   => 'src/role.ts',
                'template'      => 'web/src/role.ts.twig',
            ],
            [
                'scope'         => 'default',
                'destination'   => 'src/id.ts',
                'template'      => 'web/src/id.ts.twig',
            ],
            [
                'scope'         => 'default',
                'destination'   => 'src/channel.ts',
                'template'      => 'web/src/channel.ts.twig',
            ],
            [
                'scope'         => 'default',
                'destination'   => 'src/query.ts',
                'template'      => 'web/src/query.ts.twig',
            ],
            [
                'scope'         => 'default',
                'destination'   => 'src/operator.ts',
                'template'      => 'web/src/operator.ts.twig',
            ],
            [
                'scope'         => 'default',
                'destination'   => 'README.md',
                'template'      => 'web/README.md.twig',
            ],
            [
                'scope'         => 'default',
                'destination'   => 'CHANGELOG.md',
                'template'      => 'web/CHANGELOG.md.twig',
            ],
            [
                'scope'         => 'default',
                'destination'   => 'LICENSE',
                'template'      => 'web/LICENSE.twig',
            ],
            [
            'scope'         => 'default',
            'destination'   => 'package.json',
            'template'      => 'web/package.json.twig',
            ],
            [
                'scope'         => 'method',
                'destination'   => 'docs/examples/{{service.name | caseLower}}/{{method.name | caseKebab}}.md',
                'template'      => 'web/docs/example.md.twig',
            ],
            [
                'scope'         => 'default',
                'destination'   => 'tsconfig.json',
                'template'      => '/web/tsconfig.json.twig',
            ],
            [
                'scope'         => 'default',
                'destination'   => 'rollup.config.mjs',
                'template'      => '/web/rollup.config.mjs.twig',
            ],
            [
                'scope'         => 'default',
                'destination'   => 'dist/cjs/package.json',
                'template'      => '/web/dist/cjs/package.json.twig',
            ],
            [
                'scope'         => 'default',
                'destination'   => 'dist/esm/package.json',
                'template'      => '/web/dist/esm/package.json.twig',
            ],
            [
                'scope'         => 'default',
                'destination'   => '.github/workflows/publish.yml',
                'template'      => 'web/.github/workflows/publish.yml.twig',
            ],
            [
                'scope'         => 'enum',
                'destination'   => 'src/enums/{{ enum.name | caseKebab }}.ts',
                'template'      => 'web/src/enums/enum.ts.twig',
            ],
            [
                'scope'         => 'copy',
                'destination'   => '.gitignore',
                'template'      => 'web/.gitignore',
            ],
            [
                'scope'         => 'default',
                'destination'   => 'package-lock.json',
                'template'      => 'web/package-lock.json.twig',
            ],
        ];
    }

    /**
     * @param array $param
     * @param string $lang
     * @return string
     */
    public function getParamExample(array $param, string $lang = ''): string
    {
        $type       = $param['type'] ?? '';
        $example    = $param['example'] ?? '';

        $hasExample = !empty($example) || $example === 0 || $example === false;

        if (!$hasExample) {
            return match ($type) {
                self::TYPE_ARRAY => '[]',
                self::TYPE_FILE => 'document.getElementById(\'uploader\').files[0]',
                self::TYPE_INTEGER, self::TYPE_NUMBER, self::TYPE_BOOLEAN => 'null',
                self::TYPE_OBJECT => '{}',
                self::TYPE_STRING => "''",
            };
        }

        return match ($type) {
            self::TYPE_ARRAY => $this->isPermissionString($example) ? $this->getPermissionExample($example) : $example,
            self::TYPE_INTEGER, self::TYPE_NUMBER => $example,
            self::TYPE_FILE => 'document.getElementById(\'uploader\').files[0]',
            self::TYPE_BOOLEAN => ($example) ? 'true' : 'false',
            self::TYPE_OBJECT => ($example === '{}')
            ? '{}'
            : (($formatted = json_encode(json_decode($example, true), JSON_PRETTY_PRINT))
                ? preg_replace('/\n/', "\n    ", $formatted)
                : $example),
            self::TYPE_STRING => "'{$example}'",
        };
    }

    public function getReadOnlyProperties(array $parameter, string $responseModel, array $spec = []): array
    {
        $properties = [];

        if (
            !isset($spec['definitions'][$responseModel]['properties']) ||
            !is_array($spec['definitions'][$responseModel]['properties'])
        ) {
            return $properties;
        }

        foreach ($spec['definitions'][$responseModel]['properties'] as $property) {
            if (isset($property['readOnly']) && $property['readOnly']) {
                $properties[] = $property['name'];
            }
        }

        return $properties;
    }

    public function getTypeName(array $parameter, array $method = []): string
    {
        if (
            ($parameter['type'] ?? null) === self::TYPE_ARRAY
            && (isset($parameter['enumName']) || !empty($parameter['enumValues']))
        ) {
            $enumType = isset($parameter['enumName'])
                ? \ucfirst($parameter['enumName'])
                : \ucfirst($parameter['name']);

            return $enumType . '[]';
        }

        if (isset($parameter['enumName'])) {
            return \ucfirst($parameter['enumName']);
        }
        if (!empty($parameter['enumValues'])) {
            return \ucfirst($parameter['name']);
        }
        if (!empty($parameter['array']['model'])) {
            return 'Models.' . $this->toPascalCase($parameter['array']['model']) . '[]';
        }
        if (!empty($parameter['model'])) {
            if ($parameter['model'] === 'any') {
                return $parameter['type'] === self::TYPE_ARRAY ? 'Record<string, any>[]' : 'Record<string, any>';
            }
            $modelType = 'Models.' . $this->toPascalCase($parameter['model']);
            return $parameter['type'] === self::TYPE_ARRAY ? $modelType . '[]' : $modelType;
        }
        if (isset($parameter['items'])) {
            // Map definition nested type to parameter nested type
            $parameter['array'] = $parameter['items'];
        }
        switch ($parameter['type']) {
            case self::TYPE_INTEGER:
            case self::TYPE_NUMBER:
                if (isset($parameter['format']) && $parameter['format'] === 'int64') {
                    return 'number | bigint';
                }
                return 'number';
            case self::TYPE_ARRAY:
                if (!empty($parameter['array']['x-anyOf'] ?? [])) {
                    $unionTypes = [];
                    foreach ($parameter['array']['x-anyOf'] as $refType) {
                        if (isset($refType['$ref'])) {
                            $refParts = explode('/', $refType['$ref']);
                            $modelName = end($refParts);
                            $unionTypes[] = 'Models.' . $this->toPascalCase($modelName);
                        }
                    }
                    if (!empty($unionTypes)) {
                        return '(' . implode(' | ', $unionTypes) . ')[]';
                    }
                }
                if (!empty(($parameter['array'] ?? [])['type']) && !\is_array($parameter['array']['type'])) {
                    return $this->getTypeName($parameter['array']) . '[]';
                }
                return 'any[]';
            case self::TYPE_FILE:
                return 'File';
            case self::TYPE_OBJECT:
                if (empty($method)) {
                    return $parameter['type'];
                }
                switch ($method['responseModel']) {
                    case 'user':
                        return "Partial<Preferences>";
                    case 'document':
                        if ($method['method'] === 'post') {
                            return "Document extends Models.DefaultDocument ? Partial<Models.Document> & Record<string, any> : Partial<Models.Document> & Omit<Document, keyof Models.Document>";
                        }
                        if ($method['method'] === 'patch' || $method['method'] === 'put') {
                            return "Document extends Models.DefaultDocument ? Partial<Models.Document> & Record<string, any> : Partial<Models.Document> & Partial<Omit<Document, keyof Models.Document>>";
                        }
                        break;
                    case 'row':
                        if ($method['method'] === 'post') {
                            return "Row extends Models.DefaultRow ? Partial<Models.Row> & Record<string, any> : Partial<Models.Row> & Omit<Row, keyof Models.Row>";
                        }
                        if ($method['method'] === 'patch' || $method['method'] === 'put') {
                            return "Row extends Models.DefaultRow ? Partial<Models.Row> & Record<string, any> : Partial<Models.Row> & Partial<Omit<Row, keyof Models.Row>>";
                        }
                        break;
                }
                break;
        }
        return $parameter['type'];
    }

    protected function populateGenerics(string $model, array $spec, array &$generics, bool $skipFirst = false)
    {
        if (!array_key_exists($model, $spec['definitions'])) {
            return;
        }

        if (!$skipFirst && $spec['definitions'][$model]['additionalProperties']) {
            $generics[] = $this->toPascalCase($model);
        }

        $properties = $spec['definitions'][$model]['properties'];

        foreach ($properties as $property) {
            if (array_key_exists('sub_schema', $property) && $property['sub_schema']) {
                $this->populateGenerics($property['sub_schema'], $spec, $generics);
            }
        }
    }

    public function getGenerics(string $model, array $spec, bool $skipFirst = false): string
    {
        $generics = [];

        if (array_key_exists($model, $spec['definitions'])) {
            $this->populateGenerics($model, $spec, $generics, $skipFirst);
        }

        if (empty($generics)) {
            return '';
        }

        $generics = array_unique($generics);
        $generics = array_map(fn ($type) => "{$type} extends Models.{$type} = Models.Default{$type}", $generics);

        return '<' . implode(', ', $generics) . '>';
    }

    /**
     * Generate union return type for methods with multiple response models.
     */
    protected function getUnionReturnType(array $method, array $spec): ?string
    {
        // check for union types i.e. multiple response models
        if (!array_key_exists('responseModels', $method) || empty($method['responseModels']) || count($method['responseModels']) <= 1) {
            return null;
        }

        $unionTypes = [];

        foreach ($method['responseModels'] as $model) {
            if ($model === 'any') {
                continue;
            }

            $modelType = '';

            if (
                array_key_exists($model, $spec['definitions']) &&
                array_key_exists('additionalProperties', $spec['definitions'][$model]) &&
                !$spec['definitions'][$model]['additionalProperties']
            ) {
                $modelType .= 'Models.';
            }

            $modelType .= $this->toPascalCase($model);

            $models = [];
            $this->populateGenerics($model, $spec, $models);
            $models = array_unique($models);
            $models = array_filter($models, fn ($m) => $m != $this->toPascalCase($model));

            if (!empty($models)) {
                $modelType .= '<' . implode(', ', $models) . '>';
            }

            $unionTypes[] = $modelType;
        }

        if (empty($unionTypes)) {
            return null;
        }

        return 'Promise<' . implode(' | ', $unionTypes) . '>';
    }

    public function getReturn(array $method, array $spec): string
    {
        if ($method['type'] === 'webAuth') {
            return 'void | string';
        }

        if ($method['type'] === 'location') {
            return 'string';
        }

        // check for union types i.e. multiple response models
        $unionType = $this->getUnionReturnType($method, $spec);
        if ($unionType !== null) {
            return $unionType;
        }

        if (array_key_exists('responseModel', $method) && !empty($method['responseModel']) && $method['responseModel'] !== 'any') {
            $ret = 'Promise<';

            if (
                array_key_exists($method['responseModel'], $spec['definitions']) &&
                array_key_exists('additionalProperties', $spec['definitions'][$method['responseModel']]) &&
                !$spec['definitions'][$method['responseModel']]['additionalProperties']
            ) {
                $ret .= 'Models.';
            }

            $ret .= $this->toPascalCase($method['responseModel']);

            $models = [];

            $this->populateGenerics($method['responseModel'], $spec, $models);

            $models = array_unique($models);
            $models = array_filter($models, fn ($model) => $model != $this->toPascalCase($method['responseModel']));

            if (!empty($models)) {
                $ret .= '<' . implode(', ', $models) . '>';
            }

            $ret .= '>';

            return $ret;
        }

        return 'Promise<{}>';
    }

    public function getSubSchema(array $property, array $spec, string $methodName = ''): string
    {
        if (array_key_exists('sub_schema', $property)) {
            if ($property['sub_schema'] === 'any') {
                return $property['type'] === 'array' ? 'Record<string, any>[]' : 'Record<string, any>';
            }

            $ret = '';
            $generics = [];
            $this->populateGenerics($property['sub_schema'], $spec, $generics);

            $generics = array_filter($generics, fn ($model) => $model != $this->toPascalCase($property['sub_schema']));

            $ret .= $this->toPascalCase($property['sub_schema']);
            if (!empty($generics)) {
                $ret .= '<' . implode(', ', $generics) . '>';
            }
            if ($property['type'] === 'array') {
                $ret .= '[]';
            }

            return $ret;
        }

        if (array_key_exists('enum', $property) && !empty($methodName)) {
            if (isset($property['enumName'])) {
                return $this->toPascalCase($property['enumName']);
            }

            return $this->toPascalCase($methodName) . $this->toPascalCase($property['name']);
        }

        return $this->getTypeName($property);
    }

    /**
     * Augment spec.global.headers with any auth headers required by the unified
     * web client but missing from the loaded spec. Each platform's spec exposes
     * a different subset of securityDefinitions (e.g. console omits Session and
     * DevKey; client omits Cookie and Mode), but the unified web Client emits
     * setters for the union so factories work regardless of build target.
     *
     * TODO: Remove this augmentation once appwrite/appwrite#12211 ships and the
     * regenerated specs declare the union of auth headers in every platform's
     * securityDefinitions. After that, spec.global.headers will already carry
     * everything the unified client needs and this filter (plus its Twig
     * registration) can be deleted.
     *
     * @param array $globalHeaders headers parsed from securityDefinitions
     * @return array merged list, preserving spec entries (with descriptions)
     */
    public function webClientHeaders(array $globalHeaders): array
    {
        $required = [
            'Project'              => 'X-Appwrite-Project',
            'Key'                  => 'X-Appwrite-Key',
            'Cookie'               => 'Cookie',
            'JWT'                  => 'X-Appwrite-JWT',
            'Locale'               => 'X-Appwrite-Locale',
            'Session'              => 'X-Appwrite-Session',
            'DevKey'               => 'X-Appwrite-Dev-Key',
            'Mode'                 => 'X-Appwrite-Mode',
            'Platform'             => 'X-Appwrite-Platform',
            'ForwardedUserAgent'   => 'X-Forwarded-User-Agent',
            'ImpersonateUserId'    => 'X-Appwrite-Impersonate-User-Id',
            'ImpersonateUserEmail' => 'X-Appwrite-Impersonate-User-Email',
            'ImpersonateUserPhone' => 'X-Appwrite-Impersonate-User-Phone',
        ];
        $existing = array_column($globalHeaders, 'key');
        foreach ($required as $key => $name) {
            if (!in_array($key, $existing, true)) {
                $globalHeaders[] = ['key' => $key, 'name' => $name, 'description' => ''];
            }
        }
        return $globalHeaders;
    }

    /**
     * Determine whether a method supports client-side platforms.
     */
    protected function methodSupportsClient(array $method): bool
    {
        return in_array('client', $method['platforms'] ?? [], true);
    }

    /**
     * Determine whether a method supports server/console platforms.
     */
    protected function methodSupportsServer(array $method): bool
    {
        $platforms = $method['platforms'] ?? [];
        return in_array('server', $platforms, true) || in_array('console', $platforms, true);
    }

    /**
     * Compute auth-related flags for a Web service.
     *
     * @return array<string, mixed>
     */
    public function webServiceAuth(array $service): array
    {
        $hasClientTier = false;
        $hasServerTier = false;
        $hasServerOnly = false;
        $hasClientOnly = false;
        $hasUpload = false;

        foreach ($service['methods'] ?? [] as $method) {
            $hasClient = $this->methodSupportsClient($method);
            $hasServer = $this->methodSupportsServer($method);

            if ($hasClient) {
                $hasClientTier = true;
            }
            if ($hasServer) {
                $hasServerTier = true;
            }
            if ($hasServer && !$hasClient) {
                $hasServerOnly = true;
            }
            if ($hasClient && !$hasServer) {
                $hasClientOnly = true;
            }
            if (in_array('multipart/form-data', $method['consumes'] ?? [], true)) {
                $hasUpload = true;
            }
        }

        $hasMixedTier = $hasClientTier && $hasServerTier;

        return [
            'hasClientTier'   => $hasClientTier,
            'hasServerTier'   => $hasServerTier,
            'hasMixedTier'    => $hasMixedTier,
            'needsServerAuth' => $hasServerTier && (!$hasMixedTier || $hasServerOnly),
            'needsClientAuth' => $hasClientTier && (!$hasMixedTier || $hasClientOnly),
            'hasUpload'       => $hasUpload,
        ];
    }

    /**
     * Build the TypeScript `this:` gate string for a method in a Web service.
     */
    public function webMethodThisGate(array $method, array $service): string
    {
        $auth = $this->webServiceAuth($service);
        if (!$auth['hasMixedTier']) {
            return '';
        }

        $serviceName = $this->toPascalCase($service['name'] ?? '');

        if (!$this->methodSupportsClient($method)) {
            return 'this: ' . $serviceName . '<ServerAuth>, ';
        }
        if (!$this->methodSupportsServer($method)) {
            return 'this: ' . $serviceName . '<ClientAuth>, ';
        }

        return '';
    }

    public function getFilters(): array
    {
        return \array_merge(parent::getFilters(), [
            new TwigFilter('getPropertyType', function ($value, $method = []) {
                return $this->getTypeName($value, $method);
            }),
            new TwigFilter('getReadOnlyProperties', function ($value, $responseModel, $spec = []) {
                return $this->getReadOnlyProperties($value, $responseModel, $spec);
            }),
            new TwigFilter('getSubSchema', function (array $property, array $spec, string $methodName = '') {
                return $this->getSubSchema($property, $spec, $methodName);
            }),
            new TwigFilter('getGenerics', function (string $model, array $spec, bool $skipAdditional = false) {
                return $this->getGenerics($model, $spec, $skipAdditional);
            }),
            new TwigFilter('getReturn', function (array $method, array $spec) {
                return $this->getReturn($method, $spec);
            }),
            new TwigFilter('getOverloadCondition', function (array $method) {
                $params = $method['parameters']['all'] ?? [];

                $hasRequired = false;
                foreach ($params as $param) {
                    if ($param['required'] ?? false) {
                        $hasRequired = true;
                        break;
                    }
                }

                $condition = '';
                if (!$hasRequired) {
                    $condition .= '!paramsOrFirst || ';
                }

                $condition .= "(paramsOrFirst && typeof paramsOrFirst === 'object' && !Array.isArray(paramsOrFirst)";

                $firstParamType = $this->getTypeName($params[0], $method);
                $isPrimitive = str_starts_with($firstParamType, 'string')
                    || str_starts_with($firstParamType, 'number')
                    || str_starts_with($firstParamType, 'boolean');

                if (!$isPrimitive) {
                    $keys = [];
                    foreach ($params as $param) {
                        $name = $this->toCamelCase($param['name']);
                        $name = $this->escapeKeyword($name);
                        $keys[] = "'" . $name . "' in paramsOrFirst";
                    }

                    if (in_array('multipart/form-data', $method['consumes'] ?? [])) {
                        $keys[] = "'onProgress' in paramsOrFirst";
                    }

                    $condition .= ' && (' . implode(' || ', $keys) . ')';
                }

                $condition .= ')';

                return $condition;
            }, ['is_safe' => ['html']]),
            new TwigFilter('webServiceAuth', function (array $service) {
                return $this->webServiceAuth($service);
            }),
            new TwigFilter('webMethodThisGate', function (array $method, array $service) {
                return $this->webMethodThisGate($method, $service);
            }, ['is_safe' => ['html']]),
            new TwigFilter('webClientHeaders', function (array $globalHeaders) {
                return $this->webClientHeaders($globalHeaders);
            }),
            new TwigFilter('comment2', function ($value) {
                $value = explode("\n", $value);
                foreach ($value as $key => $line) {
                    $value[$key] = "     * " . wordwrap($line, 75, "\n     * ");
                }
                return implode("\n", $value);
            }, ['is_safe' => ['html']]),
            new TwigFilter('comment3', function ($value) {
                $value = explode("\n", $value);
                foreach ($value as $key => $line) {
                    $value[$key] = "         * " . wordwrap($line, 75, "\n         * ");
                }
                return implode("\n", $value);
            }, ['is_safe' => ['html']]),
        ]);
    }
}
