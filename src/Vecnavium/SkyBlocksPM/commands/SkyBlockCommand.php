<?php

declare(strict_types=1);

namespace Vecnavium\SkyBlocksPM\commands;

use CortexPE\Commando\BaseCommand;
use pocketmine\command\CommandSender;
use pocketmine\plugin\PluginBase;
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
use Vecnavium\SkyBlocksPM\SkyBlocksPM;

class SkyBlockCommand extends BaseCommand {

    public function __construct(SkyBlocksPM $plugin){
        parent::__construct($plugin, 'skyblock', 'The core command for SkyBlocks', ['sb', 'is']);
    }

    public function prepare(): void {
        $this->setPermission('skyblockspm.command');
        $this->registerSubCommand(new AcceptSubCommand($this->getOwningPlugin(), 'accept', 'Accept the incoming invite to a SkyBlock Island'));
        $this->registerSubCommand(new ChatSubCommand($this->getOwningPlugin(), 'chat', 'Chat with your SkyBlock Island members'));
        $this->registerSubCommand(new CreateSubCommand($this->getOwningPlugin(), 'create', 'Create your own SkyBlock Island'));
        $this->registerSubCommand(new DeleteSubCommand($this->getOwningPlugin(), 'delete', 'Delete a users SkyBlock Island', ['disband']));
        $this->registerSubCommand(new KickSubCommand($this->getOwningPlugin(), 'kick', 'Kick a member from your SkyBlock Island'));
        $this->registerSubCommand(new LeaveSubCommand($this->getOwningPlugin(), 'leave', 'Leave your current SkyBlock Island'));
        $this->registerSubCommand(new SettingsSubCommand($this->getOwningPlugin(), 'settings', 'Edit your SkyBlock Island settings'));
        $this->registerSubCommand(new SetWorldCommand($this->getOwningPlugin(), 'setworld', 'Sets the current world as the SkyBlock World which will be copied to newer worlds upon Island creation'));
        $this->registerSubCommand(new TpSubCommand($this->getOwningPlugin(), 'tp', 'Teleport to a users SkyBlock Island', ['go', 'home']));
        $this->registerSubCommand(new InviteSubCommand($this->getOwningPlugin(), 'invite', 'Invites the player to your SkyBlock Island'));
        $this->registerSubCommand(new VisitSubCommand($this->getOwningPlugin(), 'visit', 'Visit a players SkyBlock Island'));
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
        $this->sendUsage();
    }
}
