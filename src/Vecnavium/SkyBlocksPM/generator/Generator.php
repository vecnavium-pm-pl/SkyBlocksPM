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
        $zip = new ZipArchive();
        $islandZip = SkyBlocksPM::getInstance()->getDataFolder() . "island.zip";

        if (is_file($islandZip))
            unlink($islandZip);

        $zip->open($islandZip, ZipArchive::CREATE | ZipArchive::OVERWRITE);

        $files = new RecursiveIteratorIterator(new RecursiveDirectoryIterator(realpath($worldPath)), RecursiveIteratorIterator::LEAVES_ONLY);

        /** @var SplFileInfo $file */
        foreach ($files as $file)
        {
            if (!is_file($file))
                continue;
            $filePath = $file->getPath() . DIRECTORY_SEPARATOR . $file->getBasename();
            $localPath = substr($filePath, strlen(SkyBlocksPM::getInstance()->getServer()->getDataPath() . 'worlds' . DIRECTORY_SEPARATOR . $world->getFolderName()));
            $zip->addFile($filePath, $localPath);
        }

        $zip->close();
    }

    /**
     * @param Player $player
     * @param string $folderName
     *
     * Thanks SkyWars by GamakCZ
     */
    public function generateIsland(Player $player, string $folderName)
    {
        $zip = SkyBlocksPM::getInstance()->getDataFolder() . 'island.zip';

        if (!is_file($zip))
        {
            $player->sendMessage("Default islands is not selected yet, please use the command '/is setworld' to set the default island world");
            return;
        }

        $zipArchive = new ZipArchive();
        $zipArchive->open($zip);
        $path = SkyBlocksPM::getInstance()->getServer()->getDataPath() . "worlds" . DIRECTORY_SEPARATOR . $folderName;
        @mkdir($path);
        $zipArchive->extractTo($path);
        $zipArchive->close();

        SkyBlocksPM::getInstance()->getServer()->getWorldManager()->loadWorld($folderName);
        $world = SkyBlocksPM::getInstance()->getServer()->getWorldManager()->getWorldByName($folderName);
        $player->teleport(Position::fromObject($world->getSpawnLocation(), $world));
    }

}
