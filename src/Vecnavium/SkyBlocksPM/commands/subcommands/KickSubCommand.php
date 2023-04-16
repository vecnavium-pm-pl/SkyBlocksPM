<?php

declare(strict_types=1);

namespace Vecnavium\SkyBlocksPM\commands\subcommands;

use CortexPE\Commando\BaseSubCommand;
use pocketmine\command\CommandSender;
use pocketmine\player\Player as P;
use Vecnavium\SkyBlocksPM\commands\args\PlayerArgument;
use Vecnavium\SkyBlocksPM\player\Player;
use Vecnavium\SkyBlocksPM\skyblock\SkyBlock;
use Vecnavium\SkyBlocksPM\SkyBlocksPM;
use function array_search;
use function in_array;

class KickSubCommand extends BaseSubCommand {

    protected function prepare(): void {
        $this->setPermission('skyblockspm.kick');
        $this->registerArgument(0, new PlayerArgument('player'));
    }

    public function onRun(CommandSender $sender, string $aliasUsed, array $args): void {
        /** @var SkyBlocksPM $plugin */
        $plugin = $this->getOwningPlugin();
        
        if (!$sender instanceof P) return;

        $toKickPlayer = $plugin->getPlayerManager()->getPlayer(($args['player'] instanceof P ? $args['player']->getName() : $args['player']));
        $skyblockPlayer = $plugin->getPlayerManager()->getPlayer($sender->getName());
        if (!$toKickPlayer instanceof Player) {
            $sender->sendMessage($plugin->getMessages()->getMessage('not-registered'));
            return;
        }
        if ($skyblockPlayer->getSkyBlock() == '') {
            $sender->sendMessage($plugin->getMessages()->getMessage('no-sb'));
            return;
        }
        $skyblock = $plugin->getSkyBlockManager()->getSkyBlockByUuid($skyblockPlayer->getSkyBlock());
        if ($skyblock instanceof SkyBlock) {
            if($skyblock->getLeader() !== $sender->getName()) {
                $sender->sendMessage($plugin->getMessages()->getMessage('no-kick'));
                return;
            }
            $members = $skyblock->getMembers();

            if(!in_array($toKickPlayer->getName(), $members)){
                $sender->sendMessage($plugin->getMessages()->getMessage('not-member'));
                return;
            }
            $toKickPlayer->setSkyBlock('');
            $members = $skyblock->getMembers();
            unset($members[array_search($toKickPlayer->getName(), $members)]);
            $skyblock->setMembers($members);
            foreach ($skyblock->getMembers() as $member) {
                $mbr = $plugin->getServer()->getPlayerExact($member);
                if ($mbr instanceof P) {
                    $mbr->sendMessage($plugin->getMessages()->getMessage('member-kicked', [
                        "{PLAYER}" => $toKickPlayer->getName()
                    ]));
                }
            }
        }
    }
}
