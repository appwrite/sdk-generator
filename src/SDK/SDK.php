<?php

namespace Appwrite\SDK;

use MatthiasMullie\Minify\JS;
use MatthiasMullie\Minify\CSS;
use Exception;
use Appwrite\Spec\Spec;
use Throwable;
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

    /**
     * SDK constructor.
     */
    public function __construct(protected Language $language, protected Spec $spec)
    {
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
        $this->twig->addFilter(new TwigFilter('caseKebab', fn($value) => strtolower((string) preg_replace('/(?<!^)([A-Z][a-z]|(?<=[a-z])[^a-z\s]|(?<=[A-Z])[0-9_])/', '-$1', (string) $value))));
        $this->twig->addFilter(new TwigFilter('caseSlash', fn($value) => str_replace([' ', '_', '.'], '/', strtolower((string) preg_replace('/([a-zA-Z])(?=[A-Z])/', '$1/', (string) $value)))));
        $this->twig->addFilter(new TwigFilter('caseDot', fn($value) => str_replace([' ', '_'], '.', strtolower((string) preg_replace('/([a-zA-Z])(?=[A-Z])/', '$1.', (string) $value)))));
        $this->twig->addFilter(new TwigFilter('caseSnake', function ($value): string {
            preg_match_all('!([A-Za-z][A-Z0-9]*(?=$|[A-Z][a-z0-9])|[A-Za-z][a-z0-9]+)!', $value, $matches);
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
        $this->twig->addFilter(new TwigFilter('typeName', fn(array $value, array $spec = []): string => $this->language->getTypeName($value, $spec), ['is_safe' => ['html']]));
        $this->twig->addFilter(new TwigFilter('getValidResponseModels', fn(array $value): array => $this->getValidResponseModels($value)));
        $this->twig->addFilter(new TwigFilter('paramDefault', fn(array $value): string => $this->language->getParamDefault($value), ['is_safe' => ['html']]));
        $this->twig->addFilter(new TwigFilter('paramExample', fn(array $value): string => $this->language->getParamExample($value), ['is_safe' => ['html']]));
        $this->twig->addFilter(new TwigFilter('comment1', function ($value): string {
            $value = explode("\n", $value);
            foreach ($value as $key => $line) {
                $value[$key] = "     * " . wordwrap($line, 75, "\n     * ");
            }
            return implode("\n", $value);
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
                $query .= "{$param['name']}=' + {$param['name']}";
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
                preg_match_all('!([A-Za-z][A-Z0-9]*(?=$|[A-Z][a-z0-9])|[A-Za-z][a-z0-9]+)!', $str, $matches);
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

    /**
     * Get services filtered by exclusion rules
     */
    protected function getFilteredServices(): array
    {
        if ($this->filteredServicesCache !== null) {
            return $this->filteredServicesCache;
        }

        $allServices = $this->spec->getServices();
        $filteredServices = [];

        foreach ($allServices as $serviceName => $service) {
            $allMethods = $this->spec->getMethods($serviceName);

            if ($this->isServiceExcluded($serviceName, $allMethods)) {
                continue;
            }

            $methods = $this->getFilteredMethods($allMethods, $serviceName);

            if ($methods === []) {
                continue;
            }

            $service['methods'] = $methods;
            $filteredServices[$serviceName] = $service;
        }

        $this->filteredServicesCache = $filteredServices;

        return $this->filteredServicesCache;
    }

    protected function getFilteredMethods(array $methods, string $serviceName = ''): array
    {
        return \array_values(\array_filter($methods, fn (array $method): bool => !$this->isMethodExcluded($method, $serviceName)));
    }

    protected function getFilteredDefinitions(): array
    {
        return $this->getFilteredModelData()['definitions'];
    }

    protected function getFilteredRequestModels(): array
    {
        return $this->getFilteredModelData()['requestModels'];
    }

    protected function getFilteredRequestEnums(?array $filteredServices = null): array
    {
        $filteredServices ??= $this->getFilteredServices();
        $list = [];

        foreach ($filteredServices as $service) {
            foreach ($service['methods'] ?? [] as $method) {
                foreach ($method['parameters']['all'] ?? [] as $parameter) {
                    $enumName = $parameter['enumName'] ?? $parameter['name'] ?? '';

                    if (!isset($parameter['enumValues']) || empty($enumName)) {
                        continue;
                    }

                    $this->mergeEnumValues(
                        $list,
                        $enumName,
                        $parameter['enumValues'],
                        $parameter['enumKeys'] ?? []
                    );
                }
            }
        }

        return \array_values($list);
    }

    protected function getFilteredResponseEnums(?array $filteredDefinitions = null): array
    {
        $filteredDefinitions ??= $this->getFilteredDefinitions();
        $list = [];

        foreach ($filteredDefinitions as $modelName => $model) {
            foreach ($model['properties'] ?? [] as $propertyName => $property) {
                if (isset($property['enum'])) {
                    $enumName = $property['enumName'] ?? ucfirst((string)$modelName) . ucfirst((string)$propertyName);

                    $this->mergeEnumValues(
                        $list,
                        $enumName,
                        $property['enum'],
                        $property['enumKeys'] ?? []
                    );
                }

                if ((($property['type'] ?? null) === 'array') && isset($property['items']['enum'])) {
                    $enumName = $property['items']['x-enum-name']
                        ?? $property['enumName']
                        ?? ucfirst((string)$modelName) . ucfirst((string)$propertyName);

                    $this->mergeEnumValues(
                        $list,
                        $enumName,
                        $property['items']['enum'],
                        $property['items']['x-enum-keys'] ?? []
                    );
                }
            }
        }

        return \array_values($list);
    }

    protected function getFilteredRequestModelEnums(?array $filteredRequestModels = null): array
    {
        $filteredRequestModels ??= $this->getFilteredRequestModels();
        $list = [];

        foreach ($filteredRequestModels as $modelName => $model) {
            foreach ($model['properties'] ?? [] as $propertyName => $property) {
                if (isset($property['enum'])) {
                    $enumName = $property['enumName'] ?? ucfirst((string)$modelName) . ucfirst((string)$propertyName);

                    $this->mergeEnumValues(
                        $list,
                        $enumName,
                        $property['enum'],
                        $property['enumKeys'] ?? []
                    );
                }

                if ((($property['type'] ?? null) === 'array') && isset($property['enumValues'])) {
                    $enumName = $property['enumName'] ?? ucfirst((string)$modelName) . ucfirst((string)$propertyName);

                    $this->mergeEnumValues(
                        $list,
                        $enumName,
                        $property['enumValues'],
                        $property['enumKeys'] ?? []
                    );
                }
            }
        }

        return \array_values($list);
    }

    protected function getFilteredAllEnums(
        ?array $filteredRequestEnums = null,
        ?array $filteredRequestModelEnums = null,
        ?array $filteredResponseEnums = null
    ): array {
        $filteredRequestEnums ??= $this->getFilteredRequestEnums();
        $filteredRequestModelEnums ??= $this->getFilteredRequestModelEnums();
        $filteredResponseEnums ??= $this->getFilteredResponseEnums();
        $list = [];

        foreach ($filteredRequestEnums as $enum) {
            $this->mergeEnumValues(
                $list,
                $enum['name'],
                $enum['enum'],
                $enum['keys'] ?? []
            );
        }

        foreach ($filteredRequestModelEnums as $enum) {
            $this->mergeEnumValues(
                $list,
                $enum['name'],
                $enum['enum'],
                $enum['keys'] ?? []
            );
        }

        foreach ($filteredResponseEnums as $enum) {
            $this->mergeEnumValues(
                $list,
                $enum['name'],
                $enum['enum'],
                $enum['keys'] ?? []
            );
        }

        return \array_values($list);
    }

    protected function mergeEnumValues(array &$list, string $enumName, array $enumValues, array $enumKeys = []): void
    {
        if (!isset($list[$enumName])) {
            $list[$enumName] = [
                'name' => $enumName,
                'enum' => [],
                'keys' => [],
            ];
        }

        foreach ($enumValues as $index => $value) {
            if (\in_array($value, $list[$enumName]['enum'], true)) {
                continue;
            }

            $key = $enumKeys[$index] ?? $value;
            $normalizedKey = \strtoupper((string) \preg_replace('/[^a-zA-Z0-9]/', '', (string) $key));
            $existingKeys = \array_map(
                fn($existingKey) => \strtoupper((string) \preg_replace('/[^a-zA-Z0-9]/', '', (string) $existingKey)),
                $list[$enumName]['keys']
            );

            if (\in_array($normalizedKey, $existingKeys, true)) {
                continue;
            }

            $list[$enumName]['enum'][] = $value;
            $list[$enumName]['keys'][] = $key;
        }
    }

    protected function getFilteredModelData(): array
    {
        if ($this->filteredModelDataCache !== null) {
            return $this->filteredModelDataCache;
        }

        $definitions = $this->spec->getDefinitions();
        $requestModels = $this->spec->getRequestModels();
        $excludedDefinitions = $this->getExcludedDefinitions();
        $queue = [];
        $reachableDefinitions = [];
        $reachableRequestModels = [];

        foreach ($this->getFilteredServices() as $service) {
            foreach ($service['methods'] ?? [] as $method) {
                $responseModels = $method['responseModels'] ?? [];

                if (!empty($method['responseModel'])) {
                    $responseModels[] = $method['responseModel'];
                }

                foreach ($responseModels as $modelName) {
                    if (!empty($modelName)) {
                        $queue[$modelName] = true;
                    }
                }

                foreach ($method['parameters']['all'] ?? [] as $parameter) {
                    $parameterModels = [
                        $parameter['model'] ?? null,
                        $parameter['array']['model'] ?? null,
                    ];

                    foreach ($parameterModels as $modelName) {
                        if (!empty($modelName)) {
                            $queue[$modelName] = true;
                        }
                    }
                }
            }
        }

        while ($queue !== []) {
            $modelName = array_key_first($queue);

            if ($modelName === null) {
                break;
            }

            unset($queue[$modelName]);

            if (isset($excludedDefinitions[$modelName])) {
                continue;
            }

            $model = $definitions[$modelName] ?? $requestModels[$modelName] ?? null;

            if ($model === null) {
                continue;
            }

            if (isset($definitions[$modelName])) {
                if (isset($reachableDefinitions[$modelName])) {
                    continue;
                }

                $reachableDefinitions[$modelName] = true;
            } else {
                if (isset($reachableRequestModels[$modelName])) {
                    continue;
                }

                $reachableRequestModels[$modelName] = true;
            }

            foreach ($model['properties'] ?? [] as $property) {
                $dependencies = [];

                if (!empty($property['sub_schema'])) {
                    $dependencies[] = $property['sub_schema'];
                }

                foreach ($property['sub_schemas'] ?? [] as $subSchema) {
                    if (!empty($subSchema)) {
                        $dependencies[] = $subSchema;
                    }
                }

                foreach ($dependencies as $dependency) {
                    if (!isset($excludedDefinitions[$dependency])) {
                        $queue[$dependency] = true;
                    }
                }
            }
        }

        $filteredDefinitions = [];
        foreach ($definitions as $modelName => $definition) {
            if (isset($reachableDefinitions[$modelName])) {
                $filteredDefinitions[$modelName] = $definition;
            }
        }

        $filteredRequestModels = [];
        foreach ($requestModels as $modelName => $requestModel) {
            if (isset($reachableRequestModels[$modelName])) {
                $filteredRequestModels[$modelName] = $requestModel;
            }
        }

        $this->filteredModelDataCache = [
            'definitions' => $filteredDefinitions,
            'requestModels' => $filteredRequestModels,
        ];

        return $this->filteredModelDataCache;
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
            'spec' => [
                'title' => $this->spec->getTitle(),
                'description' => $this->spec->getDescription(),
                'namespace' => $this->getParam('namespace') ?: $this->spec->getNamespace(),
                'version' => $this->spec->getVersion(),
                'endpoint' => $this->spec->getEndpoint(),
                'endpointDocs' => $this->spec->getEndpointDocs(),
                'host' => parse_url($this->spec->getEndpoint(), PHP_URL_HOST),
                'basePath' => parse_url($this->spec->getEndpoint(), PHP_URL_PATH) ?: '',
                'licenseName' => $this->spec->getLicenseName(),
                'licenseURL' => $this->spec->getLicenseURL(),
                'contactName' => $this->spec->getContactName(),
                'contactURL' => $this->spec->getContactURL(),
                'contactEmail' => $this->spec->getContactEmail(),
                'services' => $filteredServices,
                'requestEnums' => $filteredRequestEnums,
                'requestModelEnums' => $filteredRequestModelEnums,
                'responseEnums' => $filteredResponseEnums,
                'allEnums' => $filteredAllEnums,
                'definitions' => $filteredDefinitions,
                'requestModels' => $filteredRequestModels,
                'global' => [
                    'headers' => $this->spec->getGlobalHeaders(),
                    'defaultHeaders' => array_merge(
                        $this->defaultHeaders,
                        $this->spec->getVersion() !== '' ? ['X-Appwrite-Response-Format' => $this->spec->getVersion()] : [],
                    ),
                ],
            ],
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
                        $methods = $service['methods'] ?? [];
                        $params['service'] = [
                            'globalParams' => $service['globalParams'] ?? [],
                            'description' => $service['description'] ?? '',
                            'name' => $key,
                            'features' => [
                                'upload' => $this->hasUploads($methods),
                                'location' => $this->hasLocation($methods),
                                'webAuth' => $this->hasWebAuth($methods),
                            ],
                            'methods' => $methods,
                            'isConsoleOnly' => $this->isConsoleOnly($key),
                        ];

                        if ($this->exclude($file, $params)) {
                            continue;
                        }

                        $this->render($template, $destination, $block, $params, $minify);
                    }
                    break;
                case 'definition':
                    foreach ($filteredDefinitions as $definition) {
                        $params['definition'] = $definition;

                        if ($this->exclude($file, $params)) {
                            continue;
                        }

                        $this->render($template, $destination, $block, $params, $minify);
                    }
                    break;
                case 'requestModel':
                    foreach ($filteredRequestModels as $requestModel) {
                        $params['requestModel'] = $requestModel;

                        if ($this->exclude($file, $params)) {
                            continue;
                        }

                        $this->render($template, $destination, $block, $params, $minify);
                    }
                    break;
                case 'method':
                    foreach ($filteredServices as $key => $service) {
                        $methods = $service['methods'] ?? [];
                        $params['service'] = [
                            'name' => $key,
                            'methods' => $methods,
                            'globalParams' => $service['globalParams'] ?? [],
                            'features' => [
                                'upload' => $this->hasUploads($methods),
                                'location' => $this->hasLocation($methods),
                                'webAuth' => $this->hasWebAuth($methods),
                            ],
                            'isConsoleOnly' => $this->isConsoleOnly($key),
                        ];

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

    protected function isMethodExcluded(array $method, string $serviceName = ''): bool
    {
        $excludeIndex = $this->getExcludeIndex();
        $methodName = $method['name'] ?? '';

        if (isset($excludeIndex['methods'][$methodName])) {
            foreach ($excludeIndex['methods'][$methodName] as $scope) {
                // true = global exclusion, string = service-scoped exclusion
                if ($scope === true || $scope === $serviceName) {
                    return true;
                }
            }
        }

        return isset($excludeIndex['types'][$method['type'] ?? '']);
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

        if (\in_array($params['service']['name'] ?? '', $services)) {
            return true;
        }

        foreach ($features as $feature) {
            if ($params['service']['features'][$feature] ?? false) {
                return true;
            }
        }

        if (\in_array($params['method']['name'] ?? '', $methods)) {
            return true;
        }

        $currentMethodName = $params['method']['name'] ?? '';
        $currentServiceName = $params['service']['name'] ?? '';
        foreach ($scopedMethods as $scopedMethod) {
            if ($scopedMethod['name'] === $currentMethodName && $scopedMethod['service'] === $currentServiceName) {
                return true;
            }
        }

        if (\in_array($params['method']['type'] ?? '', $types)) {
            return true;
        }
        return \in_array($params['definition']['name'] ?? '', $definitions);
    }

    protected function hasUploads(array $methods): bool
    {
        return array_any($methods, fn($method): bool => isset($method['type']) && $method['type'] === 'upload');
    }

    protected function hasLocation(array $methods): bool
    {
        return array_any($methods, fn($method): bool => isset($method['type']) && $method['type'] === 'location');
    }

    protected function hasWebAuth(array $methods): bool
    {
        return array_any($methods, fn($method): bool => isset($method['type']) && $method['type'] === 'webAuth');
    }

    protected function isConsoleOnly(string $serviceName): bool
    {
        $consoleOnlyServices = [
            'account',
            'locale',
            'organization',
            'organizations',
            'projects',
        ];

        return \in_array($serviceName, $consoleOnlyServices, true);
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

    /**
     * @return array<int, string>
     */
    protected function getValidResponseModels(array $method): array
    {
        $responseModels = [];

        foreach ($method['responseModels'] ?? [] as $modelName) {
            if (empty($modelName) || $modelName === 'any' || \in_array($modelName, $responseModels, true)) {
                continue;
            }

            $responseModels[] = $modelName;
        }

        if (
            $responseModels === []
            && !empty($method['responseModel'])
            && $method['responseModel'] !== 'any'
        ) {
            $responseModels[] = $method['responseModel'];
        }

        return $responseModels;
    }
}
