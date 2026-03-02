<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerTest\Service\UtilEncoding;

use Codeception\Test\Unit;
use Spryker\Service\UtilEncoding\Model\Json;
use Spryker\Service\UtilEncoding\UtilEncodingService;

/**
 * Auto-generated group annotations
 *
 * @group SprykerTest
 * @group Service
 * @group UtilEncoding
 * @group UtilEncodingServiceTest
 * Add your own group annotations below this line
 */
class UtilEncodingServiceTest extends Unit
{
    /**
     * @var string
     */
    public const JSON_ENCODED_VALUE = '{"1":"one","2":"two"}';

    public const JSON_ENCODED_VALUE_PRETTY_PRINT = <<<JSON
{
    "1": "one",
    "2": "two"
}
JSON;

    /**
     * @var array
     */
    protected $jsonData = [1 => 'one', 2 => 'two'];

    /**
     * @var \Spryker\Service\UtilEncoding\UtilEncodingService
     */
    protected $utilEncodingService;

    public function setUp(): void
    {
        parent::setUp();

        $this->utilEncodingService = new UtilEncodingService();
    }

    public function testEncodeJsonWithDefaultOptions(): void
    {
        $jsonEncodeValue = $this->utilEncodingService->encodeJson($this->jsonData);

        $this->assertSame(static::JSON_ENCODED_VALUE, $jsonEncodeValue);
    }

    public function testEncodeArrayEmpty(): void
    {
        $jsonEncodeValue = $this->utilEncodingService->encodeJson([]);

        $this->assertSame('[]', $jsonEncodeValue);
    }

    public function testEncodeString(): void
    {
        $jsonEncodeValue = $this->utilEncodingService->encodeJson('A string!');

        $this->assertSame('"A string!"', $jsonEncodeValue);
    }

    public function testEncodeBooleanAndNull(): void
    {
        $jsonEncodeValue = $this->utilEncodingService->encodeJson(true);

        $this->assertSame('true', $jsonEncodeValue);

        $jsonEncodeValue = $this->utilEncodingService->encodeJson(false);

        $this->assertSame('false', $jsonEncodeValue);

        $jsonEncodeValue = $this->utilEncodingService->encodeJson(null);

        $this->assertSame('null', $jsonEncodeValue);
    }

    public function testEncodeInvalid(): void
    {
        $jsonEncodeValue = $this->utilEncodingService->encodeJson(['x' => ['y' => 'z']], JSON_NUMERIC_CHECK, 1);

        $this->assertNull($jsonEncodeValue);
    }

    public function testEncodeJsonWithPrettyPrintIncluded(): void
    {
        $jsonEncodeValue = $this->utilEncodingService->encodeJson($this->jsonData, Json::DEFAULT_OPTIONS | JSON_PRETTY_PRINT);

        $this->assertSame(static::JSON_ENCODED_VALUE_PRETTY_PRINT, $jsonEncodeValue);
    }

    public function testDecodeJsonShouldReturnAssocArray(): void
    {
        $jsonDecodeValue = $this->utilEncodingService->decodeJson(static::JSON_ENCODED_VALUE, true);

        $this->assertEquals($this->jsonData, $jsonDecodeValue);
    }

    /**
     * Deprecated: `false` is deprecated, always use `true` for array return.
     *
     * @return void
     */
    public function testDecodeJsonWhenAssocFlagIsOffShouldReturnStdObject(): void
    {
        $jsonDecodeValue = $this->utilEncodingService->decodeJson(static::JSON_ENCODED_VALUE);

        $this->assertEquals((object)$this->jsonData, $jsonDecodeValue);
    }

    public function testDecodeArrayEmpty(): void
    {
        $jsonEncodeValue = $this->utilEncodingService->decodeJson('[]');

        $this->assertSame([], $jsonEncodeValue);
    }

    public function testDecodeStringEmpty(): void
    {
        $jsonEncodeValue = $this->utilEncodingService->decodeJson('');

        $this->assertNull($jsonEncodeValue);
    }

    /**
     * Deprecated: Non array return is deprecated.
     *
     * @return void
     */
    public function testDecodeString(): void
    {
        $jsonEncodeValue = $this->utilEncodingService->decodeJson('"A string!"');

        $this->assertSame('A string!', $jsonEncodeValue);
    }

    /**
     * Deprecated: Non array return is deprecated.
     *
     * @return void
     */
    public function testDecodeBoolean(): void
    {
        $jsonEncodeValue = $this->utilEncodingService->decodeJson('true');

        $this->assertTrue($jsonEncodeValue);

        $jsonEncodeValue = $this->utilEncodingService->decodeJson('false');

        $this->assertFalse($jsonEncodeValue);
    }

    public function testDecodeNull(): void
    {
        $jsonEncodeValue = $this->utilEncodingService->decodeJson('null');

        $this->assertNull($jsonEncodeValue);
    }
}
