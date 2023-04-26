<?php

declare(strict_types=1);

namespace Vecnavium\SkyBlocksPM\invites;

use pocketmine\player\Player;

class InviteManager {

    /** @var Invite[]  */
    private array $invites = [];

    public function addInvite(string $id, Player $inviter, Player $receiver): void {
        $this->invites[$id] = new Invite($id, $inviter, $receiver);
    }

    /**
     * @param string $name
     * @return Invite|null
     */
    public function getPlayerInvites(string $name): ?Invite {
        foreach ($this->invites as $invite) {
            if ($invite->getInviter()->getName() == $name) return $invite;
        }
        return null;
    }

    /**
     * @param Player $player
     * @return bool
     */
    public function canInvite(Player $player): bool {
        foreach ($this->invites as $invite) {
            if ($invite->getInviter()->getName() == $player->getName()) return false;
        }
        return true;
    }

    public function cancelInvite(string $id, bool $sendMessage = false): void{
        if (!isset($this->invites[$id])) return;
        if($sendMessage) {
            $this->invites[$id]->cancel();
        }
        unset($this->invites[$id]);
    }

    /**
     * @param string $id
     * @return bool
     */
    public function isInviteValid(string $id): bool{
        return isset($this->invites[$id]);
    }
}
