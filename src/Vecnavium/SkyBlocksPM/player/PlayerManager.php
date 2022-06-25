<?php

declare(strict_types=1);

namespace Vecnavium\SkyBlocksPM\player;

use Vecnavium\SkyBlocksPM\SkyBlocksPM;
use pocketmine\player\Player as P;

class PlayerManager
{

    /**@var Player[]*/
    private array $players = [];

    public function loadPlayer(P $player)
    {
        SkyBlocksPM::getInstance()->getDataBase()->executeSelect(
            'skyblockspm.player.load',
            [
                'uuid' => $player->getUniqueId()->toString()
            ],
            function (array $rows) use ($player): void
            {
                if (count($rows) == 0) {
                    $this->createPlayer($player);
                    return;
                }
                $this->players[$rows[0]['name']] = new Player($rows[0]['uuid'], $rows[0]['name'], $rows[0]['skyblock']);
                SkyBlocksPM::getInstance()->getSkyBlockManager()->loadSkyblock($rows[0]['skyblock']);
            }
        );
    }

    public function unloadPlayer(P $player)
    {
        SkyBlocksPM::getInstance()->getSkyBlockManager()->unloadSkyBlock($this->getPlayer($player)->getSkyBlock());
        unset($this->players[$player->getName()]);
    }

    public function createPlayer(P $player): void
    {
        SkyBlocksPM::getInstance()->getDataBase()->executeInsert('skyblockspm.player.create',
        [
            'uuid' => $player->getUniqueId()->toString(),
            'name' => $player->getName(),
            'skyblock' => ''
        ]);
        $this->players[$player->getName()] = new Player($player->getUniqueId()->toString(), $player->getName(), '');
    }

    public function getPlayer(P $player): Player
    {
        if ( isset($this->players[$player->getName()]) ) return $this->players[$player->getName()];
        $this->loadPlayer($player); // It should load eventually, right?
        return $this->getPlayer($player);
    }

    public function getPlayerByPrefix(string $name): ?Player
    {
        return $this->players[$name]?? null;
    }

}
