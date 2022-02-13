<?php

declare(strict_types=1);

namespace Vecnavium\SkyBlocksPM\commands\subcommands;

use Vecnavium\SkyBlocksPM\libs\CortexPE\Commando\args\RawStringArgument;
use Vecnavium\SkyBlocksPM\libs\CortexPE\Commando\BaseSubCommand;
use Vecnavium\SkyBlocksPM\SkyBlocksPM;
use Vecnavium\SkyBlocksPM\skyblock\SkyBlock;
use pocketmine\command\CommandSender;
use pocketmine\player\Player;
use pocketmine\scheduler\ClosureTask;
use Ramsey\Uuid\Uuid;

class InviteSubCommand extends BaseSubCommand
{

    protected function prepare(): void
    {
        $this->setPermission('skyblockspm.invite');
        $this->registerArgument(0, new RawStringArgument('name'));
    }

    public function onRun(CommandSender $sender, string $aliasUsed, array $args): void
    {
        if (!$sender instanceof Player)
            return;
        if (!SkyBlocksPM::getInstance()->getInviteManager()->canInvite($sender))
        {
            $sender->sendMessage(SkyBlocksPM::getInstance()->getMessages()->getMessage('invite-pending'));
            return;
        }
        $player = SkyBlocksPM::getInstance()->getServer()->getPlayerByPrefix($args['name']);
        $skyblockPlayer = SkyBlocksPM::getInstance()->getPlayerManager()->getPlayerByPrefix($sender->getName());
        $skyblock = SkyBlocksPM::getInstance()->getSkyBlockManager()->getSkyBlock($skyblockPlayer->getSkyBlock());
        if (!$skyblock instanceof SkyBlock)
        {
            $sender->sendMessage(SkyBlocksPM::getInstance()->getMessages()->getMessage('no-sb'));
            return;
        }
        if (count($skyblock->getMembers()) >= SkyBlocksPM::getInstance()->getConfig()->getNested('settings.max-members'))
        {
            $sender->sendMessage(SkyBlocksPM::getInstance()->getMessages()->getMessage('member-limit'));
            return;
        }
        if (!$player instanceof Player)
        {
            SkyBlocksPM::getInstance()->getMessages()->getMessage('player-not-online');
            return;
        }
        if ($sender === $player)
            return;
        $id =  Uuid::uuid4()->toString();
        SkyBlocksPM::getInstance()->getInviteManager()->addInvite($id, $sender, $player);
        $player->sendMessage(SkyBlocksPM::getInstance()->getMessages()->getMessage('invite-get', [
            "{INVITER}" => $sender->getName()
        ]));
        $sender->sendMessage(SkyBlocksPM::getInstance()->getMessages()->getMessage('invite-sent', [
            "{PLAYER}" => $player->getName()
        ]));
        SkyBlocksPM::getInstance()->getScheduler()->scheduleDelayedTask(new ClosureTask(function () use ($id): void {
            SkyBlocksPM::getInstance()->getInviteManager()->cancelInvite($id);
        }), SkyBlocksPM::getInstance()->getConfig()->getNested('settings.invite-timeout', 30) * 20);
    }

}
