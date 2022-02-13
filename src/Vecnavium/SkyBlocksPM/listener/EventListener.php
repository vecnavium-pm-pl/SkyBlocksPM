<?php

declare(strict_types=1);

namespace Vecnavium\SkyBlocksPM\listener;

use Vecnavium\SkyBlocksPM\SkyBlocksPM;
use Vecnavium\SkyBlocksPM\skyblock\SkyBlock;
use Vecnavium\SkyBlocksPM\player\Player;
use pocketmine\block\VanillaBlocks;
use pocketmine\event\block\BlockBreakEvent;
use pocketmine\event\block\BlockPlaceEvent;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerInteractEvent;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\event\player\PlayerQuitEvent;
use pocketmine\item\Food;
use pocketmine\player\Player as P;
use pocketmine\scheduler\ClosureTask;

class EventListener implements Listener
{

    /**
     * @param PlayerJoinEvent $event
     * @return void
     */
    public function onJoin(PlayerJoinEvent $event): void
    {
        $player = SkyBlocksPM::getInstance()->getPlayerManager()->getPlayerByPrefix($event->getPlayer()->getName());
        if (!$player instanceof Player)
            SkyBlocksPM::getInstance()->getPlayerManager()->loadPlayer($event->getPlayer());
    }

    /**
     * @param PlayerQuitEvent $event
     * @return void
     */
    public function onLeave(PlayerQuitEvent $event): void
    {
        SkyBlocksPM::getInstance()->getPlayerManager()->unloadPlayer($event->getPlayer());
    }

    /**
     * @param BlockBreakEvent $event
     * @return void
     */
    public function onBreak(BlockBreakEvent $event): void
    {
        if (!SkyBlocksPM::getInstance()->getSkyBlockManager()->isSkyBlockWorld($event->getPlayer()->getWorld()->getFolderName()))
            return;
        $block = $event->getBlock();
        $skyblock = SkyBlocksPM::getInstance()->getSkyBlockManager()->getSkyBlockByWorld($block->getPosition()->getWorld());
        if (!in_array($event->getPlayer()->getName(), $skyblock->getMembers())) {
            $event->cancel();
            return;
        }
        if (SkyBlocksPM::getInstance()->getConfig()->getNested('settings.autoinv.enabled', true)) {
            $drops = [];
            foreach ($event->getDrops() as $drop) {
                if (!$event->getPlayer()->getInventory()->canAddItem($drop))
                    $drops[] = $drop;
                else
                    $event->getPlayer()->getInventory()->addItem($drop);
            }
            $event->setDrops([]);
            if (SkyBlocksPM::getInstance()->getConfig()->getNested('settings.autoinv.drop-when-full'))
                $event->setDrops($drops);
        }
        if (SkyBlocksPM::getInstance()->getConfig()->getNested('settings.autoxp', true)) {
            $event->getPlayer()->getXpManager()->addXp($event->getXpDropAmount());
            $event->setXpDropAmount(0);
        }
    }

    /**
     * @param BlockPlaceEvent $event
     * @return void
     */
    public function onPlace(BlockPlaceEvent $event): void
    {
        if (!SkyBlocksPM::getInstance()->getSkyBlockManager()->isSkyBlockWorld($event->getPlayer()->getWorld()->getFolderName()))
            return;
        $skyblock = SkyBlocksPM::getInstance()->getSkyBlockManager()->getSkyBlockByWorld($event->getBlock()->getPosition()->getWorld());
        if (!in_array($event->getPlayer()->getName(), $skyblock->getMembers()))
        {
            $event->cancel();
        }
    }

    /**
     * @param PlayerInteractEvent $event
     * @return void
     */
    public function onInteract(PlayerInteractEvent $event): void
    {
        if (!SkyBlocksPM::getInstance()->getSkyBlockManager()->isSkyBlockWorld($event->getPlayer()->getWorld()->getFolderName()))
            return;
        if ($event->getItem() instanceof Food)
            return;
        $skyblock = SkyBlocksPM::getInstance()->getSkyBlockManager()->getSkyBlockByWorld($event->getPlayer()->getWorld());
        if (!in_array($event->getPlayer()->getName(), $skyblock->getMembers()))
        {
            $event->cancel();
        }
    }

    /**
     * @param EntityDamageEvent $event
     * @return void
     */
    public function onPlayerDamage(EntityDamageEvent $event): void
    {
        $entity = $event->getEntity();
        if (!$entity instanceof P)
            return;
        if (!SkyBlocksPM::getInstance()->getSkyBlockManager()->getSkyBlockByWorld($entity->getWorld()) instanceof SkyBlock)
            return;
        $type = match ($event->getCause()) {
            EntityDamageEvent::CAUSE_ENTITY_ATTACK => 'player',
            EntityDamageEvent::CAUSE_LAVA => 'lava',
            EntityDamageEvent::CAUSE_DROWNING => 'drown',
            EntityDamageEvent::CAUSE_FALL => 'fall',
            EntityDamageEvent::CAUSE_PROJECTILE => 'projectile',
            EntityDamageEvent::CAUSE_FIRE => 'fire',
            EntityDamageEvent::CAUSE_VOID => 'void',
            EntityDamageEvent::CAUSE_STARVATION => 'hunger',
            default => 'default'
        };
        if (SkyBlocksPM::getInstance()->getConfig()->getNested("settings.damage.$type", true))
            $event->cancel();
    }

}
