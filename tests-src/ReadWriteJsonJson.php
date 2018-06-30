<?php
/**
* @author SignpostMarv
*/
declare(strict_types=1);

namespace SignpostMarv\DaftObject;

class ReadWriteJsonJson extends AbstractArrayBackedDaftObject implements DaftJson
{
    const PROPERTIES = [
        'json',
    ];

    const EXPORTABLE_PROPERTIES = [
        'json',
    ];

    const JSON_PROPERTIES = [
        'json' => ReadWriteJson::class,
    ];

    public function GetJson() : ReadWriteJson
    {
        /**
        * @var ReadWriteJson
        */
        $out = $this->RetrievePropertyValueFromData('json');

        return $out;
    }

    public function SetJson(ReadWriteJson $json) : void
    {
        $this->NudgePropertyValue('json', $json);
    }
}
