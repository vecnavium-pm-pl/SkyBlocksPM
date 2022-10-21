<?php

declare(strict_types=1);

namespace Vecnavium\SkyblocksPM\generator;

use pocketmine\player\Player;
use pocketmine\world\Position;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use SplFileInfo;
use Vecnavium\SkyblocksPM\SkyBlocksPM;
use ZipArchive;

class Generator
{

    /**
     * @param Player $player
     * @return void
     *
     * Thanks SkyWars by GamakCZ
     */
    public function setIslandWorld(Player $player): void
    {
        $world = $player->getWorld();
        $world->setSpawnLocation($player->getPosition());
        $worldPath = SkyBlocksPM::getInstance()->getServer()->getDataPath() . "worlds" . DIRECTORY_SEPARATOR . $world->getFolderName();

        if ($world->getDisplayName() === SkyBlocksPM::getInstance()->getServer()->getWorldManager()->getDefaultWorld()->getDisplayName())
        {
            $player->sendMessage(SkyBlocksPM::getInstance()->getMessages()->getMessage('default-world'));
            return;
        }

        $files = new RecursiveIteratorIterator(new RecursiveDirectoryIterator(realpath($worldPath)), RecursiveIteratorIterator::LEAVES_ONLY);

        $player->teleport(SkyBlocksPM::getInstance()->getServer()->getWorldManager()->getDefaultWorld()->getSpawnLocation());
        SkyBlocksPM::getInstance()->getServer()->getWorldManager()->unloadWorld($world);

        /** @var SplFileInfo $file */
        foreach ($files as $file)
        {
            if (!$file->isFile()) continue;

            $filePath = $file->getPath() . DIRECTORY_SEPARATOR . $file->getBasename();
            $localPath = substr($filePath, strlen(SkyBlocksPM::getInstance()->getServer()->getDataPath() . 'worlds' . DIRECTORY_SEPARATOR . $world->getFolderName()));
            @mkdir(SkyBlocksPM::getInstance()->getDataFolder() . "cache/island/db");
            copy($filePath, SkyBlocksPM::getInstance()->getDataFolder() . "cache/island/" . $localPath);
        }

    }

    /**
     * @param Player $player
     * @param string $folderName
     * @param string $name
     *
     * Thanks SkyWars by GamakCZ
     */
    public function generateIsland(Player $player, string $folderName, string $name)
    {
        $path = SkyBlocksPM::getInstance()->getDataFolder() . "cache/island";
        $files = new RecursiveIteratorIterator(new RecursiveDirectoryIterator(realpath($path)), RecursiveIteratorIterator::LEAVES_ONLY);

        $path = SkyBlocksPM::getInstance()->getServer()->getDataPath() . "worlds" . DIRECTORY_SEPARATOR . $folderName;
        @mkdir($path);
        @mkdir($path . "/db");

        /** @var SplFileInfo $file */
        foreach ($files as $file)
        {
            $filePath = $file->getPath() . DIRECTORY_SEPARATOR . $file->getBasename();
            $localPath = substr($filePath, strlen(SkyBlocksPM::getInstance()->getDataFolder() . 'cache/island'));
            if ($file->isDir())
            {
                @mkdir($path . $localPath);
                continue;
            }
            copy($filePath,  $path . DIRECTORY_SEPARATOR . $localPath);
        }

        SkyBlocksPM::getInstance()->getServer()->getWorldManager()->loadWorld($folderName);
        $world = SkyBlocksPM::getInstance()->getServer()->getWorldManager()->getWorldByName($folderName);
        $player->teleport(Position::fromObject($world->getSpawnLocation(), $world));
        SkyBlocksPM::getInstance()->getSkyBlockManager()->createSkyBlock($world->getFolderName(), SkyBlocksPM::getInstance()->getPlayerManager()->getPlayer($player), "", $world);
    }

}
