<?php

declare(strict_types=1);

namespace kostamax27\HypixelBow;

use pocketmine\player\Player;
use pocketmine\event\Listener;
use pocketmine\plugin\PluginBase;
use Vecnavium\FormsUI\CustomForm;
use pocketmine\entity\projectile\Arrow;
use kostamax27\HypixelBow\command\hBowCommand;
use pocketmine\event\entity\ProjectileHitEntityEvent;
use pocketmine\network\mcpe\protocol\PlaySoundPacket;

class HypixelBow extends PluginBase implements Listener {

    /** @var array */
    private array $data = [];

    /**
     * @return void
     */
    public function onLoad(): void {
        $this->saveDefaultConfig();

        $this->data = $this->getConfig()->getAll();
    }

    /**
     * @return void
     */
    public function onEnable(): void {
        $this->getServer()->getPluginManager()->registerEvents($this, $this);

        $this->getServer()->getCommandMap()->register($this->getName(), new hBowCommand($this, "hbow", "HypixelBow settings"));
    }

    /**
     * @param Player $player
     *
     * @return void
     */
    public function getHypixelBowSettings(Player $player): void {
        $form = new CustomForm(function(Player $player, ?array $data = null): void {
            if($data === null) {
                return;
            }

            $this->data["sound"]["enable"] = (bool) $data[1];
            $this->data["sound"]["volume"] = (float) $data[2];
            $this->data["sound"]["pitch"] = (float) $data[3];
            $this->data["sound"]["name"] = (string) $data[4];

            $this->data["message"]["enable"] = (bool) $data[6];
            $this->data["message"]["message"] = (string) $data[7];

            $this->data["popup"]["enable"] = (bool) $data[8];
            $this->data["popup"]["message"] = (string) $data[9];

            $this->data["tip"]["enable"] = (bool) $data[10];
            $this->data["tip"]["message"] = (string) $data[11];

            $this->saveData();
        });
        $form->setTitle("HypixelBowSettings");
        $form->addLabel("Sound settings: \n\n");
        $form->addToggle("Enable sound:", (bool) $this->data["sound"]["enable"]);
        $form->addInput("Sound volume: ", "float: 1.0", (string) $this->data["sound"]["volume"]);
        $form->addInput("Sound pitch: ", "float: 1.0", (string) $this->data["sound"]["pitch"]);
        $form->addInput("Sound name: ", "string: random.orb", (string) $this->data["sound"]["name"]);
        $form->addLabel("Message settings: \n\n");
        $form->addToggle("Enable message:", (bool) $this->data["message"]["enable"]);
        $form->addInput("Hit message: ", "string: message", (string) $this->data["message"]["message"]);
        $form->addToggle("Enable popup:", (bool) $this->data["popup"]["enable"]);
        $form->addInput("Hit popup: ", "string: popup", (string) $this->data["popup"]["message"]);
        $form->addToggle("Enable tip:", (bool) $this->data["tip"]["enable"]);
        $form->addInput("Hit tip: ", "string: tip", (string) $this->data["tip"]["message"]);

        $player->sendForm($form);
    }

    /**
     *
     * @noinspection PhpUnused
     *
     * @param ProjectileHitEntityEvent $event
     *
     * @return void
     */
    public function onProjectileHit(ProjectileHitEntityEvent $event): void {
        $projectile = $event->getEntity();

        if(!($projectile instanceof Arrow)) {
            return;
        }

        $owner = $projectile->getOwningEntity();
        $target = $event->getEntityHit();

        if(!($owner instanceof Player && $target instanceof Player)) {
            return;
        }

        if($this->data["sound"]["enable"]) {
            $this->playSound($owner, $this->data["sound"]["volume"], $this->data["sound"]["pitch"], $this->data["sound"]["name"]);
        }
        if($this->data["message"]["enable"]) {
            $owner->sendMessage(str_replace(["{hp}", "{damage}", "{name}", "{display}"], [$target->getHealth(), $projectile->getResultDamage(), $target->getName(), $target->getDisplayName()], $this->data["message"]["message"]));
        }
        if($this->data["popup"]["enable"]) {
            $owner->sendPopup(str_replace(["{hp}", "{damage}", "{name}", "{display}"], [$target->getHealth(), $projectile->getResultDamage(), $target->getName(), $target->getDisplayName()], $this->data["popup"]["message"]));
        }
        if($this->data["tip"]["enable"]) {
            $owner->sendTip(str_replace(["{hp}", "{damage}", "{name}", "{display}"], [$target->getHealth(), $projectile->getResultDamage(), $target->getName(), $target->getDisplayName()], $this->data["tip"]["message"]));
        }
    }

    /**
     * @param Player $player
     * @param float $volume
     * @param float $pitch
     * @param string $sound
     *
     * @return void
     */
    private function playSound(Player $player, float $volume = 1.0, float $pitch = 1.0, string $sound = ""): void {
        $position = $player->getPosition();

        $player->getNetworkSession()->sendDataPacket(PlaySoundPacket::create(
            soundName: $sound,
            x: $position->getX(),
            y: $position->getY(),
            z: $position->getZ(),
            volume: $volume,
            pitch: $pitch
        ));
    }

    /**
     * @return void
     * @throws \JsonException
     */
    private function saveData(): void {
        $this->getConfig()->setAll($this->data);
        $this->getConfig()->save();
    }
}
