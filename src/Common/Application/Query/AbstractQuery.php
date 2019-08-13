<?php

namespace AlephTools\DDD\Common\Application\Query;

use DateTime;
use DateTimeImmutable;
use AlephTools\DDD\Common\Infrastructure\DateHelper;
use AlephTools\DDD\Common\Infrastructure\WeakDto;

/**
 * @property-read string|null $keyword
 * @property-read int|null $limit
 * @property-read int|null $offset
 * @property-read int|null $page
 * @property-read string[]|null $sort
 * @property-read string[]|null $group
 * @property-read string[]|null $fields
 * @property-read int|null $timezone The desire timezone offset in minutes.
 * @property-read bool $withoutCount
 * @property-read bool $withoutItems
 */
abstract class AbstractQuery extends WeakDto
{
    //region Constants

    public const DEFAULT_PAGE_SIZE = 10;
    public const DEFAULT_PAGE_MAX_SIZE = 1000;

    //endregion

    //region Properties

    protected static $pageMaxSize = self::DEFAULT_PAGE_MAX_SIZE;

    protected $keyword;
    protected $limit = self::DEFAULT_PAGE_SIZE;
    protected $offset;
    protected $page;
    protected $sort;
    protected $group;
    protected $fields;
    protected $timezone;
    protected $withoutCount = false;
    protected $withoutItems = false;

    //endregion

    public static function getPageMaxSize(): int
    {
        return static::$pageMaxSize;
    }

    public static function setPageMaxSize(int $size = self::DEFAULT_PAGE_MAX_SIZE): void
    {
        static::$pageMaxSize = $size;
    }

    /**
     * Returns TRUE if the fields is not set or if the given field within $fields array.
     *
     * @param string $field
     * @return bool
     */
    public function containsField(string $field): bool
    {
        return !$this->fields || in_array($field, $this->fields);
    }

    protected function toBoolean($value): bool
    {
        if (is_scalar($value)) {
            $value = strtolower(trim($value));
            return $value === 'true' || $value === '1' || $value === 'on';
        }

        return false;
    }

    protected function toDate($value): ?DateTime
    {
        return DateHelper::parse($value);
    }

    protected function toImmutableDate($value): ?DateTimeImmutable
    {
        return DateHelper::parseImmutable($value);
    }

    //region Setters

    protected function setKeyword($keyword): void
    {
        $this->keyword = is_scalar($keyword) ? (string)$keyword : null;
    }

    protected function setLimit($limit): void
    {
        $this->limit = is_numeric($limit) ? abs((int)$limit) : static::DEFAULT_PAGE_SIZE;

        if ($this->limit > static::getPageMaxSize()) {
            $this->limit = static::getPageMaxSize();
        }
    }

    protected function setOffset($offset): void
    {
        $this->offset = is_numeric($offset) ? abs((int)$offset) : null;
    }

    protected function setPage($page): void
    {
        $this->page = is_numeric($page) ? abs((int)$page) : null;
    }

    protected function setSort($sort): void
    {
        if (!is_string($sort) || $sort === '') {
            return;
        }

        $items = [];
        foreach (explode(',', $sort) as $item) {
            $item = trim($item);
            if ($item === '') {
                continue;
            }

            $first = $item[0];
            if ($first === '-') {
                $items[ltrim(substr($item, 1))] = 'DESC';
            } else if ($first === '+') {
                $items[ltrim(substr($item, 1))] = 'ASC';
            } else {
                $items[$item] = 'ASC';
            }
        }

        $this->sort = $items ?: null;
    }

    protected function setGroup($fields): void
    {
        $this->group = $this->fieldsToArray($fields);
    }

    protected function setFields($fields): void
    {
        $this->fields = $this->fieldsToArray($fields);
    }

    protected function setTimezone(?int $timezone): void
    {
        $this->timezone = $timezone;
    }

    protected function setWithoutCount($flag): void
    {
        $this->withoutCount = $this->toBoolean($flag);
    }

    protected function setWithoutItems($flag): void
    {
        $this->withoutItems = $this->toBoolean($flag);
    }

    //endregion

    private function fieldsToArray($fields): ?array
    {
        if (!is_string($fields) || $fields === '') {
            return null;
        }

        $result = [];
        foreach (explode(',', $fields) as $field) {
            $field = trim($field);
            if ($field !== '') {
                $result[] = $field;
            }
        }
        return $result ?: null;
    }
}
