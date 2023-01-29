<?php

declare(strict_types=1);

namespace kostamax27\HypixelBow;

use pocketmine\utils\Config;
use pocketmine\player\Player;
use pocketmine\event\Listener;
use dktapps\pmforms\CustomForm;
use pocketmine\plugin\PluginBase;
use dktapps\pmforms\element\Label;
use dktapps\pmforms\element\Input;
use dktapps\pmforms\element\Toggle;
use dktapps\pmforms\CustomFormResponse;
use pocketmine\entity\projectile\Arrow;
use kostamax27\HypixelBow\command\hBowCommand;
use pocketmine\event\entity\ProjectileHitEvent;
use pocketmine\event\entity\ProjectileHitEntityEvent;
use pocketmine\network\mcpe\protocol\PlaySoundPacket;

class HypixelBow extends PluginBase implements Listener {

	/** @var Config */
	private Config $config;

	/** @var array */
	private array $data = [];

	/**
	 * @return void
	 */
	public function onLoad(): void {
		$this->saveDefaultConfig();
		$this->config = new Config($this->getDataFolder() . "config.yml", Config::YAML);
		$this->data = $this->getConfig()->getAll();
	}

	/**
	 * @return void
	 */
	public function onEnable(): void {
		$this->getServer()->getPluginManager()->registerEvents($this, $this);

		$this->getServer()->getCommandMap()->register($this->getName(), new hBowCommand($this, "hbow", "HypixelBow settings", null, []));
	}

	/**
	 * @param Player $player
	 *
	 * @return void
	 */
	public function getHypixelBowSettings(Player $player): void {
		$player->sendForm(new CustomForm(
			"HypixelBowSettings",
			[
				new Label("label_0", "Sound settings: \n\n"),
				new Toggle("toggle_0", "Enable sound:", $this->data["sound"]["enable"]),
				new Input("input_0", "Sound volume: ", "float: 1.0", $this->data["sound"]["volume"]),
				new Input("input_1", "Sound pitch: ", "float: 1.0", $this->data["sound"]["pitch"]),
				new Input("input_2", "Sound name: ", "string: random.orb", $this->data["sound"]["name"]),
				new Label("label_1", "Message settings: \n\n"),
				new Toggle("toggle_1", "Enable message:", $this->data["message"]["enable"]),
				new Input("input_3", "Hit message: ", "string: message", $this->data["message"]["message"]),
				new Toggle("toggle_2", "Enable popup:", $this->data["popup"]["enable"]),
				new Input("input_4", "Hit popup: ", "string: popup", $this->data["popup"]["message"]),
				new Toggle("toggle_3", "Enable tip:", $this->data["tip"]["enable"]),
				new Input("input_5", "Hit tip: ", "string: tip", $this->data["tip"]["message"])
			],
			function(Player $player, CustomFormResponse $response) : void {
				$this->data["sound"]["enable"] = $response->getBool("toggle_0");
				$this->data["sound"]["volume"] = floatval($response->getString("input_0"));
				$this->data["sound"]["pitch"] = floatval($response->getString("input_1"));
				$this->data["sound"]["name"] = $response->getString("input_2");

				$this->data["message"]["enable"] = $response->getBool("toggle_1");
				$this->data["message"]["message"] = $response->getString("input_3");

				$this->data["popup"]["enable"] = $response->getBool("toggle_2");
				$this->data["popup"]["message"] = $response->getString("input_4");

				$this->data["tip"]["enable"] = $response->getBool("toggle_3");
				$this->data["tip"]["message"] = $response->getString("input_5");

				$this->saveData();
			}
		));
	}

	/**
	 *
	 * @noinspection PhpUnused
	 *
	 * @param ProjectileHitEvent $event
	 *
	 * @return void
	 */
	public function onProjectileHit(ProjectileHitEvent $event): void {
		$projectile = $event->getEntity();

		if(!$projectile instanceof Arrow) return;

		$player = $projectile->getOwningEntity();
		if($player instanceof Player && $event instanceof ProjectileHitEntityEvent) {
			$target = $event->getEntityHit();
			if($target instanceof Player) {
				if($this->data["sound"]["enable"]) {
					$this->playSound($player, $this->data["sound"]["volume"], $this->data["sound"]["pitch"], $this->data["sound"]["name"]);
				}
				if($this->data["message"]["enable"]) {
					$player->sendMessage(str_replace(["{hp}", "{damage}", "{name}", "{display}"], [$target->getHealth(), $projectile->getResultDamage(), $target->getName(), $target->getDisplayName()], $this->data["message"]["message"]));
				}
				if($this->data["popup"]["enable"]) {
					$player->sendPopup(str_replace(["{hp}", "{damage}", "{name}", "{display}"], [$target->getHealth(), $projectile->getResultDamage(), $target->getName(), $target->getDisplayName()], $this->data["popup"]["message"]));
				}
				if($this->data["tip"]["enable"]) {
					$player->sendTip(str_replace(["{hp}", "{damage}", "{name}", "{display}"], [$target->getHealth(), $projectile->getResultDamage(), $target->getName(), $target->getDisplayName()], $this->data["tip"]["message"]));
				}
			}
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
	 */
	private function saveData(): void {
		$this->config->setAll($this->data);
		$this->config->save();
	}
}
