<?php

namespace Appwrite\SDK;

use Exception;
use Appwrite\Spec\Spec;
use Twig\Environment;
use Twig\Loader\FilesystemLoader;
use Twig\TemplateWrapper;
use Twig\TwigFilter;
use MatthiasMullie\Minify;

class SDK
{
    /**
     * @var Language
     */
    protected $language = null;

    /**
     * @var Spec
     */
    protected $spec = null;

    /**
     * @var Environment
     */
    protected $twig = null;

    /**
     * @var array
     */
    protected $defaultHeaders = [];

    /**
     * @var array
     */
    protected $params = [
        'namespace' => '',
        'name' => '',
        'description' => '',
        'shortDescription' => '',
        'version' => '',
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
        ] );
        $this->twig->addExtension(new \Twig\Extension\DebugExtension());

        $this->twig->addFilter(new TwigFilter('caseLower', function ($value) {
            return strtolower((string)$value);
        }));
        $this->twig->addFilter(new TwigFilter('caseUpper', function ($value) {
            return strtoupper((string)$value);
        }));
        $this->twig->addFilter(new TwigFilter('caseUcfirst', function ($value) {
            return ucfirst((string)$this->helperCamelCase($value));
        }));
        $this->twig->addFilter(new TwigFilter('caseLcfirst', function ($value) {
            return lcfirst((string)$value);
        }));
        $this->twig->addFilter(new TwigFilter('caseCamel', function ($value) {
            return (string)$this->helperCamelCase($value);
        }));
        $this->twig->addFilter(new TwigFilter('caseDash', function ($value) {
            return str_replace([' ', '_'], '-', strtolower(preg_replace('/([a-zA-Z])(?=[A-Z])/', '$1-', $value)));
        }));
        $this->twig->addFilter(new TwigFilter('caseSlash', function ($value) {
            return str_replace([' ', '_'], '/', strtolower(preg_replace('/([a-zA-Z])(?=[A-Z])/', '$1/', $value)));
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
        $this->twig->addFilter(new TwigFilter('typeName', function ($value) {
            return $this->language->getTypeName($value);
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
                $value[$key] = "     * " . wordwrap($value[$key], 75, "\n     * ");
            }
            return implode("\n", $value);
        }, ['is_safe' => ['html']]));
        $this->twig->addFilter(new TwigFilter('comment2', function ($value) {
            $value = explode("\n", $value);
            foreach ($value as $key => $line) {
                $value[$key] = "     * " . wordwrap($value[$key], 75, "\n     * ");
            }
            return implode("\n", $value);
        }, ['is_safe' => ['html']]));
        $this->twig->addFilter(new TwigFilter('comment3', function ($value) {
            $value = explode("\n", $value);
            foreach ($value as $key => $line) {
                $value[$key] = "         * " . wordwrap($value[$key], 75, "\n         * ");
            }
            return implode("\n", $value);
        }, ['is_safe' => ['html']]));
        $this->twig->addFilter(new TwigFilter('dartComment', function ($value) {
            $value = explode("\n", $value);
            foreach ($value as $key => $line) {
                $value[$key] = "     /// " . wordwrap($value[$key], 75, "\n     /// ");
            }
            return implode("\n", $value);
        }, ['is_safe' => ['html']]));
        $this->twig->addFilter(new TwigFilter('dotnetComment', function ($value) {
            $value = explode("\n", $value);
            foreach ($value as $key => $line) {
                $value[$key] = "        /// " . wordwrap($value[$key], 75, "\n        /// ");
            }
            return implode("\n", $value);
        }, ['is_safe' => ['html']]));
        $this->twig->addFilter(new TwigFilter('swiftComment', function ($value) {
            $value = explode("\n", $value);
            foreach ($value as $key => $line) {
                $value[$key] = "    /// " . wordwrap($value[$key], 75, "\n    /// ");
            }
            return implode("\n", $value);
        }, ['is_safe' => ['html']]));
        $this->twig->addFilter(new TwigFilter('rubyComment', function ($value) {
            $value = explode("\n", $value);
            foreach ($value as $key => $line) {
                $value[$key] = "        # " . wordwrap($line, 75, "\n        # ");
            }
            return implode("\n", $value);
        }, ['is_safe' => ['html']]));
        $this->twig->addFilter(new TwigFilter('escapeDollarSign', function ($value) {
            return str_replace('$', '\$', $value);
        }, ['is_safe'=>['html']]));
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
        $this->twig->addFilter(new TwigFilter('godocComment', function ($value) {
            $value = explode("\n", $value);
            foreach ($value as $key => $line) {
                $value[$key] = "// " . wordwrap($value[$key], 75, "\n// ");
            }
            return implode("\n", $value);
        }, ['is_safe' => ['html']]));
        $this->twig->addFilter(new TwigFilter('escapeKeyword', function ($value) use ($language) {
            if(in_array($value, $language->getKeywords())) {
                return 'x' . $value;
            }

            return $value;
        }, ['is_safe' => ['html']]));
        $this->twig->addFilter(new TwigFilter('ucFirstAndEscape', function ($value) use ($language) {
            $value = ucfirst((string)$this->helperCamelCase($value));
            if(in_array($value, $language->getKeywords())) {
                $value = 'x' . $value;
            }

            return ucfirst((string)$this->helperCamelCase($value));
        }, ['is_safe' => ['html']]));
        $this->twig->addFilter(new TwigFilter('caseHTML', function ($value) {
            return $value;
        }, ['is_safe' => ['html']]));
        $this->twig->addFilter(new TwigFilter('removeDollarSign', function ($value) {
            return str_replace('$','',$value);
        }));
        $this->twig->addFilter(new TwigFilter('unescape', function ($value) {
            return html_entity_decode($value);
        }));
        $this->twig->addFilter(new TwigFilter('overrideIdentifier', function ($value) use ($language) {
            if(isset($language->getIdentifierOverrides()[$value])) {
                return $language->getIdentifierOverrides()[$value];
            }
            return $value;
        }));
    }

    /**
     * @param array $headers
     * @return $this
     */
    public function setDefaultHeaders($headers) {
        $this->defaultHeaders = $headers;
        
        return $this;
    }

    /**
     * @param string $text
     * @return $this
     */
    public function setNamespace($text)
    {
        $this->setParam('namespace', $text);

        return $this;
    }

    /**
     * @param string $text
     * @return $this
     */
    public function setName($text)
    {
        $this->setParam('name', $text);

        return $this;
    }

    /**
     * @param string $text
     * @return $this
     */
    public function setDescription($text)
    {
        $this->setParam('description', $text);

        return $this;
    }

    /**
     * @param string $name
     * @return $this
     */
    public function setShortDescription($text)
    {
        $this->setParam('shortDescription', $text);

        return $this;
    }

    /**
     * @param string $text
     * @return $this
     */
    public function setVersion($text)
    {
        $this->setParam('version', $text);

        return $this;
    }

    /**
     * @param string $text
     * @return $this
     */
    public function setLicense($text)
    {
        $this->setParam('license', $text);

        return $this;
    }

    /**
     * @param string $content
     * @return $this
     */
    public function setLicenseContent($content)
    {
        $this->setParam('licenseContent', $content);

        return $this;
    }

    /**
     * @param $url
     * @return $this
     */
    public function setGitRepo($url)
    {
        $this->setParam('gitRepo', $url);

        return $this;
    }

    /**
     * @param $name
     * @return $this
     */
    public function setGitRepoName($name)
    {
        $this->setParam('gitRepoName', $name);

        return $this;
    }

    /**
     * @param $name
     * @return $this
     */
    public function setGitUserName($name)
    {
        $this->setParam('gitUserName', $name);

        return $this;
    }

    /**
     * @param $url
     * @return $this
     */
    public function setGitURL($url)
    {
        $this->setParam('gitURL', $url);

        return $this;
    }

    /**
     * @param $url
     * @return $this
     */
    public function setLogo($url)
    {
        $this->setParam('logo', $url);

        return $this;
    }

    /**
     * @param $url
     * @return $this
     */
    public function setURL($url)
    {
        $this->setParam('url', $url);

        return $this;
    }

    /**
     * @param $text
     * @return $this
     */
    public function setShareText($text)
    {
        $this->setParam('shareText', $text);

        return $this;
    }

    /**
     * @param $user
     * @return $this
     */
    public function setShareVia($user)
    {
        $this->setParam('shareVia', $user);

        return $this;
    }

    /**
     * @param $url
     * @return $this
     */
    public function setShareURL($url)
    {
        $this->setParam('shareURL', $url);

        return $this;
    }

    /**
     * @param $tags string comma separated list
     * @return $this
     */
    public function setShareTags($tags)
    {
        $this->setParam('shareTags', $tags);

        return $this;
    }

    /**
     * @param $message string
     * @return $this
     */
    public function setWarning($message)
    {
        $this->setParam('warning', $message);

        return $this;
    }

    /**
     * @param $message string
     * @return $this
     */
    public function setGettingStarted($message)
    {
        $this->setParam('gettingStarted', $message);

        return $this;
    }

    /**
     * @param $text string
     * @return $this
     */
    public function setReadme($text)
    {
        $this->setParam('readme', $text);

        return $this;
    }

    /**
     * @param $text string
     * @return $this
     */
    public function setChangelog($text)
    {
        $this->setParam('changelog', $text);

        return $this;
    }

    /**
     * @param $text string
     * @return $this
     */
    public function setExamples($text)
    {
        $this->setParam('examples', $text);

        return $this;
    }

    /**
     * @param string $channel
     * @param string $url
     * @return $this
     */
    public function setDiscord(string $channel, string $url)
    {
        $this->setParam('discordChannel', $channel);
        $this->setParam('discordUrl', $url);

        return $this;
    }

    /**
     * @param string $handle
     * @return $this
     */
    public function setTwitter(string $handle)
    {
        $this->setParam('twitterHandle', $handle);

        return $this;
    }

    /**
     * @param string $test
     * @return $this
     */
    public function setTest(string $test)
    {
        $this->setParam('test', $test);

        return $this;
    }

    /**
     * @param string $key
     * @param string $value
     * @return SDK
     */
    public function setParam($key, $value)
    {
        $this->params[$key] = $value;

        return $this;
    }

    /**
     * @param $name
     * @return string
     */
    public function getParam($name)
    {
        return $this->params[$name] ?? '';
    }

    /**
     * @return array
     */
    public function getParams()
    {
        return $this->params;
    }

    /**
     * @param $target
     * @throws \Throwable
     * @throws \Twig_Error_Loader
     * @throws \Twig_Error_Runtime
     * @throws \Twig_Error_Syntax
     */
    public function generate($target)
    {
        $params = [
            'spec' => [
                'title' => $this->spec->getTitle(),
                'description' => $this->spec->getDescription(),
                'namespace' => $this->spec->getNamespace(),
                'version' => $this->spec->getVersion(),
                'endpoint' => $this->spec->getEndpoint(),
                'host' => parse_url($this->spec->getEndpoint(), PHP_URL_HOST),
                'basePath' => $this->spec->getAttribute('basePath',''),
                'licenseName' => $this->spec->getLicenseName(),
                'licenseURL' => $this->spec->getLicenseURL(),
                'contactName' => $this->spec->getContactName(),
                'contactURL' => $this->spec->getContactURL(),
                'contactEmail' => $this->spec->getContactEmail(),
                'services' => $this->spec->getServices(),
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
                $template = $this->twig->load($file['template']); /* @var $template \Twig\TemplateWrapper */
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
                    copy(realpath(__DIR__.'/../../templates/' . $file['template']), $destination);
                    break;
                case 'service':
                    foreach ($this->spec->getServices() as $key => $service) {
                        $methods = $this->spec->getMethods($key);
                        $params['service'] = [
                            'description' => $service['description'] ?? '',
                            'name' => $key,
                            'features' => [
                                'upload' => $this->hasUploads($methods),
                                'location' => $this->hasLocation($methods),
                                'webAuth' => $this->hasWebAuth($methods),
                            ],
                            'methods' => $methods,
                        ];

                        $this->render($template, $destination, $block, $params, $minify);
                    }
                    break;
                case 'definition':
                    foreach ($this->spec->getDefinitions() as $key => $definition) {

                        $params['definition'] = $definition;

                        $this->render($template, $destination, $block, $params, $minify);
                    }
                    break;
                case 'method':
                    foreach ($this->spec->getServices() as $key => $service) {
                        $methods = $this->spec->getMethods($key);
                        $params['service'] = [
                            'name' => $key,
                            'methods' => $methods,
                            'features' => [
                                'upload' => $this->hasUploads($methods),
                                'location' => $this->hasLocation($methods),
                                'webAuth' => $this->hasWebAuth($methods),
                            ],
                        ];

                        foreach ($methods as $method) {
                            $params['method'] = $method;
                            $this->render($template, $destination, $block, $params, $minify);
                        }
                    }
                    break;
            }
        }
    }

    /**
     * @return bool
     */
    protected function hasUploads($methods):bool
    {
        foreach($methods as $method) {
            if(isset($method['type']) && $method['type'] === 'upload') {
                return true;
            }
        }

        return false;
    }

    /**
     * @return bool
     */
    protected function hasLocation($methods):bool
    {
        foreach($methods as $method) {
            if(isset($method['type']) && $method['type'] === 'location') {
                return true;
            }
        }

        return false;
    }

    /**
     * @return bool
     */
    protected function hasWebAuth($methods):bool
    {
        foreach($methods as $method) {
            if(isset($method['type']) && $method['type'] === 'webAuth') {
                return true;
            }
        }

        return false;
    }

    /**
     * @param TemplateWrapper $template
     * @param string $destination
     * @param string $block
     * @param array $params
     * @param bool $minify
     *
     * @throws \Throwable
     * @throws \Twig_Error_Loader
     * @throws \Twig_Error_Syntax
     */
    protected function render(TemplateWrapper $template, $destination, $block, $params = [], $minify = false)
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
                    break;
            }
        }
    }

    /**
     * @param string $str
     * @return string
     */
    protected function helperCamelCase($str)
    {
        $str = preg_replace('/[^a-z0-9' . implode("", []) . ']+/i', ' ', $str);
        $str = trim($str);
        $str = ucwords($str);
        $str = str_replace(" ", "", $str);
        $str = lcfirst($str);

        return $str;
    }
}
