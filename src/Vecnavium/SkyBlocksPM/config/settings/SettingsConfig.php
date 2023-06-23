<?php
declare(strict_types=1);

namespace Vecnavium\SkyBlocksPM\config\settings;

use libMarshal\attributes\Field;
use libMarshal\MarshalTrait;

class SettingsConfig{
    use MarshalTrait;

    #[Field]
    public AutoInvConfig $autoinv;
    #[Field]
    public bool $autoxp = true;
    #[Field(name: 'invite-timeout')]
    public int $inviteTimeout = 30;
    #[Field(name: 'max-members')]
    public int $maxMembers = 5;
    #[Field]
    public DamageConfig $damage;
}