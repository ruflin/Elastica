<?php

namespace Elastica\Processor;

/**
 * Elastica DateIndexName Processor.
 *
 * @author Federico Panini <fpanini@gmail.com>
 *
 * @see https://www.elastic.co/guide/en/elasticsearch/reference/current/date-index-name-processor.html
 */
class DateIndexName extends AbstractProcessor
{
    public const DEFAULT_DATE_FORMATS_VALUE = ['ISO8601'];
    public const DEFAULT_INDEX_NAME_FORMAT_VALUE = 'yyyy-MM-dd';
    public const DEFAULT_TIMEZONE_VALUE = 'UTC';
    public const DEFAULT_LOCALE_VALUE = 'ENGLISH';

    /**
     * DateIndexName constructor.
     */
    public function __construct(string $field, string $dateRounding)
    {
        $this->setField($field);
        $this->setDateRounding($dateRounding);
    }

    /**
     * Set field.
     *
     * @return $this
     */
    public function setField(string $field): self
    {
        return $this->setParam('field', $field);
    }

    /**
     * Set date_rounding. Valid values are: y (year), M (month), w (week), d (day), h (hour), m (minute) and s (second).
     *
     * @return $this
     */
    public function setDateRounding(string $dateRounding): self
    {
        return $this->setParam('date_rounding', $dateRounding);
    }

    /**
     * Set field formats. Joda pattern or one of the following formats ISO8601, UNIX, UNIX_MS, or TAI64N.
     *
     * @return $this
     */
    public function setDateFormats(array $formats): self
    {
        return $this->setParam('date_formats', $formats);
    }

    /**
     * Set index_prefix_name.
     *
     * @return $this
     */
    public function setIndexNamePrefix(string $indexPrefixName): self
    {
        return $this->setParam('index_name_prefix', $indexPrefixName);
    }

    /**
     * Set format to be used when printing parsed date. An valid Joda pattern is expected here. Default yyyy-MM-dd.
     *
     * @return $this
     */
    public function setIndexNameFormat(string $indexNameFormat): self
    {
        return $this->setParam('index_name_format', $indexNameFormat);
    }

    /**
     * Set the timezone use when parsing the date. Default UTC.
     *
     * @return $this
     */
    public function setTimezone(string $timezone): self
    {
        return $this->setParam('timezone', $timezone);
    }

    /**
     * Set the locale to use when parsing the date.
     *
     * @return $this
     */
    public function setLocale(string $locale): self
    {
        return $this->setParam('locale', $locale);
    }
}
