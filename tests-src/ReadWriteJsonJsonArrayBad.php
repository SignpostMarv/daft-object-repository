<?php
/**
* @author SignpostMarv
*/
declare(strict_types=1);

namespace SignpostMarv\DaftObject;

class ReadWriteJsonJsonArrayBad extends AbstractArrayBackedDaftObject implements DaftJson
{
    const PROPERTIES = [
        'json',
    ];

    const EXPORTABLE_PROPERTIES = [
        'json',
    ];

    const JSON_PROPERTIES = [
        'json' => 'stdClass[]',
    ];

    public function GetJson() : array
    {
        /**
        * @var scalar|array|object|null
        */
        $json = $this->RetrievePropertyValueFromData('json');

        return is_array($json) ? $json : [$json];
    }

    public function SetJson(array $json) : void
    {
        $this->NudgePropertyValue('json', $json);
    }
}
