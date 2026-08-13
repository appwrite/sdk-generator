<?php

namespace Appwrite\SDK;

use Utopia\OpenAPI\Model\Schema\IntegerSchema;
use Utopia\OpenAPI\Model\Schema\NumberSchema;
use Utopia\OpenAPI\Model\Schema\BooleanSchema;
use Utopia\OpenAPI\Model\Schema\Discriminator;
use MatthiasMullie\Minify\JS;
use MatthiasMullie\Minify\CSS;
use Exception;
use Throwable;
use Utopia\OpenAPI\Model\Operation;
use Utopia\OpenAPI\Model\Parameter;
use Utopia\OpenAPI\Model\ParameterLocation;
use Utopia\OpenAPI\Model\Schema\AnySchema;
use Utopia\OpenAPI\Model\Schema\ArraySchema;
use Utopia\OpenAPI\Model\Schema\CompositeSchema;
use Utopia\OpenAPI\Model\Schema\ObjectSchema;
use Utopia\OpenAPI\Model\Schema\ReferenceSchema;
use Utopia\OpenAPI\Model\Schema\Schema;
use Utopia\OpenAPI\Model\Schema\StringSchema;
use Utopia\OpenAPI\Model\SecurityScheme;
use Utopia\OpenAPI\Model\SecuritySchemeType;
use Utopia\OpenAPI\Model\Tag;
use Utopia\OpenAPI\Specification;
use Twig\Environment;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;
use Twig\Extension\DebugExtension;
use Twig\Loader\FilesystemLoader;
use Twig\TemplateWrapper;
use Twig\TwigFilter;
use MatthiasMullie\Minify;
use Twig_Error_Loader;
use Twig_Error_Runtime;
use Twig_Error_Syntax;

class SDK
{
    protected ?Environment $twig = null;

    protected array $defaultHeaders = [];

    protected array $params = [
        'namespace' => '',
        'name' => '',
        'description' => '',
        'shortDescription' => '',
        'version' => '',
        'platform' => '',
        'license' => '',
        'licenseContent' => '',
        'gitURL' => '',
        'gitRepo' => '',
        'gitRepoName' => '',
        'gitUserName' => '',
        'coverImage' => '',
        'logo' => '',
        'url' => '',
        'shareText' => '',
        'shareURL' => '',
        'shareVia' => '',
        'shareTags' => '',
        'warning' => '',
        'gettingStarted' => '',
        'readme' => '',
        'changelog' => '',
        'examples' => '',
        'test' => 'false'
    ];

    protected array $excludeRules = [
        'services' => [],
        'methods' => [],
        'definitions' => []
    ];

    protected ?array $excludeIndex = null;

    protected ?array $filteredServicesCache = null;

    protected ?array $filteredModelDataCache = null;

    protected array $schemaNames = [];

    protected array $requiredSchemas = [];

    protected array $schemaEnumNames = [];

    /**
     * SDK constructor.
     */
    public function __construct(protected Language $language, protected Specification $spec)
    {
        $this->indexSchemas();
        $this->twig = new Environment(new FilesystemLoader(__DIR__ . '/../../templates'), [
            'debug' => true
        ]);

        /**
         * Add language-specific functions
         */
        foreach ($this->language->getFunctions() as $function) {
            $this->twig->addFunction($function);
        }

        /**
         * Add language specific filters
         */
        foreach ($this->language->getFilters() as $filter) {
            $this->twig->addFilter($filter);
        }

        $this->twig->addExtension(new DebugExtension());

        $this->twig->addFilter(new TwigFilter('caseLower', fn($value) => strtolower((string)$value)));
        $this->twig->addFilter(new TwigFilter('caseUpper', fn($value) => strtoupper((string)$value)));
        $this->twig->addFilter(new TwigFilter('caseUcfirst', fn(?string $value): string => ucfirst($this->helperCamelCase($value))));
        $this->twig->addFilter(new TwigFilter('caseUcwords', fn($value): string => ucwords((string) $value, " -_")));
        $this->twig->addFilter(new TwigFilter('caseLcfirst', fn($value): string => lcfirst((string)$value)));
        $this->twig->addFilter(new TwigFilter('caseCamel', fn(?string $value): string => $this->helperCamelCase($value)));
        $this->twig->addFilter(new TwigFilter('removeDash', fn($value): string|array => str_replace('-', '', $value)));
        $this->twig->addFilter(new TwigFilter('caseDash', fn($value) => str_replace([' ', '_'], '-', strtolower((string) preg_replace('/([a-zA-Z])(?=[A-Z])/', '$1-', (string) $value)))));
        $this->twig->addFilter(new TwigFilter('caseKebab', function ($value): string {
            $value = preg_replace('/(?<!^)([A-Z][a-z]|(?<=[a-z])[^a-z\s_]|(?<=[A-Z])\d)/', '-$1', (string) $value);
            $value = str_replace(['_', ' '], '-', strtolower((string) $value));
            return trim((string) preg_replace('/-+/', '-', $value), '-');
        }));
        $this->twig->addFilter(new TwigFilter('caseSlash', fn($value) => str_replace([' ', '_', '.'], '/', strtolower((string) preg_replace('/([a-zA-Z])(?=[A-Z])/', '$1/', (string) $value)))));
        $this->twig->addFilter(new TwigFilter('caseDot', fn($value) => str_replace([' ', '_'], '.', strtolower((string) preg_replace('/([a-zA-Z])(?=[A-Z])/', '$1.', (string) $value)))));
        $this->twig->addFilter(new TwigFilter('caseSnake', function ($value): string {
            preg_match_all('!([A-Za-z][A-Z0-9]*(?=$|[A-Z][a-z0-9])|[A-Za-z][a-z0-9]+)!', (string) $value, $matches);
            $ret = $matches[0];
            foreach ($ret as &$match) {
                $match = $match === strtoupper($match)
                    ? strtolower($match)
                    : lcfirst($match);
            }
            return implode('_', $ret);
        }));
        $this->twig->addFilter(new TwigFilter('caseJson', fn($value) => (is_array($value)) ? json_encode($value) : $value, ['is_safe' => ['html']]));
        $this->twig->addFilter(new TwigFilter('caseArray', fn($value) => (is_array($value)) ? json_encode($value) : '[]', ['is_safe' => ['html']]));
        $this->twig->addFilter(new TwigFilter('typeName', fn(Schema|Parameter $value, ?Specification $spec = null): string => $this->language->getTypeName($value, $spec), ['is_safe' => ['html']]));
        $this->twig->addFilter(new TwigFilter('getValidResponseModels', fn(Operation $value): array => $this->getValidResponseModels($value)));
        $this->twig->addFilter(new TwigFilter('paramDefault', fn(Schema|Parameter $value): string => $this->language->getParamDefault($value), ['is_safe' => ['html']]));
        $this->twig->addFilter(new TwigFilter('paramExample', fn(Schema|Parameter $value): string => $this->language->getParamExample($value), ['is_safe' => ['html']]));
        $this->twig->addFilter(new TwigFilter('methodName', fn(Operation $operation): string => $this->methodName($operation)));
        $this->twig->addFilter(new TwigFilter('methodType', fn(Operation $operation): string|false => $this->methodType($operation)));
        $this->twig->addFilter(new TwigFilter('parameters', fn(Operation $operation, string $location = 'all'): array => $this->getOperationParameters($operation, $location)));
        $this->twig->addFilter(new TwigFilter('responseModel', fn(Operation $operation): string => $this->getResponseModel($operation)));
        $this->twig->addFilter(new TwigFilter('responseModels', fn(Operation $operation): array => $this->getValidResponseModels($operation)));
        $this->twig->addFilter(new TwigFilter('schemaName', fn(Schema $schema): string => $this->getSchemaName($schema)));
        $this->twig->addFilter(new TwigFilter('schemaType', fn(Schema|Parameter $value): string => $this->getSchemaType($value)));
        $this->twig->addFilter(new TwigFilter('schemaModel', fn(Schema|Parameter $value): string => $this->getSchemaModel($value)));
        $this->twig->addFilter(new TwigFilter('schemaModels', fn(Schema|Parameter $value): array => $this->getSchemaModels($value)));
        $this->twig->addFilter(new TwigFilter('schemaRequired', fn(Schema|Parameter $value): bool => $value instanceof Parameter ? $value->required : isset($this->requiredSchemas[\spl_object_id($value)])));
        $this->twig->addFilter(new TwigFilter('schemaExample', fn(Schema|Parameter $value): mixed => $this->getSchema($value)->extensions['x-example'] ?? $this->getSchema($value)->example));
        $this->twig->addFilter(new TwigFilter('schemaDefault', fn(Schema|Parameter $value): mixed => $this->getSchema($value)->default));
        $this->twig->addFilter(new TwigFilter('enumName', fn(Schema|Parameter $value): string => $this->getEnumName($value)));
        $this->twig->addFilter(new TwigFilter('enumKeys', fn(Schema|Parameter $value): array => $this->getEnumKeys($value)));
        $this->twig->addFilter(new TwigFilter('operations', fn(Tag $tag): array => $this->getFilteredMethods($this->getMethods($tag->name), $tag->name)));
        $this->twig->addFilter(new TwigFilter('consumes', fn(Operation $operation): array => \array_keys($operation->requestBody?->content ?? [])));
        $this->twig->addFilter(new TwigFilter('produces', fn(Operation $operation): array => $this->getProduces($operation)));
        $this->twig->addFilter(new TwigFilter('endpoint', fn(Specification $spec): string => $spec->servers[0]->url ?? 'https://example.com'));
        $this->twig->addFilter(new TwigFilter('appwrite', fn(Operation|SecurityScheme $value, string $key, mixed $default = null): mixed => $value->extensions['x-appwrite'][$key] ?? $default));
        $this->twig->addFilter(new TwigFilter('extension', fn(Schema|Parameter $value, string $key, mixed $default = null): mixed => $this->getSchema($value)->extensions[$key] ?? $default));
        $this->twig->addFilter(new TwigFilter('methodHeaders', fn(Operation $operation): array => $this->getMethodHeaders($operation)));
        $this->twig->addFilter(new TwigFilter('responseDiscriminator', fn(Operation $operation): array => $this->getResponseDiscriminator($operation)));
        $this->twig->addFilter(new TwigFilter('securitySchemes', fn(Operation $operation): array => $this->getOperationSecuritySchemes($operation)));
        $this->twig->addFilter(new TwigFilter('securityHeaders', fn(Operation $operation): array => $this->getOperationSecuritySchemes($operation, ParameterLocation::HEADER)));
        $this->twig->addFilter(new TwigFilter('securityQueries', fn(Operation $operation): array => $this->getOperationSecuritySchemes($operation, ParameterLocation::QUERY)));
        $this->twig->addFilter(new TwigFilter('schemaNullable', fn(Schema|Parameter $value): bool => $this->getSchema($value)->nullable));
        $this->twig->addFilter(new TwigFilter('enumValues', function (Schema|Parameter $value): array {
            $schema = $this->getSchema($value);
            return $schema instanceof ArraySchema ? $schema->items->enum : $schema->enum;
        }));
        $this->twig->addFilter(new TwigFilter('arraySchema', fn(Schema|Parameter $value): ?Schema => ($schema = $this->getSchema($value)) instanceof ArraySchema ? $schema->items : null));
        $this->twig->addFilter(new TwigFilter('emptyResponse', fn(Operation $operation): bool => \array_keys($operation->responses) === [204] || \array_keys($operation->responses) === ['204']));
        $this->twig->addFilter(new TwigFilter('fullPath', fn(Operation $operation): string => (\parse_url($this->spec->servers[0]->url ?? '', PHP_URL_PATH) ?: '') . $operation->path));
        $this->twig->addFilter(new TwigFilter('securityNames', fn(Operation $operation): array => \array_keys($this->getOperationSecuritySchemes($operation))));
        $this->twig->addFilter(new TwigFilter('wrap', function ($value, int $width = 75, string $prefix = ''): string {
            $lines = explode("\n", (string) $value);
            foreach ($lines as $key => $line) {
                $lines[$key] = $prefix . wordwrap($line, $width, "\n" . $prefix);
            }
            return implode("\n", $lines);
        }, ['is_safe' => ['html']]));
        $this->twig->addFilter(new TwigFilter('escapeDollarSign', function ($value): string|array {
            $value = str_replace('\\', '\\\\', $value ?? ''); // Escape backslashes first
            $value = str_replace('"', '\\"', $value);   // Escape double quotes
            $value = str_replace('$', '\\$', $value);   // Escape dollar signs
            return $value;
        }, ['is_safe' => ['html']]));
        $this->twig->addFilter(new TwigFilter('paramsQuery', function ($value): string {
            $query = '';

            foreach ($value as $param) {
                $query .= (empty($query)) ? "" : " + '&";
                $query .= "{$param->name}=' + {$param->name}";
            }

            return $query;
        }, ['is_safe' => ['html']]));
        $this->twig->addFilter(new TwigFilter('html', fn($value) => $value, ['is_safe' => ['html']]));
        $this->twig->addFilter(new TwigFilter('escapeKeyword', fn(string $value): string => $language->escapeKeyword($value), ['is_safe' => ['html']]));
        $this->twig->addFilter(new TwigFilter('caseHTML', fn($value) => $value, ['is_safe' => ['html']]));
        $this->twig->addFilter(new TwigFilter('removeDollarSign', fn($value): string|array => str_replace('$', '', $value)));
        $this->twig->addFilter(new TwigFilter('unescape', fn($value): string => html_entity_decode((string) $value)));
        $this->twig->addFilter(new TwigFilter('overrideIdentifier', fn($value) => $language->getIdentifierOverrides()[$value] ?? $value));
        $this->twig->addFilter(new TwigFilter('capitalizeFirst', fn($value): string => ucfirst((string) $value)));
        $this->twig->addFilter(new TwigFilter('caseSpace', fn($value): ?string => preg_replace('/([a-z])([A-Z])/', '$1 $2', (string) $value)));
        $this->twig->addFilter(new TwigFilter('caseSnakeExceptFirstDot', function ($value): string {
            $parts = explode('.', $value, 2);
            $toSnake = function ($str): string {
                preg_match_all('!([A-Za-z][A-Z0-9]*(?=$|[A-Z][a-z0-9])|[A-Za-z][a-z0-9]+)!', (string) $str, $matches);
                return implode('_', array_map(fn(string $m): string => $m === strtoupper($m) ? strtolower($m) : lcfirst($m), $matches[0]));
            };
            if (count($parts) < 2) {
                return $toSnake($value);
            }
            return $parts[0] . '.' . $toSnake($parts[1]);
        }));
        $this->twig->addFilter(new TwigFilter('hasPermissionParam', fn(array $value): bool => $this->language->hasPermissionParam($value)));
        $this->twig->addFilter(new TwigFilter('stripMarkdown', function ($value): string|array|null {
            if ($value === null) {
                return '';
            }
            // Convert markdown links.
            // Absolute URLs (http/https) are preserved as "text (url)" so users
            // can copy or click them; relative links like "/docs/..." are
            // useless in a terminal, so we drop the URL and keep just the text.
            $value = preg_replace_callback(
                '/\[([^\]]+)\]\(([^)]+)\)/',
                function (array $m): string {
                    $text = $m[1];
                    $url = trim($m[2]);
                    if (preg_match('/^https?:\/\//i', $url)) {
                        return $text . ' (' . $url . ')';
                    }
                    return $text;
                },
                $value
            );
            // Remove bold **text** -> text (lazy to keep adjacent bold spans
            // separate; . doesn't cross newlines by default)
            $value = preg_replace('/\*\*(.+?)\*\*/', '$1', $value);
            // Remove bold __text__ -> text (lazy so inner underscores like
            // __user_id__ match correctly)
            $value = preg_replace('/__(.+?)__/', '$1', $value);
            return $value;
        }));
    }

    public function setDefaultHeaders(array $headers): SDK
    {
        $this->defaultHeaders = $headers;

        return $this;
    }

    public function setNamespace(string $namespace): SDK
    {
        $this->setParam('namespace', $namespace);

        return $this;
    }

    public function setName(string $name): SDK
    {
        $this->setParam('name', $name);

        return $this;
    }

    public function setDescription(string $text): SDK
    {
        $this->setParam('description', $text);

        return $this;
    }

    public function setShortDescription(string $text): SDK
    {
        $this->setParam('shortDescription', $text);

        return $this;
    }

    public function setVersion(string $version): SDK
    {
        $this->setParam('version', $version);

        return $this;
    }

    public function setPlatform(string $platform): SDK
    {
        $this->setParam('platform', $platform);

        return $this;
    }

    public function setLicense(string $license): SDK
    {
        $this->setParam('license', $license);

        return $this;
    }

    public function setLicenseContent(string $content): SDK
    {
        $this->setParam('licenseContent', $content);

        return $this;
    }

    public function setGitRepo(string $url): SDK
    {
        $this->setParam('gitRepo', $url);

        return $this;
    }

    public function setGitRepoName(string $name): SDK
    {
        $this->setParam('gitRepoName', $name);

        return $this;
    }

    public function setGitUserName(string $name): SDK
    {
        $this->setParam('gitUserName', $name);

        return $this;
    }

    public function setGitURL(string $url): SDK
    {
        $this->setParam('gitURL', $url);

        return $this;
    }

    public function setLogo(string $url): SDK
    {
        $this->setParam('logo', $url);

        return $this;
    }

    public function setCoverImage(string $url): SDK
    {
        $this->setParam('coverImage', $url);

        return $this;
    }

    public function setURL(string $url): SDK
    {
        $this->setParam('url', $url);

        return $this;
    }

    public function setShareText(string $text): SDK
    {
        $this->setParam('shareText', $text);

        return $this;
    }

    public function setShareVia(string $user): SDK
    {
        $this->setParam('shareVia', $user);

        return $this;
    }

    public function setShareURL(string $url): SDK
    {
        $this->setParam('shareURL', $url);

        return $this;
    }

    /**
     * @param string $tags Comma separated list
     */
    public function setShareTags(string $tags): SDK
    {
        $this->setParam('shareTags', $tags);

        return $this;
    }

    public function setWarning(string $message): SDK
    {
        $this->setParam('warning', $message);

        return $this;
    }

    /**
     * @param $message string
     */
    public function setGettingStarted(string $message): SDK
    {
        $this->setParam('gettingStarted', $message);

        return $this;
    }

    public function setReadme(string $text): SDK
    {
        $this->setParam('readme', $text);

        return $this;
    }

    public function setChangelog(string $text): SDK
    {
        $this->setParam('changelog', $text);

        return $this;
    }

    public function setExamples(string $text): SDK
    {
        $this->setParam('examples', $text);

        return $this;
    }

    public function setDiscord(string $channel, string $url): SDK
    {
        $this->setParam('discordChannel', $channel);
        $this->setParam('discordUrl', $url);

        return $this;
    }

    public function setTwitter(string $handle): SDK
    {
        $this->setParam('twitterHandle', $handle);

        return $this;
    }

    public function setTest(string $test): SDK
    {
        $this->setParam('test', $test);

        return $this;
    }

    public function setParam(string $key, string $value): SDK
    {
        $this->params[$key] = $value;

        return $this;
    }

    public function getParam(string $name): string
    {
        return $this->params[$name] ?? '';
    }

    public function getParams(): array
    {
        return $this->params;
    }

    /** @return array<string, Tag> */
    protected function getFilteredServices(): array
    {
        if ($this->filteredServicesCache !== null) {
            return $this->filteredServicesCache;
        }

        $services = [];
        foreach ($this->spec->operations() as $operation) {
            foreach ($operation->tags as $name) {
                $services[$name] ??= $this->spec->tags[$name] ?? new Tag($name);
            }
        }

        foreach (array_keys($services) as $name) {
            $methods = $this->getFilteredMethods($this->getMethods($name), $name);
            if ($methods === [] || $this->isServiceExcluded($name, $methods)) {
                unset($services[$name]);
            }
        }

        return $this->filteredServicesCache = $services;
    }

    /** @return list<Operation> */
    protected function getMethods(string $service): array
    {
        return $this->spec->operationsByTag($service);
    }

    /** @param list<Operation> $methods @return list<Operation> */
    protected function getFilteredMethods(array $methods, string $serviceName = ''): array
    {
        return \array_values(\array_filter(
            $methods,
            fn(Operation $method): bool => !$this->isClientMethod($method, $serviceName)
                && !$this->isMethodExcluded($method, $serviceName),
        ));
    }

    protected function isClientMethod(Operation $method, string $service): bool
    {
        return $service === 'ping' && $this->methodName($method) === 'get';
    }

    /** @return array<string, array<string, Operation>> */
    protected function getClientMethods(): array
    {
        $clientMethods = [];
        foreach (array_keys($this->getFilteredServices()) as $serviceName) {
            foreach ($this->getMethods($serviceName) as $method) {
                if ($this->isClientMethod($method, $serviceName)) {
                    $clientMethods[$serviceName][$this->methodName($method)] = $method;
                }
            }
        }
        return $clientMethods;
    }

    /** @return array<string, Schema> */
    protected function getFilteredDefinitions(): array
    {
        return $this->getFilteredModelData()['definitions'];
    }

    /** @return array<string, Schema> */
    protected function getFilteredRequestModels(): array
    {
        return $this->getFilteredModelData()['requestModels'];
    }

    /** @return list<StringSchema> */
    protected function getFilteredRequestEnums(?array $filteredServices = null): array
    {
        $schemas = [];
        foreach ((array_keys($filteredServices ?? $this->getFilteredServices())) as $serviceName) {
            foreach ($this->getFilteredMethods($this->getMethods($serviceName), $serviceName) as $operation) {
                foreach ($this->getOperationParameters($operation) as $parameter) {
                    $schema = $this->getSchema($parameter);
                    $enumSchema = $schema instanceof ArraySchema ? $schema->items : $schema;
                    if ($enumSchema->enum !== []) {
                        $schemas[] = $enumSchema;
                    }
                }
            }
        }
        return $this->mergeEnums($schemas);
    }

    /** @return list<StringSchema> */
    protected function getFilteredResponseEnums(?array $filteredDefinitions = null): array
    {
        return $this->modelEnums($filteredDefinitions ?? $this->getFilteredDefinitions());
    }

    /** @return list<StringSchema> */
    protected function getFilteredRequestModelEnums(?array $filteredRequestModels = null): array
    {
        return $this->modelEnums($filteredRequestModels ?? $this->getFilteredRequestModels());
    }

    /** @return list<StringSchema> */
    protected function getFilteredAllEnums(
        ?array $filteredRequestEnums = null,
        ?array $filteredRequestModelEnums = null,
        ?array $filteredResponseEnums = null,
    ): array {
        return $this->mergeEnums([
            ...($filteredRequestEnums ?? $this->getFilteredRequestEnums()),
            ...($filteredRequestModelEnums ?? $this->getFilteredRequestModelEnums()),
            ...($filteredResponseEnums ?? $this->getFilteredResponseEnums()),
        ]);
    }

    /** @param array<string, Schema> $models @return list<Schema> */
    protected function modelEnums(array $models): array
    {
        $enums = [];
        foreach ($models as $model) {
            if (!$model instanceof ObjectSchema) {
                continue;
            }
            foreach ($model->properties as $property) {
                $schema = $property instanceof ArraySchema ? $property->items : $property;
                if ($schema->enum !== []) {
                    $enums[] = $schema;
                }
            }
        }
        return $this->mergeEnums($enums);
    }

    /** @param list<Schema> $schemas @return list<StringSchema> */
    protected function mergeEnums(array $schemas): array
    {
        $values = [];
        $keys = [];
        foreach ($schemas as $schema) {
            $name = $this->getEnumName($schema);
            if ($name === '') {
                continue;
            }
            foreach ($schema->enum as $index => $value) {
                if (!\in_array($value, $values[$name] ?? [], true)) {
                    $values[$name][] = $value;
                    $keys[$name][] = $schema->extensions['x-enum-keys'][$index] ?? $value;
                }
            }
        }

        $enums = [];
        foreach ($values as $name => $enumValues) {
            $enums[] = new StringSchema(
                title: $name,
                enum: $enumValues,
                extensions: ['x-enum-keys' => $keys[$name]],
            );
        }
        return $enums;
    }

    /** @return array{definitions: array<string, Schema>, requestModels: array<string, Schema>} */
    protected function getFilteredModelData(): array
    {
        if ($this->filteredModelDataCache !== null) {
            return $this->filteredModelDataCache;
        }

        $definitions = [];
        $requestModels = [];
        $excluded = $this->getExcludedDefinitions();
        foreach ($this->spec->schemas as $name => $schema) {
            if ($name === 'any' || isset($excluded[$name])) {
                continue;
            }
            if ($schema->extensions['x-request-model'] ?? false) {
                $requestModels[$name] = $schema;
            } else {
                $definitions[$name] = $schema;
            }
        }

        return $this->filteredModelDataCache = [
            'definitions' => $definitions,
            'requestModels' => $requestModels,
        ];
    }

    /**
     * @throws Throwable
     * @throws LoaderError
     * @throws RuntimeError
     * @throws SyntaxError
     */
    public function generate(string $target): void
    {
        $filteredServices = $this->getFilteredServices();
        $filteredModelData = $this->getFilteredModelData();
        $filteredDefinitions = $filteredModelData['definitions'];
        $filteredRequestModels = $filteredModelData['requestModels'];
        $filteredRequestEnums = $this->getFilteredRequestEnums($filteredServices);
        $filteredRequestModelEnums = $this->getFilteredRequestModelEnums($filteredRequestModels);
        $filteredResponseEnums = $this->getFilteredResponseEnums($filteredDefinitions);
        $filteredAllEnums = $this->getFilteredAllEnums(
            $filteredRequestEnums,
            $filteredRequestModelEnums,
            $filteredResponseEnums
        );

        $params = [
            'spec' => $this->spec,
            'services' => $filteredServices,
            'clientMethods' => $this->getClientMethods(),
            'requestEnums' => $filteredRequestEnums,
            'requestModelEnums' => $filteredRequestModelEnums,
            'responseEnums' => $filteredResponseEnums,
            'allEnums' => $filteredAllEnums,
            'definitions' => $filteredDefinitions,
            'requestModels' => $filteredRequestModels,
            'globalHeaders' => $this->getGlobalHeaders(),
            'defaultHeaders' => array_merge(
                $this->defaultHeaders,
                $this->spec->info->version !== '' ? ['X-Appwrite-Response-Format' => $this->spec->info->version] : [],
            ),
            'namespace' => $this->getParam('namespace') ?: $this->spec->info->title,
            'endpointDocs' => $this->getEndpointDocs(),
            'language' => [
                'name' => $this->language->getName(),
                'params' => $this->language->getParams(),
            ],
            'sdk' => $this->getParams(),
        ];

        foreach ($this->language->getFiles() as $file) {
            if (($file['test'] ?? false) && $this->getParam('test') !== 'true') {
                continue;
            }

            if (!\in_array($file['scope'], ['copy', 'download'], true)) {
                $template = $this->twig->load($file['template']); /* @var $template TemplateWrapper */
            }
            $destination    = $target . '/' . $file['destination'];
            $block          = $file['block'] ?? null;
            $minify         = $file['minify'] ?? false;

            switch ($file['scope']) {
                case 'default':
                    $this->render($template, $destination, $block, $params, $minify);
                    break;
                case 'copy':
                    if (!file_exists(dirname($destination))) {
                        mkdir(dirname($destination), 0777, true);
                    }
                    copy(realpath(__DIR__ . '/../../templates/' . $file['template']), $destination);
                    break;
                case 'download':
                    $this->download($file['template'], $destination, $params);
                    break;
                case 'service':
                    foreach ($filteredServices as $key => $service) {
                        $methods = $this->getFilteredMethods($this->getMethods($key), $key);
                        $params['service'] = $service;
                        $params['methods'] = $methods;
                        $params['serviceFeatures'] = [
                            'upload' => $this->hasUploads($methods),
                            'location' => $this->hasLocation($methods),
                            'webAuth' => $this->hasWebAuth($methods),
                        ];
                        $params['isConsoleOnly'] = $this->isConsoleOnly($key);
                        $params['consoleOnlyMethods'] = $this->isConsoleOnly($key) ? [] : $this->getConsoleOnlyMethods($key);
                        $params['resourceHeader'] = $this->resourceHeaderScheme($key, $methods);

                        if ($this->exclude($file, $params)) {
                            continue;
                        }

                        $this->render($template, $destination, $block, $params, $minify);
                    }
                    break;
                case 'definition':
                    foreach ($filteredDefinitions as $definitionName => $definition) {
                        $params['definition'] = $definition;
                        $params['definitionName'] = $definitionName;

                        if ($this->exclude($file, $params)) {
                            continue;
                        }

                        $this->render($template, $destination, $block, $params, $minify);
                    }
                    break;
                case 'requestModel':
                    foreach ($filteredRequestModels as $requestModelName => $requestModel) {
                        $params['requestModel'] = $requestModel;
                        $params['requestModelName'] = $requestModelName;

                        if ($this->exclude($file, $params)) {
                            continue;
                        }

                        $this->render($template, $destination, $block, $params, $minify);
                    }
                    break;
                case 'method':
                    foreach ($filteredServices as $key => $service) {
                        $methods = $this->getFilteredMethods($this->getMethods($key), $key);
                        $params['service'] = $service;
                        $params['methods'] = $methods;
                        $params['serviceFeatures'] = [
                            'upload' => $this->hasUploads($methods),
                            'location' => $this->hasLocation($methods),
                            'webAuth' => $this->hasWebAuth($methods),
                        ];
                        $params['isConsoleOnly'] = $this->isConsoleOnly($key);

                        foreach ($methods as $method) {
                            $params['method'] = $method;

                            if ($this->exclude($file, $params)) {
                                continue;
                            }

                            $this->render($template, $destination, $block, $params, $minify);
                        }
                    }
                    break;
                case 'enum':
                    foreach ($filteredAllEnums as $enum) {
                        $params['enum'] = $enum;

                        $this->render($template, $destination, $block, $params, $minify);
                    }
                    break;
            }
        }

        $this->language->postGenerate($target);
    }

    /**
     * Add additional exclusion rules for services, methods, or definitions.
     *
     * @param array $rules Array containing exclusion rules with format:
     *                     [
     *                         'services' => [['name' => 'serviceName'], ['feature' => 'featureName']],
     *                         'methods' => [['name' => 'methodName'], ['type' => 'methodType']],
     *                         'definitions' => [['name' => 'definitionName']]
     *                     ]
     */
    public function setExclude(array $rules): SDK
    {
        foreach (['services', 'methods', 'definitions'] as $type) {
            if (isset($rules[$type]) && is_array($rules[$type])) {
                $this->excludeRules[$type] = array_merge($this->excludeRules[$type], $rules[$type]);
            }
        }

        $this->excludeIndex = null;
        $this->filteredServicesCache = null;
        $this->filteredModelDataCache = null;

        return $this;
    }

    protected function isServiceExcluded(string $serviceName, array $methods): bool
    {
        $excludeIndex = $this->getExcludeIndex();

        if (isset($excludeIndex['services'][$serviceName])) {
            return true;
        }

        $serviceFeatures = [
            'upload' => $this->hasUploads($methods),
            'location' => $this->hasLocation($methods),
            'webAuth' => $this->hasWebAuth($methods),
        ];

        foreach ($excludeIndex['features'] as $feature) {
            if ($serviceFeatures[$feature] ?? false) {
                return true;
            }
        }

        return false;
    }

    protected function isMethodExcluded(Operation $method, string $serviceName = ''): bool
    {
        $excludeIndex = $this->getExcludeIndex();
        $methodName = $this->methodName($method);

        if (isset($excludeIndex['methods'][$methodName])) {
            foreach ($excludeIndex['methods'][$methodName] as $scope) {
                // true = global exclusion, string = service-scoped exclusion
                if ($scope === true || $scope === $serviceName) {
                    return true;
                }
            }
        }

        return isset($excludeIndex['types'][$this->methodType($method) ?: '']);
    }

    protected function getExcludedDefinitions(): array
    {
        return $this->getExcludeIndex()['definitions'];
    }

    protected function getExcludeIndex(): array
    {
        if ($this->excludeIndex !== null) {
            return $this->excludeIndex;
        }

        $this->excludeIndex = [
            'services' => [],
            'features' => [],
            'methods' => [],
            'types' => [],
            'definitions' => [],
        ];

        foreach ($this->excludeRules['services'] ?? [] as $service) {
            if (isset($service['name'])) {
                $this->excludeIndex['services'][$service['name']] = true;
            }

            if (isset($service['feature'])) {
                $this->excludeIndex['features'][$service['feature']] = $service['feature'];
            }
        }

        foreach ($this->excludeRules['methods'] ?? [] as $method) {
            if (isset($method['name'])) {
                // When 'service' is set, only exclude the method from that service.
                // Otherwise, exclude it globally (true).
                $this->excludeIndex['methods'][$method['name']][] = $method['service'] ?? true;
            }

            if (isset($method['type'])) {
                $this->excludeIndex['types'][$method['type']] = true;
            }
        }

        foreach ($this->excludeRules['definitions'] ?? [] as $definition) {
            if (isset($definition['name'])) {
                $this->excludeIndex['definitions'][$definition['name']] = true;
            }
        }

        return $this->excludeIndex;
    }

    /**
     * Determine if a file should be excluded from generation.
     *
     * Allows for files to be excluded based on:
     *   - Service name or feature
     *   - Method name or type
     *   - Definition name
     *
     * @param $file
     * @param $params
     */
    protected function exclude(array $file, array $params): bool
    {
        $exclude = array_merge_recursive($file['exclude'] ?? [], $this->excludeRules);

        $services = [];
        $features = [];
        foreach ($exclude['services'] ?? [] as $service) {
            if (isset($service['name'])) {
                $services[] = $service['name'];
            }
            if (isset($service['feature'])) {
                $features[] = $service['feature'];
            }
        }

        $methods = [];
        $scopedMethods = [];
        $types = [];
        foreach ($exclude['methods'] ?? [] as $method) {
            if (isset($method['name'])) {
                if (isset($method['service'])) {
                    $scopedMethods[] = $method;
                } else {
                    $methods[] = $method['name'];
                }
            }
            if (isset($method['type'])) {
                $types[] = $method['type'];
            }
        }

        $definitions = [];
        foreach ($exclude['definitions'] ?? [] as $definition) {
            if (isset($definition['name'])) {
                $definitions[] = $definition['name'];
            }
        }

        $serviceName = ($params['service'] ?? null) instanceof Tag ? $params['service']->name : '';
        if (\in_array($serviceName, $services, true)) {
            return true;
        }

        foreach ($features as $feature) {
            if ($params['serviceFeatures'][$feature] ?? false) {
                return true;
            }
        }

        $operation = $params['method'] ?? null;
        $currentMethodName = $operation instanceof Operation ? $this->methodName($operation) : '';
        if (\in_array($currentMethodName, $methods, true)) {
            return true;
        }

        $currentServiceName = $serviceName;
        foreach ($scopedMethods as $scopedMethod) {
            if ($scopedMethod['name'] === $currentMethodName && $scopedMethod['service'] === $currentServiceName) {
                return true;
            }
        }

        if ($operation instanceof Operation && \in_array($this->methodType($operation), $types, true)) {
            return true;
        }
        return \in_array($params['definitionName'] ?? '', $definitions, true);
    }

    protected function hasUploads(array $methods): bool
    {
        return array_any($methods, fn(Operation $method): bool => $this->methodType($method) === 'upload');
    }

    protected function hasLocation(array $methods): bool
    {
        return array_any($methods, fn(Operation $method): bool => $this->methodType($method) === 'location');
    }

    protected function hasWebAuth(array $methods): bool
    {
        return array_any($methods, fn(Operation $method): bool => $this->methodType($method) === 'webAuth');
    }

    protected function isConsoleOnly(string $serviceName): bool
    {
        $consoleOnlyServices = [
            'account',
            'console',
            'domains',
            'locale',
            'manager',
            'notifications',
            'organization',
            'organizations',
            'projects',
        ];

        return \in_array($serviceName, $consoleOnlyServices, true);
    }

    /**
     * Methods on an otherwise project-scoped service that the API only serves on
     * the console project. The `oauth2` discovery endpoints reject any other
     * project, while the rest of the service stays bound to the caller's project.
     *
     * @return array<string>
     */
    protected function getConsoleOnlyMethods(string $serviceName): array
    {
        return match ($serviceName) {
            'oauth2' => ['listOrganizations', 'listProjects'],
            default => [],
        };
    }

    /**
     * The security scheme naming a service's own resource, or null when the
     * service takes its target in the path like everything else.
     *
     * `/project` and `/organization` carry no ID: they act on whatever
     * `X-Appwrite-Project` or `X-Appwrite-Organization` names, and without that
     * header the API resolves an empty resource and answers 404. The spec says
     * which those are — a header security scheme named after the service, and
     * declared on that service's own methods:
     *
     *   project      -> Project        declared on its methods  => scoped
     *   organization -> Organization   declared on its methods  => scoped
     *   locale       -> Locale         NOT declared on them     => not scoped
     *   projects     -> no such scheme                          => not scoped
     *
     * The `Locale` case is why the scheme has to appear in the method security
     * and not merely exist: every service would otherwise match on name alone.
     */
    protected function resourceHeaderScheme(string $serviceName, array $methods): ?string
    {
        foreach (array_keys($this->getGlobalHeaders()) as $key) {
            if (\strtolower($key) !== \strtolower($serviceName)) {
                continue;
            }

            foreach ($methods as $method) {
                foreach ($method->security as $requirement) {
                    if (isset($requirement->schemes[$key])) {
                        return $key;
                    }
                }
            }
        }

        return null;
    }

    /**
     *
     * @throws Throwable
     * @throws Twig_Error_Loader
     * @throws Twig_Error_Syntax
     */
    protected function render(TemplateWrapper $template, string $destination, ?string $block, array $params = [], bool $minify = false): void
    {
        $destination    = $this->twig->createTemplate($destination);
        $destination    = $destination->render($params);
        $output         = (empty($block)) ? $template->render($params) : $template->renderBlock($block, $params);

        if (!file_exists(dirname($destination))) {
            mkdir(dirname($destination), 0777, true);
        }

        $result = file_put_contents($destination, $output);

        if ($result === false) {
            throw new Exception('Can\'t save file: ' . $destination);
        }

        if ($minify) {
            $ext = pathinfo($destination, PATHINFO_EXTENSION);

            switch ($ext) {
                case 'js':
                    $minifier = new JS($destination);
                    $minifier->minify($destination);
                    break;
                case 'css':
                    $minifier = new CSS($destination);
                    $minifier->minify($destination);
                    break;
                default:
                    throw new Exception('No minifier found for ' . $ext . ' file');
            }
        }
    }

    /**
     *
     * @throws Exception
     */
    protected function download(string $url, string $destination, array $params = []): void
    {
        $destination = $this->twig->createTemplate($destination)->render($params);

        if (!file_exists(dirname($destination))) {
            mkdir(dirname($destination), 0777, true);
        }

        $output = @file_get_contents($url);

        if ($output === false) {
            throw new Exception('Can\'t download file: ' . $url);
        }

        $result = file_put_contents($destination, $output);

        if ($result === false) {
            throw new Exception('Can\'t save file: ' . $destination);
        }
    }

    protected function helperCamelCase(?string $str): string
    {
        if ($str == null) {
            return '';
        }
        $str = preg_replace('/[^a-z0-9' . implode("", []) . ']+/i', ' ', $str);
        $str = trim((string) $str);
        $str = ucwords($str);
        $str = str_replace(" ", "", $str);

        return lcfirst($str);
    }

    /** @return list<string> */
    protected function getValidResponseModels(Operation $method): array
    {
        $models = [];
        foreach ($method->responses as $response) {
            foreach ($response->content as $mediaType) {
                if ($mediaType->schema !== null) {
                    \array_push($models, ...$this->getSchemaModels($mediaType->schema));
                }
            }
        }
        return \array_values(\array_filter(\array_unique($models), static fn(string $name): bool => $name !== 'any'));
    }

    protected function getResponseModel(Operation $operation): string
    {
        return $this->getValidResponseModels($operation)[0] ?? '';
    }

    protected function methodName(Operation $operation): string
    {
        return (string) ($operation->extensions['x-appwrite']['method'] ?? $operation->id);
    }

    protected function methodType(Operation $operation): string|false
    {
        return $operation->extensions['x-appwrite']['type'] ?? false;
    }

    /** @return list<Parameter> */
    protected function getOperationParameters(Operation $operation, string $location = 'all'): array
    {
        $parameters = [];
        if ($location !== 'body') {
            foreach ($operation->parameters as $parameter) {
                if ($location === 'all' || $parameter->location->value === $location) {
                    $parameters[] = $parameter;
                }
            }
        }

        if ($location === 'all' || $location === 'body') {
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
        }

        \usort($parameters, static fn(Parameter $left, Parameter $right): int => (int) $right->required - (int) $left->required);
        return $parameters;
    }

    protected function getSchema(Schema|Parameter $value): Schema
    {
        return $value instanceof Parameter ? ($value->schema ?? new AnySchema()) : $value;
    }

    protected function getSchemaType(Schema|Parameter $value): string
    {
        $schema = $this->getSchema($value);
        return match (true) {
            $schema instanceof StringSchema && $schema->format === 'binary' => 'file',
            $schema instanceof StringSchema => 'string',
            $schema instanceof IntegerSchema => 'integer',
            $schema instanceof NumberSchema => 'number',
            $schema instanceof BooleanSchema => 'boolean',
            $schema instanceof ArraySchema => 'array',
            default => 'object',
        };
    }

    protected function getSchemaName(Schema $schema): string
    {
        return $schema->title ?? $this->schemaNames[\spl_object_id($schema)] ?? '';
    }

    protected function getSchemaModel(Schema|Parameter $value): string
    {
        $schema = $this->getSchema($value);
        if ($schema instanceof ReferenceSchema) {
            return $this->normalizeSchemaReference($schema->reference);
        }
        if ($schema instanceof ArraySchema && $schema->items instanceof ReferenceSchema) {
            return $this->normalizeSchemaReference($schema->items->reference);
        }
        return (string) ($schema->extensions['x-model'] ?? '');
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
        foreach (['x-oneOf', 'x-anyOf'] as $key) {
            if (!\is_array($schema->extensions[$key] ?? null)) {
                continue;
            }
            return \array_values(\array_filter(\array_map(
                fn(array $member): string => $this->normalizeSchemaReference((string) ($member['$ref'] ?? '')),
                $schema->extensions[$key],
            )));
        }
        return [];
    }

    protected function getEnumName(Schema|Parameter $value): string
    {
        $schema = $this->getSchema($value);
        $enumSchema = $schema instanceof ArraySchema ? $schema->items : $schema;
        if ($enumSchema->enum === []) {
            return '';
        }
        return (string) ($enumSchema->extensions['x-enum-name']
            ?? $enumSchema->title
            ?? $this->schemaEnumNames[\spl_object_id($enumSchema)]
            ?? ($value instanceof Parameter ? $value->name : ''));
    }

    protected function getEnumKeys(Schema|Parameter $value): array
    {
        return $this->getSchema($value)->extensions['x-enum-keys'] ?? [];
    }

    protected function normalizeSchemaReference(string $reference): string
    {
        return \str_replace(['#/components/schemas/', '#/definitions/'], '', $reference);
    }

    protected function indexSchemas(): void
    {
        foreach ($this->spec->schemas as $modelName => $schema) {
            $this->schemaNames[\spl_object_id($schema)] = $modelName;
            if (!$schema instanceof ObjectSchema) {
                continue;
            }
            foreach ($schema->properties as $propertyName => $property) {
                $id = \spl_object_id($property);
                $this->schemaNames[$id] = $propertyName;
                $this->schemaEnumNames[$id] = \ucfirst($modelName) . \ucfirst($propertyName);
                if (\in_array($propertyName, $schema->required, true)) {
                    $this->requiredSchemas[$id] = true;
                }
                if ($property instanceof ArraySchema) {
                    $itemId = \spl_object_id($property->items);
                    $this->schemaNames[$itemId] = $propertyName;
                    $this->schemaEnumNames[$itemId] = \ucfirst($modelName) . \ucfirst($propertyName);
                }
            }
        }
    }

    /** @return array<string, SecurityScheme> */
    protected function getGlobalHeaders(): array
    {
        return \array_filter(
            $this->spec->securitySchemes,
            static fn(SecurityScheme $scheme): bool => ($scheme->type === SecuritySchemeType::API_KEY && $scheme->location === ParameterLocation::HEADER)
                || ($scheme->type === SecuritySchemeType::HTTP && $scheme->scheme === 'bearer'),
        );
    }

    protected function getEndpointDocs(): string
    {
        foreach ($this->spec->servers as $server) {
            if (\str_contains($server->url, '{region}')) {
                return \preg_replace_callback('/\{([^}]+)\}/', static fn(array $match): string => '<' . \strtoupper($match[1]) . '>', $server->url) ?? '';
            }
        }
        return $this->spec->servers[0]->url ?? '';
    }

    /** @return list<string> */
    protected function getProduces(Operation $operation): array
    {
        $produces = [];
        foreach ($operation->responses as $response) {
            foreach (\array_keys($response->content) as $contentType) {
                if ($contentType !== '' && !\in_array($contentType, $produces, true)) {
                    $produces[] = $contentType;
                }
            }
        }
        return $produces;
    }

    /** @return array<string, string> */
    protected function getMethodHeaders(Operation $operation): array
    {
        $headers = [];
        $consumes = \array_keys($operation->requestBody?->content ?? []);
        if ($consumes !== []) {
            $headers['content-type'] = array_last($consumes);
        }
        $produces = $this->getProduces($operation);
        if ($produces !== []) {
            $headers['accept'] = \implode(', ', $produces);
        }
        if ($this->methodType($operation) === 'graphql') {
            $headers['x-sdk-graphql'] = 'true';
        }
        return $headers;
    }

    /** @return array<string, SecurityScheme> */
    protected function getOperationSecuritySchemes(Operation $operation, ?ParameterLocation $location = null): array
    {
        $schemes = [];
        foreach ($operation->security[0]->schemes ?? [] as $name => $scopes) {
            $scheme = $this->spec->securitySchemes[$name] ?? null;
            if ($scheme === null || ($location instanceof ParameterLocation && $scheme->location !== $location)) {
                continue;
            }
            $schemes[$name] = $scheme;
        }
        return $schemes;
    }

    protected function getResponseDiscriminator(Operation $operation): array
    {
        foreach ($operation->responses as $response) {
            foreach ($response->content as $mediaType) {
                $schema = $mediaType->schema;
                if (!$schema instanceof CompositeSchema || !$schema->discriminator instanceof Discriminator) {
                    continue;
                }
                $mapping = [];
                foreach ($schema->discriminator->mapping as $value => $reference) {
                    $mapping[$this->normalizeSchemaReference($reference)] = [$schema->discriminator->propertyName => $value];
                }
                return $mapping;
            }
        }
        return [];
    }
}
