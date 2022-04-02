<?php

namespace Vecnavium\SkyBlocksPM\commands\subcommands;

use Vecnavium\SkyBlocksPM\libs\CortexPE\Commando\BaseSubCommand;
use Vecnavium\SkyBlocksPM\SkyBlocksPM;
use pocketmine\player\Player as P;
use pocketmine\command\CommandSender;

class ChatSubCommand extends BaseSubCommand
{

    protected function prepare(): void
    {
        $this->setPermission("skyblockspm.chat");
    }

    public function onRun(CommandSender $sender, string $aliasUsed, array $args): void
    {
        if (!$sender instanceof P)
            return;
        if (!in_array($sender->getName(), SkyBlocksPM::getInstance()->getChat()))
            SkyBlocksPM::getInstance()->addPlayerToChat($sender);
        else
            SkyBlocksPM::getInstance()->removePlayerFromChat($sender);
        $sender->sendMessage(SkyBlocksPM::getInstance()->getMessages()->getMessage("toggle-chat"));
    }

}
