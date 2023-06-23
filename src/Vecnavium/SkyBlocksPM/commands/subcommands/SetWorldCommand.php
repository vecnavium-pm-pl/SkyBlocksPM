<?php

declare(strict_types=1);

namespace Vecnavium\SkyBlocksPM\commands\subcommands;

use CortexPE\Commando\BaseSubCommand;
use pocketmine\command\CommandSender;
use pocketmine\player\Player;
use Vecnavium\SkyBlocksPM\SkyBlocksPM;

class SetWorldCommand extends BaseSubCommand {

    protected function prepare(): void {
        $this->setPermission('skyblockspm.setworld');
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
        
        if (!$sender instanceof Player) return;

        $plugin->getGenerator()->setIslandWorld($sender);
    }

}
