<?php

declare(strict_types=1);

namespace Vecnavium\SkyBlocksPM\commands;

use Vecnavium\SkyBlocksPM\commands\subcommands\ChatSubCommand;
use Vecnavium\SkyBlocksPM\commands\subcommands\SetWorldCommand;
use Vecnavium\SkyBlocksPM\libs\CortexPE\Commando\BaseCommand;
use Vecnavium\SkyBlocksPM\commands\subcommands\AcceptSubCommand;
use Vecnavium\SkyBlocksPM\commands\subcommands\CreateSubCommand;
use Vecnavium\SkyBlocksPM\commands\subcommands\DeleteSubCommand;
use Vecnavium\SkyBlocksPM\commands\subcommands\TpSubCommand;
use Vecnavium\SkyBlocksPM\commands\subcommands\InviteSubCommand;
use Vecnavium\SkyBlocksPM\commands\subcommands\VisitSubCommand;
use pocketmine\command\CommandSender;

class SkyBlockCommand extends BaseCommand
{

    public function prepare(): void
    {
        $this->setPermission('skyblockspm.command');
        $this->registerSubCommand(new AcceptSubCommand('accept', 'Accept the incoming invite to a SkyBlock island'));
        $this->registerSubCommand(new ChatSubCommand('chat', 'Chat with your island members'));
        $this->registerSubCommand(new CreateSubCommand('create', 'Create your own SkyBlock island'));
        $this->registerSubCommand(new DeleteSubCommand('delete', 'Delete a users SkyBlock Island'));
        $this->registerSubCommand(new SetWorldCommand('setworld', 'Sets the current world as the SkyBlock World which will be copied to newer worlds upon Island creation'));
        $this->registerSubCommand(new TpSubCommand('tp', 'Teleport to a users SkyBlock Island'));
        $this->registerSubCommand(new InviteSubCommand('invite', 'Invites the player to your SkyBlock island'));
        $this->registerSubCommand(new VisitSubCommand('visit', 'Visit a players SkyBlock island'));

    }

    public function onRun(CommandSender $sender, string $aliasUsed, array $args): void
    {
    }

}
