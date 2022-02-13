<?php

declare(strict_types=1);

namespace Vecnavium\SkyBlocksPM\commands\subcommands;

use Vecnavium\SkyBlocksPM\libs\CortexPE\Commando\args\RawStringArgument;
use Vecnavium\SkyBlocksPM\libs\CortexPE\Commando\BaseSubCommand;
use Vecnavium\SkyBlocksPM\SkyBlocksPM;
use Vecnavium\SkyBlocksPM\player\Player;
use pocketmine\player\Player as P;
use pocketmine\command\CommandSender;

class DeleteSubCommand extends BaseSubCommand
{
    
    protected function prepare(): void
    {
        $this->setPermission('skyblockspm.delete');
        $this->registerArgument(0, new RawStringArgument('name'));
    }
    
    public function onRun(CommandSender $sender, string $aliasUsed, array $args): void
    {
        $name = $args['name'];
        if ($name !== $sender->getName() && !$sender->hasPermission('skyblockspm.deleteothers'))
        {
            $sender->sendMessage(SkyBlocksPM::getInstance()->getMessages()->getMessage('no-perms-delete'));
            return;
        }
        $skyblockPlayer = SkyBlocksPM::getInstance()->getPlayerManager()->getPlayerByPrefix($name);
        if (!$skyblockPlayer instanceof Player)
        {
            $sender->sendMessage(SkyBlocksPM::getInstance()->getMessages()->getMessage('not-registered'));
            return;
        }
        if ($skyblockPlayer->getSkyBlock() == '')
        {
            $sender->sendMessage(SkyBlocksPM::getInstance()->getMessages()->getMessage('no-island'));
            return;
        }
        $skyblock = SkyBlocksPM::getInstance()->getSkyBlockManager()->getSkyBlock($skyblockPlayer->getSkyBlock());
        foreach ($skyblock->getMembers() as $member)
        {
            $player = SkyBlocksPM::getInstance()->getServer()->getPlayerByPrefix($member);
            if ($player instanceof P)
                $player->teleport(SkyBlocksPM::getInstance()->getServer()->getWorldManager()->getDefaultWorld()->getSpawnLocation());
            SkyBlocksPM::getInstance()->getPlayerManager()->getPlayerByPrefix($member)->setSkyBlock('');
        }
        SkyBlocksPM::getInstance()->getSkyBlockManager()->deleteSkyBlock($skyblock->getName());
        $world = SkyBlocksPM::getInstance()->getServer()->getWorldManager()->getWorldByName($skyblock->getWorld());
        foreach ($world->getPlayers() as $p)
            $p->teleport(SkyBlocksPM::getInstance()->getServer()->getWorldManager()->getDefaultWorld()->getSpawnLocation());
        if ($world->isLoaded())
        {
            $folderName = $world->getFolderName();
            SkyBlocksPM::getInstance()->getServer()->getWorldManager()->unloadWorld($world);
            $this->deleteWorld(SkyBlocksPM::getInstance()->getServer()->getDataPath() . 'worlds' . DIRECTORY_SEPARATOR . $folderName);
        }
        $sender->sendMessage(SkyBlocksPM::getInstance()->getMessages()->getMessage('deleted-sb', [
            "{NAME}" => $skyblockPlayer->getName()
        ]));
    }

    public function deleteWorld(string $path, string $previousPath = ''): void
    {
        foreach (array_diff(scandir($path . DIRECTORY_SEPARATOR), ['..', '.']) as $file)
        {
            if (is_dir($path . DIRECTORY_SEPARATOR . $file))
                $this->deleteWorld($path. DIRECTORY_SEPARATOR . $file. DIRECTORY_SEPARATOR);
            else
                unlink($path . DIRECTORY_SEPARATOR . $file);
        }
        rmdir($path);
    }

}
