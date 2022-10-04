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
use Frago9876543210\EasyForms\elements\Label;
use Frago9876543210\EasyForms\elements\Input;
use Frago9876543210\EasyForms\elements\Toggle;
use Frago9876543210\EasyForms\forms\CustomForm;
use Frago9876543210\EasyForms\forms\CustomFormResponse;

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
				new Label("Sound settings: \n\n"),
				new Toggle("Enable sound:", $this->data['sound']['enable']),
				new Input("Sound volume: ", "float: 1.0", $this->data['sound']['volume']),
				new Input("Sound pitch: ", "float: 1.0", $this->data['sound']['pitch']),
				new Input("Sound name: ", "string: random.orb", $this->data['sound']['name']),
				new Label("Message settings: \n\n"),
				new Toggle("Enable message:", $this->data['message']['enable']),
				new Input("Hit message: ", "string: message", $this->data['message']['message']),
				new Toggle("Enable popup:", $this->data['popup']['enable']),
				new Input("Hit popup: ", "string: popup", $this->data['popup']['message']),
				new Toggle("Enable tip:", $this->data['tip']['enable']),
				new Input("Hit tip: ", "string: tip", $this->data['tip']['message'])
			],
			function(Player $player, CustomFormResponse $response) : void {
				[
					$enable_sound,
					$sound_volume,
					$sound_pitch,
					$sound_name,
					$enable_message,
					$hit_message,
					$enable_popup,
					$hit_popup,
					$enable_tip,
					$hit_tip
				] = $response->getValues();

				$this->data['sound']['enable'] = $enable_sound;
				$this->data['sound']['volume'] = is_numeric($sound_volume) ? $sound_volume : $this->data['sound']['volume'];
				$this->data['sound']['pitch'] = is_numeric($sound_pitch) ? $sound_pitch : $this->data['sound']['pitch'];
				$this->data['sound']['name'] = $sound_name == null ? $this->data['sound']['name'] : $sound_name;

				$this->data['message']['enable'] = $enable_message;
				$this->data['message']['message'] = $hit_message == null ? $this->data['message']['message'] : $hit_message;

				$this->data['popup']['enable'] = $enable_popup;
				$this->data['popup']['message'] = $hit_popup == null ? $this->data['popup']['message'] : $hit_popup;

				$this->data['tip']['enable'] = $enable_tip;
				$this->data['tip']['message'] = $hit_tip == null ? $this->data['tip']['message'] : $hit_tip;

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

	private function playSound(Player $player, float $volume, float $pitch, string $sound): void {
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
