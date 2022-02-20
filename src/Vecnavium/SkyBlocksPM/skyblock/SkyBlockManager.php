<?php

declare(strict_types=1);

namespace Vecnavium\SkyBlocksPM\skyblock;

use Vecnavium\SkyBlocksPM\SkyBlocksPM;
use Vecnavium\SkyBlocksPM\player\Player;
use pocketmine\math\Vector3;
use pocketmine\world\World;

class SkyBlockManager
{

    /**@var SkyBlock[]*/
    private array $SkyBlocks = [];
    /** @var array */
    private array $worlds = [];

    public function loadSkyblock(string $uuid): void
    {
        SkyBlocksPM::getInstance()->getDataBase()->executeSelect(
            'skyblockspm.sb.load',
            [
                'uuid' => $uuid
            ],
            function (array $rows): void
            {
                if (count($rows) == 0)
                    return;
                $row = $rows[0];
                if(isset($this->SkyBlocks[$row['uuid']]))
                    return;
                $spawn = (array)json_decode($row['spawn']);
                $this->SkyBlocks[$row['uuid']] = new SkyBlock($row['uuid'], $row['name'], $row['leader'], explode(',', $row['members']), $row['world'], (array)json_decode($row['settings']), new Vector3($spawn["x"], $spawn["y"], $spawn['z']));
                SkyBlocksPM::getInstance()->getServer()->getWorldManager()->loadWorld($row['world']);
                $this->worlds[] = $row['world'];
            }
        );
    }

    public function unloadSkyBlock(string $uuid)
    {
        $skyblock = $this->getSkyBlockByUuid($uuid);
        if (!$skyblock instanceof SkyBlock)
            return;
        foreach ($this->getSkyBlockByUuid($uuid)->getMembers() as $member)
        {
            if (SkyBlocksPM::getInstance()->getPlayerManager()->getPlayerByPrefix($member) instanceof Player)
                return;
        }
        SkyBlocksPM::getInstance()->getServer()->getWorldManager()->unloadWorld(SkyBlocksPM::getInstance()->getServer()->getWorldManager()->getWorldByName($this->getSkyBlockByUuid($uuid)->getWorld()));
        unset($this->SkyBlocks[$uuid]);
    }

    public function createSkyBlock(string $uuid, Player $player, string $name, World $world): void
    {
        $spawn = $world->getSpawnLocation();
        $SkyBlock = new SkyBlock($uuid, $name, $player->getName(), [$player->getName()], $world->getFolderName(), ['visit' => true, 'pvp' => false], $spawn);
        $this->SkyBlocks[$uuid] = $SkyBlock;
        $this->worlds[] = $world->getFolderName();
        SkyBlocksPM::getInstance()->getDataBase()->executeInsert('skyblockspm.sb.create', [
            'uuid' => $uuid,
            'name' => $name,
            'leader' => $player->getName(),
            'members' => implode(',', [$player->getName()]),
            'world' => $world->getFolderName(),
            'settings' => json_encode(['visit' => true, 'pvp' => false]),
            'spawn' => json_encode([
                'x' => $spawn->getX(),
                'y' => $spawn->getY(),
                'z' => $spawn->getZ()
            ])
        ]);
        $skyBlockPlayer = SkyBlocksPM::getInstance()->getPlayerManager()->getPlayerByPrefix($player->getName());
        $skyBlockPlayer->setSkyBlock($uuid);
        $SkyBlock->save();
    }

    public function getSkyBlockByUuid(string $uuid): ?SkyBlock
    {
        return $this->SkyBlocks[$uuid] ?? null;
    }

    public function getSkyBlock(string $name): ?SkyBlock
    {
        foreach ($this->SkyBlocks as $SkyBlock)
        {
            if ($SkyBlock->getName() == $name)
                return $SkyBlock;
        }
        return null;
    }

    public function getSkyBlockByWorld(World $world): ?SkyBlock
    {
        foreach ($this->SkyBlocks as $SkyBlock)
        {
            if ($SkyBlock->getWorld() == $world->getFolderName())
                return $SkyBlock;
        }
        return null;
    }

    public function isSkyBlockWorld(string $world): bool
    {
        if (in_array($world, $this->worlds))
            return true;
        return false;
    }

    public function deleteSkyBlock(string $uuid): void
    {
        unset($this->SkyBlocks[$uuid]);
        SkyBlocksPM::getInstance()->getDataBase()->executeGeneric(
            'skyblockspm.sb.delete', [
                'uuid' => $uuid
            ]
        );
    }

}
