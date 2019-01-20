<?php
/**
* @author SignpostMarv
*/
declare(strict_types=1);

namespace SignpostMarv\DaftObject\Tests\DefinitionAssistant;

use SignpostMarv\DaftObject\AbstractDaftObject;

class DefinesPropertyOnInterfaceClassImplementation extends AbstractDaftObject implements DefinesPropertyOnInterface
{
    /**
    * @var string|null
    */
    protected $foo = '';

    /**
    * @var string|null
    */
    protected $was = '';

    public function __isset(string $k) : bool
    {
        return 'foo' === $k;
    }

    public function __unset(string $k) : void
    {
        $this->__set('foo', null);
    }

    public function GetFoo() : string
    {
        return (string) $this->foo;
    }

    public function SetFoo(string $value) : void
    {
        $was = $this->foo;
        $this->foo = $value;
    }

    public function ChangedProperties() : array
    {
        return $this->HasPropertyChanged('foo') ? ['foo'] : [];
    }

    public function HasPropertyChanged(string $property) : bool
    {
        return 'foo' === $property && $this->foo !== $this->was;
    }

    public function MakePropertiesUnchanged(string ...$properties) : void
    {
        if (in_array('foo', $properties, true)) {
            $this->was = $this->foo;
        }
    }

    protected function NudgePropertyValue(string $property, $value) : void
    {
        if ($this->__isset($property)) {
            $this->__set($property, $value);
        }
    }
}
