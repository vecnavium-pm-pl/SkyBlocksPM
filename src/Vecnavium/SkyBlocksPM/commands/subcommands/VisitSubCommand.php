<?php

declare(strict_types=1);

namespace Vecnavium\SkyBlocksPM\commands\subcommands;

use CortexPE\Commando\args\RawStringArgument;
use CortexPE\Commando\BaseSubCommand;
use pocketmine\command\CommandSender;
use pocketmine\player\Player as P;
use pocketmine\utils\Config;
use pocketmine\utils\TextFormat;
use Vecnavium\SkyBlocksPM\libs\jojoe77777\FormAPI\SimpleForm;
use Vecnavium\SkyBlocksPM\player\Player;
use Vecnavium\SkyBlocksPM\skyblock\SkyBlock;
use Vecnavium\SkyBlocksPM\skyblock\SkyblockSettingTypes;
use Vecnavium\SkyBlocksPM\SkyBlocksPM;
use function in_array;

class VisitSubCommand extends BaseSubCommand {

    protected function prepare(): void {
        $this->setPermission('skyblockspm.visit');
        $this->registerArgument(0, new RawStringArgument('name', true));
    }

    public function onRun(CommandSender $sender, string $aliasUsed, array $args): void {
        /** @var SkyBlocksPM $plugin */
        $plugin = $this->getOwningPlugin();
        
        if (!($sender instanceof P)) return;

        if (isset($args['name'])) {
            $p = $plugin->getPlayerManager()->getPlayer($args['name']);
            if (!$p instanceof Player) {
                $sender->sendMessage($plugin->getMessages()->getMessage('not-registered'));
                return;
            }
            $skyblock = $plugin->getSkyBlockManager()->getSkyBlock($p->getSkyBlock());
            if (!$skyblock instanceof SkyBlock) {
                $sender->sendMessage($plugin->getMessages()->getMessage('no-island'));
                return;
            }
            if(!$skyblock->getSetting(SkyblockSettingTypes::SETTING_VISIT)) {
                $sender->sendMessage($plugin->getMessages()->getMessage('island-not-open'));
                return;
            }
            $sender->teleport($plugin->getServer()->getWorldManager()->getWorldByName($skyblock->getWorld())->getSpawnLocation());
            $sender->teleport($skyblock->getSpawn());
        }
        $skyblocks = [];
        foreach ($plugin->getServer()->getOnlinePlayers() as $player) {
            $sbPlayer = $plugin->getPlayerManager()->getPlayer($player->getName());
            if(!$sbPlayer instanceof Player) continue;
            $skyblock = $plugin->getSkyBlockManager()->getSkyBlockByUuid($sbPlayer->getSkyBlock());
            if ($skyblock instanceof SkyBlock) {
                if (!in_array($skyblock->getUuid(), $skyblocks) && $skyblock->getSetting(SkyblockSettingTypes::SETTING_VISIT)) {
                    $skyblocks[] = $skyblock->getUuid();
                }
            }
        }
        $form = new SimpleForm(function (P $player, ?int $data) use ($plugin, $skyblocks) {
            if($data === null) return;
            if (!isset($skyblocks[$data])) return;

            $skyblock = $plugin->getSkyBlockManager()->getSkyBlockByUuid($skyblocks[$data]);
            $player->teleport($plugin->getServer()->getWorldManager()->getWorldByName($skyblock->getWorld())->getSpawnLocation());
            $player->teleport($skyblock->getSpawn());
        });
        $formConfig = new Config($plugin->getDataFolder() . "forms.yml", Config::YAML);
        $form->setTitle(TextFormat::colorize($formConfig->getNested('visit.title')));
        foreach ($skyblocks as $uuid) {
            $skyblock = $plugin->getSkyBlockManager()->getSkyBlockByUuid($uuid);
            $form->addButton(TextFormat::colorize(str_replace('{NAME}', $skyblock->getLeader() , $formConfig->getNested('visit.buttons', '&l&a{NAME} SkyBlock'))));
        }
        $sender->sendForm($form);
    }

}
