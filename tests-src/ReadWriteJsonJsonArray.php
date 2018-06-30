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
        /**
        * @var ReadWriteJson[]
        */
        $out = (array) $this->RetrievePropertyValueFromData('json');

        return $out;
    }

    public function SetJson(array $json) : void
    {
        $this->NudgePropertyValue('json', $json);
    }
}
