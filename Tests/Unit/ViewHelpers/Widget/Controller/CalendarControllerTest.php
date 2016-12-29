<?php
namespace DWenzel\T3calendar\Tests\ViewHelpers\Widget\Controller;

/**
 * This file is part of the TYPO3 CMS project.
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 *
 * The TYPO3 project - inspiring people to share!
 */

use DWenzel\T3calendar\Domain\Factory\CalendarFactory;
use DWenzel\T3calendar\Domain\Model\Dto\CalendarConfiguration;
use DWenzel\T3calendar\Domain\Model\Dto\CalendarConfigurationFactoryInterface;
use DWenzel\T3calendar\ViewHelpers\Widget\Controller\CalendarController;
use TYPO3\CMS\Core\Tests\AccessibleObjectInterface;
use TYPO3\CMS\Core\Tests\UnitTestCase;
use TYPO3\CMS\Extbase\Mvc\View\ViewInterface;
use TYPO3\CMS\Extbase\Object\ObjectManagerInterface;
use TYPO3\CMS\Extbase\Persistence\QueryResultInterface;

/**
 * Class CalendarControllerTest
 * @package DWenzel\T3calendar\Tests\ViewHelpers\Widget\Controller
 */
class CalendarControllerTest extends UnitTestCase
{
    /**
     * @var CalendarController|\PHPUnit_Framework_MockObject_MockObject|AccessibleObjectInterface
     */
    protected $subject;

    /**
     * @var ViewInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $view;

    /**
     * @var ObjectManagerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $objectManager;

    /**
     * @var CalendarConfiguration|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $configuration;

    /**
     * @var CalendarConfigurationFactoryInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $calendarFactory;

    /**
     * @var array
     */
    protected $objects = [];

    /**
     * set up subject
     */
    public function setUp()
    {
        $this->subject = $this->getAccessibleMock(
            CalendarController::class, ['dummy']
        );
        $this->view = $this->getMock(ViewInterface::class);
        $this->subject->_set('view', $this->view);
        $this->mockConfiguration();
        $this->objectManager = $this->getMock(ObjectManagerInterface::class);
        $this->subject->injectObjectManager($this->objectManager);
        $this->calendarFactory = $this->getMock(
            CalendarFactory::class, ['create']
        );
        $this->calendarFactory->method('create')->will($this->returnValue($this->configuration));
        $this->subject->injectCalendarFactory($this->calendarFactory);
        $this->subject->_set('objects', $this->objects);
    }

    /**
     * mocks the configuration
     */
    protected function mockConfiguration()
    {
        $this->configuration = $this->getMock(
            CalendarConfiguration::class,
            ['getDisplayPeriod', 'setDisplayPeriod']
        );
        $startDate = new \DateTime();
        $this->configuration->setStartDate($startDate);

        $this->subject->_set('configuration', $this->configuration);
    }

    /**
     * @test
     */
    public function initializeActionSetsObjectsFromWidgetConfiguration()
    {
        $objects = $this->getMock(QueryResultInterface::class);
        $widgetConfiguration = [
            'objects' => $objects
        ];
        $this->subject->_set('widgetConfiguration', $widgetConfiguration);
        $this->subject->initializeAction();
        $this->assertAttributeSame(
            $objects,
            'objects',
            $this->subject
        );
    }

    /**
     * @test
     */
    public function initializeActionSetsConfigurationFromWidgetConfiguration()
    {
        $configuration = $this->getMock(CalendarConfiguration::class);
        $widgetConfiguration = [
            'configuration' => $configuration
        ];
        $this->subject->_set('widgetConfiguration', $widgetConfiguration);
        $this->subject->initializeAction();
        $this->assertAttributeSame(
            $configuration,
            'configuration',
            $this->subject
        );
    }


    /**
     * @test
     */
    public function initializeActionSetsIdFromWidgetId()
    {
        $id = 'foo';
        $widgetConfiguration = [
            'id' => $id
        ];
        $this->subject->_set('widgetConfiguration', $widgetConfiguration);
        $this->subject->initializeAction();
        $this->assertAttributeSame(
            $id,
            'id',
            $this->subject
        );
    }

    /**
     * @test
     */
    public function indexActionGetsCalendarFromFactory()
    {
        $this->calendarFactory->expects($this->once())
            ->method('create')
            ->with($this->configuration, $this->objects);
        $this->subject->indexAction();
    }

    /**
     * @test
     */
    public function indexActionAssignsVariablesToView()
    {
        $this->view->expects($this->once())
            ->method('assignMultiple');

        $this->subject->indexAction();

    }

    /**
     * @test
     */
    public function dayActionSetsDisplayPeriod()
    {
        $this->configuration->expects($this->once())
            ->method('setDisplayPeriod')
            ->with(CalendarConfiguration::PERIOD_DAY);

        $this->subject->dayAction();
    }

    /**
     * @test
     */
    public function dayActionSetsDefaultStartDateToday()
    {
        $timeZone = new \DateTimeZone(date_default_timezone_get());
        $expectedStartDate = new \DateTime('today', $timeZone);
        $this->subject->dayAction();

        $this->assertEquals(
            $expectedStartDate,
            $this->configuration->getStartDate()
        );

    }

    /**
     * provides valid shift and origin arguments for dayAction
     * @return array
     */
    public function dayActionShiftOriginDataProvider()
    {
        return [
            [1234567, 'next', 'P1D', false],
            [1234567, 'previous', 'P1D', true]
        ];
    }

    /**
     * @test
     * @dataProvider dayActionShiftOriginDataProvider
     * @param int $origin
     * @param string $shift
     * @param string $interval Interval spec
     * @param bool $invertInterval
     */
    public function dayActionAdjustsStartDateByShiftAndOrigin($origin, $shift, $interval, $invertInterval)
    {
        $expectedInterval = new \DateInterval($interval);
        $expectedInterval->invert = $invertInterval;
        $timeZone = new \DateTimeZone(date_default_timezone_get());
        $expectedStartDate = new \DateTime('@' . $origin, $timeZone);
        $expectedStartDate->add($expectedInterval);

        $this->subject->dayAction($shift, $origin);

        $this->assertEquals(
            $expectedStartDate,
            $this->configuration->getStartDate()
        );
    }

    /**
     * @test
     */
    public function dayActionSetsDefaultStartDateForInvalidShift()
    {
        $invalidShift = 'foo';

        $timeZone = new \DateTimeZone(date_default_timezone_get());
        $expectedStartDate = new \DateTime('today', $timeZone);
        $this->subject->dayAction($invalidShift);

        $this->assertEquals(
            $expectedStartDate,
            $this->configuration->getStartDate()
        );

    }

    /**
     * @test
     */
    public function weekActionSetsDisplayPeriod()
    {
        $this->configuration->expects($this->once())
            ->method('setDisplayPeriod')
            ->with(CalendarConfiguration::PERIOD_WEEK);

        $this->subject->weekAction();
    }

    /**
     * @test
     */
    public function weekActionSetsDefaultStartDate()
    {
        $timeZone = new \DateTimeZone(date_default_timezone_get());
        $expectedStartDate = new \DateTime('monday this week', $timeZone);
        $this->subject->weekAction();

        $this->assertEquals(
            $expectedStartDate,
            $this->configuration->getStartDate()
        );

    }

    /**
     * @test
     */
    public function weekActionSetsDefaultStartDateForInvalidShift()
    {
        $invalidShift = 'foo';

        $timeZone = new \DateTimeZone(date_default_timezone_get());
        $expectedStartDate = new \DateTime('monday this week', $timeZone);
        $this->subject->weekAction($invalidShift);

        $this->assertEquals(
            $expectedStartDate,
            $this->configuration->getStartDate()
        );

    }

    /**
     * provides valid shift and origin arguments for weekAction
     * @return array
     */
    public function weekActionShiftOriginDataProvider()
    {
        return [
            [1482706800, 'next', 'P1W', false],
            [1482706800, 'previous', 'P1W', true],
        ];
    }

    /**
     * @test
     * @dataProvider weekActionShiftOriginDataProvider
     * @param int $origin
     * @param string $shift
     * @param string $interval Interval spec
     * @param bool $invertInterval
     */
    public function weekActionAdjustsStartDateByShiftAndOrigin($origin, $shift, $interval, $invertInterval)
    {
        $this->configuration->expects($this->atLeast(1))
            ->method('getDisplayPeriod')
            ->will($this->returnValue(CalendarConfiguration::PERIOD_WEEK));
        $expectedInterval = new \DateInterval($interval);
        $expectedInterval->invert = $invertInterval;
        $timeZone = new \DateTimeZone(date_default_timezone_get());
        $expectedStartDate = new \DateTime('@' . $origin);
        $expectedStartDate->setTimezone($timeZone);
        $expectedStartDate->add($expectedInterval);

        $this->subject->weekAction($shift, $origin);

        $this->assertEquals(
            $expectedStartDate,
            $this->configuration->getStartDate()
        );
    }

    /**
     * @test
     */
    public function monthActionSetsDisplayPeriod()
    {
        $this->configuration->expects($this->once())
            ->method('setDisplayPeriod')
            ->with(CalendarConfiguration::PERIOD_MONTH);

        $this->subject->monthAction();
    }

    /**
     * @test
     */
    public function monthActionSetsDefaultStartDate()
    {
        $timeZone = new \DateTimeZone(date_default_timezone_get());
        $expectedStartDate = new \DateTime('first day of this month 00:00:00', $timeZone);
        $this->subject->monthAction();

        $this->assertEquals(
            $expectedStartDate,
            $this->configuration->getStartDate()
        );

    }

    /**
     * @test
     */
    public function monthActionSetsDefaultStartDateForInvalidShift()
    {
        $invalidShift = 'foo';

        $timeZone = new \DateTimeZone(date_default_timezone_get());
        $expectedStartDate = new \DateTime('first day of this month 00:00:00', $timeZone);
        $this->subject->monthAction($invalidShift);

        $this->assertEquals(
            $expectedStartDate,
            $this->configuration->getStartDate()
        );

    }

    /**
     * provides valid shift and origin arguments for monthAction
     * @return array
     */
    public function monthActionShiftOriginDataProvider()
    {
        return [
            [1482706800, 'next', 'P1M', false],
            [1482706800, 'previous', 'P1M', true],
        ];
    }

    /**
     * @test
     * @dataProvider monthActionShiftOriginDataProvider
     * @param int $origin
     * @param string $shift
     * @param string $interval Interval spec
     * @param bool $invertInterval
     */
    public function monthActionAdjustsStartDateByShiftAndOrigin($origin, $shift, $interval, $invertInterval)
    {
        $this->configuration->expects($this->atLeast(1))
            ->method('getDisplayPeriod')
            ->will($this->returnValue(CalendarConfiguration::PERIOD_MONTH));
        $expectedInterval = new \DateInterval($interval);
        $expectedInterval->invert = $invertInterval;
        $timeZone = new \DateTimeZone(date_default_timezone_get());
        $expectedStartDate = new \DateTime('@' . $origin);
        $expectedStartDate->setTimezone($timeZone);
        $expectedStartDate->add($expectedInterval);

        $this->subject->monthAction($shift, $origin);

        $this->assertEquals(
            $expectedStartDate,
            $this->configuration->getStartDate()
        );
    }

    /**
     * @test
     */
    public function yearActionSetsDisplayPeriod()
    {
        $this->configuration->expects($this->once())
            ->method('setDisplayPeriod')
            ->with(CalendarConfiguration::PERIOD_YEAR);

        $this->subject->yearAction();
    }

    /**
     * @test
     */
    public function yearActionSetsDefaultStartDate()
    {
        $timeZone = new \DateTimeZone(date_default_timezone_get());
        $expectedStartDate = new \DateTime(date('Y') . '-01-01', $timeZone);
        $this->subject->yearAction();

        $this->assertEquals(
            $expectedStartDate,
            $this->configuration->getStartDate()
        );

    }

    /**
     * @test
     */
    public function yearActionSetsDefaultStartDateForInvalidShift()
    {
        $invalidShift = 'foo';

        $timeZone = new \DateTimeZone(date_default_timezone_get());
        $expectedStartDate = new \DateTime(date('Y') . '-01-01', $timeZone);
        $this->subject->yearAction($invalidShift);

        $this->assertEquals(
            $expectedStartDate,
            $this->configuration->getStartDate()
        );

    }

    /**
     * provides valid shift and origin arguments for yearAction
     * @return array
     */
    public function yearActionShiftOriginDataProvider()
    {
        return [
            [1482706800, 'next', 'P1Y', false],
            [1482706800, 'previous', 'P1Y', true],
        ];
    }

    /**
     * @test
     * @dataProvider yearActionShiftOriginDataProvider
     * @param int $origin
     * @param string $shift
     * @param string $interval Interval spec
     * @param bool $invertInterval
     */
    public function yearActionAdjustsStartDateByShiftAndOrigin($origin, $shift, $interval, $invertInterval)
    {
        $this->configuration->expects($this->atLeast(1))
            ->method('getDisplayPeriod')
            ->will($this->returnValue(CalendarConfiguration::PERIOD_YEAR));
        $expectedInterval = new \DateInterval($interval);
        $expectedInterval->invert = $invertInterval;
        $timeZone = new \DateTimeZone(date_default_timezone_get());
        $expectedStartDate = new \DateTime('@' . $origin);
        $expectedStartDate->setTimezone($timeZone);
        $expectedStartDate->add($expectedInterval);

        $this->subject->yearAction($shift, $origin);

        $this->assertEquals(
            $expectedStartDate,
            $this->configuration->getStartDate()
        );
    }
    //
    /**
     * @test
     */
    public function quarterActionSetsDisplayPeriod()
    {
        $this->configuration->expects($this->once())
            ->method('setDisplayPeriod')
            ->with(CalendarConfiguration::PERIOD_QUARTER);

        $this->subject->quarterAction();
    }

    /**
     * @test
     */
    public function quarterActionSetsDefaultStartDate()
    {
        $timeZone = new \DateTimeZone(date_default_timezone_get());
        $dateString = date(sprintf('Y-%s-01', floor((date('n') - 1) / 3) * 3 + 1));
        $expectedStartDate = new \DateTime($dateString, $timeZone);
        $this->subject->quarterAction();

        $this->assertEquals(
            $expectedStartDate,
            $this->configuration->getStartDate()
        );

    }

    /**
     * @test
     */
    public function quarterActionSetsDefaultStartDateForInvalidShift()
    {
        $invalidShift = 'foo';

        $timeZone = new \DateTimeZone(date_default_timezone_get());
        $dateString = date(sprintf('Y-%s-01', floor((date('n') - 1) / 3) * 3 + 1));
        $expectedStartDate = new \DateTime($dateString, $timeZone);
        $this->subject->quarterAction($invalidShift);

        $this->assertEquals(
            $expectedStartDate,
            $this->configuration->getStartDate()
        );

    }

    /**
     * provides valid shift and origin arguments for quarterAction
     * @return array
     */
    public function quarterActionShiftOriginDataProvider()
    {
        return [
            [1482706800, 'next', 'P3M', false],
            [1482706800, 'previous', 'P3M', true],
        ];
    }

    /**
     * @test
     * @dataProvider quarterActionShiftOriginDataProvider
     * @param int $origin
     * @param string $shift
     * @param string $interval Interval spec
     * @param bool $invertInterval
     */
    public function quarterActionAdjustsStartDateByShiftAndOrigin($origin, $shift, $interval, $invertInterval)
    {
        $this->configuration->expects($this->atLeast(1))
            ->method('getDisplayPeriod')
            ->will($this->returnValue(CalendarConfiguration::PERIOD_QUARTER));
        $expectedInterval = new \DateInterval($interval);
        $expectedInterval->invert = $invertInterval;
        $timeZone = new \DateTimeZone(date_default_timezone_get());
        $expectedStartDate = new \DateTime('@' . $origin);
        $expectedStartDate->setTimezone($timeZone);
        $expectedStartDate->add($expectedInterval);

        $this->subject->quarterAction($shift, $origin);

        $this->assertEquals(
            $expectedStartDate,
            $this->configuration->getStartDate()
        );
    }
}