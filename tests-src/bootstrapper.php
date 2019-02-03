<?php
/**
* @author SignpostMarv
*/
declare(strict_types=1);

namespace SignpostMarv\DaftObject;

if (DefinitionAssistant::IsTypeUnregistered(DefinesOwnIdPropertiesInterface::class)) {
    DefinitionAssistant::RegisterType(
        DefinesOwnIdPropertiesInterface::class,
        DefinitionAssistant::SetterOrGetterClosure(
            DefinesOwnIdPropertiesInterface::class,
            DefinitionAssistant::BOOL_EXPECTING_GETTER,
            'id'
        ),
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
