<?php

declare(strict_types=1);

namespace Vecnavium\SkyBlocksPM\skyblock;

use Vecnavium\SkyBlocksPM\SkyBlocksPM;
use pocketmine\math\Vector3;

class SkyBlock
{

    /**@var string*/
    private string $uuid, $name, $leader, $world;
    /**@var array*/
    private array $members;
    /** @var array */
    private array $settings;
    /**@var Vector3*/
    private Vector3 $spawn;

    public function __construct(string $uuid, string $name, string $leader, array $members, string $world, array $settings, Vector3 $spawn)
    {
        $this->uuid = $uuid;
        $this->name = $name;
        $this->leader = $leader;
        $this->members = $members;
        $this->world = $world;
        $this->settings = $settings;
        $this->spawn = $spawn;
    }

    /**
     * @return string
     */
    public function getUuid(): string
    {
        return $this->uuid;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @param string $name
     */
    public function setName(string $name): void
    {
        $this->name = $name;
        $this->save();
    }

    /**
     * @return string
     */
    public function getLeader(): string
    {
        return $this->leader;
    }

    /**
     * @param string $leader
     */
    public function setLeader(string $leader): void
    {
        $this->leader = $leader;
        $this->save();
    }

    /**
     * @return array
     */
    public function getMembers(): array
    {
        return $this->members;
    }

    /**
     * @param array $members
     */
    public function setMembers(array $members): void
    {
        $this->members = $members;
        $this->save();
    }

    /**
     * @return string
     */
    public function getWorld(): string
    {
        return $this->world;
    }

    /**
     * @param string $world
     */
    public function setWorld(string $world): void
    {
        $this->world = $world;
        $this->save();
    }

    /**
     * @return array
     */
    public function getSettings(): array
    {
        return $this->settings;
    }

    /**
     * @param array $settings
     */
    public function updateSettings(array $settings): void
    {
        $this->settings = $settings;
    }

    /**
     * @return Vector3
     */
    public function getSpawn(): Vector3
    {
        return $this->spawn;
    }

    /**
     * @param Vector3 $spawn
     */
    public function setSpawn(Vector3 $spawn): void
    {
        $this->spawn = $spawn;
        $this->save();
    }

    public function save(): void
    {
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
