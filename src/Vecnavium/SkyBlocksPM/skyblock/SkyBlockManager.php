<?php
declare(strict_types=1);

namespace Vecnavium\SkyBlocksPM\skyblock;

use pocketmine\math\Vector3;
use pocketmine\world\World;
use Vecnavium\SkyBlocksPM\player\Player;
use Vecnavium\SkyBlocksPM\SkyBlocksPM;
use function json_decode;

class SkyBlockManager {

    /** @var SkyBlock[] */
    private array $SkyBlocks = [];
    /** @var string[] */
    private array $worlds = [];

    public function __construct(private SkyBlocksPM $plugin) {}

    public function loadSkyblock(string $uuid): void {
        $this->plugin->getDataBase()->executeSelect(
            'skyblockspm.sb.load',
            [
                'uuid' => $uuid
            ],
            function (array $rows): void {
                if (count($rows) == 0) return;
                $row = $rows[0];
                if(isset($this->SkyBlocks[$row['uuid']])) return;
                /** @var array<string,int|float> $spawn */
                $spawn = (array)json_decode($row['spawn'], true);
                /** @var array<string,bool> $settings */
                $settings = (array)json_decode($row['settings'], true);
                $this->SkyBlocks[$row['uuid']] = new SkyBlock($row['uuid'], $row['name'], $row['leader'], explode(',', $row['members']), $row['world'], $settings, new Vector3($spawn['x'], $spawn['y'], $spawn['z']));
                $this->plugin->getServer()->getWorldManager()->loadWorld($row['world']);
                $this->worlds[] = $row['world'];
            }
        );
    }

    public function unloadSkyBlock(string $uuid): void{
        $skyblock = $this->getSkyBlockByUuid($uuid);
        if (!$skyblock instanceof SkyBlock) return;

        foreach ($skyblock->getMembers() as $member) {
            if ($this->plugin->getPlayerManager()->getPlayer($member) instanceof Player) return;
        }

        $world = $this->plugin->getServer()->getWorldManager()->getWorldByName($skyblock->getWorld());
        if(!$world instanceof World) return;
        $this->plugin->getServer()->getWorldManager()->unloadWorld($world);

        unset($this->SkyBlocks[$uuid]);
    }

    public function createSkyBlock(string $uuid, Player $player, string $name, World $world): void {
        $skyBlockPlayer = $this->plugin->getPlayerManager()->getPlayer($player->getName());
        if(!$skyBlockPlayer instanceof Player) return;

        $spawn = $world->getSpawnLocation();
        $SkyBlock = new SkyBlock($uuid, $name, $player->getName(), [$player->getName()], $world->getFolderName(), ['visit' => true, 'pvp' => false, 'interact_chest' => false, 'interact_door' => false,'pickup' => false, 'break' => false, 'place' => false], $spawn);
        $this->SkyBlocks[$uuid] = $SkyBlock;
        $this->worlds[] = $world->getFolderName();
        $this->plugin->getDataBase()->executeInsert('skyblockspm.sb.create', [
            'uuid' => $uuid,
            'name' => $name,
            'leader' => $player->getName(),
            'members' => implode(',', [$player->getName()]),
            'world' => $world->getFolderName(),
            'settings' => json_encode(['visit' => true, 'pvp' => false, 'interact_chest' => false, 'interact_door' => false,'pickup' => false, 'break' => false, 'place' => false]),
            'spawn' => json_encode([
                'x' => $spawn->getX(),
                'y' => $spawn->getY(),
                'z' => $spawn->getZ()
            ])
        ]);
        $skyBlockPlayer->setSkyBlock($uuid);
        $SkyBlock->save();
    }

    /**
     * @param string $uuid
     * @return SkyBlock|null
     */
    public function getSkyBlockByUuid(string $uuid): ?SkyBlock {
        return $this->SkyBlocks[$uuid] ?? null;
    }

    /**
     * @param string $name
     * @return SkyBlock|null
     */
    public function getSkyBlock(string $name): ?SkyBlock {
        foreach ($this->SkyBlocks as $SkyBlock) {
            if ($SkyBlock->getName() == $name) return $SkyBlock;
        }
        return null;
    }

    /**
     * @param World $world
     * @return SkyBlock|null
     */
    public function getSkyBlockByWorld(World $world): ?SkyBlock {
        foreach ($this->SkyBlocks as $SkyBlock) {
            if ($SkyBlock->getWorld() == $world->getFolderName()) return $SkyBlock;
        }
        return null;
    }

    /**
     * @param string $world
     * @return bool
     */
    public function isSkyBlockWorld(string $world): bool {
        if (in_array($world, $this->worlds, true)) return true;
        return false;
    }

    public function deleteSkyBlock(string $uuid): void {
        unset($this->SkyBlocks[$uuid]);
        $this->plugin->getDataBase()->executeGeneric(
            'skyblockspm.sb.delete', [
                'uuid' => $uuid
            ]
        );
    }
}
