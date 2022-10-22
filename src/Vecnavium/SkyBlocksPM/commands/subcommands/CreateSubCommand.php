<?php

declare(strict_types=1);

namespace Vecnavium\SkyBlocksPM\commands\subcommands;

use Vecnavium\SkyBlocksPM\libs\CortexPE\Commando\args\RawStringArgument;
use Vecnavium\SkyBlocksPM\libs\CortexPE\Commando\BaseSubCommand;
use Vecnavium\SkyBlocksPM\SkyBlocksPM;
use pocketmine\block\VanillaBlocks;
use pocketmine\command\CommandSender;
use pocketmine\player\Player;
use pocketmine\player\PlayerChunkLoader;
use pocketmine\world\Position;
use Ramsey\Uuid\Uuid;

class CreateSubCommand extends BaseSubCommand
{

    protected function prepare(): void
    {
        $this->setPermission('skyblockspm.create');
        $this->registerArgument(0, new RawStringArgument('name'));
    }

    public function onRun(CommandSender $sender, string $aliasUsed, array $args): void
    {
        if (!$sender instanceof Player) return;

        $player = SkyBlocksPM::getInstance()->getPlayerManager()->getPlayer($sender);
        if ($player->getSkyBlock() !== '')
        {
            $sender->sendMessage(SkyBlocksPM::getInstance()->getMessages()->getMessage('have-sb'));
            return;
        }
        if (count(array_diff(scandir(SkyBlocksPM::getInstance()->getDataFolder() . 'cache/island'), ['..', "."])) == 0)
        {
            $sender->sendMessage(SkyBlocksPM::getInstance()->getMessages()->getMessage("no-default-island"));
            return;
        }
        $sender->sendMessage(SkyBlocksPM::getInstance()->getMessages()->getMessage('skyblock-creating'));
        $id = Uuid::uuid4()->toString();
        $player->setSkyBlock($id);
        SkyBlocksPM::getInstance()->getGenerator()->generateIsland($sender, $id, $args['name']);
    }

}
