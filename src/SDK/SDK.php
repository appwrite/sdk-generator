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
    protected $params = [
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
        'readme' => '',
        'changelog' => '',
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

        $this->twig = new Environment(new FilesystemLoader(__DIR__ . '/../../templates'));
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
        $this->twig->addFilter(new TwigFilter('caseSnake', function ($value) {
            return str_replace([' ', '-'], '_', strtolower(preg_replace('/([a-zA-Z])(?=[A-Z])/', '$1_', $value)));
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
                $value[$key] = "         * " . wordwrap($value[$key], 75, "\n\         * ");
            }
            return implode("\n", $value);
        }, ['is_safe' => ['html']]));
        $this->twig->addFilter(new TwigFilter('comment3', function ($value) {
            $value = explode("\n", $value);
            foreach ($value as $key => $line) {
                $value[$key] = "             * " . wordwrap($value[$key], 75, "\n             * ");
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
        $this->twig->addFilter(new TwigFilter('escapeDollarSign', function ($value) {
            return str_replace('$', '\$', $value);
        }));
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
    }

    /**
     * @param string $name
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
     * @param string $name
     * @return $this
     */
    public function setVersion($name)
    {
        $this->setParam('version', $name);

        return $this;
    }

    /**
     * @param string $name
     * @return $this
     */
    public function setLicense($name)
    {
        $this->setParam('license', $name);

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
        return (isset($this->params[$name])) ? $this->params[$name] : '';
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
                'version' => $this->spec->getVersion(),
                'endpoint' => $this->spec->getEndpoint(),
                'host' => parse_url($this->spec->getEndpoint(), PHP_URL_SCHEME) . '://' . parse_url($this->spec->getEndpoint(), PHP_URL_HOST),
                'licenseName' => $this->spec->getLicenseName(),
                'licenseURL' => $this->spec->getLicenseURL(),
                'contactName' => $this->spec->getContactName(),
                'contactURL' => $this->spec->getContactURL(),
                'contactEmail' => $this->spec->getContactEmail(),
                'services' => $this->spec->getServices(),
                'global' => [
                    'headers' => $this->spec->getGlobalHeaders(),
                ],
            ],
            'language' => [
                'name' => $this->language->getName(),
                'params' => $this->language->getParams(),
            ],
            'sdk' => $this->getParams(),
        ];

        foreach ($this->language->getFiles() as $file) {
            /** @var $file [] */
            $template       = $this->twig->load($file['template']);
            $destination    = $target . '/' . $file['destination'];
            $block          = (isset($file['block'])) ? $file['block'] : null;
            $minify         = (isset($file['minify'])) ? $file['minify'] : false;

            switch ($file['scope']) {
                case 'default':
                    $this->render($template, $destination, $block, $params, $minify);
                    break;
                case 'service':
                    foreach ($this->spec->getServices() as $key => $service) {
                        $params['service'] = [
                            'name' => $key,
                            'methods' => $this->spec->getMethods($key),
                        ];

                        $this->render($template, $destination, $block, $params, $minify);
                    }
                    break;
                case 'method':
                    foreach ($this->spec->getServices() as $key => $service) {
                        $params['service'] = [
                            'name' => $key,
                            'methods' => $this->spec->getMethods($key),
                        ];
                        foreach ($this->spec->getMethods($key) as $method) {
                            $params['method'] = $method;
                            $this->render($template, $destination, $block, $params, $minify);
                        }
                    }
                    break;
            }
        }
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

        if (!$result) {
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
