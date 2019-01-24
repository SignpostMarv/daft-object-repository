<?php
/**
* @author SignpostMarv
*/
declare(strict_types=1);

namespace SignpostMarv\DaftObject;

if (DefinitionAssistant::IsTypeUnregistered(DefinesOwnIdPropertiesInterface::class)) {
    DefinitionAssistant::RegisterType(
        DefinesOwnIdPropertiesInterface::class,
        DefinitionAssistant::GetterClosure(DefinesOwnIdPropertiesInterface::class, 'id'),
        null,
        'id'
    );
}

if (
    DefinitionAssistant::IsTypeUnregistered(
        Tests\DefinitionAssistant\DefinesPropertyOnInterfaceClassImplementation::class
    )
) {
    DefinitionAssistant::AutoRegisterType(
        Tests\DefinitionAssistant\DefinesPropertyOnInterfaceClassImplementation::class,
        'foo'
    );
}
