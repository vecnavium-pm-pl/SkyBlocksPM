<?php

declare(strict_types=1);

namespace Vecnavium\SkyBlocksPM\messages;

use Vecnavium\SkyBlocksPM\SkyBlocksPM;
use pocketmine\utils\Config;
use pocketmine\utils\TextFormat;

class Messages
{

    /**@var Config*/
    private Config $messages;

    public function __construct()
    {
        $this->messages = new Config(SkyBlocksPM::getInstance()->getDataFolder() . "messages.yml", Config::YAML);
    }

    public function getMessage(string $msg, array $args = []): string
    {
        $message = $this->messages->getNested("messages.$msg");
        foreach ($args as $key => $value)
            $message = str_replace($key, $value, $message);
        return  TextFormat::colorize("{$this->messages->get('prefix')} {$this->messages->get('seperator')} $message");
    }

}
