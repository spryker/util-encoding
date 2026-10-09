<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerTest\Service\UtilEncoding;

use Codeception\Test\Unit;
use Spryker\Service\UtilEncoding\Exception\XmlReadException;
use Spryker\Service\UtilEncoding\Model\Xml;

/**
 * Locks the array shape this reader inherited from `Laminas\Config\Reader\Xml`. Every assertion
 * here is a contract some caller already depends on, so a "cleaner" shape is a breaking change,
 * not an improvement.
 *
 * Auto-generated group annotations
 *
 * @group SprykerTest
 * @group Service
 * @group UtilEncoding
 * @group XmlTest
 * Add your own group annotations below this line
 */
class XmlTest extends Unit
{
    protected function dataFile(string $name): string
    {
        return __DIR__ . DIRECTORY_SEPARATOR . '_data' . DIRECTORY_SEPARATOR . $name;
    }

    protected function createReader(): Xml
    {
        return new Xml();
    }

    public function testReadsNestedNavigationIntoAnArray(): void
    {
        // Act
        $result = $this->createReader()->readFile($this->dataFile('navigation.xml'));

        // Assert
        $this->assertSame(
            [
            'acl' => [
                'label' => 'Users Control',
                'pages' => [
            'roles' => [
                    'label' => 'Roles',
                    'pages' => ['role-create' => ['label' => 'Create new Role', 'visible' => '0']],
                ]],
            ]],
            $result,
        );
    }

    /**
     * Callers branch on `isset($definition['transfer'][0])` to tell one transfer from many, so the
     * single case MUST stay associative.
     *
     * @return void
     */
    public function testASingleRepeatedElementStaysAssociative(): void
    {
        // Act
        $result = $this->createReader()->readFile($this->dataFile('transfers.xml'));

        // Assert
        $this->assertArrayNotHasKey(0, $result['transfer']);
        $this->assertSame('Single', $result['transfer']['name']);
        $this->assertSame(['name' => 'alpha', 'type' => 'string'], $result['transfer']['property']);
    }

    public function testRepeatedElementsBecomeAList(): void
    {
        // Act
        $result = $this->createReader()->readFile($this->dataFile('transfers_repeated.xml'));

        // Assert
        $this->assertArrayHasKey(0, $result['transfer']);
        $this->assertCount(2, $result['transfer']);
        $this->assertSame('First', $result['transfer'][0]['name']);
        $this->assertSame('Second', $result['transfer'][1]['name']);
        $this->assertCount(2, $result['transfer'][0]['property']);
        $this->assertArrayNotHasKey(0, $result['transfer'][1]['property']);
    }

    /**
     * Symfony's own `XmlUtils::convertDomElementToArray()` casts these to int/bool, which would
     * silently change every `visible="0"` comparison in the navigation tree.
     *
     * @return void
     */
    public function testScalarValuesAreNeverCoerced(): void
    {
        // Act
        $result = $this->createReader()->readFile($this->dataFile('shapes.xml'));

        // Assert
        $this->assertSame('0', $result['zero']);
        $this->assertSame('1', $result['one']);
        $this->assertSame('true', $result['boolish']);
        $this->assertSame('007', $result['padded']);
        $this->assertSame('1.5', $result['decimal']);
    }

    /**
     * `\Spryker\Zed\Development\Business\ArchitectureSniffer\ArchitectureSniffer` reads PHPMD
     * violation messages from this key.
     *
     * @return void
     */
    public function testTextOfAnAttributedElementIsStoredUnderTheUnderscoreKey(): void
    {
        // Act
        $result = $this->createReader()->readFile($this->dataFile('shapes.xml'));

        // Assert
        $this->assertSame(['_' => '  text body  ', 'rule' => 'R'], $result['attributed']);
        $this->assertSame(['_' => 'cdata body', 'rule' => 'C'], $result['cdata']);
    }

    public function testASelfClosingElementReadsAsAnEmptyArray(): void
    {
        // Act
        $result = $this->createReader()->readFile($this->dataFile('shapes.xml'));

        // Assert
        $this->assertSame([], $result['selfClosing']);
    }

    public function testAnElementHoldingOnlyWhitespaceReadsAsThatWhitespace(): void
    {
        // Act
        $result = $this->createReader()->readFile($this->dataFile('shapes.xml'));

        // Assert
        $this->assertIsString($result['emptyWithWhitespace']);
        $this->assertSame('', trim($result['emptyWithWhitespace']));
    }

    public function testAnAttributeOverwritesAChildOfTheSameName(): void
    {
        // Act
        $result = $this->createReader()->readFile($this->dataFile('shapes.xml'));

        // Assert
        $this->assertSame('fromAttribute', $result['collision']['name']);
    }

    public function testReadStringAppliesTheSameShapeRules(): void
    {
        // Arrange
        $xml = '<?xml version="1.0"?><pmd><file name="A.php">'
            . '<violation beginline="3" rule="R">first</violation>'
            . '<violation beginline="9" rule="S">second</violation>'
            . '</file></pmd>';

        // Act
        $result = $this->createReader()->readString($xml);

        // Assert
        $this->assertSame('A.php', $result['file']['name']);
        $this->assertSame('first', $result['file']['violation'][0]['_']);
        $this->assertSame('3', $result['file']['violation'][0]['beginline']);
        $this->assertSame('second', $result['file']['violation'][1]['_']);
    }

    public function testReadingAMalformedFileThrows(): void
    {
        // Assert
        $this->expectException(XmlReadException::class);

        // Act
        $this->createReader()->readFile($this->dataFile('malformed.xml'));
    }

    public function testReadingAMissingFileThrows(): void
    {
        // Assert
        $this->expectException(XmlReadException::class);

        // Act
        $this->createReader()->readFile($this->dataFile('does-not-exist.xml'));
    }

    public function testReadingAMalformedStringThrows(): void
    {
        // Assert
        $this->expectException(XmlReadException::class);

        // Act
        $this->createReader()->readString('<?xml version="1.0"?><a><b></a>');
    }
}
