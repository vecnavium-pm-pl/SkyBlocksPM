<?php

declare(strict_types=1);

namespace Vecnavium\SkyBlocksPM\commands\subcommands;

use Vecnavium\SkyBlocksPM\libs\CortexPE\Commando\args\RawStringArgument;
use Vecnavium\SkyBlocksPM\libs\CortexPE\Commando\BaseSubCommand;
use Vecnavium\SkyBlocksPM\libs\SimpleForm;
use Vecnavium\SkyBlocksPM\skyblock\SkyBlock;
use Vecnavium\SkyBlocksPM\player\Player;
use pocketmine\command\CommandSender;
use pocketmine\player\Player as P;
use pocketmine\utils\Config;
use pocketmine\utils\TextFormat;
use Vecnavium\SkyBlocksPM\SkyBlocksPM;

class VisitSubCommand extends BaseSubCommand
{

    protected function prepare(): void
    {
        $this->setPermission('skyblockspm.visit');
        $this->registerArgument(0, new RawStringArgument('name', true));
    }

    public function onRun(CommandSender $sender, string $aliasUsed, array $args): void
    {
        if (!($sender instanceof P))
            return;
        if (isset($args['name']))
        {
            $p = SkyBlocksPM::getInstance()->getPlayerManager()->getPlayerByPrefix($args['name']);
            if (!$p instanceof Player)
            {
                $sender->sendMessage(SkyBlocksPM::getInstance()->getMessages()->getMessage('not-registered'));
                return;
            }
            $skyblock = SkyBlocksPM::getInstance()->getSkyBlockManager()->getSkyBlock($p->getSkyBlock());
            if (!$skyblock instanceof SkyBlock)
            {
                $sender->sendMessage(SkyBlocksPM::getInstance()->getMessages()->getMessage('no-island'));
                return;
            }
            $sender->teleport(SkyBlocksPM::getInstance()->getServer()->getWorldManager()->getWorldByName($skyblock->getWorld())->getSpawnLocation());
            $sender->teleport($skyblock->getSpawn());
        }
        $skyblocks = [];
        foreach (SkyBlocksPM::getInstance()->getServer()->getOnlinePlayers() as $player)
        {
            $skyblock = SkyBlocksPM::getInstance()->getSkyBlockManager()->getSkyBlockByUuid(SkyBlocksPM::getInstance()->getPlayerManager()->getPlayer($player)->getSkyBlock());
            if  ($skyblock instanceof SkyBlock)
                $skyblocks[] = $skyblock;
        }
        $form = new SimpleForm(function (P $player, ?int $data) use ($skyblocks) {
            if (!isset($skyblocks[$data]))
                return;
            $skyblock = $skyblocks[$data];
            $player->teleport(SkyBlocksPM::getInstance()->getServer()->getWorldManager()->getWorldByName($skyblock->getWorld())->getSpawnLocation());
            $player->teleport($skyblock->getSpawn());
        });
        $formConfig = new Config(SkyBlocksPM::getInstance()->getDataFolder() . "forms.yml", Config::YAML);
        $form->setTitle(TextFormat::colorize($formConfig->getNested('visit.title')));
        foreach ($skyblocks as $skyblock)
        {
            $form->addButton(TextFormat::colorize(str_replace('{NAME}', $skyblock->getLeader() , $formConfig->getNested('visit.buttons', '&l&a{NAME} SkyBlock'))));
        }
        $form->sendToPlayer($sender);
    }

}
