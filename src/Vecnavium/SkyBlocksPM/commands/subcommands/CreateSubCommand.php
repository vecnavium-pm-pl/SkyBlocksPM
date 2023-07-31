<?php

declare(strict_types=1);

namespace Vecnavium\SkyBlocksPM\commands\subcommands;

use CortexPE\Commando\args\RawStringArgument;
use CortexPE\Commando\BaseSubCommand;
use pocketmine\command\CommandSender;
use pocketmine\player\Player as P;
use pocketmine\utils\Utils;
use Ramsey\Uuid\Uuid;
use Vecnavium\SkyBlocksPM\commands\args\IslandNameArgument;
use Vecnavium\SkyBlocksPM\player\Player;
use Vecnavium\SkyBlocksPM\SkyBlocksPM;
use function strval;
use function substr;

class CreateSubCommand extends BaseSubCommand {

    protected function prepare(): void {
        $this->setPermission('skyblockspm.create');
        $this->registerArgument(0, new IslandNameArgument('name'));
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

        // Hotfix for those with pre-existing SkyBlocksPM databases where the UUID length is specified as 32, and not 36
        $id = substr(Uuid::uuid4()->toString(), 0, -4);

        $player->setSkyBlock($id);
        $plugin->getGenerator()->generateIsland($sender, $id, strval($args['name']));
    }

}
