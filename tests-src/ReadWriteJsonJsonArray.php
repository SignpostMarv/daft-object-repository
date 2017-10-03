<?php
/**
* @author SignpostMarv
*/
declare(strict_types=1);

namespace SignpostMarv\DaftObject;

class ReadWriteJsonJsonArray extends AbstractArrayBackedDaftObject implements DaftJson
{
    const PROPERTIES = [
        'json',
    ];

    const EXPORTABLE_PROPERTIES = [
        'json',
    ];

    const JSON_PROPERTIES = [
        'json' => ReadWriteJson::class . '[]',
    ];

    /**
    * @return ReadWriteJson[]
    */
    public function GetJson() : array
    {
        return $this->RetrievePropertyValueFromData('json');
    }

    public function SetJson(array $json) : void
    {
        $this->NudgePropertyValue('json', $json);
    }
}
