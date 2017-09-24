<?php
/**
* @author SignpostMarv
*/
declare(strict_types=1);

namespace SignpostMarv\DaftObject;

final class PasswordHashTestObject extends AbstractArrayBackedDaftObject
{
    const PROPERTIES = [
        'password',
        'passwordHash',
    ];

    protected function GetPassword() : void
    {
    }

    protected function SetPasswordHash(string $hash) : void
    {
        $this->NudgePropertyValue('passwordHash', $hash);
        $this->NudgePropertyValue('password', '');
    }

    public function SetPassword(string $password) : void
    {
        $this->SetPasswordHash(password_hash($password, PASSWORD_DEFAULT));
    }

    public function GetPasswordHash() : string
    {
        return $this->RetrievePropertyValueFromData('passwordHash');
    }
}
