<?php
declare(strict_types=1);

namespace Vecnavium\SkyBlocksPM\config\settings;

use libMarshal\attributes\Field;
use libMarshal\MarshalTrait;

class AutoInvConfig{
    use MarshalTrait;

    #[Field]
    public bool $enabled = true;
    #[Field(name: 'drop-when-full')]
    public bool $dropWhenFull = true;
}