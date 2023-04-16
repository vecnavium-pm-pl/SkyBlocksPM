<?php
declare(strict_types=1);

namespace Vecnavium\SkyBlocksPM\commands\args;

use CortexPE\Commando\args\BaseArgument;
use pocketmine\command\CommandSender;
use pocketmine\network\mcpe\protocol\AvailableCommandsPacket;
use pocketmine\Server;
use function preg_match;

class PlayerArgument extends BaseArgument {

    public function getNetworkType(): int{
        return AvailableCommandsPacket::ARG_TYPE_TARGET;
    }

    public function canParse(string $testString, CommandSender $sender): bool {
        // PM player username validity regex
        return (bool)preg_match("/^(?!rcon|console)[a-zA-Z0-9_ ]{1,16}$/i", $testString);
    }

    public function parse(string $argument, CommandSender $sender) {
        $player = Server::getInstance()->getPlayerExact($argument);
        if($player !== null){
            return $player;
        }
        return $argument;
    }

    public function getTypeName(): string{
        return "player";
    }
}