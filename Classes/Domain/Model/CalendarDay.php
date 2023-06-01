<?php
namespace DWenzel\T3calendar\Domain\Model;

/**
 * This file is part of the TYPO3 CMS project.
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 * The TYPO3 project - inspiring people to share!
 */

use TYPO3\CMS\Extbase\Persistence\ObjectStorage;

/**
 * Class CalendarDay
 * represents a day in a calendar object
 *
 * @package DWenzel\T3calendar\Domain\Model
 */
class CalendarDay
{

    /**
     * @var \DateTime
     */
    protected $date;

    /**
     * @var ObjectStorage
     * @TYPO3\CMS\Extbase\Annotation\ORM\Lazy
     */
    protected $items;

    /**
     * @var bool
     */
    protected $isCurrent = false;

    /**
     * @param \DateTime|null $date
     */
    public function __construct($date = null)
    {
        if ($date !== null) {
            $this->date = $date;
        }
        $this->initStorageObjects();
    }

    /**
     * Initializes all \TYPO3\CMS\Extbase\Persistence\ObjectStorage properties.
     *
     * @return void
     */
    protected function initStorageObjects()
    {
        /**
         * Do not modify this method!
         * It will be rewritten on each save in the extension builder
         * You may modify the constructor of this class instead
         */
        $this->items = new ObjectStorage();
    }

    /**
     * Gets the date
     *
     * @return \DateTime
     */
    public function getDate()
    {
        return $this->date;
    }


    /**
     * Sets the date
     */
    public function setDate(\DateTime $dateTime)
    {
        $this->date = $dateTime;
    }

    /**
     * Gets the day of month
     *
     * @return int
     */
    public function getDay()
    {
        if ($this->date !== null) {
            return $this->date->format('d');
        }

        return null;
    }

    /**
     * Gets the day of week
     */
    public function getDayOfWeek(): ?int
    {
        if ($this->date !== null) {
            return (int)date('w', $this->date->getTimestamp());
        }

        return null;
    }

    /**
     * Get the is current state
     *
     * @return bool
     */
    public function isCurrent()
    {
        return $this->isCurrent;
    }

    /**
     * Sets the is current state
     *
     * @param bool $isCurrent
     */
    public function setCurrent($isCurrent)
    {
        $this->isCurrent = $isCurrent;
    }

    /**
     * Gets the items
     *
     * @return ObjectStorage
     */
    public function getItems()
    {
        return $this->items;
    }

    /**
     * Sets the items
     *
     * @param ObjectStorage $items
     */
    public function setItems($items)
    {
        $this->items = $items;
    }

    /**
     * Adds an item
     */
    public function addItem(CalendarItemInterface $item)
    {
        $this->items->attach($item);
    }

    /**
     * Removes an item
     */
    public function removeItem(CalendarItemInterface $item)
    {
        $this->items->detach($item);
    }

    /**
     * Gets the month
     *
     * @param string $format A format as understood by date() Default. 'n'
     */
    public function getMonth($format = 'n'): ?string
    {
        if ($this->date !== null) {
            return $this->date->format($format);
        }

        return null;
    }

    /**
     * Tells if the calendar day has items
     * @return bool
     */
    public function getHasItems()
    {
        return ($this->items->count()) ? true : false;
    }
}
