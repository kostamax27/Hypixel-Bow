<?php

namespace kostamax27\HypixelBow;

use pocketmine\utils\Config;
use pocketmine\player\Player;
use pocketmine\event\Listener;
use pocketmine\plugin\PluginBase;
use pocketmine\entity\projectile\Arrow;
use kostamax27\HypixelBow\command\hBowCommand;
use pocketmine\event\entity\ProjectileHitEvent;
use pocketmine\event\entity\ProjectileHitEntityEvent;
use pocketmine\network\mcpe\protocol\PlaySoundPacket;
use dktapps\pmforms\element\Label;
use dktapps\pmforms\element\Input;
use dktapps\pmforms\element\Toggle;
use dktapps\pmforms\CustomForm;
use dktapps\pmforms\CustomFormResponse;

class HypixelBow extends PluginBase implements Listener {

	private Config $config;

	private array $data = [];

	public function onLoad(): void {
		$this->saveDefaultConfig();
		$this->config = new Config($this->getDataFolder() . "config.yml", Config::YAML);
		$this->data = $this->getConfig()->getAll();
	}

	public function onEnable(): void {
		$this->getServer()->getPluginManager()->registerEvents($this, $this);

		$this->getServer()->getCommandMap()->register('HypixelBow', new hBowCommand($this, 'hbow', 'HypixelBow settings', null, []));
	}

	public function getHypixelBowSettings(Player $player): void {
		$player->sendForm(new CustomForm(
			"HypixelBowSettings",
			[
				new Label("label_0", "Sound settings: \n\n"),
				new Toggle("toggle_0", "Enable sound:", $this->data['sound']['enable']),
				new Input("input_0", "Sound volume: ", "float: 1.0", $this->data['sound']['volume']),
				new Input("input_1", "Sound pitch: ", "float: 1.0", $this->data['sound']['pitch']),
				new Input("input_2", "Sound name: ", "string: random.orb", $this->data['sound']['name']),
				new Label("label_1", "Message settings: \n\n"),
				new Toggle("toggle_1", "Enable message:", $this->data['message']['enable']),
				new Input("input_3", "Hit message: ", "string: message", $this->data['message']['message']),
				new Toggle("toggle_2", "Enable popup:", $this->data['popup']['enable']),
				new Input("input_4", "Hit popup: ", "string: popup", $this->data['popup']['message']),
				new Toggle("toggle_3", "Enable tip:", $this->data['tip']['enable']),
				new Input("input_5", "Hit tip: ", "string: tip", $this->data['tip']['message'])
			],
			function(Player $player, CustomFormResponse $response) : void {
				$this->data['sound']['enable'] = $response->getBool('toggle_0');
				$this->data['sound']['volume'] = floatval($response->getString('input_0'));
				$this->data['sound']['pitch'] = floatval($response->getString('input_1'));
				$this->data['sound']['name'] = $response->getString('input_2');

				$this->data['message']['enable'] = $response->getBool('toggle_1');
				$this->data['message']['message'] = $response->getString('input_3');

				$this->data['popup']['enable'] = $response->getBool('toggle_2');
				$this->data['popup']['message'] = $response->getString('input_4');

				$this->data['tip']['enable'] = $response->getBool('toggle_3');
				$this->data['tip']['message'] = $response->getString('input_5');

				$this->saveData();
			}
		));
	}

	public function onProjectileHit(ProjectileHitEvent $event): void {
		$player = $event->getEntity()->getOwningEntity();
		if(!$event->getEntity() instanceof Arrow) return;
		if($player instanceof Player && $event instanceof ProjectileHitEntityEvent) {
			$target = $event->getEntityHit();
			if($target instanceof Player) {
				if($this->data['sound']['enable']) {
					$this->playSound($player, $this->data['sound']['volume'], $this->data['sound']['pitch'], $this->data['sound']['name']);
				}
				if($this->data['message']['enable']) {
					$player->sendMessage(str_replace(["{hp}", "{damage}", "{name}", "{display}"], [$target->getHealth(), $event->getEntity()->getResultDamage(), $target->getName(), $target->getDisplayName()], $this->data['message']['message']));
				}
				if($this->data['popup']['enable']) {
					$player->sendPopup(str_replace(["{hp}", "{damage}", "{name}", "{display}"], [$target->getHealth(), $event->getEntity()->getResultDamage(), $target->getName(), $target->getDisplayName()], $this->data['popup']['message']));
				}
				if($this->data['tip']['enable']) {
					$player->sendTip(str_replace(["{hp}", "{damage}", "{name}", "{display}"], [$target->getHealth(), $event->getEntity()->getResultDamage(), $target->getName(), $target->getDisplayName()], $this->data['tip']['message']));
				}
			}
		}
	}

	private function playSound(Player $player, float $volume = 1.0, float $pitch = 1.0, string $sound = ''): void {
		$pk = new PlaySoundPacket();
		$pk->x = $player->getPosition()->getX();
		$pk->y = $player->getPosition()->getY();
		$pk->z = $player->getPosition()->getZ();
		$pk->volume = $volume;
		$pk->pitch = $pitch;
		$pk->soundName = $sound;
		$player->getNetworkSession()->sendDataPacket($pk);
	}

	private function saveData(): void {
		$this->config->setAll($this->data);
		$this->config->save();
	}
}
