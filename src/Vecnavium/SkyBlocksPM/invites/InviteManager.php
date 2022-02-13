<?php

declare(strict_types=1);

namespace Vecnavium\SkyBlocksPM\invites;

use pocketmine\player\Player;

class InviteManager
{

    /** @var Invite[]  */
    private array $invites = [];

    public function addInvite(string $id, Player $inviter, Player $receiver): void
    {
        $this->invites[$id] = new Invite($inviter, $receiver);
    }

    public function getPlayerInvites(string $name): ?Invite
    {
        foreach ($this->invites as $invite)
        {
            if ($invite->getInviter()->getName() == $name)
                return $invite;
        }
        return null;
    }

    public function canInvite(Player $player): bool
    {
        foreach ($this->invites as $invite)
        {
            if ($invite->getInviter() instanceof Player)
                if ($invite->getInviter()->getName() == $player->getName())
                    return false;
        }
        return true;
    }

    public function cancelInvite(string $id)
    {
        if (!isset($this->invites[$id]))
            return;
        $this->invites[$id]->cancel();
        unset($this->invites[$id]);
    }

}
