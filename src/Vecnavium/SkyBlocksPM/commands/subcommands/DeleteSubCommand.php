<?php

declare(strict_types=1);

namespace Vecnavium\SkyBlocksPM\commands\subcommands;

use CortexPE\Commando\args\RawStringArgument;
use CortexPE\Commando\BaseSubCommand;
use pocketmine\command\CommandSender;
use pocketmine\player\Player as P;
use pocketmine\utils\Filesystem;
use pocketmine\utils\Utils;
use pocketmine\world\World;
use Vecnavium\SkyBlocksPM\player\Player;
use Vecnavium\SkyBlocksPM\skyblock\SkyBlock;
use Vecnavium\SkyBlocksPM\SkyBlocksPM;
use function strval;

class DeleteSubCommand extends BaseSubCommand {
    
    protected function prepare(): void {
        $this->setPermission('skyblockspm.delete');
        $this->registerArgument(0, new RawStringArgument('name'));
    }

    /**
     * @param CommandSender $sender
     * @param string $aliasUsed
     * @param array $args
     * @return void
     *
     * @phpstan-ignore-next-line
     */
    public function onRun(CommandSender $sender, string $aliasUsed, array $args): void {
        /** @var SkyBlocksPM $plugin */
        $plugin = $this->getOwningPlugin();
        
        $name = strval($args['name']);
        if ($name !== $sender->getName() && !$sender->hasPermission('skyblockspm.deleteothers')) {
            $sender->sendMessage($plugin->getMessages()->getMessage('no-perms-delete'));
            return;
        }
        $skyblockPlayer = $plugin->getPlayerManager()->getPlayer($name);
        if (!$skyblockPlayer instanceof Player) {
            $sender->sendMessage($plugin->getMessages()->getMessage('player-not-online'));
            return;
        }
        if ($skyblockPlayer->getSkyBlock() == '') {
            $sender->sendMessage($plugin->getMessages()->getMessage('no-island'));
            return;
        }
        $defaultWorld = $plugin->getServer()->getWorldManager()->getDefaultWorld();
        if(!$defaultWorld instanceof World) return;

        $skyblock = $plugin->getSkyBlockManager()->getSkyBlockByUuid($skyblockPlayer->getSkyBlock());
        if(!$skyblock instanceof SkyBlock) return;

        foreach ($skyblock->getMembers() as $member) {
            $player = $plugin->getServer()->getPlayerExact($member);
            if ($player instanceof P) {
                $player->teleport($defaultWorld->getSpawnLocation());
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
        if($world instanceof World) {
            foreach ($world->getPlayers() as $p) {
                $p->teleport($defaultWorld->getSpawnLocation());
            }
            if ($world->isLoaded()) {
                $folderName = $world->getFolderName();
                $plugin->getServer()->getWorldManager()->unloadWorld($world);
                Filesystem::recursiveUnlink($plugin->getServer()->getDataPath() . 'worlds' . DIRECTORY_SEPARATOR . $folderName);
            }
        }
        $sender->sendMessage($plugin->getMessages()->getMessage('deleted-sb', [
            '{NAME}' => $skyblockPlayer->getName()
        ]));
    }
}
