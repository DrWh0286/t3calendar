<?php
namespace DWenzel\T3calendar\Tests\Unit\Domain\Model;

/**
 * This file is part of the TYPO3 CMS project.
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 * The TYPO3 project - inspiring people to share!
 */

use Nimut\TestingFramework\TestCase\UnitTestCase;
use TYPO3\CMS\Extbase\Persistence\ObjectStorage;
use DWenzel\T3calendar\Domain\Model\CalendarDay;
use DWenzel\T3calendar\Domain\Model\CalendarItemInterface;

/**
 * Class CalendarDayTest
 *
 * @package DWenzel\T3calendar\Tests\Unit\Domain\Model
 * @coversDefaultClass \DWenzel\T3calendar\Domain\Model\CalendarDay
 */
class CalendarDayTest extends UnitTestCase
{

    /**
     * @var CalendarDay
     */
    protected $subject;

    public function setUp(): void
    {
        $this->subject = $this->getAccessibleMock(
            CalendarDay::class,
            ['dummy'], [], '', true
        );
    }

    /**
     * @test
     * @covers ::getDate
     */
    public function getDateReturnsInitiallyNull()
    {
        $this->assertNull(
            $this->subject->getDate()
        );
    }

    /**
     * @test
     * @covers ::setDate
     */
    public function setDateForDateTimeSetsDate()
    {
        $dateTime = new \DateTime();
        $this->subject->setDate($dateTime);

        $this->assertSame(
            $dateTime,
            $this->subject->getDate()
        );
    }

    /**
     * @test
     * @covers ::getDay
     */
    public function getDayReturnsInitiallyNull()
    {
        $this->assertNull(
            $this->subject->getDay()
        );
    }

    /**
     * @test
     * @covers ::getDay
     */
    public function getDayForStringReturnsDayOfMonth()
    {
        $timeStamp = 1_441_065_600;
        $dateTime = new \DateTime('@' . $timeStamp);
        $expectedDay = date('d', $timeStamp);
        $this->subject->setDate($dateTime);
        $this->assertSame(
            $expectedDay,
            $this->subject->getDay()
        );
    }

    /**
     * @test
     * @covers ::getMonth
     */
    public function getMonthReturnsInitiallyNull()
    {
        $this->assertNull(
            $this->subject->getMonth()
        );
    }

    /**
     * @test
     * @covers ::getMonth
     */
    public function getMonthForStringReturnsMonth()
    {
        $timeStamp = 1_441_065_600;
        $dateTime = new \DateTime('@' . $timeStamp);
        $expectedMonth = date('n', $timeStamp);
        $this->subject->setDate($dateTime);
        $this->assertSame(
            $expectedMonth,
            $this->subject->getMonth()
        );
    }

    /**
     * @test
     * @covers ::getDayOfWeek
     */
    public function getDayOfWeekForIntegerReturnsInitiallyNull()
    {
        $this->assertNull(
            $this->subject->getDayOfWeek()
        );
    }

    /**
     * @test
     * @covers ::getDayOfWeek
     */
    public function getDayOfWeekForIntegerReturnsDayOfWeek()
    {
        $timeStamp = 1_441_065_600;
        $dateTime = new \DateTime('@' . $timeStamp);
        $dayOfWeek = (int)date('w', $timeStamp);
        $this->subject->setDate($dateTime);
        $this->assertSame(
            $dayOfWeek,
            $this->subject->getDayOfWeek()
        );
    }

    /**
     * @test
     * @covers ::isCurrent
     */
    public function getIsCurrentForBooleanReturnsInitiallyFalse()
    {
        $this->assertFalse(
            $this->subject->isCurrent()
        );
    }

    /**
     * @test
     * @covers ::setCurrent
     */
    public function setIsCurrentForBooleanSetsIsCurrent()
    {
        $this->subject->setCurrent(true);
        $this->assertTrue(
            $this->subject->isCurrent()
        );
    }

    /**
     * @test
     * @covers ::getItems
     */
    public function getItemsForReturnsInitiallyEmptyObjectStorage()
    {
        $emptyObjectStorage = new ObjectStorage();

        $this->assertEquals(
            $emptyObjectStorage,
            $this->subject->getItems()
        );
    }

    /**
     * @test
     * @covers ::setItems
     */
    public function setItemsForObjectStorageSetsItems()
    {
        $emptyObjectStorage = new ObjectStorage();
        $this->subject->setItems($emptyObjectStorage);

        $this->assertSame(
            $emptyObjectStorage,
            $this->subject->getItems()
        );
    }

    /**
     * @test
     * @covers ::addItem
     */
    public function addItemForObjectAddsItem()
    {
        $item = $this->getMockForAbstractClass(CalendarItemInterface::class);
        $this->subject->addItem($item);
        $this->assertTrue(
            $this->subject->getItems()->contains($item)
        );
    }

    /**
     * @test
     * @covers ::removeItem
     */
    public function removeItemForObjectRemovesItem()
    {
        $item = $this->getMockForAbstractClass(CalendarItemInterface::class);
        $objectStorageContainingOneItem = new ObjectStorage();
        $objectStorageContainingOneItem->attach($item);

        $this->subject->setItems($objectStorageContainingOneItem);
        $this->subject->removeItem($item);
        $this->assertFalse(
            $this->subject->getItems()->contains($item)
        );
    }

    /**
     * @test
     */
    public function constructorInitializesStorageObjects()
    {
        $expectedObjectStorage = new ObjectStorage();
        $this->subject->__construct();

        $this->assertEquals(
            $expectedObjectStorage,
            $this->subject->getItems()
        );
    }

    /**
     * @test
     */
    public function constructorInitializesDate()
    {
        $expectedDate = new \DateTime();
        $this->subject->__construct($expectedDate);

        $this->assertEquals(
            $expectedDate,
            $this->subject->getDate()
        );
    }

    /**
     * @test
     */
    public function hasItemsInitiallyReturnsFalse()
    {
        $this->assertFalse(
            $this->subject->getHasItems()
        );
    }

    /**
     * @test
     */
    public function hasItemsReturnsTrueIfItemsNotEmpty()
    {
        $item = $this->getMockForAbstractClass(CalendarItemInterface::class);
        $this->subject->addItem($item);

        $this->assertTrue(
            $this->subject->getHasItems()
        );
    }
}
