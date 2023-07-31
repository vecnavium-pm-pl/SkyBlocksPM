<?php
declare(strict_types=1);

namespace Vecnavium\SkyBlocksPM\commands\args;

use CortexPE\Commando\args\BaseArgument;
use pocketmine\command\CommandSender;
use pocketmine\network\mcpe\protocol\AvailableCommandsPacket;
use function preg_match;

class IslandNameArgument extends BaseArgument {

    /**
     * @return int
     */
    public function getNetworkType(): int{
        return AvailableCommandsPacket::ARG_TYPE_STRING;
    }

    /**
     * @param string $testString
     * @param CommandSender $sender
     * @return bool
     */
    public function canParse(string $testString, CommandSender $sender): bool {
        // PM player username validity regex
        return (bool)preg_match('/^\w{3,32}$/i', $testString);
    }

    /**
     * @param string $argument
     * @param CommandSender $sender
     * @return mixed
     */
    public function parse(string $argument, CommandSender $sender): mixed {
        return $argument;
    }

    /**
     * @return string
     */
    public function getTypeName(): string{
        return 'island';
    }
}