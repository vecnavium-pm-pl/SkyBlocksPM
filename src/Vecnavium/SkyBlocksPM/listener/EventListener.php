<?php

declare(strict_types=1);

namespace Vecnavium\SkyBlocksPM\listener;

use pocketmine\block\Chest;
use pocketmine\block\Door;
use pocketmine\event\block\BlockBreakEvent;
use pocketmine\event\block\BlockPlaceEvent;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\event\entity\EntityItemPickupEvent;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerChatEvent;
use pocketmine\event\player\PlayerInteractEvent;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\event\player\PlayerQuitEvent;
use pocketmine\item\Food;
use pocketmine\player\Player as P;
use pocketmine\utils\TextFormat;
use Vecnavium\SkyBlocksPM\player\Player;
use Vecnavium\SkyBlocksPM\skyblock\SkyBlock;
use Vecnavium\SkyBlocksPM\skyblock\SkyblockSettingTypes;
use Vecnavium\SkyBlocksPM\SkyBlocksPM;
use function in_array;
use function strval;

class EventListener implements Listener {

    private SkyBlocksPM $plugin;
    
    public function __construct(SkyBlocksPM $plugin) {
        $this->plugin = $plugin;
    }

    /**
     * @param PlayerJoinEvent $event
     * @return void
     */
    public function onJoin(PlayerJoinEvent $event): void {
        $player = $this->plugin->getPlayerManager()->getPlayer($event->getPlayer()->getName());
        if (!$player instanceof Player) {
            $this->plugin->getPlayerManager()->loadPlayer($event->getPlayer());
        }
    }

    /**
     * @param PlayerQuitEvent $event
     * @return void
     */
    public function onLeave(PlayerQuitEvent $event): void {
        $this->plugin->getPlayerManager()->unloadPlayer($event->getPlayer());
    }

    /**
     * @param BlockBreakEvent $event
     * @return void
     */
    public function onBreak(BlockBreakEvent $event): void {
        $player = $event->getPlayer();
        $skyblock = $this->plugin->getSkyBlockManager()->getSkyBlockByWorld($event->getBlock()->getPosition()->getWorld());
        if(!$skyblock instanceof SkyBlock) return;

        if (!in_array($player->getName(), $skyblock->getMembers(), true) && !$skyblock->getSetting(SkyblockSettingTypes::SETTING_BREAK)) {
            $event->cancel();
            return;
        }

        if ($this->plugin->getNewConfig()->settings->autoinv->enabled) {
            $drops = [];
            foreach ($event->getDrops() as $drop) {
                if (!$player->getInventory()->canAddItem($drop)) {
                    $drops[] = $drop;
                } else {
                    $player->getInventory()->addItem($drop);
                }
            }
            $event->setDrops([]);
            if ($this->plugin->getNewConfig()->settings->autoinv->dropWhenFull) {
                $event->setDrops($drops);
            }
        }
        if ($this->plugin->getNewConfig()->settings->autoxp) {
            $player->getXpManager()->addXp($event->getXpDropAmount());
            $event->setXpDropAmount(0);
        }
    }

    /**
     * @param BlockPlaceEvent $event
     * @return void
     */
    public function onPlace(BlockPlaceEvent $event): void {
        $skyblock = $this->plugin->getSkyBlockManager()->getSkyBlockByWorld($event->getPlayer()->getPosition()->getWorld());
        if(!$skyblock instanceof SkyBlock) return;

        if (!in_array($event->getPlayer()->getName(), $skyblock->getMembers(), true) && !$skyblock->getSetting(SkyblockSettingTypes::SETTING_PLACE)) {
            $event->cancel();
        }
    }

    /**
     * @param PlayerInteractEvent $event
     * @return void
     */
    public function onInteract(PlayerInteractEvent $event): void {
        $player = $event->getPlayer();
        $skyblock = $this->plugin->getSkyBlockManager()->getSkyBlockByWorld($player->getWorld());
        if(!$skyblock instanceof SkyBlock) return;
        if ($event->getItem() instanceof Food) return;

        if (!in_array($player->getName(), $skyblock->getMembers(), true)) {
            if ($event->getBlock() instanceof Chest) {
                if (!$skyblock->getSetting(SkyblockSettingTypes::SETTING_INTERACT_CHEST)) {
                    $event->cancel();
                }
            } elseif ($event->getBlock() instanceof Door) {
                if (!$skyblock->getSetting(SkyblockSettingTypes::SETTING_INTERACT_DOOR)) {
                    $event->cancel();
                }
            } else {
                $event->cancel();
            }
        }
    }

    /**
     * @param EntityDamageEvent $event
     * @return void
     */
    public function onPlayerDamage(EntityDamageEvent $event): void {
        $entity = $event->getEntity();
        if (!$entity instanceof P) return;
        $skyblock = $this->plugin->getSkyBlockManager()->getSkyBlockByWorld($entity->getWorld());
        if (!$skyblock instanceof SkyBlock) return;

        if ($event instanceof EntityDamageByEntityEvent && $skyblock->getSetting(SkyblockSettingTypes::SETTING_PVP)) return;

        $shouldCancel = match ($event->getCause()) {
            EntityDamageEvent::CAUSE_LAVA => $this->plugin->getNewConfig()->settings->damage->lava,
            EntityDamageEvent::CAUSE_DROWNING => $this->plugin->getNewConfig()->settings->damage->drown,
            EntityDamageEvent::CAUSE_FALL => $this->plugin->getNewConfig()->settings->damage->fall,
            EntityDamageEvent::CAUSE_PROJECTILE => $this->plugin->getNewConfig()->settings->damage->projectile,
            EntityDamageEvent::CAUSE_FIRE => $this->plugin->getNewConfig()->settings->damage->fire,
            EntityDamageEvent::CAUSE_VOID => $this->plugin->getNewConfig()->settings->damage->void,
            EntityDamageEvent::CAUSE_STARVATION => $this->plugin->getNewConfig()->settings->damage->hunger,
            default => $this->plugin->getNewConfig()->settings->damage->default
        };
        if ($shouldCancel) $event->cancel();
    }

    /**
     * @param PlayerChatEvent $event
     * @return void
     */
    public function onChat(PlayerChatEvent $event): void {
        $player = $event->getPlayer();
        if (!in_array($player->getName(), $this->plugin->getChat(), true)) return;

        $skyBlockPlayer = $this->plugin->getPlayerManager()->getPlayer($player->getName());
        if(!$skyBlockPlayer instanceof Player) return;

        $skyBlock = $this->plugin->getSkyBlockManager()->getSkyBlockByUuid($skyBlockPlayer->getSkyBlock());
        if (!$skyBlock instanceof SkyBlock) {
            $this->plugin->setPlayerChat($player, false);
            $player->sendMessage($this->plugin->getMessages()->getMessage('toggle-chat'));
            return;
        }
        foreach ($skyBlock->getMembers() as $member) {
            $m = $this->plugin->getServer()->getPlayerExact($member);
            if (!$m instanceof P) continue;
            $m->sendMessage(str_replace(['{PLAYER}', '{MSG}'], [$player->getName(), $event->getMessage()], TextFormat::colorize(strval($this->plugin->getMessages()->getMessageConfig()->get('skyblock-chat', '&d[SkyBlocksPM] &e[{PLAYER}] &6=> {MSG}')))));
        }
        $event->cancel();
    }

    public function onPickup(EntityItemPickupEvent $event): void {
        $entity = $event->getEntity();
        if(!$entity instanceof P) return;

        $skyblock = $this->plugin->getSkyBlockManager()->getSkyBlockByWorld($entity->getWorld());
        if (!$skyblock instanceof SkyBlock) return;

        if(!in_array($entity->getName(), $skyblock->getMembers(), true) && !$skyblock->getSetting(SkyblockSettingTypes::SETTING_PICKUP)) {
            $event->cancel();
        }
    }
}
