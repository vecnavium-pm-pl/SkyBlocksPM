<?php

declare(strict_types=1);

namespace Vecnavium\SkyBlocksPM\commands\subcommands;

use CortexPE\Commando\args\RawStringArgument;
use CortexPE\Commando\BaseSubCommand;
use pocketmine\command\CommandSender;
use pocketmine\player\Player as P;
use Vecnavium\SkyBlocksPM\player\Player;
use Vecnavium\SkyBlocksPM\SkyBlocksPM;

class DeleteSubCommand extends BaseSubCommand {
    
    protected function prepare(): void {
        $this->setPermission('skyblockspm.delete');
        $this->registerArgument(0, new RawStringArgument('name'));
    }
    
    public function onRun(CommandSender $sender, string $aliasUsed, array $args): void {
        /** @var SkyBlocksPM $plugin */
        $plugin = $this->getOwningPlugin();
        
        $name = $args['name'];
        if ($name !== $sender->getName() && !$sender->hasPermission('skyblockspm.deleteothers')) {
            $sender->sendMessage($plugin->getMessages()->getMessage('no-perms-delete'));
            return;
        }
        $skyblockPlayer = $plugin->getPlayerManager()->getPlayer($name);
        if (!$skyblockPlayer instanceof Player) {
            $sender->sendMessage($plugin->getMessages()->getMessage('not-registered'));
            return;
        }
        if ($skyblockPlayer->getSkyBlock() == '') {
            $sender->sendMessage($plugin->getMessages()->getMessage('no-island'));
            return;
        }
        $skyblock = $plugin->getSkyBlockManager()->getSkyBlockByUuid($skyblockPlayer->getSkyBlock());
        foreach ($skyblock->getMembers() as $member) {
            $player = $plugin->getServer()->getPlayerExact($member);
            if ($player instanceof P) {
                $player->teleport($plugin->getServer()->getWorldManager()->getDefaultWorld()->getSpawnLocation());
            }
            if(($mPlayer = $plugin->getPlayerManager()->getPlayer($member)) instanceof Player) {
                $mPlayer->setSkyBlock('');
            } else {
                // Hacky but it works.
                $plugin->getPlayerManager()->deleteSkyBlockOffline($member);
            }
        }
        $plugin->getSkyBlockManager()->deleteSkyBlock($skyblock->getName());
        $world = $plugin->getServer()->getWorldManager()->getWorldByName($skyblock->getWorld());
        foreach ($world->getPlayers() as $p) {
            $p->teleport($plugin->getServer()->getWorldManager()->getDefaultWorld()->getSpawnLocation());
        }
        if ($world->isLoaded()) {
            $folderName = $world->getFolderName();
            $plugin->getServer()->getWorldManager()->unloadWorld($world);
            $this->deleteWorld($plugin->getServer()->getDataPath() . 'worlds' . DIRECTORY_SEPARATOR . $folderName);
        }
        $sender->sendMessage($plugin->getMessages()->getMessage('deleted-sb', [
            "{NAME}" => $skyblockPlayer->getName()
        ]));
    }

    public function deleteWorld(string $path): void {
        foreach (array_diff(scandir($path . DIRECTORY_SEPARATOR), ['..', '.']) as $file) {
            if (is_dir($path . DIRECTORY_SEPARATOR . $file)) {
                $this->deleteWorld($path . DIRECTORY_SEPARATOR . $file . DIRECTORY_SEPARATOR);
            } else {
                unlink($path . DIRECTORY_SEPARATOR . $file);
            }
        }
        rmdir($path);
    }

}
