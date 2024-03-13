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
            return $this->language->toPascalCase($value);
        }));
        $this->twig->addFilter(new TwigFilter('caseUcwords', function ($value) {
            return ucwords($value, " -_");
        }));
        $this->twig->addFilter(new TwigFilter('caseLcfirst', function ($value) {
            return lcfirst((string)$value);
        }));
        $this->twig->addFilter(new TwigFilter('caseCamel', function ($value) {
            return $this->language->toCamelCase($value);
        }));
        $this->twig->addFilter(new TwigFilter('caseDash', function ($value) {
            return $this->language->toKebabCase($value);
        }));
        $this->twig->addFilter(new TwigFilter('caseSlash', function ($value) {
            return $this->language->toSlashCase($value);
        }));
        $this->twig->addFilter(new TwigFilter('caseDot', function ($value) {
            return $this->language->toDotCase($value);
        }));
        $this->twig->addFilter(new TwigFilter('caseSnake', function ($value) {
            return $this->language->toSnakeCase($value);
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
            return str_replace('$', '\$', $value);
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
            if (in_array($value, $language->getKeywords())) {
                return 'x' . $value;
            }

            return $value;
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
     * @param string $target
     * @throws Throwable
     * @throws LoaderError
     * @throws RuntimeError
     * @throws SyntaxError
     */
    public function generate(string $target): void
    {
        $params = [
            'spec' => [
                'title' => $this->spec->getTitle(),
                'description' => $this->spec->getDescription(),
                'namespace' => $this->spec->getNamespace(),
                'version' => $this->spec->getVersion(),
                'endpoint' => $this->spec->getEndpoint(),
                'host' => parse_url($this->spec->getEndpoint(), PHP_URL_HOST),
                'basePath' => $this->spec->getAttribute('basePath', ''),
                'licenseName' => $this->spec->getLicenseName(),
                'licenseURL' => $this->spec->getLicenseURL(),
                'contactName' => $this->spec->getContactName(),
                'contactURL' => $this->spec->getContactURL(),
                'contactEmail' => $this->spec->getContactEmail(),
                'services' => $this->spec->getServices(),
                'enums' => $this->spec->getEnums(),
                'definitions' => $this->spec->getDefinitions(),
                'global' => [
                    'headers' => $this->spec->getGlobalHeaders(),
                    'defaultHeaders' => $this->defaultHeaders,
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
                    foreach ($this->spec->getServices() as $key => $service) {
                        $methods = $this->spec->getMethods($key);
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
                        ];

                        if ($this->exclude($file, $params)) {
                            continue;
                        }

                        $this->render($template, $destination, $block, $params, $minify);
                    }
                    break;
                case 'definition':
                    foreach ($this->spec->getDefinitions() as $key => $definition) {
                        $params['definition'] = $definition;

                        if ($this->exclude($file, $params)) {
                            continue;
                        }

                        $this->render($template, $destination, $block, $params, $minify);
                    }
                    break;
                case 'method':
                    foreach ($this->spec->getServices() as $key => $service) {
                        $methods = $this->spec->getMethods($key);
                        $params['service'] = [
                            'name' => $key,
                            'methods' => $methods,
                            'globalParams' => $service['globalParams'] ?? [],
                            'features' => [
                                'upload' => $this->hasUploads($methods),
                                'location' => $this->hasLocation($methods),
                                'webAuth' => $this->hasWebAuth($methods),
                            ],
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
                    foreach ($this->spec->getEnums() as $key => $enum) {
                        $params['enum'] = $enum;

                        $this->render($template, $destination, $block, $params, $minify);
                    }
                    break;
            }
        }
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
        $exclude = $file['exclude'] ?? [];

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
        $types = [];
        foreach ($exclude['methods'] ?? [] as $method) {
            if (isset($method['name'])) {
                $methods[] = $method['name'];
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
}
