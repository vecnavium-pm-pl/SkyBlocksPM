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

class InviteSubCommand extends BaseSubCommand {

    protected function prepare(): void {
        $this->setPermission('skyblockspm.invite');
        $this->registerArgument(0, new RawStringArgument('name'));
    }

    public function onRun(CommandSender $sender, string $aliasUsed, array $args): void {
        /** @var SkyBlocksPM $plugin */
        $plugin = $this->getOwningPlugin();
        
        if (!$sender instanceof Player) return;

        if (!$plugin->getInviteManager()->canInvite($sender)) {
            $sender->sendMessage($plugin->getMessages()->getMessage('invite-pending'));
            return;
        }
        $player = $plugin->getServer()->getPlayerByPrefix($args['name']);
        $skyblockPlayer = $plugin->getPlayerManager()->getPlayerByPrefix($sender->getName());
        $skyblock = $plugin->getSkyBlockManager()->getSkyBlockByUuid($skyblockPlayer->getSkyBlock());
        if (!$skyblock instanceof SkyBlock) {
            $sender->sendMessage($plugin->getMessages()->getMessage('no-sb'));
            return;
        }
        if (count($skyblock->getMembers()) >= $plugin->getConfig()->getNested('settings.max-members')) {
            $sender->sendMessage($plugin->getMessages()->getMessage('member-limit'));
            return;
        }
        if (!$player instanceof Player) {
            $plugin->getMessages()->getMessage('player-not-online');
            return;
        }
        if ($sender === $player) return;

        $id =  Uuid::uuid4()->toString();
        $plugin->getInviteManager()->addInvite($id, $sender, $player);
        $player->sendMessage($plugin->getMessages()->getMessage('invite-get', [
            "{INVITER}" => $sender->getName()
        ]));
        $sender->sendMessage($plugin->getMessages()->getMessage('invite-sent', [
            "{PLAYER}" => $player->getName()
        ]));
        $plugin->getScheduler()->scheduleDelayedTask(new ClosureTask(function () use ($plugin, $id): void {
            if($plugin->getInviteManager()->isInviteValid($id))
                $plugin->getInviteManager()->cancelInvite($id, true);
        }), $plugin->getConfig()->getNested('settings.invite-timeout', 30) * 20);
    }

}
