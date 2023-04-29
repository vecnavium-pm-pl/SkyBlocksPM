<?php
declare(strict_types=1);

namespace Vecnavium\SkyBlocksPM\config;

use libMarshal\attributes\Field;
use libMarshal\MarshalTrait;
use Vecnavium\SkyBlocksPM\config\database\DatabaseConfig;
use Vecnavium\SkyBlocksPM\config\settings\SettingsConfig;

class Config{
    use MarshalTrait;

    #[Field]
    public DatabaseConfig $database;
    #[Field]
    public SettingsConfig $settings;
}