<?php
declare(strict_types=1);

namespace Vecnavium\SkyBlocksPM\config\database;

use libMarshal\attributes\Field;
use libMarshal\MarshalTrait;

class SQLiteConfig{
    use MarshalTrait;

    #[Field]
    public string $file = 'players.sqlite';
}