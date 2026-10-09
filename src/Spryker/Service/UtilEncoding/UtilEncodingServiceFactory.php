<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Service\UtilEncoding;

use Spryker\Service\Kernel\AbstractServiceFactory;
use Spryker\Service\UtilEncoding\Decoder\Decoder;
use Spryker\Service\UtilEncoding\Decoder\DecoderInterface;
use Spryker\Service\UtilEncoding\Encoder\Encoder;
use Spryker\Service\UtilEncoding\Encoder\EncoderInterface;
use Spryker\Service\UtilEncoding\FormatDecoder\FormatDecoderInterface;
use Spryker\Service\UtilEncoding\FormatDecoder\JsonFormatDecoder;
use Spryker\Service\UtilEncoding\FormatDecoder\QueryStringFormatDecoder;
use Spryker\Service\UtilEncoding\FormatEncoder\FormatEncoderInterface;
use Spryker\Service\UtilEncoding\FormatEncoder\JsonFormatEncoder;
use Spryker\Service\UtilEncoding\Model\Json;
use Spryker\Service\UtilEncoding\Model\Xml;
use Spryker\Service\UtilEncoding\Model\XmlInterface;

class UtilEncodingServiceFactory extends AbstractServiceFactory
{
    /**
     * @return \Spryker\Service\UtilEncoding\Model\JsonInterface
     */
    public function createJsonEncoder()
    {
        return new Json();
    }

    public function createEncoder(): EncoderInterface
    {
        return new Encoder(
            $this->getFormatEncoders(),
        );
    }

    public function createDecoder(): DecoderInterface
    {
        return new Decoder(
            $this->getFormatDecoders(),
        );
    }

    /**
     * @return array<\Spryker\Service\UtilEncoding\FormatEncoder\FormatEncoderInterface>
     */
    public function getFormatEncoders(): array
    {
        return [
            $this->createJsonFormatEncoder(),
        ];
    }

    /**
     * @return array<\Spryker\Service\UtilEncoding\FormatDecoder\FormatDecoderInterface>
     */
    public function getFormatDecoders(): array
    {
        return [
            $this->createJsonFormatDecoder(),
            $this->createQueryStringFormatDecoder(),
        ];
    }

    public function createJsonFormatEncoder(): FormatEncoderInterface
    {
        return new JsonFormatEncoder(
            $this->createJsonEncoder(),
        );
    }

    public function createJsonFormatDecoder(): FormatDecoderInterface
    {
        return new JsonFormatDecoder(
            $this->createJsonEncoder(),
        );
    }

    public function createQueryStringFormatDecoder(): FormatDecoderInterface
    {
        return new QueryStringFormatDecoder();
    }

    public function createXmlReader(): XmlInterface
    {
        return new Xml();
    }
}
