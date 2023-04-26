<?php

declare(strict_types=1);

namespace Vecnavium\SkyBlocksPM\commands\subcommands;

use CortexPE\Commando\BaseSubCommand;
use pocketmine\command\CommandSender;
use pocketmine\player\Player as P;
use pocketmine\world\Position;
use Vecnavium\SkyBlocksPM\player\Player;
use Vecnavium\SkyBlocksPM\skyblock\SkyBlock;
use Vecnavium\SkyBlocksPM\SkyBlocksPM;

class TpSubCommand extends BaseSubCommand {

    protected function prepare(): void {
        $this->setPermission('skyblockspm.tp');
    }

    /**
     * @param CommandSender $sender
     * @param string $aliasUsed
     * @param array<string,mixed> $args
     * @return void
     */
    public function onRun(CommandSender $sender, string $aliasUsed, array $args): void {
        /** @var SkyBlocksPM $plugin */
        $plugin = $this->getOwningPlugin();
        $skyblockPlayer = $plugin->getPlayerManager()->getPlayer($sender->getName());
        
        if (!$sender instanceof P || !$skyblockPlayer instanceof Player) return;

        if ($skyblockPlayer->getSkyBlock() == '') {
            $sender->sendMessage($plugin->getMessages()->getMessage('no-sb-go'));
            return;
        }
        $skyblockIsland = $plugin->getSkyBlockManager()->getSkyBlockByUuid($skyblockPlayer->getSkyBlock());
        if(!$skyblockIsland instanceof SkyBlock) return;

        $sender->teleport(Position::fromObject($skyblockIsland->getSpawn()->up(), $plugin->getServer()->getWorldManager()->getWorldByName($skyblockIsland->getWorld())));
    }
}
