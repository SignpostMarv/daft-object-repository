<?php
/**
* Base daft objects.
*
* @author SignpostMarv
*/
declare(strict_types=1);

namespace SignpostMarv\DaftObject;

interface DaftObjectRepository
{
    public function RememberDaftObject(
        DefinesOwnIdPropertiesInterface $object
    ) : void;

    public function RememberAllNotForgotten() : void;

    public function RetrieveById($id) : DefinesOwnIdPropertiesInterface;

    public function RetrieveDaftObject(
        DefinesOwnIdPropertiesInterface $object
    ) : DefinesOwnIdPropertiesInterface;

    public function ForgetById($id) : void;

    public function ForgetDaftObject(
        DefinesOwnIdPropertiesInterface $object
    ) : void;

    public function RemoveById($id) : void;

    public function RemoveDaftObject(
        DefinesOwnIdPropertiesInterface $object
    ) : void;

    public function UpdateDaftObject(
        DefinesOwnIdPropertiesInterface $object
    ) : void;

    public static function GetRepositoryForDaftObject(
        DefinesOwnIdPropertiesInterface $object
    ) : DaftObjectRepository;

    public static function GetRepositoryForImplementation(
        string $implementation
    ) : DaftObjectRepository;
}
