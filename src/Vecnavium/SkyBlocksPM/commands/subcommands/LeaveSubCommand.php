<?php

declare(strict_types=1);

namespace Vecnavium\SkyBlocksPM\commands\subcommands;

use CortexPE\Commando\BaseSubCommand;
use pocketmine\command\CommandSender;
use pocketmine\player\Player as P;
use Vecnavium\SkyBlocksPM\player\Player;
use Vecnavium\SkyBlocksPM\skyblock\SkyBlock;
use Vecnavium\SkyBlocksPM\SkyBlocksPM;
use function array_search;

class LeaveSubCommand extends BaseSubCommand {

    protected function prepare(): void {
        $this->setPermission('skyblockspm.leave');
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
        
        if (!$sender instanceof P) return;

        $skyblockPlayer = $plugin->getPlayerManager()->getPlayer($sender->getName());
        if (!$skyblockPlayer instanceof Player) return;

        if ($skyblockPlayer->getSkyBlock() == '') {
            $sender->sendMessage($plugin->getMessages()->getMessage('no-sb'));
            return;
        }
        $skyblock = $plugin->getSkyBlockManager()->getSkyBlockByUuid($skyblockPlayer->getSkyBlock());
        if ($skyblock instanceof SkyBlock) {
            if($skyblock->getLeader() == $sender->getName()) {
                $sender->sendMessage($plugin->getMessages()->getMessage('no-leave'));
                return;
            }
            $skyblockPlayer->setSkyBlock('');
            $members = $skyblock->getMembers();
            unset($members[array_search($sender->getName(), $members, true)]);
            $skyblock->setMembers($members);
            foreach ($skyblock->getMembers() as $member) {
                $mbr = $plugin->getServer()->getPlayerExact($member);
                if ($mbr instanceof P) {
                    $mbr->sendMessage($plugin->getMessages()->getMessage('member-left', [
                        '{PLAYER}' => $sender->getName()
                    ]));
                }
            }
        }
    }
}
