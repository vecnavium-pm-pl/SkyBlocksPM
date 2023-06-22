<?php

declare(strict_types=1);

namespace Vecnavium\SkyBlocksPM;

use CortexPE\Commando\BaseCommand;
use CortexPE\Commando\PacketHooker;
use libMarshal\exception\GeneralMarshalException;
use libMarshal\exception\UnmarshalException;
use libMarshal\MarshalTrait;
use pocketmine\player\Player;
use pocketmine\plugin\DisablePluginException;
use pocketmine\plugin\PluginBase;
use pocketmine\utils\Config as PMConfig;
use pocketmine\utils\ConfigLoadException;
use pocketmine\utils\SingletonTrait;
use poggit\libasynql\DataConnector;
use poggit\libasynql\libasynql;
use Vecnavium\SkyBlocksPM\commands\SkyBlockCommand;
use Vecnavium\SkyBlocksPM\config\Config;
use Vecnavium\SkyBlocksPM\config\database\DatabaseConfig;
use Vecnavium\SkyBlocksPM\generator\Generator;
use Vecnavium\SkyBlocksPM\invites\InviteManager;
use Vecnavium\SkyBlocksPM\listener\EventListener;
use Vecnavium\SkyBlocksPM\messages\Messages;
use Vecnavium\SkyBlocksPM\player\PlayerManager;
use Vecnavium\SkyBlocksPM\skyblock\SkyBlockManager;
use function array_search;
use function class_exists;
use function rename;
use function strval;
use function trait_exists;
use function version_compare;

class SkyBlocksPM extends PluginBase {

    use SingletonTrait;

    const MSG_VER = '1';
    const FORM_VER = '1';

    private DataConnector $dataConnector;
    private Generator $generator;
    private Messages $messages;
    private PlayerManager $playerManager;
    private SkyBlockManager $SkyBlockManager;
    private InviteManager $inviteManager;
    private Config $config;

    /** @var string[] */
    private array $chat;

    public function onEnable(): void {
        self::setInstance($this);
        $this->checkVirions();

        if (!PacketHooker::isRegistered()) PacketHooker::register($this);

        $this->saveDefaultConfig();
        $this->saveResource('messages.yml');
        $this->saveResource('forms.yml');
        $this->checkConfigs();

        try{
            $this->config = Config::unmarshal($this->getConfig()->getAll());
        }catch(GeneralMarshalException|UnmarshalException|ConfigLoadException $e){
            $this->getLogger()->error($e->getMessage());
            throw new DisablePluginException;
        }

        $this->initDataBase();
        $this->getServer()->getPluginManager()->registerEvents(new EventListener($this), $this);
        $this->generator = new Generator($this);
        $this->messages = new Messages($this);
        $this->playerManager = new PlayerManager($this);
        $this->SkyBlockManager = new SkyBlockManager($this);
        $this->inviteManager = new InviteManager();
        $this->getServer()->getCommandMap()->register('SkyBlocksPM', new SkyBlockCommand($this));
        @mkdir($this->getDataFolder() . 'cache');
        @mkdir($this->getDataFolder() . 'cache/island');
        $this->chat = [];

        $this->getServer()->getAsyncPool()->submitTask(new CheckUpdateTask($this->getDescription()->getName(), $this->getDescription()->getVersion()));
    }

    public function onDisable(): void {
        $this->dataConnector->waitAll();
        $this->dataConnector->close();
    }

    /**
     * @return void
     *
     * @credit JavierLeon9966
     */
    public function initDataBase(): void {
        $databaseConfig = $this->config->database ?? new DatabaseConfig();
        $friendlyConfig = [
            'type' => $databaseConfig->type,
            'sqlite' => [
                'file' => $databaseConfig->sqlite->file
            ],
            'mysql' => [
                'host' => $databaseConfig->mysql->host,
                'username' => $databaseConfig->mysql->username,
                'password' => $databaseConfig->mysql->password,
                'schema' => $databaseConfig->mysql->schema,
                'port' => $databaseConfig->mysql->port
            ],
            'worker-limit' => $databaseConfig->type !== 'sqlite' ? $databaseConfig->workerLimit : 1
        ];

        $db = libasynql::create($this, $friendlyConfig, [
            'mysql' => 'mysql.sql',
            'sqlite' => 'sqlite.sql'
        ]);
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
        $messagesCfg = new PMConfig($this->getDataFolder() . 'messages.yml', PMConfig::YAML);
        if(version_compare(strval($messagesCfg->get('version', '0')), self::MSG_VER, '<>')) {
            $this->getLogger()->error('Your message files are outdated. SkyBlocksPM will automatically create a new config.');
            $this->getLogger()->error('The old message files can be found at "messages.old.yml"');
            rename($this->getDataFolder() . 'messages.yml', $this->getDataFolder() . 'messages.old.yml');
            $this->saveResource('messages.yml');
            $messagesCfg->reload();
        }
        $formsCfg = new PMConfig($this->getDataFolder() . 'forms.yml', PMConfig::YAML);
        if(version_compare(strval($formsCfg->get('version', '0')), self::FORM_VER, '<>')) {
            $this->getLogger()->error('Your form message files are outdated. SkyBlocksPM will automatically create a new config.');
            $this->getLogger()->error('The old form message files can be found at "forms.old.yml"');
            rename($this->getDataFolder() . 'forms.yml', $this->getDataFolder() . 'forms.old.yml');
            $this->saveResource('forms.yml');
            $formsCfg->reload();
        }
    }

    public function checkVirions(): void{
        if(!class_exists(libasynql::class)) {
            $this->getLogger()->error('Virion "libasynql" was not found. Please download SkyBlocksPM from Poggit-CI for the plugin to work correctly.');
            throw new DisablePluginException;
        }
        if(!class_exists(BaseCommand::class)) {
            $this->getLogger()->error('Virion "Commando" was not found. Please download SkyBlocksPM from Poggit-CI for the plugin to work correctly.');
            throw new DisablePluginException;
        }
        if(!trait_exists(MarshalTrait::class)) {
            $this->getLogger()->error('Virion "libMarshal" was not found. Please download SkyBlocksPM from Poggit-CI for the plugin to work correctly.');
            throw new DisablePluginException;
        }
    }

    public function getNewConfig(): Config{
        return $this->config;
    }
}
