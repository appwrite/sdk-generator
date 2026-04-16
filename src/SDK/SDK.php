<?php

namespace Appwrite\SDK;

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
    /**
     * @var Language|null
     */
    protected ?Language $language = null;

    /**
     * @var Spec|null
     */
    protected ?Spec $spec = null;

    /**
     * @var Environment|null
     */
    protected ?Environment $twig = null;

    /**
     * @var array
     */
    protected array $defaultHeaders = [];

    /**
     * @var array
     */
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

    /**
     * @var array
     */
    protected array $excludeRules = [
        'services' => [],
        'methods' => [],
        'definitions' => []
    ];

    /**
     * @var array|null
     */
    protected ?array $excludeIndex = null;

    /**
     * @var array|null
     */
    protected ?array $filteredServicesCache = null;

    /**
     * @var array|null
     */
    protected ?array $filteredModelDataCache = null;

    /**
     * SDK constructor.
     *
     * @param Language $language
     * @param Spec $spec
     */
    public function __construct(Language $language, Spec $spec)
    {
        $this->language = $language;
        $this->spec     = $spec;

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

        $this->twig->addFilter(new TwigFilter('caseLower', function ($value) {
            return strtolower((string)$value);
        }));
        $this->twig->addFilter(new TwigFilter('caseUpper', function ($value) {
            return strtoupper((string)$value);
        }));
        $this->twig->addFilter(new TwigFilter('caseUcfirst', function ($value) {
            return ucfirst($this->helperCamelCase($value));
        }));
        $this->twig->addFilter(new TwigFilter('caseUcwords', function ($value) {
            return ucwords($value, " -_");
        }));
        $this->twig->addFilter(new TwigFilter('caseLcfirst', function ($value) {
            return lcfirst((string)$value);
        }));
        $this->twig->addFilter(new TwigFilter('caseCamel', function ($value) {
            return $this->helperCamelCase($value);
        }));
        $this->twig->addFilter(new TwigFilter('removeDash', function ($value) {
            return str_replace('-', '', $value);
        }));
        $this->twig->addFilter(new TwigFilter('caseDash', function ($value) {
            return str_replace([' ', '_'], '-', strtolower(preg_replace('/([a-zA-Z])(?=[A-Z])/', '$1-', $value)));
        }));
        $this->twig->addFilter(new TwigFilter('caseKebab', function ($value) {
            return strtolower(preg_replace('/(?<!^)([A-Z][a-z]|(?<=[a-z])[^a-z\s]|(?<=[A-Z])[0-9_])/', '-$1', $value));
        }));
        $this->twig->addFilter(new TwigFilter('caseSlash', function ($value) {
            return str_replace([' ', '_', '.'], '/', strtolower(preg_replace('/([a-zA-Z])(?=[A-Z])/', '$1/', $value)));
        }));
        $this->twig->addFilter(new TwigFilter('caseDot', function ($value) {
            return str_replace([' ', '_'], '.', strtolower(preg_replace('/([a-zA-Z])(?=[A-Z])/', '$1.', $value)));
        }));
        $this->twig->addFilter(new TwigFilter('caseSnake', function ($value) {
            preg_match_all('!([A-Za-z][A-Z0-9]*(?=$|[A-Z][a-z0-9])|[A-Za-z][a-z0-9]+)!', $value, $matches);
            $ret = $matches[0];
            foreach ($ret as &$match) {
                $match = $match == strtoupper($match)
                    ? strtolower($match)
                    : lcfirst($match);
            }
            return implode('_', $ret);
        }));
        $this->twig->addFilter(new TwigFilter('caseJson', function ($value) {
            return (is_array($value)) ? json_encode($value) : $value;
        }, ['is_safe' => ['html']]));
        $this->twig->addFilter(new TwigFilter('caseArray', function ($value) {
            return (is_array($value)) ? json_encode($value) : '[]';
        }, ['is_safe' => ['html']]));
        $this->twig->addFilter(new TwigFilter('typeName', function ($value, $spec = []) {
            return $this->language->getTypeName($value, $spec);
        }, ['is_safe' => ['html']]));
        $this->twig->addFilter(new TwigFilter('getValidResponseModels', function ($value) {
            return $this->getValidResponseModels($value);
        }));
        $this->twig->addFilter(new TwigFilter('getUnionDispatch', function ($value, array $spec = []) {
            return $this->getUnionDispatch($value, $spec);
        }));
        $this->twig->addFilter(new TwigFilter('paramDefault', function ($value) {
            return $this->language->getParamDefault($value);
        }, ['is_safe' => ['html']]));
        $this->twig->addFilter(new TwigFilter('paramExample', function ($value) {
            return $this->language->getParamExample($value);
        }, ['is_safe' => ['html']]));
        $this->twig->addFilter(new TwigFilter('comment1', function ($value) {
            $value = explode("\n", $value);
            foreach ($value as $key => $line) {
                $value[$key] = "     * " . wordwrap($line, 75, "\n     * ");
            }
            return implode("\n", $value);
        }, ['is_safe' => ['html']]));
        $this->twig->addFilter(new TwigFilter('escapeDollarSign', function ($value) {
            $value = str_replace('\\', '\\\\', $value ?? ''); // Escape backslashes first
            $value = str_replace('"', '\\"', $value);   // Escape double quotes
            $value = str_replace('$', '\\$', $value);   // Escape dollar signs
            return $value;
        }, ['is_safe' => ['html']]));
        $this->twig->addFilter(new TwigFilter('paramsQuery', function ($value) {
            $query = '';

            foreach ($value as $key => $param) {
                $query .= (!empty($query)) ? " + '&" : "";
                $query .= "{$param['name']}=' + {$param['name']}";
            }

            return $query;
        }, ['is_safe' => ['html']]));
        $this->twig->addFilter(new TwigFilter('html', function ($value) {
            return $value;
        }, ['is_safe' => ['html']]));
        $this->twig->addFilter(new TwigFilter('escapeKeyword', function ($value) use ($language) {
            return $language->escapeKeyword($value);
        }, ['is_safe' => ['html']]));
        $this->twig->addFilter(new TwigFilter('caseHTML', function ($value) {
            return $value;
        }, ['is_safe' => ['html']]));
        $this->twig->addFilter(new TwigFilter('removeDollarSign', function ($value) {
            return str_replace('$', '', $value);
        }));
        $this->twig->addFilter(new TwigFilter('unescape', function ($value) {
            return html_entity_decode($value);
        }));
        $this->twig->addFilter(new TwigFilter('overrideIdentifier', function ($value) use ($language) {
            if (isset($language->getIdentifierOverrides()[$value])) {
                return $language->getIdentifierOverrides()[$value];
            }
            return $value;
        }));
        $this->twig->addFilter(new TwigFilter('capitalizeFirst', function ($value) {
            return ucfirst($value);
        }));
        $this->twig->addFilter(new TwigFilter('caseSpace', function ($value) {
            return preg_replace('/([a-z])([A-Z])/', '$1 $2', $value);
        }));
        $this->twig->addFilter(new TwigFilter('caseSnakeExceptFirstDot', function ($value) {
            $parts = explode('.', $value, 2);
            $toSnake = function ($str) {
                preg_match_all('!([A-Za-z][A-Z0-9]*(?=$|[A-Z][a-z0-9])|[A-Za-z][a-z0-9]+)!', $str, $matches);
                return implode('_', array_map(function ($m) {
                    return $m === strtoupper($m) ? strtolower($m) : lcfirst($m);
                }, $matches[0]));
            };
            if (count($parts) < 2) {
                return $toSnake($value);
            }
            return $parts[0] . '.' . $toSnake($parts[1]);
        }));
        $this->twig->addFilter(new TwigFilter('hasPermissionParam', function ($value) {
            return $this->language->hasPermissionParam($value);
        }));
    }

    /**
     * @param array $headers
     * @return $this
     */
    public function setDefaultHeaders(array $headers): SDK
    {
        $this->defaultHeaders = $headers;

        return $this;
    }

    /**
     * @param string $namespace
     * @return $this
     */
    public function setNamespace(string $namespace): SDK
    {
        $this->setParam('namespace', $namespace);

        return $this;
    }

    /**
     * @param string $name
     * @return $this
     */
    public function setName(string $name): SDK
    {
        $this->setParam('name', $name);

        return $this;
    }

    /**
     * @param string $text
     * @return $this
     */
    public function setDescription(string $text): SDK
    {
        $this->setParam('description', $text);

        return $this;
    }

    /**
     * @param string $text
     * @return $this
     */
    public function setShortDescription(string $text): SDK
    {
        $this->setParam('shortDescription', $text);

        return $this;
    }

    /**
     * @param string $version
     * @return $this
     */
    public function setVersion(string $version): SDK
    {
        $this->setParam('version', $version);

        return $this;
    }

    /**
     * @param string $platform
     * @return $this
     */
    public function setPlatform(string $platform): SDK
    {
        $this->setParam('platform', $platform);

        return $this;
    }

    /**
     * @param string $license
     * @return $this
     */
    public function setLicense(string $license): SDK
    {
        $this->setParam('license', $license);

        return $this;
    }

    /**
     * @param string $content
     * @return $this
     */
    public function setLicenseContent(string $content): SDK
    {
        $this->setParam('licenseContent', $content);

        return $this;
    }

    /**
     * @param string $url
     * @return $this
     */
    public function setGitRepo(string $url): SDK
    {
        $this->setParam('gitRepo', $url);

        return $this;
    }

    /**
     * @param string $name
     * @return $this
     */
    public function setGitRepoName(string $name): SDK
    {
        $this->setParam('gitRepoName', $name);

        return $this;
    }

    /**
     * @param string $name
     * @return $this
     */
    public function setGitUserName(string $name): SDK
    {
        $this->setParam('gitUserName', $name);

        return $this;
    }

    /**
     * @param string $url
     * @return $this
     */
    public function setGitURL(string $url): SDK
    {
        $this->setParam('gitURL', $url);

        return $this;
    }

    /**
     * @param string $url
     * @return $this
     */
    public function setLogo(string $url): SDK
    {
        $this->setParam('logo', $url);

        return $this;
    }

    /**
     * @param string $url
     * @return $this
     */
    public function setURL(string $url): SDK
    {
        $this->setParam('url', $url);

        return $this;
    }

    /**
     * @param string $text
     * @return $this
     */
    public function setShareText(string $text): SDK
    {
        $this->setParam('shareText', $text);

        return $this;
    }

    /**
     * @param string $user
     * @return $this
     */
    public function setShareVia(string $user): SDK
    {
        $this->setParam('shareVia', $user);

        return $this;
    }

    /**
     * @param string $url
     * @return $this
     */
    public function setShareURL(string $url): SDK
    {
        $this->setParam('shareURL', $url);

        return $this;
    }

    /**
     * @param string $tags Comma separated list
     * @return $this
     */
    public function setShareTags(string $tags): SDK
    {
        $this->setParam('shareTags', $tags);

        return $this;
    }

    /**
     * @param string $message
     * @return $this
     */
    public function setWarning(string $message): SDK
    {
        $this->setParam('warning', $message);

        return $this;
    }

    /**
     * @param $message string
     * @return $this
     */
    public function setGettingStarted(string $message): SDK
    {
        $this->setParam('gettingStarted', $message);

        return $this;
    }

    /**
     * @param string $text
     * @return $this
     */
    public function setReadme(string $text): SDK
    {
        $this->setParam('readme', $text);

        return $this;
    }

    /**
     * @param string $text
     * @return $this
     */
    public function setChangelog(string $text): SDK
    {
        $this->setParam('changelog', $text);

        return $this;
    }

    /**
     * @param string $text
     * @return $this
     */
    public function setExamples(string $text): SDK
    {
        $this->setParam('examples', $text);

        return $this;
    }

    /**
     * @param string $channel
     * @param string $url
     * @return $this
     */
    public function setDiscord(string $channel, string $url): SDK
    {
        $this->setParam('discordChannel', $channel);
        $this->setParam('discordUrl', $url);

        return $this;
    }

    /**
     * @param string $handle
     * @return $this
     */
    public function setTwitter(string $handle): SDK
    {
        $this->setParam('twitterHandle', $handle);

        return $this;
    }

    /**
     * @param string $test
     * @return $this
     */
    public function setTest(string $test): SDK
    {
        $this->setParam('test', $test);

        return $this;
    }

    /**
     * @param string $key
     * @param string $value
     * @return SDK
     */
    public function setParam(string $key, string $value): SDK
    {
        $this->params[$key] = $value;

        return $this;
    }

    /**
     * @param string $name
     * @return string
     */
    public function getParam(string $name): string
    {
        return $this->params[$name] ?? '';
    }

    /**
     * @return array
     */
    public function getParams(): array
    {
        return $this->params;
    }

    /**
     * Get services filtered by exclusion rules
     *
     * @return array
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

            if (empty($methods)) {
                continue;
            }

            $service['methods'] = $methods;
            $filteredServices[$serviceName] = $service;
        }

        $this->filteredServicesCache = $filteredServices;

        return $this->filteredServicesCache;
    }

    /**
     * @param array $methods
     * @param string $serviceName
     * @return array
     */
    protected function getFilteredMethods(array $methods, string $serviceName = ''): array
    {
        return \array_values(\array_filter($methods, fn (array $method) => !$this->isMethodExcluded($method, $serviceName)));
    }

    /**
     * @return array
     */
    protected function getFilteredDefinitions(): array
    {
        return $this->getFilteredModelData()['definitions'];
    }

    /**
     * @return array
     */
    protected function getFilteredRequestModels(): array
    {
        return $this->getFilteredModelData()['requestModels'];
    }

    /**
     * @return array
     */
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

    /**
     * @return array
     */
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

    /**
     * @return array
     */
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

    /**
     * @return array
     */
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

    /**
     * @param array $list
     * @param string $enumName
     * @param array $enumValues
     * @param array $enumKeys
     * @return void
     */
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

            $list[$enumName]['enum'][] = $value;
            $list[$enumName]['keys'][] = $enumKeys[$index] ?? $value;
        }
    }

    /**
     * @return array
     */
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

        while (!empty($queue)) {
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
     * @param string $target
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
                'basePath' => $this->spec->getAttribute('basePath', ''),
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
            if ($file['scope'] != 'copy') {
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
                    foreach ($filteredDefinitions as $key => $definition) {
                        $params['definition'] = $definition;

                        if ($this->exclude($file, $params)) {
                            continue;
                        }

                        $this->render($template, $destination, $block, $params, $minify);
                    }
                    break;
                case 'requestModel':
                    foreach ($filteredRequestModels as $key => $requestModel) {
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
                    foreach ($filteredAllEnums as $key => $enum) {
                        $params['enum'] = $enum;

                        $this->render($template, $destination, $block, $params, $minify);
                    }
                    break;
            }
        }
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
     * @return $this
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

    /**
     * @param string $serviceName
     * @param array $methods
     * @return bool
     */
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

    /**
     * @param array $method
     * @param string $serviceName
     * @return bool
     */
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

    /**
     * @return array
     */
    protected function getExcludedDefinitions(): array
    {
        return $this->getExcludeIndex()['definitions'];
    }

    /**
     * @return array
     */
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
     * @return bool
     */
    protected function exclude($file, $params): bool
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

        if (\in_array($params['definition']['name'] ?? '', $definitions)) {
            return true;
        }

        return false;
    }

    /**
     * @param array $methods
     * @return bool
     */
    protected function hasUploads(array $methods): bool
    {
        foreach ($methods as $method) {
            if (isset($method['type']) && $method['type'] === 'upload') {
                return true;
            }
        }

        return false;
    }

    /**
     * @param array $methods
     * @return bool
     */
    protected function hasLocation(array $methods): bool
    {
        foreach ($methods as $method) {
            if (isset($method['type']) && $method['type'] === 'location') {
                return true;
            }
        }

        return false;
    }

    /**
     * @param array $methods
     * @return bool
     */
    protected function hasWebAuth(array $methods): bool
    {
        foreach ($methods as $method) {
            if (isset($method['type']) && $method['type'] === 'webAuth') {
                return true;
            }
        }

        return false;
    }

    protected function isConsoleOnly(string $serviceName): bool
    {
        $consoleOnlyServices = [
            'account',
            'locale',
            'organizations',
            'projects',
        ];

        return \in_array($serviceName, $consoleOnlyServices, true);
    }

    /**
     * @param TemplateWrapper $template
     * @param string $destination
     * @param string|null $block
     * @param array $params
     * @param bool $minify
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
                    $minifier = new Minify\JS($destination);
                    $minifier->minify($destination);
                    break;
                case 'css':
                    $minifier = new Minify\CSS($destination);
                    $minifier->minify($destination);
                    break;
                default:
                    throw new Exception('No minifier found for ' . $ext . ' file');
            }
        }
    }

    /**
     * @param string|null $str
     * @return string
     */
    protected function helperCamelCase(?string $str): string
    {
        if ($str == null) {
            return '';
        }
        $str = preg_replace('/[^a-z0-9' . implode("", []) . ']+/i', ' ', $str);
        $str = trim($str);
        $str = ucwords($str);
        $str = str_replace(" ", "", $str);
        $str = lcfirst($str);

        return $str;
    }

    /**
     * @param array $method
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
            empty($responseModels)
            && !empty($method['responseModel'])
            && $method['responseModel'] !== 'any'
        ) {
            $responseModels[] = $method['responseModel'];
        }

        return $responseModels;
    }

    /**
     * @param array $method
     * @param array $spec
     * @return array<string, array{
     *     conditions: array<string, scalar|null>,
     *     required: array<int, string>,
     *     all: array<int, string>
     * }>
     */
    protected function getUnionDispatch(array $method, array $spec = []): array
    {
        $dispatch = [];

        foreach ($method['responseDiscriminator'] ?? [] as $modelName => $conditions) {
            if (empty($modelName) || $modelName === 'any' || isset($dispatch[$modelName])) {
                continue;
            }

            $dispatch[$modelName] = [
                'conditions' => \is_array($conditions) ? $conditions : [],
                'required' => [],
                'all' => [],
            ];
        }

        if (!empty($dispatch)) {
            return $dispatch;
        }

        foreach ($method['responseModels'] ?? [] as $modelName) {
            if (empty($modelName) || $modelName === 'any' || isset($dispatch[$modelName])) {
                continue;
            }

            $definition = $spec['definitions'][$modelName] ?? null;

            if ($definition === null || !\is_array($definition['properties'] ?? null)) {
                continue;
            }

            $dispatch[$modelName] = [
                'conditions' => [],
                'required' => [],
                'all' => [],
            ];

            foreach ($definition['properties'] as $propertyName => $property) {
                $dispatch[$modelName]['all'][] = $propertyName;

                if (!empty($property['required'])) {
                    $dispatch[$modelName]['required'][] = $propertyName;
                }
            }
        }

        return $dispatch;
    }
}
