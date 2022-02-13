<?php

declare(strict_types=1);

namespace Vecnavium\SkyBlocksPM\generator;

use Vecnavium\SkyBlocksPM\SkyBlocksPM;
use pocketmine\world\generator\Flat;
use pocketmine\world\WorldCreationOptions;

class Generator
{

    public function generateWorld(string $name): void
    {
        $wco = new WorldCreationOptions();
        $wco->setGeneratorClass(Flat::class);
        $wco->setGeneratorOptions("2;64x0");
        SkyBlocksPM::getInstance()->getServer()->getWorldManager()->generateWorld($name, $wco);
    }

}