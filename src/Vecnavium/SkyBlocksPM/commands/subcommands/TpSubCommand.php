<?php

declare(strict_types=1);

namespace Vecnavium\SkyBlocksPM\commands\subcommands;

use Vecnavium\SkyBlocksPM\libs\CortexPE\Commando\BaseSubCommand;
use Vecnavium\SkyBlocksPM\SkyBlocksPM;
use pocketmine\command\CommandSender;
use pocketmine\player\Player;
use pocketmine\world\Position;

class TpSubCommand extends BaseSubCommand {

    protected function prepare(): void {
        $this->setPermission('skyblockspm.tp');
    }

    public function onRun(CommandSender $sender, string $aliasUsed, array $args): void {
        /** @var SkyBlocksPM $plugin */
        $plugin = $this->getOwningPlugin();
        
        if (!$sender instanceof Player) return;

        $skyblock = $plugin->getPlayerManager()->getPlayer($sender)->getSkyblock();
        if ($skyblock == '') {
            $sender->sendMessage($plugin->getMessages()->getMessage('no-sb-go'));
            return;
        }
        $spawn = $plugin->getSkyBlockManager()->getSkyBlockByUuid($skyblock)->getSpawn();
        $sender->teleport(Position::fromObject($spawn->up(), $plugin->getServer()->getWorldManager()->getWorldByName($plugin->getSkyBlockManager()->getSkyBlockByUuid($skyblock)->getWorld())));
    }
}
