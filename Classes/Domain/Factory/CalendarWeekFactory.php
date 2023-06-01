<?php
namespace DWenzel\T3calendar\Domain\Factory;

/**
 * This file is part of the TYPO3 CMS project.
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 * The TYPO3 project - inspiring people to share!
 */

use DWenzel\T3calendar\Cache\CacheManagerTrait;
use DWenzel\T3calendar\Domain\Model\CalendarWeek;
use TYPO3\CMS\Core\SingletonInterface;

/**
 * Class CalendarWeek
 *
 * @package DWenzel\T3calendar\Domain\Factory
 */
class CalendarWeekFactory implements CalendarWeekFactoryInterface, SingletonInterface
{
    use ObjectManagerTrait, CalendarDayFactoryTrait;

    /**
     * creates a CalendarWeek object
     *
     * @param array|\Iterator|null $items
     * @return CalendarWeek
     */
    public function create(\DateTime $startDate, \DateTime $currentDate, $items = null)
    {
        /** @var CalendarWeek $calendarWeek */
        $calendarWeek = $this->objectManager->get(CalendarWeek::class);

        for ($weekDay = 0; $weekDay < 7; $weekDay++) {
            $dateOfDay = clone $startDate;
            if ($weekDay > 0) {
                $interval = new \DateInterval('P' . $weekDay . 'D');
                $dateOfDay->add($interval);
            }
            $current = ($currentDate == $dateOfDay) ? true : false;

            $day = $this->calendarDayFactory->create(
                $dateOfDay,
                $items,
                $current
            );
            $calendarWeek->addDay($day);
        }

        return $calendarWeek;
    }
}
