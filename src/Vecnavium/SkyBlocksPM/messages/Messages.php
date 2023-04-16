<?php

declare(strict_types=1);

namespace Vecnavium\SkyBlocksPM\messages;

use pocketmine\utils\Config;
use pocketmine\utils\TextFormat;
use Vecnavium\SkyBlocksPM\SkyBlocksPM;

class Messages {

    private Config $messages;

    public function __construct(SkyBlocksPM $plugin) {
        $this->messages = new Config($plugin->getDataFolder() . 'messages.yml', Config::YAML);
    }

    public function getMessage(string $msg, array $args = []): string {
        $message = $this->messages->getNested("messages.$msg");
        foreach ($args as $key => $value) {
            $message = str_replace($key, $value, $message);
        }
        return  TextFormat::colorize("{$this->messages->get('prefix')} {$this->messages->get('separator')} $message");
    }

    public function getMessageConfig(): Config {
        return $this->messages;
    }
}
