<?php

declare(strict_types=1);

namespace Vecnavium\SkyBlocksPM\player;

use Vecnavium\SkyBlocksPM\SkyBlocksPM;

class Player {

    public function __construct(
        private string $uuid,
        private string $name,
        private string $skyblocks
    ) {}

    /**
     * @return string
     */
    public function getUuid(): string {
        return $this->uuid;
    }

    /**
     * @return string
     */
    public function getName(): string {
        return $this->name;
    }

    public function setName(string $name): void {
        $this->name = $name;
        $this->save();
    }

    /**
     * @return string
     */
    public function getSkyBlock(): string {
        return $this->skyblocks;
    }

    public function setSkyBlock(string $skyblock): void {
        $this->skyblocks = $skyblock;
        $this->save();
    }

    public function save(): void {
        SkyBlocksPM::getInstance()->getDataBase()->executeChange('skyblockspm.player.update', [
            'uuid' => $this->uuid,
            'name' => $this->name,
            'skyblock' => $this->skyblocks
        ]);
        SkyBlocksPM::getInstance()->getDataBase()->waitAll();
    }
}
