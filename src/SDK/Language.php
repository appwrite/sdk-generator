<?php

namespace Appwrite\SDK;

use Utopia\OpenAPI\Model\Schema\CompositeSchema;
use Normalizer;
use Utopia\OpenAPI\Model\Operation;
use Utopia\OpenAPI\Model\Parameter;
use Utopia\OpenAPI\Model\ParameterLocation;
use Utopia\OpenAPI\Model\Schema\AnySchema;
use Utopia\OpenAPI\Model\Schema\ArraySchema;
use Utopia\OpenAPI\Model\Schema\BooleanSchema;
use Utopia\OpenAPI\Model\Schema\IntegerSchema;
use Utopia\OpenAPI\Model\Schema\NumberSchema;
use Utopia\OpenAPI\Model\Schema\ObjectSchema;
use Utopia\OpenAPI\Model\Schema\ReferenceSchema;
use Utopia\OpenAPI\Model\Schema\Schema;
use Utopia\OpenAPI\Model\Schema\StringSchema;
use Utopia\OpenAPI\Specification;

abstract class Language
{
    public const TYPE_INTEGER = 'integer';
    public const TYPE_NUMBER = 'number';
    public const TYPE_STRING = 'string';
    public const TYPE_BOOLEAN = 'boolean';
    public const TYPE_ARRAY = 'array';
    public const TYPE_OBJECT = 'object';
    public const TYPE_FILE = 'file';

    /**
     * @var array
     */
    protected $params = [];

    abstract public function getName(): string;

    /**
     * @return array<string>
     */
    abstract public function getKeywords(): array;

    /**
     * @return array<string>
     */
    abstract public function getIdentifierOverrides(): array;

    /**
     * Get the static access operator for the language (e.g. '::' for PHP, '.' for JS)
     */
    abstract public function getStaticAccessOperator(): string;

    /**
     * Get the string quote character for the language (e.g. '"' for PHP, "'" for JS)
     */
    abstract public function getStringQuote(): string;

    /**
     * Wrap elements in an array syntax for the language
     * @param string $elements Comma-separated elements
     */
    abstract public function getArrayOf(string $elements): string;

    /**
     * @return array<array>
     */
    abstract public function getFiles(): array;

    /**
     * Hook invoked once after all files have been generated.
     *
     * Languages can override this to run post-processing over the
     * generated output (e.g. emitting sidecar files that depend on the
     * full file tree). Default implementation is a no-op.
     *
     * @param string $target Absolute path the SDK was generated into.
     */
    public function postGenerate(string $target): void
    {
    }

    abstract public function getTypeName(Schema|Parameter $parameter, ?Specification $spec = null): string;

    abstract public function getParamDefault(Schema|Parameter $param): string;

    /**
     * @param string $lang Optional language variant (for multi-language SDKs)
     */
    abstract public function getParamExample(Schema|Parameter $param, string $lang = ''): string;

    public function setParam(string $key, string $value): Language
    {
        $this->params[$key] = $value;

        return $this;
    }

    public function getParams(): array
    {
        return $this->params;
    }

    /**
     * Language specific filters.
     */
    public function getFilters(): array
    {
        return [];
    }

    /**
     * Language specific functions.
     */
    public function getFunctions(): array
    {
        return [];
    }

    protected function toPascalCase(string $value): string
    {
        return \ucfirst($this->toCamelCase($value));
    }

    protected function toCamelCase($str): string
    {
        // Normalize the string to decompose accented characters
        $str = Normalizer::normalize($str, Normalizer::FORM_D);

        // Remove accents and other residual non-ASCII characters
        $str = \preg_replace('/\p{M}/u', '', (string) $str);

        $str = \preg_replace('/[^a-zA-Z0-9]+/', ' ', (string) $str);
        $str = \trim((string) $str);
        $str = \ucwords($str);
        $str = \str_replace(' ', '', $str);

        return \lcfirst($str);
    }

    protected function toSnakeCase($str): string
    {
        // Normalize the string to decompose accented characters
        $str = Normalizer::normalize($str, Normalizer::FORM_D);

        // Remove accents and other residual non-ASCII characters
        $str = \preg_replace('/\p{M}/u', '', (string) $str);

        // Remove apostrophes before replacing non-word characters with underscores
        $str = \str_replace("'", '', $str);
        $str = \preg_replace('/[^a-zA-Z0-9]+/', '_', $str);
        $str = \preg_replace('/_+/', '_', (string) $str);
        $str = \trim((string) $str, '_');

        return \strtolower($str);
    }

    protected function toUpperSnakeCase($str): string
    {
        return \strtoupper($this->toSnakeCase($str));
    }

    /**
     * Escape reserved keywords by prefixing with 'x'
     */
    public function escapeKeyword(string $value): string
    {
        if (in_array($value, $this->getKeywords())) {
            return 'x' . $value;
        }

        return $value;
    }

    public function isPermissionString(string $string): bool
    {
        $pattern = '/^\["(read|update|delete|write)\(\\"[^\\"]+\\"\)"(,\s*"(read|update|delete|write)\(\\"[^\\"]+\\"\)")*\]$/';
        return preg_match($pattern, $string) === 1;
    }

    public function extractPermissionParts(string $string): array
    {
        $inner = substr($string, 1, -1);
        preg_match_all('/"(read|update|delete|write)\(\\"([^\\"]+)\\"\)"/', $inner, $matches, PREG_SET_ORDER);

        $result = [];
        foreach ($matches as $match) {
            $action = $match[1];
            $roleString = $match[2];

            $role = null;
            $id = null;
            $innerRole = null;

            if (str_contains($roleString, ':')) {
                $role = explode(':', $roleString, 2)[0];
                $idString = explode(':', $roleString, 2)[1];

                if (str_contains($idString, '/')) {
                    $id = explode('/', $idString, 2)[0];
                    $innerRole = explode('/', $idString, 2)[1];
                } else {
                    $id = $idString;
                }
            } else {
                $role = $roleString;
            }

            $result[] = [
                'action' => $action,
                'role' => $role,
                'id' => $id ?? null,
                'innerRole' => $innerRole
            ];
        }

        return $result;
    }

    public function hasPermissionParam(array $parameters): bool
    {
        foreach ($parameters as $parameter) {
            $example = $this->getSchema($parameter)->extensions['x-example'] ?? $this->getSchema($parameter)->example;
            if (!empty($example) && is_string($example) && $this->isPermissionString($example)) {
                return true;
            }
        }
        return false;
    }

    protected function getSchema(Schema|Parameter $value): Schema
    {
        return $value instanceof Parameter ? ($value->schema ?? new AnySchema()) : $value;
    }

    protected function getSchemaType(Schema|Parameter $value): string
    {
        $schema = $this->getSchema($value);

        return match (true) {
            $schema instanceof StringSchema && $schema->format === 'binary' => self::TYPE_FILE,
            $schema instanceof StringSchema => self::TYPE_STRING,
            $schema instanceof IntegerSchema => self::TYPE_INTEGER,
            $schema instanceof NumberSchema => self::TYPE_NUMBER,
            $schema instanceof BooleanSchema => self::TYPE_BOOLEAN,
            $schema instanceof ArraySchema => self::TYPE_ARRAY,
            $schema instanceof ObjectSchema, $schema instanceof ReferenceSchema => self::TYPE_OBJECT,
            default => self::TYPE_OBJECT,
        };
    }

    protected function getArraySchema(Schema|Parameter $value): ?Schema
    {
        $schema = $this->getSchema($value);
        return $schema instanceof ArraySchema ? $schema->items : null;
    }

    protected function getSchemaModel(Schema|Parameter $value): ?string
    {
        $schema = $this->getSchema($value);
        if ($schema instanceof ReferenceSchema) {
            return $this->normalizeSchemaReference($schema->reference);
        }
        if ($schema instanceof ArraySchema) {
            if ($schema->items instanceof ReferenceSchema) {
                return $this->normalizeSchemaReference($schema->items->reference);
            }
            return $schema->items->extensions['x-model'] ?? $schema->extensions['x-model'] ?? null;
        }

        return $schema->extensions['x-model'] ?? null;
    }

    protected function getArraySchemaModel(Schema|Parameter $value): ?string
    {
        $items = $this->getArraySchema($value);
        return $items instanceof ReferenceSchema ? $this->normalizeSchemaReference($items->reference) : null;
    }

    protected function getSchemaExample(Schema|Parameter $value): mixed
    {
        $schema = $this->getSchema($value);
        return $schema->extensions['x-example'] ?? $schema->example;
    }

    protected function getSchemaDefault(Schema|Parameter $value): mixed
    {
        return $this->getSchema($value)->default;
    }

    protected function getSchemaEnumName(Schema|Parameter $value, ?Specification $spec = null): string
    {
        $schema = $this->getSchema($value);
        $enumSchema = $schema instanceof ArraySchema ? $schema->items : $schema;
        $name = $enumSchema->extensions['x-enum-name']
            ?? $enumSchema->title
            ?? ($value instanceof Parameter ? $value->name : null);
        if (\is_string($name) && $name !== '') {
            return $name;
        }

        foreach ($spec?->schemas ?? [] as $modelName => $model) {
            if (!$model instanceof ObjectSchema) {
                continue;
            }
            foreach ($model->properties as $propertyName => $property) {
                $propertySchema = $property instanceof ArraySchema ? $property->items : $property;
                if ($propertySchema === $enumSchema) {
                    return \ucfirst($modelName) . \ucfirst($propertyName);
                }
            }
        }
        return '';
    }

    protected function getSpecificationSchemaName(Schema $schema, Specification $spec): string
    {
        foreach ($spec->schemas as $name => $candidate) {
            if ($candidate === $schema) {
                return $name;
            }
        }
        return $schema->title ?? '';
    }

    protected function isSpecificationSchemaRequired(Schema $schema, Specification $spec): bool
    {
        foreach ($spec->schemas as $model) {
            if (!$model instanceof ObjectSchema) {
                continue;
            }
            foreach ($model->properties as $name => $property) {
                if ($property === $schema) {
                    return \in_array($name, $model->required, true);
                }
            }
        }
        return false;
    }

    protected function normalizeSchemaReference(string $reference): string
    {
        return \str_replace(['#/components/schemas/', '#/definitions/'], '', $reference);
    }

    /** @return list<string> */
    protected function getSchemaModels(Schema|Parameter $value): array
    {
        $schema = $this->getSchema($value);
        if ($schema instanceof ReferenceSchema) {
            return [$this->normalizeSchemaReference($schema->reference)];
        }
        if ($schema instanceof ArraySchema) {
            return $this->getSchemaModels($schema->items);
        }
        if ($schema instanceof CompositeSchema) {
            $models = [];
            foreach ($schema->schemas as $member) {
                \array_push($models, ...$this->getSchemaModels($member));
            }
            return \array_values(\array_unique($models));
        }
        return [];
    }

    /** @return list<Parameter> */
    protected function getOperationParameters(Operation $operation): array
    {
        $parameters = $operation->parameters;
        foreach ($operation->requestBody?->content ?? [] as $mediaType) {
            if (!$mediaType->schema instanceof ObjectSchema) {
                continue;
            }
            foreach ($mediaType->schema->properties as $name => $schema) {
                $parameters[] = new Parameter(
                    name: $name,
                    location: ParameterLocation::QUERY,
                    description: $schema->description,
                    required: \in_array($name, $mediaType->schema->required, true),
                    schema: $schema,
                    extensions: $schema->extensions,
                );
            }
            break;
        }
        return $parameters;
    }

    /** @return list<string> */
    protected function getOperationResponseModels(Operation $operation): array
    {
        $models = [];
        foreach ($operation->responses as $response) {
            foreach ($response->content as $mediaType) {
                if ($mediaType->schema !== null) {
                    \array_push($models, ...$this->getSchemaModels($mediaType->schema));
                }
            }
        }
        return \array_values(\array_unique($models));
    }

    /**
     * Get the prefix for Permission and Role classes (e.g., 'sdk.' for Node)
     */
    protected function getPermissionPrefix(): string
    {
        return '';
    }

    /**
     * Transform permission action name for language-specific casing
     * Override in child classes if needed (e.g., DotNet uses ucfirst)
     */
    protected function transformPermissionAction(string $action): string
    {
        return $action;
    }

    /**
     * Transform permission role name for language-specific casing
     * Override in child classes if needed (e.g., DotNet uses ucfirst)
     */
    protected function transformPermissionRole(string $role): string
    {
        return $role;
    }

    /**
     * Generate permission example code for the language
     * @param string $example Permission string example
     */
    public function getPermissionExample(string $example): string
    {
        $permissions = [];
        $staticOp = $this->getStaticAccessOperator();
        $quote = $this->getStringQuote();
        $prefix = $this->getPermissionPrefix();

        foreach ($this->extractPermissionParts($example) as $permission) {
            $args = [];
            if ($permission['id'] !== null) {
                $args[] = $quote . $permission['id'] . $quote;
            }
            if ($permission['innerRole'] !== null) {
                $args[] = $quote . $permission['innerRole'] . $quote;
            }
            $argsString = implode(', ', $args);

            $action = $permission['action'];
            $role = $permission['role'];
            $action = $this->transformPermissionAction($action);
            $role = $this->transformPermissionRole($role);

            $permissions[] = $prefix . 'Permission' . $staticOp . $action . '(' . $prefix . 'Role' . $staticOp . $role . '(' . $argsString . '))';
        }

        return $this->getArrayOf(implode(', ', $permissions));
    }
}
