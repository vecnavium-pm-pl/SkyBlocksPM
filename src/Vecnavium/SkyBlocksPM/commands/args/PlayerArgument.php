<?php
declare(strict_types=1);

namespace Vecnavium\SkyBlocksPM\commands\args;

use CortexPE\Commando\args\BaseArgument;
use pocketmine\command\CommandSender;
use pocketmine\network\mcpe\protocol\AvailableCommandsPacket;
use pocketmine\Server;
use function preg_match;

class PlayerArgument extends BaseArgument {

    /**
     * @return int
     */
    public function getNetworkType(): int{
        return AvailableCommandsPacket::ARG_TYPE_TARGET;
    }

    /**
     * @param string $testString
     * @param CommandSender $sender
     * @return bool
     */
    public function canParse(string $testString, CommandSender $sender): bool {
        // PM player username validity regex
        return (bool)preg_match('/^(?!rcon|console)[a-zA-Z0-9_ ]{1,16}$/i', $testString);
    }

    /**
     * @param string $argument
     * @param CommandSender $sender
     * @return mixed
     */
    public function parse(string $argument, CommandSender $sender): mixed {
        $player = Server::getInstance()->getPlayerExact($argument);
        if($player !== null){
            return $player;
        }
        return $argument;
    }

    /**
     * @return string
     */
    public function getTypeName(): string{
        return 'player';
    }
}