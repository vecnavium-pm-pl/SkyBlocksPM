<?php

declare(strict_types=1);

namespace Vecnavium\SkyBlocksPM;

use CortexPE\Commando\PacketHooker;
use pocketmine\player\Player;
use pocketmine\plugin\PluginBase;
use pocketmine\utils\Config;
use pocketmine\utils\SingletonTrait;
use poggit\libasynql\DataConnector;
use poggit\libasynql\libasynql;
use Vecnavium\SkyBlocksPM\commands\SkyBlockCommand;
use Vecnavium\SkyBlocksPM\generator\Generator;
use Vecnavium\SkyBlocksPM\invites\InviteManager;
use Vecnavium\SkyBlocksPM\listener\EventListener;
use Vecnavium\SkyBlocksPM\messages\Messages;
use Vecnavium\SkyBlocksPM\player\PlayerManager;
use Vecnavium\SkyBlocksPM\skyblock\SkyBlockManager;
use function array_search;
use function rename;
use function version_compare;

class SkyBlocksPM extends PluginBase {

    use SingletonTrait;

    const MSG_VER = "1";
    const FORM_VER = "1";

    private DataConnector $dataConnector;
    private Generator $generator;
    private Messages $messages;
    private PlayerManager $playerManager;
    private SkyBlockManager $SkyBlockManager;
    private InviteManager $inviteManager;

    /** @var string[] */
    private array $chat;

    public function onEnable(): void {
        self::setInstance($this);

        if (!PacketHooker::isRegistered()) {
            PacketHooker::register($this);
        }
        $this->saveDefaultConfig();
        $this->saveResource('messages.yml');
        $this->saveResource('forms.yml');
        $this->checkConfigs();
        $this->initDataBase();
        $this->getServer()->getPluginManager()->registerEvents(new EventListener($this), $this);
        $this->generator = new Generator($this);
        $this->messages = new Messages($this);
        $this->playerManager = new PlayerManager($this);
        $this->SkyBlockManager = new SkyBlockManager($this);
        $this->inviteManager = new InviteManager();
        $this->getServer()->getCommandMap()->register('SkyBlocksPM', new SkyBlockCommand($this, 'skyblock', 'The core command for SkyBlocks', ['sb', 'is']));
        @mkdir($this->getDataFolder() . "cache");
        @mkdir($this->getDataFolder() . "cache/island");
        $this->checkUpdate();
        $this->chat = [];
    }

    public function onDisable(): void {
        $this->dataConnector->waitAll();
        $this->dataConnector->close();
    }

    public function initDataBase(): void {
        $db = libasynql::create($this, $this->getConfig()->get('database'), ['mysql' => 'mysql.sql', 'sqlite' => 'sqlite.sql']);
        $db->executeGeneric('skyblockspm.player.init');
        $db->executeGeneric('skyblockspm.sb.init');
        $db->waitAll();
        $this->dataConnector = $db;
    }

    /**
     * @return string[]
     */
    public function getChat(): array {
        return $this->chat;
    }

    public function setPlayerChat(Player $player, bool $status): void{
        if($status) $this->chat[] = $player->getName();
        else unset($this->chat[array_search($player->getName(), $this->chat, true)]);
    }

    public function checkUpdate(): void {
        $this->getServer()->getAsyncPool()->submitTask(new CheckUpdateTask($this->getDescription()->getName(), $this->getDescription()->getVersion()));
    }

    
    /**
     * @return DataConnector
     */
    public function getDataBase(): DataConnector {
        return $this->dataConnector;
    }

    /**
     * @return Generator
     */
    public function getGenerator(): Generator {
        return $this->generator;
    }

    /**
     * @return Messages
     */
    public function getMessages(): Messages {
        return $this->messages;
    }

    /**
     * @return PlayerManager
     */
    public function getPlayerManager(): PlayerManager {
        return $this->playerManager;
    }

    /**
     * @return SkyBlockManager
     */
    public function getSkyBlockManager(): SkyBlockManager {
        return $this->SkyBlockManager;
    }

    /**
     * @return InviteManager
     */
    public function getInviteManager(): InviteManager {
        return $this->inviteManager;
    }

    public function checkConfigs(): void {
        $messagesCfg = new Config($this->getDataFolder() . "messages.yml", Config::YAML);
        if(version_compare((string)$messagesCfg->get("version", "0"), self::MSG_VER, "<>")) {
            $this->getLogger()->error("Your message files are outdated. SkyBlocksPM will automatically create a new config.");
            $this->getLogger()->error("The old message files can be found at 'messages.old.yml'");
            rename($this->getDataFolder() . "messages.yml", $this->getDataFolder() . "messages.old.yml");
            $this->saveResource('messages.yml');
            $messagesCfg->reload();
        }
        $formsCfg = new Config($this->getDataFolder() . "forms.yml", Config::YAML);
        if(version_compare((string)$formsCfg->get("version", "0"), self::FORM_VER, "<>")) {
            $this->getLogger()->error("Your form message files are outdated. SkyBlocksPM will automatically create a new config.");
            $this->getLogger()->error("The old form message files can be found at 'forms.old.yml'");
            rename($this->getDataFolder() . "forms.yml", $this->getDataFolder() . "forms.old.yml");
            $this->saveResource('forms.yml');
            $formsCfg->reload();
        }
    }
}
