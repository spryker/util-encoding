<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Service\UtilEncoding\Model;

interface XmlInterface
{
    /**
     * @param string $xmlFilePath
     *
     * @throws \Spryker\Service\UtilEncoding\Exception\XmlReadException
     *
     * @return array<mixed>
     */
    public function readFile(string $xmlFilePath): array;

    /**
     * @param string $xmlContent
     *
     * @throws \Spryker\Service\UtilEncoding\Exception\XmlReadException
     *
     * @return array<mixed>
     */
    public function readString(string $xmlContent): array;
}
