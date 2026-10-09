<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Service\UtilEncoding\Model;

use Spryker\Service\UtilEncoding\Exception\XmlReadException;
use Symfony\Component\Config\Util\Exception\InvalidXmlException;
use Symfony\Component\Config\Util\Exception\XmlParsingException;
use Symfony\Component\Config\Util\XmlUtils;
use Throwable;
use XMLReader;

/**
 * Reads an XML file into the nested array shape that `Laminas\Config\Reader\Xml` produced, so that
 * callers keep the exact structure they were written against after `laminas/laminas-config` (which
 * is abandoned) was dropped.
 *
 * The shape rules this reproduces, all of them relied on somewhere in the codebase:
 *
 * - attributes become plain string keys alongside the element's children;
 * - an element carrying attributes AND text stores the text under `_`
 *   (`\Spryker\Zed\Development\Business\ArchitectureSniffer\ArchitectureSniffer` reads PHPMD
 *   violation messages from exactly that key);
 * - a repeated element name becomes a numerically indexed list, a single one stays associative
 *   (callers branch on `isset($definition['transfer'][0])` to tell the two apart);
 * - values are NEVER coerced - `visible="0"` stays the string `'0'`;
 * - `<foo/>` yields `[]` while `<foo></foo>` yields `''`.
 *
 * That last rule is why the walk uses XMLReader rather than a DOM tree: DOM cannot distinguish a
 * self-closing element from an empty pair, and Symfony's own `XmlUtils::convertDomElementToArray()`
 * additionally coerces scalars and discards the text of an attribute-carrying element. XmlUtils is
 * still used to parse the file first, for its libxml error handling.
 */
class Xml implements XmlInterface
{
    protected const string TEXT_KEY = '_';

    /**
     * @var list<int>
     */
    protected const array TEXT_NODE_TYPES = [
        XMLReader::TEXT,
        XMLReader::CDATA,
        XMLReader::WHITESPACE,
        XMLReader::SIGNIFICANT_WHITESPACE,
    ];

    /**
     * @param string $xmlFilePath
     *
     * @throws \Spryker\Service\UtilEncoding\Exception\XmlReadException
     *
     * @return array<mixed>
     */
    public function readFile(string $xmlFilePath): array
    {
        $this->assertParsable($xmlFilePath);

        $reader = new XMLReader();
        if ($reader->open($xmlFilePath, null, LIBXML_XINCLUDE) === false) {
            throw new XmlReadException(sprintf('Cannot open XML file "%s".', $xmlFilePath));
        }

        try {
            $content = $this->readNextElement($reader);
        } finally {
            $reader->close();
        }

        return is_array($content) ? $content : [];
    }

    /**
     * @param string $xmlContent
     *
     * @throws \Spryker\Service\UtilEncoding\Exception\XmlReadException
     *
     * @return array<mixed>
     */
    public function readString(string $xmlContent): array
    {
        $this->assertStringParsable($xmlContent);

        $reader = new XMLReader();
        if ($reader->XML($xmlContent, null, LIBXML_XINCLUDE) === false) {
            throw new XmlReadException('Cannot read the given XML string.');
        }

        try {
            $content = $this->readNextElement($reader);
        } finally {
            $reader->close();
        }

        return is_array($content) ? $content : [];
    }

    /**
     * @param string $xmlContent
     *
     * @throws \Spryker\Service\UtilEncoding\Exception\XmlReadException
     *
     * @return void
     */
    protected function assertStringParsable(string $xmlContent): void
    {
        try {
            XmlUtils::parse($xmlContent);
        } catch (InvalidXmlException | XmlParsingException $exception) {
            throw new XmlReadException(
                sprintf('The given XML string is not well formed: %s', $exception->getMessage()),
                0,
                $exception,
            );
        } catch (Throwable $exception) {
            throw new XmlReadException(
                sprintf('The given XML string could not be read: %s', $exception->getMessage()),
                0,
                $exception,
            );
        }
    }

    /**
     * Parsing through Symfony first turns a malformed document into a typed exception instead of a
     * bare libxml warning; XMLReader alone would walk a broken file and silently return less.
     *
     * @param string $xmlFilePath
     *
     * @throws \Spryker\Service\UtilEncoding\Exception\XmlReadException
     *
     * @return void
     */
    protected function assertParsable(string $xmlFilePath): void
    {
        try {
            XmlUtils::loadFile($xmlFilePath);
        } catch (InvalidXmlException | XmlParsingException $exception) {
            throw new XmlReadException(
                sprintf('XML file "%s" is not well formed: %s', $xmlFilePath, $exception->getMessage()),
                0,
                $exception,
            );
        } catch (Throwable $exception) {
            throw new XmlReadException(
                sprintf('XML file "%s" could not be read: %s', $xmlFilePath, $exception->getMessage()),
                0,
                $exception,
            );
        }
    }

    /**
     * @param \XMLReader $reader
     *
     * @return array<mixed>|string
     */
    protected function readNextElement(XMLReader $reader)
    {
        $children = [];
        $text = '';

        while ($reader->read()) {
            if ($reader->nodeType === XMLReader::ELEMENT) {
                if ($reader->depth === 0) {
                    return $this->readNextElement($reader);
                }

                $children = $this->addChild($reader, $children);

                continue;
            }

            if ($reader->nodeType === XMLReader::END_ELEMENT) {
                break;
            }

            if (in_array($reader->nodeType, static::TEXT_NODE_TYPES, true)) {
                $text .= $reader->value;
            }
        }

        return $children ?: $text;
    }

    /**
     * @param \XMLReader $reader
     * @param array<mixed> $children
     *
     * @return array<mixed>
     */
    protected function addChild(XMLReader $reader, array $children): array
    {
        $attributes = $this->readAttributes($reader);
        $name = $reader->name;
        $child = $reader->isEmptyElement ? [] : $this->readNextElement($reader);

        if ($attributes !== []) {
            if (is_string($child)) {
                $child = [static::TEXT_KEY => $child];
            }

            $child = array_merge($child, $attributes);
        }

        if (!isset($children[$name])) {
            $children[$name] = $child;

            return $children;
        }

        if (!is_array($children[$name]) || !array_key_exists(0, $children[$name])) {
            $children[$name] = [$children[$name]];
        }

        $children[$name][] = $child;

        return $children;
    }

    /**
     * @param \XMLReader $reader
     *
     * @return array<string, string>
     */
    protected function readAttributes(XMLReader $reader): array
    {
        if (!$reader->hasAttributes) {
            return [];
        }

        $attributes = [];
        $reader->moveToFirstAttribute();

        do {
            $attributes[$reader->localName] = $reader->value;
        } while ($reader->moveToNextAttribute());

        $reader->moveToElement();

        return $attributes;
    }
}
