<?php

declare(strict_types=1);

namespace Vecnavium\SkyBlocksPM\messages;

use pocketmine\utils\Config;
use pocketmine\utils\TextFormat;
use Vecnavium\SkyBlocksPM\SkyBlocksPM;

class Messages {

    private Config $messages;
    private string $prefix = "", $separator = "";

    public function __construct(SkyBlocksPM $plugin) {
        $this->messages = new Config($plugin->getDataFolder() . 'messages.yml', Config::YAML);
        $this->prefix = (string)$this->messages->get("prefix");
        $this->separator = (string)$this->messages->get("separator");
    }

    /**
     * @param string $msg
     * @param string[] $args
     * @return string
     */
    public function getMessage(string $msg, array $args = []): string {
        $message = (string)$this->messages->getNested("messages.$msg");
        foreach ($args as $key => $value) {
            $message = str_replace($key, $value, $message);
        }
        return  TextFormat::colorize("{$this->prefix} {$this->separator} $message");
    }

    /**
     * @return Config
     */
    public function getMessageConfig(): Config {
        return $this->messages;
    }
}
