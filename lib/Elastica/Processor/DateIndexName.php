<?php
namespace Elastica\Processor;

/**
 * Elastica DateIndexName Processor.
 *
 * @author   Federico Panini <fpanini@gmail.com>
 *
 * @link https://www.elastic.co/guide/en/elasticsearch/reference/current/date-index-name-processor.html
 */
class DateIndexName extends AbstractProcessor
{
    /**
     * DateIndexName constructor.
     *
     * @param string $field
     * @param string $dateRounding
     */
    public function __construct(string $field, string $dateRounding)
    {
        $this->setField($field);
        $this->setDateRounding($dateRounding);
    }

    /**
     * Set field.
     *
     * @param string $field
     *
     * @return $this
     */
    public function setField(string $field)
    {
        return $this->setParam('field', $field);
    }

    /**
     * Set date_rounding. Valid values are: y (year), M (month), w (week), d (day), h (hour), m (minute) and s (second).
     *
     * @param string $dateRounding
     *
     * @return $this
     */
    public function setDateRounding(string $dateRounding)
    {
        return $this->setParam('date_rounding', $dateRounding);
    }

    /**
     * Set field formats. Joda pattern or one of the following formats ISO8601, UNIX, UNIX_MS, or TAI64N.
     *
     * @param array $formats
     *
     * @return $this
     */
    public function setDateFormats(array $formats)
    {
        return $this->setParam('date_formats', $formats);
    }

    /**
     * Set index_prefix_name.
     *
     * @param string $indexPrefixName
     *
     * @return $this
     */
    public function setIndexNamePrefix(string $indexPrefixName)
    {
        return $this->setParam('index_name_prefix', $indexPrefixName);
    }

    /**
     * Set format to be used when printing parsed date. An valid Joda pattern is expected here. Default yyyy-MM-dd.
     *
     * @param string $indexNameFormat
     *
     * @return $this
     */
    public function setIndexNameFormat(string $indexNameFormat)
    {
        return $this->setParam('index_name_format', $indexNameFormat);
    }

    /**
     * Set the timezone use when parsing the date. Default UTC.
     *
     * @param string $timezone
     *
     * @return $this
     */
    public function setTimezone(string $timezone)
    {
        return $this->setParam('timezone', $timezone);
    }

    /**
     * Set the locale to use when parsing the date.
     *
     * @param string $locale
     *
     * @return $this
     */
    public function setLocale(string $locale)
    {
        return $this->setParam('locale', $locale);
    }
}
