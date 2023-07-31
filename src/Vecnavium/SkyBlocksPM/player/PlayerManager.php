<?php

declare(strict_types=1);

namespace Vecnavium\SkyBlocksPM\player;

use pocketmine\player\Player as P;
use Vecnavium\SkyBlocksPM\SkyBlocksPM;
use function substr;

class PlayerManager {

    /** @var Player[] */
    private array $players = [];
    
    public function __construct(private SkyBlocksPM $plugin) {}

    public function loadPlayer(P $player): void{
        $this->plugin->getDataBase()->executeSelect(
            'skyblockspm.player.load',
            [
                'uuid' => $player->getUniqueId()->toString()
            ],
            function (array $rows) use ($player): void {
                if (count($rows) == 0) {
                    $this->createPlayer($player);
                    return;
                }
                $name = $player->getName();
                $this->players[$name] = new Player($rows[0]['uuid'], $rows[0]['name'], $rows[0]['skyblock']);
                if ($name !== $rows[0]['name']) {
                    $this->getPlayer($name)?->setName($name);
                }
                $this->plugin->getSkyBlockManager()->loadSkyblock($rows[0]['skyblock']);
            }
        );
    }

    public function unloadPlayer(P $player): void{
        $skyBlockPlayer = $this->getPlayer($player->getName());

        if($skyBlockPlayer instanceof Player) {
            $this->plugin->getSkyBlockManager()->unloadSkyBlock($skyBlockPlayer->getSkyBlock());
        }

        if(isset($this->players[$player->getName()])) {
            unset($this->players[$player->getName()]);
        }
    }

    public function createPlayer(P $player): void {
        // Hotfix for those with pre-existing SkyBlocksPM databases where the UUID length is specified as 32, and not 36
        $uuid = substr($player->getUniqueId()->toString(), 0, -4);
        $this->plugin->getDataBase()->executeInsert('skyblockspm.player.create',
        [
            'uuid' => $uuid,
            'name' => $player->getName(),
            'skyblock' => ''
        ]);
        $this->players[$player->getName()] = new Player($uuid, $player->getName(), '');
    }

    /**
     * @param string $name
     * @return Player|null
     * @phpstan-return Player|null
     */
    public function getPlayer(string $name): ?Player {
        return $this->players[$name] ?? null;
    }

    /**
     * This is used for Skyblock members that are offline when the Skyblock is deleted by the leader.
     *
     * @param string $name
     * @param string $skyblock
     * @return void
     */
    public function deleteSkyBlockOffline(string $name, string $skyblock = ''): void{
        $this->plugin->getDataBase()->executeGeneric(
            'skyblockspm.sb.delete_offline', [
                'name' => $name,
                'skyblock' => $skyblock
            ]
        );
    }
}
