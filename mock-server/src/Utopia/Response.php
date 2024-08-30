<?php

namespace Utopia\MockServer\Utopia;

use Utopia\MockServer\Utopia\BodyMultipart;
use Swoole\Http\Response as SwooleResponse;
use Utopia\CLI\Console;
use Utopia\Database\Document;
use Utopia\Swoole\Response as UtopiaResponse;

/**
 * @method int getStatusCode()
 * @method Response setStatusCode(int $code = 200)
 */
class Response extends UtopiaResponse
{
    // General
    public const MODEL_NONE = 'none';
    public const MODEL_ANY = 'any';
    public const MODEL_LOG = 'log';
    public const MODEL_LOG_LIST = 'logList';
    public const MODEL_ERROR = 'error';
    public const MODEL_METRIC = 'metric';
    public const MODEL_METRIC_LIST = 'metricList';
    public const MODEL_ERROR_DEV = 'errorDev';
    public const MODEL_BASE_LIST = 'baseList';
    public const MODEL_MULTIPART = 'multipart';

    // Mock
    public const MODEL_MOCK = 'mock';

    private static $filter = null;

    /**
     * @var array
     */
    protected array $payload = [];

    /**
     * Response constructor.
     *
     * @param float $time
     */
    public function __construct(SwooleResponse $response)
    {
        parent::__construct($response);
    }

    /**
     * HTTP content types
     */
    public const CONTENT_TYPE_YAML = 'application/x-yaml';
    public const CONTENT_TYPE_NULL = 'null';
    public const CONTENT_TYPE_MULTIPART = 'multipart/form-data';

    /**
     * List of defined output objects
     */
    protected $models = [];


    /**
     * Set Model Object
     *
     * @return self
     */
    public function setModel(Model $instance)
    {
        $this->models[$instance->getType()] = $instance;

        return $this;
    }

    /**
     * Get Model Object
     *
     * @param string $key
     * @return Model
     * @throws Exception
     */
    public function getModel(string $key): Model
    {
        if (!isset($this->models[$key])) {
            throw new \Exception('Undefined model: ' . $key);
        }

        return $this->models[$key];
    }

    /**
     * Get Models List
     *
     * @return Model[]
     */
    public function getModels(): array
    {
        return $this->models;
    }

    public function multipart(array $data): void
    {
        $multipart = new BodyMultipart();
        foreach ($data as $key => $value) {
            $multipart->setPart($key, $value);
        }

        $this
            ->setContentType($multipart->exportHeader())
            ->send($multipart->exportBody());
    }

    /**
     * Validate response objects and outputs
     *  the response according to given format type
     *
     * @param Document $document
     * @param string $model
     *
     * return void
     * @throws Exception
     */
    public function dynamic(Document $document, string $model): void
    {
        $output = $this->output(clone $document, $model);

        // If filter is set, parse the output
        if (self::hasFilter()) {
            $output = self::getFilter()->parse($output, $model);
        }

        switch ($this->getContentType()) {
            case self::CONTENT_TYPE_JSON:
                $this->json(!empty($output) ? $output : new \stdClass());
                break;

            case self::CONTENT_TYPE_NULL:
                break;

            case self::CONTENT_TYPE_MULTIPART:
                $this->multipart(!empty($output) ? $output : new \stdClass());
                break;

            default:
                if ($model === self::MODEL_NONE) {
                    $this->noContent();
                } else {
                    $this->json(!empty($output) ? $output : new \stdClass());
                }
                break;
        }
    }



    /**
     * Generate valid response object from document data
     *
     * @param Document $document
     * @param string $model
     *
     * return array
     * @return array
     * @throws Exception
     */
    public function output(Document $document, string $model): array
    {
        $data       = clone $document;
        $model      = $this->getModel($model);
        $output     = [];

        $data = $model->filter($data);

        if ($model->isAny()) {
            $this->payload = $data->getArrayCopy();

            return $this->payload;
        }

        foreach ($model->getRules() as $key => $rule) {
            if (!$data->isSet($key) && $rule['required']) { // do not set attribute in response if not required
                if (\array_key_exists('default', $rule)) {
                    $data->setAttribute($key, $rule['default']);
                } else {
                    throw new \Exception('Model ' . $model->getName() . ' is missing response key: ' . $key);
                }
            }

            if ($rule['array']) {
                if (!is_array($data[$key])) {
                    throw new \Exception($key . ' must be an array of type ' . $rule['type']);
                }

                foreach ($data[$key] as $index => $item) {
                    if ($item instanceof Document) {
                        if (\is_array($rule['type'])) {
                            foreach ($rule['type'] as $type) {
                                $condition = false;
                                foreach ($this->getModel($type)->conditions as $attribute => $val) {
                                    $condition = $item->getAttribute($attribute) === $val;
                                    if (!$condition) {
                                        break;
                                    }
                                }
                                if ($condition) {
                                    $ruleType = $type;
                                    break;
                                }
                            }
                        } else {
                            $ruleType = $rule['type'];
                        }

                        if (!array_key_exists($ruleType, $this->models)) {
                            throw new \Exception('Missing model for rule: ' . $ruleType);
                        }

                        $data[$key][$index] = $this->output($item, $ruleType);
                    }
                }
            } else {
                if ($data[$key] instanceof Document) {
                    $data[$key] = $this->output($data[$key], $rule['type']);
                }
            }

            $output[$key] = $data[$key];
        }

        $this->payload = $output;

        return $this->payload;
    }

    /**
     * Output response
     *
     * Generate HTTP response output including the response header (+cookies) and body and prints them.
     *
     * @param string $body
     *
     * @return void
     */
    public function file(string $body = ''): void
    {
        $this->payload = [
            'payload' => $body
        ];

        $this->send($body);
    }

    /**
     * @return array
     */
    public function getPayload(): array
    {
        return $this->payload;
    }

    /**
     * Function to set a response filter
     *
     * @param $filter the response filter to set
     *
     * @return void
     */
    public static function setFilter(?Filter $filter)
    {
        self::$filter = $filter;
    }

    /**
     * Return the currently set filter
     *
     * @return Filter
     */
    public static function getFilter(): ?Filter
    {
        return self::$filter;
    }

    /**
     * Check if a filter has been set
     *
     * @return bool
     */
    public static function hasFilter(): bool
    {
        return self::$filter != null;
    }

    /**
     * Set Header
     *
     * @param  string  $key
     * @param  string  $value
     * @return void
     */
    public function setHeader(string $key, string $value): void
    {
        $this->sendHeader($key, $value);
    }
}
