<?php
declare(strict_types=1);

namespace Vecnavium\SkyBlocksPM\config\settings;

use libMarshal\attributes\Field;
use libMarshal\MarshalTrait;

class DamageConfig{
    use MarshalTrait;

    #[Field]
    public bool $default = true;
    #[Field]
    public bool $lava = true;
    #[Field]
    public bool $drown = true;
    #[Field]
    public bool $fall = false;
    #[Field]
    public bool $projectile = false;
    #[Field]
    public bool $fire = true;
    #[Field]
    public bool $void = false;
    #[Field]
    public bool $hunger = true;
}