<?php

declare(strict_types=1);

namespace Vecnavium\SkyBlocksPM\generator;

use pocketmine\player\Player as P;
use pocketmine\world\Position;
use pocketmine\world\World;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use SplFileInfo;
use Vecnavium\SkyBlocksPM\player\Player;
use Vecnavium\SkyBlocksPM\SkyBlocksPM;

class Generator {

    private SkyBlocksPM $plugin;
    
    public function __construct(SkyBlocksPM $plugin) {
        $this->plugin = $plugin;
    }

    /**
     * @param P $player
     * @return void
     *
     * Thanks SkyWars by GamakCZ
     */
    public function setIslandWorld(P $player): void {
        $world = $player->getWorld();
        $defaultWorld = $this->plugin->getServer()->getWorldManager()->getDefaultWorld();
        if(!$defaultWorld instanceof World) return;

        $world->setSpawnLocation($player->getPosition());
        $worldPath = $this->plugin->getServer()->getDataPath() . 'worlds' . DIRECTORY_SEPARATOR . $world->getFolderName();

        if ($world->getDisplayName() === $defaultWorld->getDisplayName()) {
            $player->sendMessage($this->plugin->getMessages()->getMessage('default-world'));
            return;
        }

        $files = new RecursiveIteratorIterator(new RecursiveDirectoryIterator((string)realpath($worldPath)), RecursiveIteratorIterator::LEAVES_ONLY);

        $player->teleport($defaultWorld->getSpawnLocation());
        $this->plugin->getServer()->getWorldManager()->unloadWorld($world);

        /** @var SplFileInfo $file */
        foreach ($files as $file) {
            if (!$file->isFile()) continue;

            $filePath = $file->getPath() . DIRECTORY_SEPARATOR . $file->getBasename();
            $localPath = substr($filePath, strlen($this->plugin->getServer()->getDataPath() . 'worlds' . DIRECTORY_SEPARATOR . $world->getFolderName()));
            @mkdir($this->plugin->getDataFolder() . 'cache/island/db');
            copy($filePath, $this->plugin->getDataFolder() . 'cache/island/' . $localPath);
        }

    }

    /**
     * @param P $player
     * @param string $folderName
     * @param string $name
     *
     * Thanks SkyWars by GamakCZ
     */
    public function generateIsland(P $player, string $folderName, string $name): void{
        $path = $this->plugin->getDataFolder() . 'cache/island';
        $files = new RecursiveIteratorIterator(new RecursiveDirectoryIterator((string)realpath($path)), RecursiveIteratorIterator::LEAVES_ONLY);

        $path = $this->plugin->getServer()->getDataPath() . 'worlds' . DIRECTORY_SEPARATOR . $folderName;
        @mkdir($path);
        @mkdir($path . '/db');

        /** @var SplFileInfo $file */
        foreach ($files as $file) {
            $filePath = $file->getPath() . DIRECTORY_SEPARATOR . $file->getBasename();
            $localPath = substr($filePath, strlen($this->plugin->getDataFolder() . 'cache/island'));
            if ($file->isDir()) {
                @mkdir($path . $localPath);
                continue;
            }
            copy($filePath,  $path . DIRECTORY_SEPARATOR . $localPath);
        }

        $this->plugin->getServer()->getWorldManager()->loadWorld($folderName);
        $world = $this->plugin->getServer()->getWorldManager()->getWorldByName($folderName);
        $skyBlockPlayer = $this->plugin->getPlayerManager()->getPlayer($player->getName());
        if($world instanceof World && $skyBlockPlayer instanceof Player) {
            $player->teleport(Position::fromObject($world->getSpawnLocation(), $world));
            $this->plugin->getSkyBlockManager()->createSkyBlock($world->getFolderName(), $skyBlockPlayer, $name, $world);
        }
    }

}
