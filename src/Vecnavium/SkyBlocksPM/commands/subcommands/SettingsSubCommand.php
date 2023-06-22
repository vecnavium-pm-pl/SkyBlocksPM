<?php

declare(strict_types=1);

namespace Vecnavium\SkyBlocksPM\commands\subcommands;

use CortexPE\Commando\BaseSubCommand;
use pocketmine\command\CommandSender;
use pocketmine\player\Player as P;
use pocketmine\utils\Config;
use pocketmine\utils\TextFormat;
use Vecnavium\SkyBlocksPM\libs\jojoe77777\FormAPI\CustomForm;
use Vecnavium\SkyBlocksPM\player\Player;
use Vecnavium\SkyBlocksPM\skyblock\SkyBlock;
use Vecnavium\SkyBlocksPM\skyblock\SkyblockSettingTypes;
use Vecnavium\SkyBlocksPM\SkyBlocksPM;
use function array_shift;
use function strval;

class SettingsSubCommand extends BaseSubCommand {

    protected function prepare(): void {
        $this->setPermission('skyblockspm.settings');
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
            if ($skyblock->getLeader() !== $sender->getName()) {
                $sender->sendMessage($plugin->getMessages()->getMessage('no-edit'));
                return;
            }

            $settingsForm = new CustomForm(function(P $player, $data) use ($plugin, $skyblock){
                if($data === null) return;
                array_shift($data);

                $newSettings = [];
                $newSettings[SkyblockSettingTypes::SETTING_VISIT] = (bool)$data[0];
                $newSettings[SkyblockSettingTypes::SETTING_PVP] = (bool)$data[1];
                $newSettings[SkyblockSettingTypes::SETTING_INTERACT_CHEST] = (bool)$data[2];
                $newSettings[SkyblockSettingTypes::SETTING_INTERACT_DOOR] = (bool)$data[3];
                $newSettings[SkyblockSettingTypes::SETTING_PICKUP] = (bool)$data[4];
                $newSettings[SkyblockSettingTypes::SETTING_BREAK] = (bool)$data[5];
                $newSettings[SkyblockSettingTypes::SETTING_PLACE] = (bool)$data[6];

                $skyblock->updateSettings($newSettings);
                $player->sendMessage($plugin->getMessages()->getMessage('updated-settings'));
            });
            $formConfig = new Config($plugin->getDataFolder() . 'forms.yml', Config::YAML);
            $settingsForm->setTitle(TextFormat::colorize(strval($formConfig->getNested('settings.title'))));
            $settingsForm->addLabel(TextFormat::colorize(strval($formConfig->getNested('settings.text'))));

            $settingsForm->addToggle('Open for Visiting', $skyblock->getSetting(SkyblockSettingTypes::SETTING_VISIT));
            $settingsForm->addToggle('PvP', $skyblock->getSetting(SkyblockSettingTypes::SETTING_PVP));
            $settingsForm->addToggle('Open Chests', $skyblock->getSetting(SkyblockSettingTypes::SETTING_INTERACT_CHEST));
            $settingsForm->addToggle('Open Doors', $skyblock->getSetting(SkyblockSettingTypes::SETTING_INTERACT_DOOR));
            $settingsForm->addToggle('Pickup Items', $skyblock->getSetting(SkyblockSettingTypes::SETTING_PICKUP));
            $settingsForm->addToggle('Break Blocks', $skyblock->getSetting(SkyblockSettingTypes::SETTING_BREAK));
            $settingsForm->addToggle('Place Blocks', $skyblock->getSetting(SkyblockSettingTypes::SETTING_PLACE));

            $sender->sendForm($settingsForm);
        }
    }
}
