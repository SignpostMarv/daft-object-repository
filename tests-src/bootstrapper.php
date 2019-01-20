<?php
/**
* @author SignpostMarv
*/
declare(strict_types=1);

namespace SignpostMarv\DaftObject;

if (DefinitionAssistant::IsTypeUnregistered(DefinesOwnIdPropertiesInterface::class)) {
    DefinitionAssistant::RegisterType(DefinesOwnIdPropertiesInterface::class, [
        'id',
    ]);
}

if (
    DefinitionAssistant::IsTypeUnregistered(
        Tests\DefinitionAssistant\DefinesPropertyOnInterfaceClassImplementation::class
    )
) {
    DefinitionAssistant::RegisterType(
        Tests\DefinitionAssistant\DefinesPropertyOnInterfaceClassImplementation::class,
        [
            'foo',
        ]
    );
}
