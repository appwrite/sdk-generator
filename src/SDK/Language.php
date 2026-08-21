<?php

namespace Appwrite\SDK;

use Utopia\OpenAPI\Model\AnySchema;
use Normalizer;
use Utopia\OpenAPI\Model\ArraySchema;
use Utopia\OpenAPI\Model\BooleanSchema;
use Utopia\OpenAPI\Model\CompositeSchema;
use Utopia\OpenAPI\Model\Composition;
use Utopia\OpenAPI\Model\IntegerSchema;
use Utopia\OpenAPI\Model\NumberSchema;
use Utopia\OpenAPI\Model\ObjectSchema;
use Utopia\OpenAPI\Model\Operation;
use Utopia\OpenAPI\Model\Parameter;
use Utopia\OpenAPI\Model\ParameterLocation;
use Utopia\OpenAPI\Model\ReferenceSchema;
use Utopia\OpenAPI\Model\Schema;
use Utopia\OpenAPI\Model\StringSchema;
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
     * Derive the service-local SDK method name from a service-qualified
     * OpenAPI operation ID.
     */
    public function getMethodName(Operation $operation): string
    {
        $serviceName = '';
        foreach ($operation->tags as $tag) {
            if (
                \strlen($tag) <= \strlen($serviceName)
                || !\str_starts_with($operation->id, $tag)
                || \strlen($operation->id) === \strlen($tag)
            ) {
                continue;
            }
            $serviceName = $tag;
        }

        if ($serviceName === '') {
            return $operation->id;
        }

        return \lcfirst(\substr($operation->id, \strlen($serviceName)));
    }

    /**
     * Language specific filters.
     */
    public function getFilters(): array
    {
        return [];
    }

    /**
     * Whether method signatures keep the documented enum as a type for open string enums.
     */
    public function keepsOpenEnumType(): bool
    {
        return false;
    }

    /**
     * Enum schema used in generated signatures. Open string enums are untyped
     * except in languages that keep the documented enum as a type.
     */
    protected function getTypedEnumSchema(Schema|Parameter $value): ?Schema
    {
        if ($this->isOpenStringEnum($value) && !$this->keepsOpenEnumType()) {
            return null;
        }

        $enumSchema = $this->getEnumSchema($value);

        return $enumSchema->enum === [] ? null : $enumSchema;
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
            $example = $this->getSchemaExample($parameter);
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
            $schema instanceof StringSchema,
            $schema instanceof CompositeSchema && $this->getEnumSchema($schema) instanceof StringSchema => self::TYPE_STRING,
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
        $models = $this->getSchemaModels($schema);
        if (\count($models) === 1) {
            return $models[0];
        }
        if ($schema instanceof ArraySchema) {
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
        $example = $schema->example;

        if (!\is_array($example) && !\is_object($example)) {
            return $example;
        }

        if ($this->getSchemaType($schema) === self::TYPE_OBJECT && empty((array) $example)) {
            return '{}';
        }

        return \json_encode($example, JSON_THROW_ON_ERROR | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    }

    protected function getSchemaDefault(Schema|Parameter $value): mixed
    {
        return $this->getSchema($value)->default;
    }

    /**
     * Whether an array's element schema names a type the language can spell.
     *
     * A `oneOf`/`anyOf` element, or one with no type at all, does not, and the
     * array degrades to the language's untyped list rather than rendering each
     * element as an object.
     */
    protected function hasConcreteItemsType(Schema $schema): bool
    {
        if (!$schema instanceof ArraySchema) {
            return false;
        }
        $items = $schema->items;
        return !($items instanceof AnySchema)
            && (!($items instanceof CompositeSchema) || $this->isOpenStringEnum($items))
            && $this->getSchemaType($items) !== '';
    }

    /**
     * Whether a nested array should keep its element type untyped.
     *
     * A model property carries its full schema tree, so an array of arrays of
     * numbers resolves all the way down. A method parameter is flattened to a
     * single level before it reaches a language, so anything past the first
     * nesting has no type to render and the published SDKs leave it untyped.
     */
    protected function isUntypedNestedArray(Schema|Parameter $value, Schema $schema): bool
    {
        return $value instanceof Parameter
            && $schema instanceof ArraySchema
            && $schema->items instanceof ArraySchema;
    }

    public function getEnumSchema(Schema|Parameter $value): Schema
    {
        $schema = $this->getSchema($value);
        $enumSchema = $schema instanceof ArraySchema ? $schema->items : $schema;

        if (!$enumSchema instanceof CompositeSchema) {
            return $enumSchema;
        }

        return $enumSchema->openStringEnumBranch()
            ?? $this->closedStringEnumFromComposite($enumSchema)
            ?? $enumSchema;
    }

    /**
     * Live specs encode closed string enums as type:string plus oneOf of
     * single-value string schemas, which is not an open string enum.
     */
    protected function closedStringEnumFromComposite(CompositeSchema $schema): ?StringSchema
    {
        if (
            ($schema->composition !== Composition::ONE_OF && $schema->composition !== Composition::ANY_OF)
            || $schema->not instanceof Schema
        ) {
            return null;
        }

        $values = [];
        $keys = [];
        foreach ($schema->schemas as $member) {
            if (!$member instanceof StringSchema || $member->enum === []) {
                return null;
            }
            foreach ($member->enum as $index => $value) {
                if (\in_array($value, $values, true)) {
                    continue;
                }
                $values[] = $value;
                $key = $member->enumKeys[$index] ?? null;
                $keys[] = \is_string($key) && $key !== ''
                    ? $key
                    : (string) ($member->title ?? $value);
            }
        }

        if ($values === []) {
            return null;
        }

        return new StringSchema(
            title: $schema->title,
            description: $schema->description,
            nullable: $schema->nullable,
            default: $schema->default,
            enum: $values,
            format: $schema->format,
            readOnly: $schema->readOnly,
            writeOnly: $schema->writeOnly,
            deprecated: $schema->deprecated,
            example: $schema->example,
            extensions: $schema->extensions,
            enumName: $schema->title,
            enumKeys: $keys,
            open: false,
        );
    }

    public function isOpenStringEnum(Schema|Parameter $value): bool
    {
        $enumSchema = $this->getEnumSchema($value);

        return $enumSchema instanceof StringSchema && $enumSchema->open;
    }

    protected function getSchemaEnumName(Schema|Parameter $value, ?Specification $spec = null): string
    {
        $enumSchema = $this->getEnumSchema($value);
        $name = ($enumSchema instanceof StringSchema ? $enumSchema->enumName : null)
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
                if ($this->getEnumSchema($property) === $enumSchema) {
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

    protected function hasGenericSchemaType(?string $modelName, Specification $spec, array $visited = []): bool
    {
        if (in_array($modelName, [null, '', 'any'], true) || isset($visited[$modelName])) {
            return false;
        }

        $model = $spec->schemas[$modelName] ?? null;
        if (!$model instanceof ObjectSchema) {
            return false;
        }
        if ($model->additionalProperties) {
            return true;
        }

        $visited[$modelName] = true;
        return array_any($model->properties, fn(Schema|Parameter $property): bool => $this->hasGenericSchemaType($this->getSchemaModel($property), $spec, $visited));
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
        $parameters = \array_values(\array_filter(
            $operation->parameters,
            static fn(Parameter $parameter): bool => ($parameter->extensions['x-sdk-source'] ?? '') !== 'security',
        ));
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
        \usort($parameters, static fn(Parameter $left, Parameter $right): int => (int) $right->required <=> (int) $left->required);
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
