<?php

declare(strict_types=1);

namespace Vecnavium\SkyBlocksPM\commands;

use CortexPE\Commando\BaseCommand;
use pocketmine\command\CommandSender;
use Vecnavium\SkyBlocksPM\commands\subcommands\AcceptSubCommand;
use Vecnavium\SkyBlocksPM\commands\subcommands\ChatSubCommand;
use Vecnavium\SkyBlocksPM\commands\subcommands\CreateSubCommand;
use Vecnavium\SkyBlocksPM\commands\subcommands\DeleteSubCommand;
use Vecnavium\SkyBlocksPM\commands\subcommands\InviteSubCommand;
use Vecnavium\SkyBlocksPM\commands\subcommands\KickSubCommand;
use Vecnavium\SkyBlocksPM\commands\subcommands\LeaveSubCommand;
use Vecnavium\SkyBlocksPM\commands\subcommands\SettingsSubCommand;
use Vecnavium\SkyBlocksPM\commands\subcommands\SetWorldCommand;
use Vecnavium\SkyBlocksPM\commands\subcommands\TpSubCommand;
use Vecnavium\SkyBlocksPM\commands\subcommands\VisitSubCommand;

class SkyBlockCommand extends BaseCommand {

    public function prepare(): void {
        $this->setPermission('skyblockspm.command');
        $this->registerSubCommand(new AcceptSubCommand('accept', 'Accept the incoming invite to a SkyBlock Island'));
        $this->registerSubCommand(new ChatSubCommand('chat', 'Chat with your SkyBlock Island members'));
        $this->registerSubCommand(new CreateSubCommand('create', 'Create your own SkyBlock Island'));
        $this->registerSubCommand(new DeleteSubCommand('delete', 'Delete a users SkyBlock Island', ['disband']));
        $this->registerSubCommand(new KickSubCommand('kick', 'Kick a member from your SkyBlock Island'));
        $this->registerSubCommand(new LeaveSubCommand('leave', 'Leave your current SkyBlock Island'));
        $this->registerSubCommand(new SettingsSubCommand('settings', 'Edit your SkyBlock Island settings'));
        $this->registerSubCommand(new SetWorldCommand('setworld', 'Sets the current world as the SkyBlock World which will be copied to newer worlds upon Island creation'));
        $this->registerSubCommand(new TpSubCommand('tp', 'Teleport to a users SkyBlock Island', ['go', 'home']));
        $this->registerSubCommand(new InviteSubCommand('invite', 'Invites the player to your SkyBlock Island'));
        $this->registerSubCommand(new VisitSubCommand('visit', 'Visit a players SkyBlock Island'));
    }

    public function onRun(CommandSender $sender, string $aliasUsed, array $args): void {
        $this->sendUsage();
    }
}
