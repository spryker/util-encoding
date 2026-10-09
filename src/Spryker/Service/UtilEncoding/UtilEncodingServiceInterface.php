<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Service\UtilEncoding;

/**
 * @method \Spryker\Service\UtilEncoding\UtilEncodingServiceFactory getFactory()
 */
interface UtilEncodingServiceInterface
{
    /**
     * Specification:
     * - Encodes given array value to JSON string.
     *
     * @api
     *
     * @param array<string, mixed> $value
     * @param int|null $options
     * @param int|null $depth
     *
     * @return string|null
     */
    public function encodeJson($value, $options = null, $depth = null);

    /**
     * Specification:
     * - Decodes given JSON string, returns array or stdObject. Array usage is preferred.
     *
     * @api
     *
     * @param string $jsonValue
     * @param bool $assoc Deprecated: `false` is deprecated, always use `true` for array return.
     * @param int|null $depth
     * @param int|null $options
     *
     * @return object|array<mixed>|null
     */
    public function decodeJson($jsonValue, $assoc = false, $depth = null, $options = null);

    /**
     * Specification:
     * - Encodes the given array to the given format.
     * - Throws an exception if format is not supported.
     *
     * @api
     *
     * @param array<string, mixed> $data
     * @param string $format
     *
     * @throws \Spryker\Service\UtilEncoding\Exception\FormatNotSupportedException
     *
     * @return string|null
     */
    public function encodeToFormat(array $data, string $format): ?string;

    /**
     * Specification:
     * - Decodes the given string from the given format.
     * - Throws an exception if format is not supported.
     * - Returns null if decoding process was failed.
     *
     * @api
     *
     * @param string $data
     * @param string $format
     *
     * @throws \Spryker\Service\UtilEncoding\Exception\FormatNotSupportedException
     *
     * @return array|null
     */
    public function decodeFromFormat(string $data, string $format): ?array;

    /**
     * Specification:
     * - Reads an XML file into a nested array.
     * - Element attributes become string keys next to the element's children.
     * - Text of an element that also carries attributes is stored under the `_` key.
     * - A repeated element name becomes a numerically indexed list; a single occurrence stays
     *   associative.
     * - Values are never type-coerced; every leaf is a string.
     * - `<foo/>` reads as an empty array, `<foo></foo>` as an empty string.
     * - Throws a `XmlReadException` when the file is missing or not well formed.
     *
     * @api
     *
     * @param string $xmlFilePath
     *
     * @throws \Spryker\Service\UtilEncoding\Exception\XmlReadException
     *
     * @return array<mixed>
     */
    public function convertXmlFileToArray(string $xmlFilePath): array;

    /**
     * Specification:
     * - Reads an XML string into a nested array.
     * - Applies the same shape rules as {@link \Spryker\Service\UtilEncoding\UtilEncodingServiceInterface::convertXmlFileToArray()}.
     * - Throws a `XmlReadException` when the string is not well formed.
     *
     * @api
     *
     * @param string $xmlContent
     *
     * @throws \Spryker\Service\UtilEncoding\Exception\XmlReadException
     *
     * @return array<mixed>
     */
    public function convertXmlStringToArray(string $xmlContent): array;
}
