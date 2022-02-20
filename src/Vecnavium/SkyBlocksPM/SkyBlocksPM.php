<?php

declare(strict_types=1);

namespace Vecnavium\SkyBlocksPM;

use Vecnavium\SkyBlocksPM\commands\SkyBlockCommand;
use Vecnavium\SkyBlocksPM\libs\CortexPE\Commando\PacketHooker;
use Vecnavium\SkyBlocksPM\generator\Generator;
use Vecnavium\SkyBlocksPM\invites\InviteManager;
use Vecnavium\SkyBlocksPM\listener\EventListener;
use Vecnavium\SkyBlocksPM\messages\Messages;
use Vecnavium\SkyBlocksPM\skyblock\SkyBlockManager;
use Vecnavium\SkyBlocksPM\player\PlayerManager;
use pocketmine\plugin\PluginBase;
use Vecnavium\SkyBlocksPM\libs\poggit\libasynql\DataConnector;
use Vecnavium\SkyBlocksPM\libs\poggit\libasynql\libasynql;

class SkyBlocksPM extends PluginBase
{

    /** @var DataConnector */
    private DataConnector $dataConnector;
    /** @var Generator */
    private Generator $generator;
    /** @var Messages */
    private Messages $messages;
    /** @var PlayerManager */
    private PlayerManager $playerManager;
    /** @var SkyBlockManager */
    private SkyBlockManager $SkyBlockManager;
    /** @var InviteManager */
    private InviteManager $inviteManager;
    /** @var SkyBlocksPM */
    private static self $instance;

    protected function onLoad(): void
    {
        self::$instance = $this;
    }

    public function onEnable(): void
    {
        if (!PacketHooker::isRegistered())
            PacketHooker::register($this);
        $this->saveDefaultConfig();
        $this->saveResource('messages.yml');
        $this->saveResource('forms.yml');
        $this->initDataBase();
        $this->getServer()->getPluginManager()->registerEvents(new EventListener(), $this);
        $this->generator = new Generator();
        $this->messages = new Messages();
        $this->playerManager = new PlayerManager();
        $this->SkyBlockManager = new SkyBlockManager();
        $this->inviteManager = new InviteManager();
        $this->getServer()->getCommandMap()->register('SkyBlocksPM', new SkyBlockCommand($this, 'skyblock', 'The core command for SkyBlocks', ['sb']));
        @mkdir($this->getDataFolder() . "cache");
        @mkdir($this->getDataFolder() . "cache/island");
    }

    public function onDisable(): void
    {
        $this->dataConnector->waitAll();
        $this->dataConnector->close();
    }

    public function initDataBase(): void
    {
        $db = libasynql::create($this, $this->getConfig()->get('database'), ['sqlite' => 'sqlite.sql']);
        $db->executeGeneric('skyblockspm.player.init');
        $db->executeGeneric('skyblockspm.sb.init');
        $db->waitAll();
        $this->dataConnector = $db;
    }

    /**
     * @return DataConnector
     */
    public function getDataBase(): DataConnector
    {
        return $this->dataConnector;
    }

    /**
     * @return Generator
     */
    public function getGenerator(): Generator
    {
        return $this->generator;
    }

    /**
     * @return Messages
     */
    public function getMessages(): Messages
    {
        return $this->messages;
    }

    /**
     * @return PlayerManager
     */
    public function getPlayerManager(): PlayerManager
    {
        return $this->playerManager;
    }

    /**
     * @return SkyBlockManager
     */
    public function getSkyBlockManager(): SkyBlockManager
    {
        return $this->SkyBlockManager;
    }

    /**
     * @return InviteManager
     */
    public function getInviteManager(): InviteManager
    {
        return $this->inviteManager;
    }

    /**
     * @return self
    */
    public static function getInstance(): self
    {
        return self::$instance;
    }

}
