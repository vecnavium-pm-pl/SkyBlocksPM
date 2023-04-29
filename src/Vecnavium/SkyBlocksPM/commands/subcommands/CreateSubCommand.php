<?php

declare(strict_types=1);

namespace Vecnavium\SkyBlocksPM\commands\subcommands;

use CortexPE\Commando\args\RawStringArgument;
use CortexPE\Commando\BaseSubCommand;
use pocketmine\command\CommandSender;
use pocketmine\player\Player as P;
use pocketmine\utils\Utils;
use Ramsey\Uuid\Uuid;
use Vecnavium\SkyBlocksPM\player\Player;
use Vecnavium\SkyBlocksPM\SkyBlocksPM;
use function strval;

class CreateSubCommand extends BaseSubCommand {

    protected function prepare(): void {
        $this->setPermission('skyblockspm.create');
        $this->registerArgument(0, new RawStringArgument('name'));
    }

    /**
     * @param CommandSender $sender
     * @param string $aliasUsed
     * @param array<string,mixed> $args
     * @return void
     */
    public function onRun(CommandSender $sender, string $aliasUsed, array $args): void {
        /** @var SkyBlocksPM $plugin */
        $plugin = $this->getOwningPlugin();
        
        if (!$sender instanceof P) return;

        $player = $plugin->getPlayerManager()->getPlayer($sender->getName());
        if(!$player instanceof Player) return;

        if ($player->getSkyBlock() !== '') {
            $sender->sendMessage($plugin->getMessages()->getMessage('have-sb'));
            return;
        }
        if (count(array_diff(Utils::assumeNotFalse(scandir($plugin->getDataFolder() . 'cache/island')), ['..', '.'])) == 0) {
            $sender->sendMessage($plugin->getMessages()->getMessage('no-default-island'));
            return;
        }
        $sender->sendMessage($plugin->getMessages()->getMessage('skyblock-creating'));
        $id = Uuid::uuid4()->toString();
        $player->setSkyBlock($id);
        $plugin->getGenerator()->generateIsland($sender, $id, strval($args['name'])); // Name validation?
    }

}
