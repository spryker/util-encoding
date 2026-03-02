<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Service\UtilEncoding\FormatEncoder;

use Spryker\Service\UtilEncoding\Model\JsonInterface;

class JsonFormatEncoder implements FormatEncoderInterface
{
    /**
     * @var \Spryker\Service\UtilEncoding\Model\JsonInterface
     */
    protected $json;

    public function __construct(JsonInterface $json)
    {
        $this->json = $json;
    }

    public function getFormatName(): string
    {
        return 'json';
    }

    /**
     * @param array<string, mixed> $data
     *
     * @return string|null
     */
    public function encode(array $data): ?string
    {
        return $this->json->encode($data);
    }
}
