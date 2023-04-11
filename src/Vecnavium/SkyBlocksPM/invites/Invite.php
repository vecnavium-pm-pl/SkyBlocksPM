<?php

declare(strict_types=1);

namespace Vecnavium\SkyBlocksPM\invites;

use Vecnavium\SkyBlocksPM\SkyBlocksPM;
use pocketmine\player\Player;

class Invite {

    public function __construct(
        private string $id,
        private Player $inviter,
        private Player $receiver
    ) {}

    public function getId(): string {
        return $this->id;
    }

    public function getInviter(): Player {
        return $this->inviter;
    }

    public function getReceiver(): Player {
        return $this->receiver;
    }

    public function cancel(): void {
        /** @var Player[] $players */
        $players = [$this->inviter, $this->receiver];
        foreach ($players as $player) {
            if ($player->isConnected()) {
                $player->sendMessage(SkyBlocksPM::getInstance()->getMessages()->getMessage('invite-expired'));
            }
        }
    }

    public function handleInvite(): bool {
        if (!$this->inviter->isConnected()) return false;

        $inviter = SkyBlocksPM::getInstance()->getPlayerManager()->getPlayer($this->inviter->getName());
        $receiver = SkyBlocksPM::getInstance()->getPlayerManager()->getPlayer($this->receiver->getName());

        if ($inviter->getSkyBlock() == '' || $receiver->getSkyBlock() !== '') return false;
        return true;
    }

}
