<?php

declare(strict_types=1);

namespace Vecnavium\SkyBlocksPM\skyblock;

use pocketmine\math\Vector3;
use Vecnavium\SkyBlocksPM\SkyBlocksPM;

class SkyBlock {

    /**
     * @param string $uuid
     * @param string $name
     * @param string $leader
     * @param string[] $members
     * @param string $world
     * @param array<string,bool> $settings
     * @param Vector3 $spawn
     */
    public function __construct(
        private string $uuid,
        private string $name,
        private string $leader,
        private array $members,
        private string $world,
        private array $settings,
        private Vector3 $spawn
    ) {}

    /**
     * @return string
     */
    public function getUuid(): string {
        return $this->uuid;
    }

    /**
     * @return string
     */
    public function getName(): string {
        return $this->name;
    }

    /**
     * @param string $name
     */
    public function setName(string $name): void {
        $this->name = $name;
        $this->save();
    }

    /**
     * @return string
     */
    public function getLeader(): string {
        return $this->leader;
    }

    /**
     * @param string $leader
     */
    public function setLeader(string $leader): void {
        $this->leader = $leader;
        $this->save();
    }

    /**
     * @return string[]
     */
    public function getMembers(): array {
        return $this->members;
    }

    /**
     * @param string[] $members
     */
    public function setMembers(array $members): void {
        $this->members = $members;
        $this->save();
    }

    /**
     * @return string
     */
    public function getWorld(): string {
        return $this->world;
    }

    /**
     * @param string $world
     */
    public function setWorld(string $world): void {
        $this->world = $world;
        $this->save();
    }

    /**
     * @return array<string,bool>
     */
    public function getSettings(): array {
        return $this->settings;
    }

    /**
     * @param string $setting
     * @return bool
     */
    public function getSetting(string $setting): bool {
        return (isset($this->settings[$setting]) && $this->settings[$setting]);
    }

    /**
     * @param array<string,bool> $settings
     */
    public function updateSettings(array $settings): void {
        $this->settings = $settings;
        $this->save();
    }

    /**
     * @return Vector3
     */
    public function getSpawn(): Vector3 {
        return $this->spawn;
    }

    /**
     * @param Vector3 $spawn
     */
    public function setSpawn(Vector3 $spawn): void {
        $this->spawn = $spawn;
        $this->save();
    }

    public function save(): void {
        $spawn = [
            'x' => $this->spawn->getX(),
            'y' => $this->spawn->getY(),
            'z' => $this->spawn->getZ()
        ];
        SkyBlocksPM::getInstance()->getDataBase()->executeChange('skyblockspm.sb.update', [
            'uuid' => $this->uuid,
            'name' => $this->name,
            'leader' => $this->leader,
            'members' => implode(',', $this->members),
            'world' => $this->world,
            'settings' => json_encode($this->settings),
            'spawn' => json_encode($spawn)
        ]);
    }
}
