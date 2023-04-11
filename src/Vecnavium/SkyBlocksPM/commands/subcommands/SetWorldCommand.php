<?php

declare(strict_types=1);

namespace Vecnavium\SkyBlocksPM\commands\subcommands;

use pocketmine\command\CommandSender;
use CortexPE\Commando\BaseSubCommand;
use pocketmine\player\Player;
use Vecnavium\SkyBlocksPM\SkyBlocksPM;

class SetWorldCommand extends BaseSubCommand {

    protected function prepare(): void {
        $this->setPermission('skyblockspm.setworld');
    }

    public function onRun(CommandSender $sender, string $aliasUsed, array $args): void {
        /** @var SkyBlocksPM $plugin */
        $plugin = $this->getOwningPlugin();
        
        if (!$sender instanceof Player) return;

        $plugin->getGenerator()->setIslandWorld($sender);
    }

}
