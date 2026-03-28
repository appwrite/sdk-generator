<?php

namespace Tests;

use Appwrite\SDK\Language\Rust;
use Appwrite\SDK\SDK;
use Appwrite\Spec\Swagger2;
use PHPUnit\Framework\TestCase;

class RustTemplateRegressionTest extends TestCase
{
    public function testRustGeneratorFixesIssueTenRegressions(): void
    {
        $spec = json_encode([
            'swagger' => '2.0',
            'info' => [
                'version' => '1.0.0',
                'title' => 'Appwrite',
                'description' => 'Regression test spec',
            ],
            'host' => 'localhost',
            'basePath' => '/v1',
            'schemes' => ['https'],
            'tags' => [
                ['name' => 'tablesDB', 'description' => 'TablesDB service'],
                ['name' => 'storage', 'description' => 'Storage service'],
            ],
            'paths' => [
                '/tablesdb/{databaseId}/tables/{tableId}/indexes' => [
                    'post' => [
                        'summary' => 'Create index',
                        'operationId' => 'tablesDBCreateIndex',
                        'tags' => ['tablesDB'],
                        'description' => 'Create index',
                        'consumes' => ['application/json'],
                        'responses' => [
                            '201' => [
                                'description' => 'Index created',
                                'schema' => ['$ref' => '#/definitions/ColumnList'],
                            ],
                        ],
                        'x-appwrite' => [
                            'method' => 'createIndex',
                            'type' => '',
                            'auth' => [
                                'Project' => [],
                            ],
                        ],
                        'security' => [
                            [
                                'Project' => [],
                            ],
                        ],
                        'parameters' => [
                            [
                                'name' => 'databaseId',
                                'in' => 'path',
                                'required' => true,
                                'type' => 'string',
                                'x-example' => 'db',
                            ],
                            [
                                'name' => 'tableId',
                                'in' => 'path',
                                'required' => true,
                                'type' => 'string',
                                'x-example' => 'table',
                            ],
                            [
                                'name' => 'payload',
                                'in' => 'body',
                                'schema' => [
                                    'type' => 'object',
                                    'required' => ['key', 'type'],
                                    'properties' => [
                                        'key' => [
                                            'type' => 'string',
                                            'description' => 'Index key',
                                            'default' => null,
                                            'x-example' => 'my_index',
                                        ],
                                        'type' => [
                                            'type' => 'string',
                                            'description' => 'Index type',
                                            'default' => null,
                                            'enum' => ['key', 'fulltext'],
                                            'x-enum-name' => 'IndexType',
                                            'x-enum-keys' => ['KEY', 'FULLTEXT'],
                                            'x-example' => 'key',
                                        ],
                                        'orders' => [
                                            'type' => 'array',
                                            'description' => 'Order directions',
                                            'default' => null,
                                            'items' => [
                                                'type' => 'string',
                                                'enum' => ['asc', 'desc'],
                                                'x-enum-name' => 'OrderBy',
                                                'x-enum-keys' => ['ASC', 'DESC'],
                                            ],
                                        ],
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
                '/storage/buckets/{bucketId}/files' => [
                    'post' => [
                        'summary' => 'Create file',
                        'operationId' => 'storageCreateFile',
                        'tags' => ['storage'],
                        'description' => 'Create file',
                        'consumes' => ['multipart/form-data'],
                        'responses' => [
                            '201' => [
                                'description' => 'File created',
                                'schema' => ['$ref' => '#/definitions/AttributeList'],
                            ],
                        ],
                        'x-appwrite' => [
                            'method' => 'createFile',
                            'type' => 'upload',
                            'auth' => [
                                'Project' => [],
                            ],
                        ],
                        'security' => [
                            [
                                'Project' => [],
                            ],
                        ],
                        'parameters' => [
                            [
                                'name' => 'bucketId',
                                'in' => 'path',
                                'required' => true,
                                'type' => 'string',
                                'x-example' => 'bucket',
                            ],
                            [
                                'name' => 'fileId',
                                'in' => 'formData',
                                'required' => true,
                                'type' => 'string',
                                'x-upload-id' => true,
                                'x-example' => 'file',
                            ],
                            [
                                'name' => 'file',
                                'in' => 'formData',
                                'required' => true,
                                'type' => 'file',
                            ],
                        ],
                    ],
                ],
            ],
            'definitions' => [
                'ColumnString' => [
                    'type' => 'object',
                    'required' => ['key', 'type'],
                    'properties' => [
                        'key' => ['type' => 'string', 'description' => 'Column key'],
                        'type' => ['type' => 'string', 'description' => 'Column type'],
                    ],
                ],
                'ColumnInteger' => [
                    'type' => 'object',
                    'required' => ['key', 'type'],
                    'properties' => [
                        'key' => ['type' => 'string', 'description' => 'Column key'],
                        'type' => ['type' => 'string', 'description' => 'Column type'],
                    ],
                ],
                'AttributeString' => [
                    'type' => 'object',
                    'required' => ['key', 'type'],
                    'properties' => [
                        'key' => ['type' => 'string', 'description' => 'Attribute key'],
                        'type' => ['type' => 'string', 'description' => 'Attribute type'],
                    ],
                ],
                'AttributeBoolean' => [
                    'type' => 'object',
                    'required' => ['key', 'type'],
                    'properties' => [
                        'key' => ['type' => 'string', 'description' => 'Attribute key'],
                        'type' => ['type' => 'string', 'description' => 'Attribute type'],
                    ],
                ],
                'ColumnList' => [
                    'type' => 'object',
                    'required' => ['total', 'columns'],
                    'properties' => [
                        'total' => ['type' => 'integer', 'description' => 'Total columns'],
                        'columns' => [
                            'type' => 'array',
                            'description' => 'Columns collection',
                            'items' => [
                                'x-oneOf' => [
                                    ['$ref' => '#/definitions/ColumnString'],
                                    ['$ref' => '#/definitions/ColumnInteger'],
                                ],
                            ],
                        ],
                    ],
                ],
                'AttributeList' => [
                    'type' => 'object',
                    'required' => ['total', 'attributes'],
                    'properties' => [
                        'total' => ['type' => 'integer', 'description' => 'Total attributes'],
                        'attributes' => [
                            'type' => 'array',
                            'description' => 'Attributes collection',
                            'items' => [
                                'x-oneOf' => [
                                    ['$ref' => '#/definitions/AttributeString'],
                                    ['$ref' => '#/definitions/AttributeBoolean'],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
            'securityDefinitions' => [
                'Project' => [
                    'type' => 'apiKey',
                    'name' => 'X-Appwrite-Project',
                    'in' => 'header',
                    'description' => 'Project header',
                ],
            ],
        ], JSON_THROW_ON_ERROR);

        $sdk = new SDK(new Rust(), new Swagger2($spec));
        $sdk
            ->setName('rust')
            ->setVersion('0.0.1')
            ->setPlatform('server')
            ->setDescription('Regression test')
            ->setShortDescription('Regression test')
            ->setLogo('https://appwrite.io/logo.png')
            ->setGitUserName('appwrite')
            ->setGitRepoName('sdk-for-rust')
            ->setLicense('BSD-3-Clause')
            ->setLicenseContent('license')
            ->setChangelog('changelog')
            ->setExamples('examples')
            ->setTest('true');

        $outputDir = sys_get_temp_dir() . '/sdk-generator-rust-regression-' . uniqid();
        mkdir($outputDir, 0777, true);

        try {
            $sdk->generate($outputDir);

            $columnList = file_get_contents($outputDir . '/src/models/column_list.rs');
            $attributeList = file_get_contents($outputDir . '/src/models/attribute_list.rs');
            $tablesDb = file_get_contents($outputDir . '/src/services/tables_db.rs');
            $client = file_get_contents($outputDir . '/src/client.rs');

            $this->assertStringContainsString('pub columns: Vec<serde_json::Value>,', $columnList);
            $this->assertStringContainsString('pub attributes: Vec<serde_json::Value>,', $attributeList);
            $this->assertStringContainsString('orders: Option<Vec<crate::enums::OrderBy>>', $tablesDb);
            $this->assertStringContainsString('let url = format!("{}{}", state.config.endpoint, path);', $client);
            $this->assertStringNotContainsString('let url = if let Some(id) = &options.upload_id {', $client);
        } finally {
            $this->rmdirRecursive($outputDir);
        }
    }

    private function rmdirRecursive(string $dir): void
    {
        if (!is_dir($dir)) {
            return;
        }

        foreach (scandir($dir) as $file) {
            if ($file === '.' || $file === '..') {
                continue;
            }

            $path = $dir . '/' . $file;

            if (is_dir($path)) {
                $this->rmdirRecursive($path);
            } else {
                unlink($path);
            }
        }

        rmdir($dir);
    }
}
