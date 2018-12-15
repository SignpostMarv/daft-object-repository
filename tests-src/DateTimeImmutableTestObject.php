<?php
/**
* @author SignpostMarv
*/
declare(strict_types=1);

namespace SignpostMarv\DaftObject;

use DateTimeImmutable;

class DateTimeImmutableTestObject extends AbstractArrayBackedDaftObject
{
    const PROPERTIES = [
        'datetime',
    ];

    const EXPORTABLE_PROPERTIES = self::PROPERTIES;

    const JSON_PROPERTIES = self::EXPORTABLE_PROPERTIES;

    public function GetDatetime() : DateTimeImmutable
    {
        /**
        * @var DateTimeImmutable|string
        */
        $out = $this->RetrievePropertyValueFromData('datetime');

        return ($out instanceof DateTimeImmutable) ? $out : new DateTimeImmutable($out);
    }

    public function SetDatetime(DateTimeImmutable $value) : void
    {
        $this->NudgePropertyValue('datetime', $value);
    }
}
