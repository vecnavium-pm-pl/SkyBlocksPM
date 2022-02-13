<?php

declare(strict_types=1);

namespace Vecnavium\SkyBlocksPM\commands\subcommands;

use Vecnavium\SkyBlocksPM\libs\CortexPE\Commando\args\RawStringArgument;
use Vecnavium\SkyBlocksPM\libs\CortexPE\Commando\BaseSubCommand;
use Vecnavium\SkyBlocksPM\SkyBlocksPM;
use Vecnavium\SkyBlocksPM\invites\Invite;
use pocketmine\command\CommandSender;
use pocketmine\player\Player;

class AcceptSubCommand extends BaseSubCommand
{

    protected function prepare(): void
    {
        $this->setPermission('skyblockspm.accept');
        $this->registerArgument(0, new RawStringArgument('name'));
    }

    public function onRun(CommandSender $sender, string $aliasUsed, array $args): void
    {
        $invite = SkyBlocksPM::getInstance()->getInviteManager()->getPlayerInvites($args['name']);
        if (!$invite instanceof Invite)
            return;

        if (!$invite->handleInvite())
            return;

        $player = SkyBlocksPM::getInstance()->getPlayerManager()->getPlayerByPrefix($sender->getName());
        $inviter = SkyBlocksPM::getInstance()->getPlayerManager()->getPlayer($invite->getInviter());
        $player->setSkyBlock($inviter->getSkyBlock());
        $skyblock = SkyBlocksPM::getInstance()->getSkyBlockManager()->getSkyBlock($player->getSkyBlock());
        $members = $skyblock->getMembers();
        $skyblock->setMembers($members);
        array_push($members, $sender->getName());
        foreach ($skyblock->getMembers() as $member)
        {
            $mbr = SkyBlocksPM::getInstance()->getServer()->getPlayerByPrefix($member);
            if ($mbr instanceof Player)
                $mbr->sendMessage(SkyBlocksPM::getInstance()->getMessages()->getMessage('invite-accepted', [
                    "{PLAYER}" => $sender->getName()
                ]));
        }
    }

}
