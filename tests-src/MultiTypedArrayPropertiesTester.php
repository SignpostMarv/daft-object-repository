<?php
/**
* @author SignpostMarv
*/
declare(strict_types=1);

namespace SignpostMarv\DaftObject;

use DateTimeImmutable;

class MultiTypedArrayPropertiesTester
    extends
        AbstractArrayBackedDaftObject
    implements
        DaftObjectHasPropertiesWithMultiTypedArraysOfUniqueValues
{
    const PROPERTIES = [
        'dates',
        'datesOrStrings',
        'trimmedStrings',
        'trimmedString',
    ];

    const PROPERTIES_WITH_MULTI_TYPED_ARRAYS = [
        'dates' => [
            DateTimeImmutable::class,
        ],
        'datesOrStrings' => [
            'string',
            DateTimeImmutable::class,
        ],
        'trimmedStrings' => [
            'string',
        ],
    ];

    /**
    * @return array<int, DateTimeImmutable>
    */
    public function GetDates() : array
    {
        /**
        * @var array<int, DateTimeImmutable>
        */
        $out = (array) $this->RetrievePropertyValueFromData('dates');

        return $out;
    }

    /**
    * @param scalar|array|object|null $value
    */
    public function SetDates($value) : void
    {
        $this->NudgePropertyValue('dates', $value, false, true);
    }

    /**
    * @return array<int, DateTimeImmutable|string>
    */
    public function GetDatesOrStrings() : array
    {
        /**
        * @var array<int, DateTimeImmutable|string>
        */
        $out = (array) $this->RetrievePropertyValueFromData('datesOrStrings');

        return $out;
    }

    /**
    * @param scalar|array|object|null $value
    */
    public function SetDatesOrStrings($value) : void
    {
        $this->NudgePropertyValue('datesOrStrings', $value, false, true);
    }

    /**
    * @return array<int, string>
    */
    public function GetTrimmedStrings() : array
    {
        /**
        * @var array<int, string>
        */
        $out = (array) $this->RetrievePropertyValueFromData('trimmedStrings');

        return $out;
    }

    /**
    * @param scalar|array|object|null $value
    */
    public function SetTrimmedStrings($value) : void
    {
        $this->NudgePropertyValue('trimmedStrings', $value, true);
    }

    public function GetTrimmedString() : string
    {
        return (string) $this->RetrievePropertyValueFromData('trimmedString');
    }

    /**
    * @param scalar|array|object|null $value
    */
    public function SetTrimmedString($value) : void
    {
        $this->NudgePropertyValue('trimmedString', $value, true);
    }
}
