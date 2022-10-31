<?php

declare(strict_types=1);

namespace Vecnavium\SkyBlocksPM\commands\subcommands;

use pocketmine\utils\Config;
use pocketmine\utils\TextFormat;
use Vecnavium\SkyBlocksPM\libs\CortexPE\Commando\BaseSubCommand;
use Vecnavium\SkyBlocksPM\libs\jojoe77777\FormAPI\CustomForm;
use Vecnavium\SkyBlocksPM\skyblock\SkyBlock;
use Vecnavium\SkyBlocksPM\SkyBlocksPM;
use Vecnavium\SkyBlocksPM\player\Player;
use pocketmine\player\Player as P;
use pocketmine\command\CommandSender;

class SettingsSubCommand extends BaseSubCommand {

    protected function prepare(): void {
        $this->setPermission('skyblockspm.settings');
    }

    public function onRun(CommandSender $sender, string $aliasUsed, array $args): void {
        /** @var SkyBlocksPM $plugin */
        $plugin = $this->getOwningPlugin();
        
        if (!$sender instanceof P) return;

        $skyblockPlayer = $plugin->getPlayerManager()->getPlayer($sender);
        if (!$skyblockPlayer instanceof Player) return;

        if ($skyblockPlayer->getSkyBlock() == '') {
            $sender->sendMessage($plugin->getMessages()->getMessage('no-sb'));
            return;
        }
        $skyblock = $plugin->getSkyBlockManager()->getSkyBlockByUuid($skyblockPlayer->getSkyBlock());
        if ($skyblock instanceof SkyBlock) {
            if ($skyblock->getLeader() !== $sender->getName()) {
                $sender->sendMessage($plugin->getMessages()->getMessage('no-edit'));
                return;
            }

            $settingsForm = new CustomForm(function(P $player, $data) use ($plugin, $skyblock){
                if($data === null) return;

                // $data[0] will always be NULL because of the label and how it gets handled
                $newSettings = [];
                $newSettings['visit'] = (bool)$data[1];
                $newSettings['pvp'] = (bool)$data[2];
                $newSettings['interact_chest'] = (bool)$data[3];
                $newSettings['interact_door'] = (bool)$data[4];
                $newSettings['pickup'] = (bool)$data[5];
                $newSettings['break'] = (bool)$data[6];
                $newSettings['place'] = (bool)$data[7];

                $skyblock->updateSettings($newSettings);
                $player->sendMessage($plugin->getMessages()->getMessage('updated-settings'));
            });
            $formConfig = new Config($plugin->getDataFolder() . 'forms.yml', Config::YAML);
            $settingsForm->setTitle(TextFormat::colorize($formConfig->getNested('settings.title')));
            $settingsForm->addLabel(TextFormat::colorize($formConfig->getNested('settings.text')));

            $settingsForm->addToggle("Open for Visiting", $skyblock->getSetting('visit'));
            $settingsForm->addToggle("PvP", $skyblock->getSetting('pvp'));
            $settingsForm->addToggle("Open Chests", $skyblock->getSetting('interact_chest'));
            $settingsForm->addToggle("Open Doors", $skyblock->getSetting('interact_door'));
            $settingsForm->addToggle("Pickup Items", $skyblock->getSetting('pickup'));
            $settingsForm->addToggle("Break Blocks", $skyblock->getSetting('break'));
            $settingsForm->addToggle("Place Blocks", $skyblock->getSetting('place'));

            $sender->sendForm($settingsForm);
        }
    }
}
