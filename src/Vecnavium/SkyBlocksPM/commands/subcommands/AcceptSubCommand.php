<?php

declare(strict_types=1);

namespace Vecnavium\SkyBlocksPM\commands\subcommands;

use CortexPE\Commando\BaseSubCommand;
use pocketmine\command\CommandSender;
use pocketmine\player\Player as P;
use Vecnavium\SkyBlocksPM\commands\args\PlayerArgument;
use Vecnavium\SkyBlocksPM\invites\Invite;
use Vecnavium\SkyBlocksPM\player\Player;
use Vecnavium\SkyBlocksPM\skyblock\SkyBlock;
use Vecnavium\SkyBlocksPM\SkyBlocksPM;
use function strval;

class AcceptSubCommand extends BaseSubCommand {

    protected function prepare(): void {
        $this->setPermission('skyblockspm.accept');
        $this->registerArgument(0, new PlayerArgument('player'));
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
        
        $invite = $plugin->getInviteManager()->getPlayerInvites(($args['player'] instanceof P ? $args['player']->getName() : strval($args['player'])));

        if (!$invite instanceof Invite) return;
        if (!$invite->handleInvite()) return;

        $plugin->getInviteManager()->cancelInvite($invite->getId());

        $player = $plugin->getPlayerManager()->getPlayer($sender->getName());
        $inviter = $plugin->getPlayerManager()->getPlayer($invite->getInviter()->getName());
        if($player instanceof Player && $inviter instanceof Player) {
            $player->setSkyBlock($inviter->getSkyBlock());
            $skyblock = $plugin->getSkyBlockManager()->getSkyBlockByUuid($inviter->getSkyBlock());
            if($skyblock instanceof SkyBlock) {
                $members = $skyblock->getMembers();
                $members[] = $sender->getName();
                $skyblock->setMembers($members);
                foreach ($skyblock->getMembers() as $member) {
                    $mbr = $plugin->getServer()->getPlayerExact($member);
                    if ($mbr instanceof P) {
                        $mbr->sendMessage($plugin->getMessages()->getMessage('invite-accepted', [
                            '{PLAYER}' => $sender->getName()
                        ]));
                    }
                }
            }
        }
    }
}
