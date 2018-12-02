<?php
/**
* @author SignpostMarv
*/
declare(strict_types=1);

namespace SignpostMarv\DaftObject\Tests;

use SignpostMarv\DaftObject\LinkedData\HasId;

require_once(__DIR__ . '/../vendor/autoload.php');

$foo = new HasId(['@id' => 'foo']);

echo var_export([
    json_encode($foo),
    HasId::DaftObjectFromJsonString('{"@id":"foo"}'),
], true); exit;
