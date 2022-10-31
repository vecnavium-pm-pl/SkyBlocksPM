<?php

declare(strict_types=1);

namespace Vecnavium\SkyBlocksPM\listener;

use pocketmine\block\Chest;
use pocketmine\block\Door;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\event\entity\EntityItemPickupEvent;
use Vecnavium\SkyBlocksPM\SkyBlocksPM;
use Vecnavium\SkyBlocksPM\skyblock\SkyBlock;
use Vecnavium\SkyBlocksPM\player\Player;
use pocketmine\event\block\BlockBreakEvent;
use pocketmine\event\block\BlockPlaceEvent;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerChatEvent;
use pocketmine\event\player\PlayerInteractEvent;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\event\player\PlayerQuitEvent;
use pocketmine\item\Food;
use pocketmine\player\Player as P;
use pocketmine\utils\TextFormat;
use function in_array;
use function var_dump;

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
        $player = $this->plugin->getPlayerManager()->getPlayerByPrefix($event->getPlayer()->getName());
        if (!$player instanceof Player)
            $this->plugin->getPlayerManager()->loadPlayer($event->getPlayer());
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
        $skyblock = $this->plugin->getSkyBlockManager()->getSkyBlockByWorld($event->getBlock()->getPosition()->getWorld());
        if(!$skyblock instanceof SkyBlock) return;

        if (!in_array($event->getPlayer()->getName(), $skyblock->getMembers()) && !$skyblock->getSetting('break')) {
            $event->cancel();
            return;
        }

        if ($this->plugin->getConfig()->getNested('settings.autoinv.enabled', true)) {
            $drops = [];
            foreach ($event->getDrops() as $drop) {
                if (!$event->getPlayer()->getInventory()->canAddItem($drop))
                    $drops[] = $drop;
                else
                    $event->getPlayer()->getInventory()->addItem($drop);
            }
            $event->setDrops([]);
            if ($this->plugin->getConfig()->getNested('settings.autoinv.drop-when-full'))
                $event->setDrops($drops);
        }
        if ($this->plugin->getConfig()->getNested('settings.autoxp', true)) {
            $event->getPlayer()->getXpManager()->addXp($event->getXpDropAmount());
            $event->setXpDropAmount(0);
        }
    }

    /**
     * @param BlockPlaceEvent $event
     * @return void
     */
    public function onPlace(BlockPlaceEvent $event): void {
        $skyblock = $this->plugin->getSkyBlockManager()->getSkyBlockByWorld($event->getBlock()->getPosition()->getWorld());
        if(!$skyblock instanceof SkyBlock) return;

        if (!in_array($event->getPlayer()->getName(), $skyblock->getMembers()) && !$skyblock->getSetting('place'))
            $event->cancel();
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

        if (!in_array($player->getName(), $skyblock->getMembers()))
            if($event->getBlock() instanceof Chest )
                if(!$skyblock->getSetting('interact_chest'))
                    $event->cancel();
            elseif ($event->getBlock() instanceof Door)
                if(!$skyblock->getSetting('interact_door'))
                    $event->cancel();
            else
                $event->cancel();
    }

    /**
     * @param EntityDamageEvent $event
     * @return void
     */
    public function onPlayerDamage(EntityDamageEvent $event): void
    {
        $entity = $event->getEntity();
        if (!$entity instanceof P) return;
        $skyblock = $this->plugin->getSkyBlockManager()->getSkyBlockByWorld($entity->getWorld());
        if (!$skyblock instanceof SkyBlock) return;

        if ($event instanceof EntityDamageByEntityEvent && $skyblock->getSetting('pvp')) return;

        $type = match ($event->getCause()) {
            EntityDamageEvent::CAUSE_LAVA => 'lava',
            EntityDamageEvent::CAUSE_DROWNING => 'drown',
            EntityDamageEvent::CAUSE_FALL => 'fall',
            EntityDamageEvent::CAUSE_PROJECTILE => 'projectile',
            EntityDamageEvent::CAUSE_FIRE => 'fire',
            EntityDamageEvent::CAUSE_VOID => 'void',
            EntityDamageEvent::CAUSE_STARVATION => 'hunger',
            default => 'default'
        };
        if ($this->plugin->getConfig()->getNested("settings.damage.$type", true))
            $event->cancel();
    }

    public function onChat(PlayerChatEvent $event): void {
        if (!in_array($event->getPlayer()->getName(), $this->plugin->getChat())) return;

        $skyBlock = $this->plugin->getSkyBlockManager()->getSkyBlockByUuid($this->plugin->getPlayerManager()->getPlayer($event->getPlayer())->getSkyBlock());
        if (!$skyBlock instanceof SkyBlock) {
            $this->plugin->removePlayerFromChat($event->getPlayer());
            $event->getPlayer()->sendMessage($this->plugin->getMessages()->getMessage('toggle-chat'));
            return;
        }
        foreach ($skyBlock->getMembers() as $member) {
            $m = $this->plugin->getServer()->getPlayerByPrefix($member);
            if (!$m instanceof P) continue;
            $m->sendMessage(str_replace(['{PLAYER}', '{MSG}'], [$event->getPlayer()->getName(), $event->getMessage()], TextFormat::colorize($this->plugin->getMessages()->getMessageConfig()->get('skyblock-chat', '&d[SkyBlocksPM] &e[{PLAYER}] &6=> {MSG}'))));
        }
        $event->cancel();
    }

    public function onPickup(EntityItemPickupEvent $event): void {
        $entity = $event->getEntity();
        if(!$entity instanceof P) return;

        $skyblock = $this->plugin->getSkyBlockManager()->getSkyBlockByWorld($entity->getWorld());
        if (!$skyblock instanceof SkyBlock) return;

        if(!in_array($entity->getName(), $skyblock->getMembers()) && !$skyblock->getSetting('pickup'))
            $event->cancel();
    }
}
