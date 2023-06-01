<?php
namespace DWenzel\T3calendar\ViewHelpers\Format;

/*                                                                        *
 * This script is back ported from the TYPO3 Flow package "TYPO3.Fluid".   *
 *                                                                        *
 * It is free software; you can redistribute it and/or modify it under    *
 * the terms of the GNU Lesser General Public License, either version 3   *
 *  of the License, or (at your option) any later version.                *
 *                                                                        *
 * The TYPO3 project - inspiring people to share!                         *
 *                                                                        */
use TYPO3\CMS\Core\Utility\MathUtility;
use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;
use TYPO3Fluid\Fluid\Core\ViewHelper\Exception;

/**
 * Formats a \DateTime object. This is an extended version which allows to
 * add a time value (timestamp integer) to a date. Thus a given time can be
 * formatted according to the date (day light saving, time zone etc.)
 * = Examples =
 * <code title="Defaults">
 * <t3c:format.date>{dateObject}</t3c:format.date>
 * </code>
 * <output>
 * 1980-12-13
 * (depending on the current date)
 * </output>
 * <code title="Custom date format">
 * <t3c:format.date format="H:i">{dateObject}</t3c:format.date>
 * </code>
 * <output>
 * 01:23
 * (depending on the current time)
 * </output>
 * <code title="strtotime string">
 * <t3c:format.date format="d.m.Y - H:i:s">+1 week 2 days 4 hours 2 seconds</t3c:format.date>
 * </code>
 * <output>
 * 13.12.1980 - 21:03:42
 * (depending on the current time, see http://www.php.net/manual/en/function.strtotime.php)
 * </output>
 * <code title="Localized dates using strftime date format">
 * <t3c:format.date format="%d. %B %Y">{dateObject}</t3c:format.date>
 * </code>
 * <output>
 * 13. Dezember 1980
 * (depending on the current date and defined locale. In the example you see the 1980-12-13 in a german locale)
 * </output>
 * <code title="Inline notation">
 * {t3c:format.date(date: dateObject)}
 * </code>
 * <output>
 * 1980-12-13
 * (depending on the value of {dateObject})
 * </output>
 * <code title="Inline notation (2nd variant)">
 * {dateObject -> t3c:format.date()}
 * </code>
 * <output>
 * 1980-12-13
 * (depending on the value of {dateObject})
 * </output>
 *
 */
class DateViewHelper extends AbstractViewHelper
{
    final public const DEFAULT_DATE_FORMAT = 'Y-m-d';

    /**
     * @var boolean
     */
    protected $escapingInterceptorEnabled = false;

    /**
     * Render the supplied DateTime object as a formatted date.
     * If a time is given it will be added to the date (by adding the timestamps)
     *
     * @param mixed $date either a DateTime object or a string that is accepted by DateTime constructor
     * @param string $format Format String which is taken to format the Date/Time
     * @param int $time an integer representing a time value
     * @param mixed $base A base time (a DateTime object or a string) used if $date is a relative date specification. Defaults to current time.
     * @return string Formatted date
     * @throws Exception
     */
    public function render(mixed $date = null, $format = '', $time = null, mixed $base = null)
    {
        if ($format === '') {
            $format = $GLOBALS['TYPO3_CONF_VARS']['SYS']['ddmmyy'] ?: static::DEFAULT_DATE_FORMAT;
        }

        if (empty($base)) {
            $base = time();
        }

        if ($date === null) {
            $date = $this->renderChildren();
            if ($date === null) {
                return '';
            }
        }

        if ($date === '') {
            $date = 'now';
        }

        if (!$date instanceof \DateTime) {
            try {
                $base = $base instanceof \DateTime ? $base->format('U') : strtotime((MathUtility::canBeInterpretedAsInteger($base) ? '@' : '') . $base);
                $dateTimestamp = strtotime((MathUtility::canBeInterpretedAsInteger($date) ? '@' : '') . $date, $base);
                $modifiedDate = new \DateTime('@' . $dateTimestamp);
                $modifiedDate->setTimezone(new \DateTimeZone(date_default_timezone_get()));
            } catch (\Exception) {
                throw new Exception('"' . $date . '" could not be parsed by \DateTime constructor.', 1_241_722_579);
            }
        } else {
            $modifiedDate = clone($date);
        }

        if ($time !== null) {
            $modifiedDate->setTimestamp($modifiedDate->getTimestamp() + $time);
        }

        if (str_contains((string) $format, '%')) {
            return strftime($format, $modifiedDate->format('U'));
        } else {
            return $modifiedDate->format($format);
        }
    }
}
