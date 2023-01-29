<?php

namespace Vecnavium\SkyBlocksPM\commands\subcommands;

use Vecnavium\SkyBlocksPM\libs\CortexPE\Commando\BaseSubCommand;
use Vecnavium\SkyBlocksPM\SkyBlocksPM;
use pocketmine\player\Player as P;
use pocketmine\command\CommandSender;

class ChatSubCommand extends BaseSubCommand {

    protected function prepare(): void {
        $this->setPermission("skyblockspm.chat");
    }

    public function onRun(CommandSender $sender, string $aliasUsed, array $args): void {
        /** @var SkyBlocksPM $plugin */
        $plugin = $this->getOwningPlugin();
        
        if (!$sender instanceof P) return;

        if (!in_array($sender->getName(), $plugin->getChat()))
            $plugin->addPlayerToChat($sender);
        else
            $plugin->removePlayerFromChat($sender);
        $sender->sendMessage($plugin->getMessages()->getMessage("toggle-chat"));
    }

}
