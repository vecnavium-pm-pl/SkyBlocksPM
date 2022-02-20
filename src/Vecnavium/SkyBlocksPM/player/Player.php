<?php

declare(strict_types=1);

namespace Vecnavium\SkyBlocksPM\player;

use Vecnavium\SkyBlocksPM\SkyBlocksPM;

class Player
{

    /**@var string*/
    private string $uuid, $name, $skyblocks;

    public function __construct(string $uuid, string $name, string $skyblocks)
    {
        $this->uuid = $uuid;
        $this->name = $name;
        $this->skyblocks = $skyblocks;
    }

    /**
     * @return string
     */
    public function getUuid(): string
    {
        return $this->uuid;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getSkyBlock(): string
    {
        return $this->skyblocks;
    }

    public function setSkyBlock(string $skyblock): void
    {
        $this->skyblocks = $skyblock;
        $this->save();
    }

    public function save(): void
    {
        SkyBlocksPM::getInstance()->getDataBase()->executeChange('skyblockspm.player.update', [
            'uuid' => $this->uuid,
            'name' => $this->name,
            'skyblock' => $this->skyblocks
        ]);
        SkyBlocksPM::getInstance()->getDataBase()->waitAll();
    }

}
