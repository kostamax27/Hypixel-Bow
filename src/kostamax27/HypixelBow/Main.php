<?php

namespace kostamax27\HypixelBow;

use pocketmine\network\mcpe\protocol\PlaySoundPacket;
use pocketmine\event\entity\ProjectileHitEntityEvent;
use pocketmine\event\entity\ProjectileHitEvent;
use pocketmine\entity\projectile\Arrow;
use pocketmine\command\CommandSender;
use pocketmine\plugin\PluginBase;
use pocketmine\command\Command;
use pocketmine\event\Listener;
use pocketmine\player\Player;
use pocketmine\utils\Config;

use kostamax27\HypixelBow\FormAPI\CustomForm;

class Main extends PluginBase implements Listener {

    private Config $config;

    public function onEnable() : void {
		@mkdir($this->getDataFolder());
		$this->saveResource("config.yml");
		$this->config = new Config($this->getDataFolder() . "config.yml", Config::YAML);
		$this->getServer()->getPluginManager()->registerEvents($this, $this);
	}
	
	public function onCommand(CommandSender $sender, Command $command, string $label, array $args): bool {
        if($command->getName() == "hbow") {
            if($sender instanceof Player) {
                $this->getHypixelBowSettings($sender);
            } else {
                $sender->sendMessage("Use this command in-game");
            }
        }
        return true;
    }
	
	public function getHypixelBowSettings($player) {
		$form = new CustomForm(function(Player $player, $data) {
			if($data === null) {
				return;
			}
			if($data[1] === true) {
				$this->config->set("enable-sound", true);
			} else {
				$this->config->set("enable-sound", false);
			}
			if($data[2] == null || !is_numeric($data[2])) {
				return;
			} else {
				$this->config->set("sound-volume", $data[2]);
			}
			if($data[3] == null || !is_numeric($data[3])) {
				return;
			} else {
				$this->config->set("sound-pitch", $data[3]);
			}
			if($data[4] == null) {
				return;
			} else {
				$this->config->set("sound-name", $data[4]);
			}
			if($data[6] === true) {
				$this->config->set("enable-message", true);
			} else {
				$this->config->set("enable-message", false);
			}
			if($data[7] == null) {
				return;
			} else {
				$this->config->set("hit-message", $data[7]);
			}
			if($data[8] === true) {
				$this->config->set("enable-popup", true);
			} else {
				$this->config->set("enable-popup", false);
			}
			if($data[9] == null) {
				return;
			} else {
				$this->config->set("hit-popup", $data[9]);
			}
			if($data[10] === true) {
				$this->config->set("enable-tip", true);
			} else {
				$this->config->set("enable-tip", false);
			}
			if($data[11] == null) {
				return;
			} else {
				$this->config->set("hit-tip", $data[11]);
			}
        });
        $form->setTitle("HypixelBowSettings");
		$form->addLabel("Sound settings: \n\n");
		if($this->config->get("enable-sound", true)) {
			$form->addToggle("Enable sound:", true);
		} else {
			$form->addToggle("Enable sound: ", false);
		}
		$form->addInput("Sound volume: ", "float: 1.0", $this->config->get("sound-volume"));
		$form->addInput("Sound pitch: ", "float: 1.0", $this->config->get("sound-pitch"));
		$form->addInput("Sound name: ", "string: random.orb", $this->config->get("sound-name"));
		
		$form->addLabel("Message settings: \n\n");
		if($this->config->get("enable-message", true)) {
			$form->addToggle("Enable message:", true);
		} else {
			$form->addToggle("Enable sound: ", false);
		}
		$form->addInput("Hit message: ", "string: message", $this->config->get("hit-message"));
		
		if($this->config->get("enable-popup", true)) {
			$form->addToggle("Enable popup:", true);
		} else {
			$form->addToggle("Enable popup: ", false);
		}
		$form->addInput("Hit popup: ", "string: popup", $this->config->get("hit-popup"));
		
		if($this->config->get("enable-tip", true)) {
			$form->addToggle("Enable tip:", true);
		} else {
			$form->addToggle("Enable tip: ", false);
		}
		$form->addInput("Hit tip: ", "string: tip", $this->config->get("hit-tip"));
        $player->sendForm($form);
    }

    
    public function onProjectileHit(ProjectileHitEvent $event){
        $player = $event->getEntity()->getOwningEntity();
		if(!$event->getEntity() instanceof Arrow) return;
        if($player instanceof Player && $event instanceof ProjectileHitEntityEvent) {
			$target = $event->getEntityHit();
			if($target instanceof Player) {
				if($this->config->get("enable-sound", true)) {
					$pk = new PlaySoundPacket();
					$pk->x = $player->getPosition()->getX();
					$pk->y = $player->getPosition()->getY();
					$pk->z = $player->getPosition()->getZ();
					$pk->volume = $this->config->get("sound-volume");
					$pk->pitch = $this->config->get("sound-pitch");
					$pk->soundName = $this->config->get("sound-name");
					$player->getNetworkSession()->sendDataPacket($pk);
				}
                if($this->config->get("enable-message", true)) {
                    $player->sendMessage(str_replace(["{hp}", "{damage}", "{name}", "{display}"], [$target->getHealth(), $event->getEntity()->getResultDamage(), $target->getName(), $target->getDisplayName()], $this->config->get("hit-message")));
                }
                if($this->config->get("enable-popup", true)) {
                    $player->sendPopup(str_replace(["{hp}", "{damage}", "{name}", "{display}"], [$target->getHealth(), $event->getEntity()->getResultDamage(), $target->getName(), $target->getDisplayName()], $this->config->get("hit-popup")));
                }
				if($this->config->get("enable-tip", true)) {
                    $player->sendTip(str_replace(["{hp}", "{damage}", "{name}", "{display}"], [$target->getHealth(), $event->getEntity()->getResultDamage(), $target->getName(), $target->getDisplayName()], $this->config->get("hit-tip")));
                }
            }
        }
    }
	
	public function onDisable() : void {
		$this->config->save();
	}
}
