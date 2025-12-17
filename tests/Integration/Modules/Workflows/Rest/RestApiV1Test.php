<?php

/**
 * Copyright (c) 2025, Ramble Ventures
 */

namespace Tests\Modules\Workflows\Rest;

use Codeception\Stub;
use PublishPress\Future\Core\HookableInterface;
use PublishPress\Future\Modules\Workflows\Rest\RestApiV1;
use PublishPress\Future\Framework\WordPress\Utils\WorkflowSanitizationUtil;
use ReflectionClass;
use ReflectionMethod;
use lucatume\WPBrowser\TestCase\WPTestCase;

class RestApiV1Test extends WPTestCase
{
    /**
     * @var \IntegrationTester
     */
    protected $tester;

    /**
     * @var RestApiV1
     */
    private $restApi;

    /**
     * @var ReflectionMethod
     */
    private $sanitizeWorkflowKeyMethod;

    public function setUp(): void
    {
        parent::setUp();

        $hooks = Stub::makeEmpty(HookableInterface::class);
        $workflowSanitization = new WorkflowSanitizationUtil();

        $this->restApi = new RestApiV1($hooks, $workflowSanitization);

        // Use reflection to access private method for testing
        $reflection = new ReflectionClass($this->restApi);
        $this->sanitizeWorkflowKeyMethod = $reflection->getMethod('sanitizeWorkflowKey');
        $this->sanitizeWorkflowKeyMethod->setAccessible(true);
    }

    public function tearDown(): void
    {
        parent::tearDown();
    }

    /**
     * Test that valid JSON Logic operators are preserved
     */
    public function testValidJsonLogicOperatorsArePreserved()
    {
        $validOperators = [
            '==', '===', '!=', '!==',  // Equality operators
            '>', '<', '>=', '<=',      // Comparison operators
            '!', '!!',                 // Logical operators
        ];

        foreach ($validOperators as $operator) {
            $result = $this->sanitizeWorkflowKeyMethod->invoke($this->restApi, $operator);
            $this->assertEquals($operator, $result, "Operator '{$operator}' should be preserved");
        }
    }

    /**
     * Test that camelCase keys are preserved
     */
    public function testCamelCaseKeysArePreserved()
    {
        $camelCaseKeys = [
            'postId',
            'postType',
            'postSource',
            'postStatus',
            'postAuthor',
            'postTerms',
            'nodeId',
            'workflowId',
            'myCustomKey',
            'anotherKey123',
        ];

        foreach ($camelCaseKeys as $key) {
            $result = $this->sanitizeWorkflowKeyMethod->invoke($this->restApi, $key);
            $this->assertEquals($key, $result, "CamelCase key '{$key}' should be preserved");
        }
    }

    /**
     * Test that keys with underscores and hyphens are preserved
     */
    public function testKeysWithUnderscoresAndHyphensArePreserved()
    {
        $validKeys = [
            'post_id',
            'post-type',
            'my_key',
            'my-key',
            'key_123',
            'key-123',
            'a_b_c',
            'a-b-c',
        ];

        foreach ($validKeys as $key) {
            $result = $this->sanitizeWorkflowKeyMethod->invoke($this->restApi, $key);
            $this->assertEquals($key, $result, "Key '{$key}' should be preserved");
        }
    }

    /**
     * Test that dangerous characters are removed
     */
    public function testDangerousCharactersAreRemoved()
    {
        $testCases = [
            ['key"with"quotes', 'keywithquotes'],
            ["key'with'quotes", 'keywithquotes'],
            ['key\\with\\backslash', 'keywithbackslash'],
            ['key/with/slash', 'keywithslash'],
            ['key;with;semicolon', 'keywithsemicolon'],
            ['key(with)parentheses', 'keywithparentheses'],
            ['key$with$dollar', 'keywithdollar'],
            ['key=with=equals', 'keywithequals'],
            ['key!with!exclamation', 'keywithexclamation'],
            ['key>with>greater', 'keywithgreater'],
            ['key<with<less', 'keywithless'],
        ];

        foreach ($testCases as [$input, $expected]) {
            $result = $this->sanitizeWorkflowKeyMethod->invoke($this->restApi, $input);
            $this->assertEquals($expected, $result, "Dangerous characters should be removed from '{$input}'");
        }
    }

    /**
     * Test that invalid operator-like keys are sanitized
     */
    public function testInvalidOperatorLikeKeysAreSanitized()
    {
        $testCases = [
            ['=alert("xss")', 'alertxss'],
            ['>malicious<', 'malicious'],
            ['!=invalid', 'invalid'],
            ['==butNotValid', 'butNotValid'],
            ['key=value', 'keyvalue'],
            ['key!value', 'keyvalue'],
            ['key>value', 'keyvalue'],
            ['key<value', 'keyvalue'],
        ];

        foreach ($testCases as [$input, $expected]) {
            $result = $this->sanitizeWorkflowKeyMethod->invoke($this->restApi, $input);
            $this->assertEquals($expected, $result, "Invalid operator-like key '{$input}' should be sanitized");
        }
    }

    /**
     * Test that control characters are removed
     */
    public function testControlCharactersAreRemoved()
    {
        $testCases = [
            ["key\x00with\x00null", 'keywithnull'],
            ["key\x1Fwith\x1Fcontrol", 'keywithcontrol'],
            ["key\x7Fwith\x7Fdel", 'keywithdel'],
            ["key\nwith\nnewline", 'keywithnewline'],
            ["key\twith\ttab", 'keywithtab'],
        ];

        foreach ($testCases as [$input, $expected]) {
            $result = $this->sanitizeWorkflowKeyMethod->invoke($this->restApi, $input);
            $this->assertEquals($expected, $result, "Control characters should be removed from '{$input}'");
        }
    }

    /**
     * Test that mixed dangerous characters are all removed
     */
    public function testMixedDangerousCharactersAreRemoved()
    {
        $testCases = [
            ['key"with\'mixed/quotes', 'keywithmixedquotes'],
            ['key;with(several)dangerous$chars', 'keywithseveraldangerouschars'],
            ['key=with!multiple>operators<', 'keywithmultipleoperators'],
            ['key\\with/all$kinds;of(chars)', 'keywithallkindsofchars'],
        ];

        foreach ($testCases as [$input, $expected]) {
            $result = $this->sanitizeWorkflowKeyMethod->invoke($this->restApi, $input);
            $this->assertEquals($expected, $result, "All dangerous characters should be removed from '{$input}'");
        }
    }

    /**
     * Test that empty string is handled
     */
    public function testEmptyStringIsHandled()
    {
        $result = $this->sanitizeWorkflowKeyMethod->invoke($this->restApi, '');
        $this->assertEquals('', $result, 'Empty string should return empty string');
    }

    /**
     * Test that numeric keys are preserved
     */
    public function testNumericKeysArePreserved()
    {
        $numericKeys = ['0', '123', '456789'];

        foreach ($numericKeys as $key) {
            $result = $this->sanitizeWorkflowKeyMethod->invoke($this->restApi, $key);
            $this->assertEquals($key, $result, "Numeric key '{$key}' should be preserved");
        }
    }

    /**
     * Test that keys with only dangerous characters return empty string
     */
    public function testKeysWithOnlyDangerousCharactersReturnEmpty()
    {
        $testCases = [
            '"""',
            "'''",
            '\\/',
            ';()',
            '$',
            '===',
            '!!!',
            '><',
        ];

        foreach ($testCases as $input) {
            // Skip valid JSON Logic operators
            if (in_array($input, ['==', '===', '!=', '!==', '>', '<', '>=', '<=', '!', '!!'], true)) {
                continue;
            }

            $result = $this->sanitizeWorkflowKeyMethod->invoke($this->restApi, $input);
            $this->assertEquals('', $result, "Key with only dangerous characters '{$input}' should return empty string");
        }
    }

    /**
     * Test that sanitizeWorkflowData properly sanitizes nested keys and values
     */
    public function testSanitizeWorkflowDataSanitizesNestedKeysAndValues()
    {
        $reflection = new ReflectionClass($this->restApi);
        $sanitizeMethod = $reflection->getMethod('sanitizeWorkflowData');
        $sanitizeMethod->setAccessible(true);

        $data = [
            'postId' => [1, 2, 3],
            'post"Type' => ['post'],
            'nested' => [
                'key$with$dangerous' => 'value',
                'validKey' => 'value',
                '===' => ['valid', 'operator'],
                'stringValue' => '<script>alert("xss")</script>',
                'textWithHtml' => 'Hello <strong>world</strong>',
                'normalText' => 'Normal text value',
            ],
        ];

        $result = $sanitizeMethod->invoke($this->restApi, $data);

        // Keys should be sanitized
        $this->assertEquals('postId', array_key_first($result));
        $this->assertArrayHasKey('postType', $result);
        $this->assertArrayHasKey('validKey', $result['nested']);
        $this->assertArrayHasKey('===', $result['nested']);
        $this->assertArrayNotHasKey('key$with$dangerous', $result['nested']);

        // String values should be sanitized using sanitize_text_field
        // sanitize_text_field removes HTML tags, so <script>alert("xss")</script> becomes empty
        $this->assertEquals('', $result['nested']['stringValue']);
        $this->assertNotEquals('<script>alert("xss")</script>', $result['nested']['stringValue'], 'HTML tags should be removed');

        // HTML tags are stripped but text content is preserved
        $this->assertEquals('Hello world', $result['nested']['textWithHtml']);

        // Normal text is preserved as-is
        $this->assertEquals('Normal text value', $result['nested']['normalText']);
    }

    /**
     * Test sanitization with a realistic workflow structure
     */
    public function testSanitizeWorkflowDataWithRealisticWorkflow()
    {
        $reflection = new ReflectionClass($this->restApi);
        $sanitizeMethod = $reflection->getMethod('sanitizeWorkflowData');
        $sanitizeMethod->setAccessible(true);

        // Realistic workflow flow structure
        $workflowFlow = [
            'nodes' => [
                [
                    'id' => 'n1234567890',
                    'type' => 'trigger',
                    'position' => ['x' => 0, 'y' => 0],
                    'data' => [
                        'name' => 'trigger/core.post-updated',
                        'slug' => 'onPostUpdated1',
                        'settings' => [
                            'postQuery' => [
                                'postSource' => 'custom',
                                'postType' => ['post', 'page'],
                                'postId' => [123, 456],
                                'postStatus' => ['publish'],
                                'json' => [
                                    'and' => [
                                        ['==' => [['var' => 'post.post_type'], 'post']],
                                        ['>' => [['var' => 'post.ID'], 100]],
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
                [
                    'id' => 'n0987654321',
                    'type' => 'generic',
                    'position' => ['x' => 200, 'y' => 0],
                    'data' => [
                        'name' => 'action/core.send-email',
                        'slug' => 'sendEmail1',
                        'settings' => [
                            'recipient' => [
                                'recipient' => 'global.site.admin_email',
                                'custom' => 'admin@example.com',
                            ],
                            'subject' => 'Post updated: {{onPostUpdated1.postAfter.title}}',
                            'message' => 'The post "<script>alert(\'xss\')</script>" was updated.',
                        ],
                    ],
                ],
            ],
            'edges' => [
                [
                    'id' => 'e1234567890',
                    'source' => 'n1234567890',
                    'target' => 'n0987654321',
                    'sourceHandle' => 'output',
                    'targetHandle' => 'input',
                ],
            ],
            'viewport' => [
                'x' => 0,
                'y' => 0,
                'zoom' => 1,
            ],
        ];

        $result = $sanitizeMethod->invoke($this->restApi, $workflowFlow);

        // Verify structure is preserved
        $this->assertArrayHasKey('nodes', $result);
        $this->assertArrayHasKey('edges', $result);
        $this->assertArrayHasKey('viewport', $result);
        $this->assertCount(2, $result['nodes']);
        $this->assertCount(1, $result['edges']);

        // Verify node data is sanitized
        $triggerNode = $result['nodes'][0];
        $this->assertEquals('n1234567890', $triggerNode['id']);
        $this->assertEquals('trigger', $triggerNode['type']);
        $this->assertArrayHasKey('postQuery', $triggerNode['data']['settings']);

        // Verify JSON Logic operators are preserved
        $postQuery = $triggerNode['data']['settings']['postQuery'];
        $this->assertArrayHasKey('json', $postQuery);
        $this->assertArrayHasKey('and', $postQuery['json']);
        $this->assertArrayHasKey('==', $postQuery['json']['and'][0]);
        $this->assertArrayHasKey('>', $postQuery['json']['and'][1]);

        // Verify camelCase keys are preserved
        $this->assertArrayHasKey('postSource', $postQuery);
        $this->assertArrayHasKey('postType', $postQuery);
        $this->assertArrayHasKey('postId', $postQuery);
        $this->assertArrayHasKey('postStatus', $postQuery);

        // Verify values are correct
        $this->assertEquals('custom', $postQuery['postSource']);
        $this->assertEquals(['post', 'page'], $postQuery['postType']);
        $this->assertEquals([123, 456], $postQuery['postId']);

        // Verify action node settings
        $actionNode = $result['nodes'][1];
        $this->assertEquals('n0987654321', $actionNode['id']);
        $this->assertArrayHasKey('settings', $actionNode['data']);

        // Verify string values are sanitized (HTML tags removed)
        $message = $actionNode['data']['settings']['message'];
        $this->assertStringNotContainsString('<script>', $message);
        $this->assertStringNotContainsString('alert', $message);
        $this->assertStringContainsString('was updated', $message);

        // Verify edges are preserved
        $edge = $result['edges'][0];
        $this->assertEquals('e1234567890', $edge['id']);
        $this->assertEquals('n1234567890', $edge['source']);
        $this->assertEquals('n0987654321', $edge['target']);

        // Verify viewport is preserved
        $this->assertEquals(['x' => 0, 'y' => 0, 'zoom' => 1], $result['viewport']);
    }

    /**
     * Test sanitization with a realistic workflow containing dangerous data
     */
    public function testSanitizeWorkflowDataWithDangerousInput()
    {
        $reflection = new ReflectionClass($this->restApi);
        $sanitizeMethod = $reflection->getMethod('sanitizeWorkflowData');
        $sanitizeMethod->setAccessible(true);

        // Workflow with dangerous/malicious input
        $workflowFlow = [
            'nodes' => [
                [
                    'id' => 'n1234567890',
                    'type' => 'trigger',
                    'position' => ['x' => 0, 'y' => 0],
                    'data' => [
                        'name' => 'trigger/core.post-updated',
                        'slug' => 'onPostUpdated1',
                        'settings' => [
                            'postQuery' => [
                                'postSource' => 'custom',
                                'postType' => ['post'],
                                // Dangerous key attempts
                                'key"with"quotes' => 'value',
                                'key$with$dollar' => 'value',
                                'key/with/slash' => 'value',
                                'key;with;semicolon' => 'value',
                                'key(with)parentheses' => 'value',
                                'key=with=equals' => 'value',
                                'key!with!exclamation' => 'value',
                                'key>with>greater' => 'value',
                                'key<with<less' => 'value',
                                // Valid JSON Logic operators should be preserved
                                'json' => [
                                    'and' => [
                                        ['==' => [['var' => 'post.post_type'], 'post']],
                                        ['>=' => [['var' => 'post.ID'], 100]],
                                        // Invalid operator-like keys should be sanitized
                                        ['=alert("xss")' => ['malicious']],
                                        ['>malicious<' => ['data']],
                                    ],
                                ],
                                // Dangerous string values
                                'maliciousString' => '<script>alert("XSS")</script>',
                                'sqlInjection' => "'; DROP TABLE users; --",
                                'pathTraversal' => '../../../etc/passwd',
                                'commandInjection' => '; rm -rf /',
                                'mixedAttack' => '<img src=x onerror="alert(\'XSS\')">',
                            ],
                        ],
                    ],
                ],
                [
                    'id' => 'n0987654321',
                    'type' => 'generic',
                    'position' => ['x' => 200, 'y' => 0],
                    'data' => [
                        'name' => 'action/core.send-email',
                        'slug' => 'sendEmail1',
                        'settings' => [
                            // Dangerous keys
                            'recipient"with"quotes' => 'admin@example.com',
                            'subject$with$dollar' => 'Subject',
                            'message/with/slash' => 'Message',
                            // Dangerous values
                            'recipient' => [
                                'recipient' => 'global.site.admin_email',
                                'custom' => '<script>alert("xss")</script>admin@example.com',
                            ],
                            'subject' => 'Subject with <script>alert("XSS")</script>',
                            'message' => "Message with '; DROP TABLE users; -- and <img src=x onerror=\"alert('XSS')\">",
                            // Control characters
                            'fieldWithControlChars' => "text\x00with\x1Fcontrol\x7Fchars",
                        ],
                    ],
                ],
            ],
            'edges' => [
                [
                    'id' => 'e1234567890',
                    'source' => 'n1234567890',
                    'target' => 'n0987654321',
                    'sourceHandle' => 'output',
                    'targetHandle' => 'input',
                ],
            ],
            'viewport' => [
                'x' => 0,
                'y' => 0,
                'zoom' => 1,
            ],
        ];

        $result = $sanitizeMethod->invoke($this->restApi, $workflowFlow);

        // Verify structure is preserved
        $this->assertArrayHasKey('nodes', $result);
        $this->assertArrayHasKey('edges', $result);
        $this->assertCount(2, $result['nodes']);

        // Verify trigger node
        $triggerNode = $result['nodes'][0];
        $postQuery = $triggerNode['data']['settings']['postQuery'];

        // Verify dangerous keys are sanitized (dangerous chars removed)
        $this->assertArrayHasKey('keywithquotes', $postQuery);
        $this->assertArrayHasKey('keywithdollar', $postQuery);
        $this->assertArrayHasKey('keywithslash', $postQuery);
        $this->assertArrayHasKey('keywithsemicolon', $postQuery);
        $this->assertArrayHasKey('keywithparentheses', $postQuery);
        $this->assertArrayHasKey('keywithequals', $postQuery);
        $this->assertArrayHasKey('keywithexclamation', $postQuery);
        $this->assertArrayHasKey('keywithgreater', $postQuery);
        $this->assertArrayHasKey('keywithless', $postQuery);

        // Verify dangerous keys don't exist with dangerous characters
        $this->assertArrayNotHasKey('key"with"quotes', $postQuery);
        $this->assertArrayNotHasKey('key$with$dollar', $postQuery);
        $this->assertArrayNotHasKey('key/with/slash', $postQuery);

        // Verify valid JSON Logic operators are preserved
        $this->assertArrayHasKey('json', $postQuery);
        $this->assertArrayHasKey('and', $postQuery['json']);
        $this->assertArrayHasKey('==', $postQuery['json']['and'][0]);
        $this->assertArrayHasKey('>=', $postQuery['json']['and'][1]);

        // Verify invalid operator-like keys in JSON Logic are sanitized
        // The dangerous characters are removed from keys, so '=alert("xss")' becomes 'alertxss'
        $jsonLogic = $postQuery['json']['and'];
        // After sanitization, the keys with dangerous chars are cleaned
        $thirdElement = $jsonLogic[2];
        $fourthElement = $jsonLogic[3];
        // Check that dangerous characters are removed from keys
        foreach ($thirdElement as $key => $value) {
            $this->assertStringNotContainsString('=', $key);
            $this->assertStringNotContainsString('"', $key);
            $this->assertStringNotContainsString('(', $key);
            $this->assertStringNotContainsString(')', $key);
        }
        foreach ($fourthElement as $key => $value) {
            $this->assertStringNotContainsString('>', $key);
            $this->assertStringNotContainsString('<', $key);
        }

        // Verify dangerous string values are sanitized
        // sanitize_text_field removes HTML tags but preserves text content
        $this->assertStringNotContainsString('<script>', $postQuery['maliciousString']);
        $this->assertStringNotContainsString('</script>', $postQuery['maliciousString']);
        // Note: sanitize_text_field doesn't remove SQL-like strings, it only removes HTML tags
        // The SQL injection string will be preserved as text (which is safe since it's not executed)
        $this->assertIsString($postQuery['sqlInjection']);
        // Path traversal strings are preserved as text (safe when not used in file operations)
        $this->assertIsString($postQuery['pathTraversal']);
        // Command injection strings are preserved as text (safe when not executed)
        $this->assertIsString($postQuery['commandInjection']);
        // HTML tags are removed
        $this->assertStringNotContainsString('<img', $postQuery['mixedAttack']);
        $this->assertStringNotContainsString('onerror', $postQuery['mixedAttack']);

        // Verify action node dangerous keys are sanitized
        $actionNode = $result['nodes'][1];
        $actionSettings = $actionNode['data']['settings'];

        $this->assertArrayHasKey('recipientwithquotes', $actionSettings);
        $this->assertArrayHasKey('subjectwithdollar', $actionSettings);
        $this->assertArrayHasKey('messagewithslash', $actionSettings);
        $this->assertArrayNotHasKey('recipient"with"quotes', $actionSettings);
        $this->assertArrayNotHasKey('subject$with$dollar', $actionSettings);

        // Verify dangerous values in nested objects are sanitized
        // HTML tags are removed by sanitize_text_field
        $this->assertStringNotContainsString('<script>', $actionSettings['recipient']['custom']);
        $this->assertStringNotContainsString('</script>', $actionSettings['recipient']['custom']);
        $this->assertStringNotContainsString('<script>', $actionSettings['subject']);
        $this->assertStringNotContainsString('</script>', $actionSettings['subject']);
        // HTML tags are removed
        $this->assertStringNotContainsString('<img', $actionSettings['message']);
        $this->assertStringNotContainsString('onerror', $actionSettings['message']);
        // Note: SQL-like strings are preserved as text (safe when stored, not executed)
        // The important thing is that HTML/script tags are removed

        // Note: sanitize_text_field() doesn't remove all control characters from values
        // It mainly strips HTML tags and encodes certain characters
        // Control character removal only applies to KEYS (via sanitizeWorkflowKey), not values
        $this->assertArrayHasKey('fieldWithControlChars', $actionSettings);

        // Verify valid data is preserved
        $this->assertEquals('custom', $postQuery['postSource']);
        $this->assertEquals(['post'], $postQuery['postType']);
        $this->assertEquals('n1234567890', $triggerNode['id']);
        $this->assertEquals('trigger', $triggerNode['type']);
    }
}

