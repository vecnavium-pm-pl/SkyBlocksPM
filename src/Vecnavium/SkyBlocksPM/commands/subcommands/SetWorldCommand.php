<?php

declare(strict_types=1);

namespace Vecnavium\SkyBlocksPM\commands\subcommands;

use pocketmine\command\CommandSender;
use Vecnavium\SkyBlocksPM\libs\CortexPE\Commando\BaseSubCommand;
use pocketmine\player\Player;
use Vecnavium\SkyBlocksPM\SkyBlocksPM;

class SetWorldCommand extends BaseSubCommand
{

    protected function prepare(): void
    {
        $this->setPermission('skyblockspm.setworld');
    }

    public function onRun(CommandSender $sender, string $aliasUsed, array $args): void
    {
        if (!$sender instanceof Player)
            return;
        SkyBlocksPM::getInstance()->getGenerator()->setIslandWorld($sender);
    }

}
