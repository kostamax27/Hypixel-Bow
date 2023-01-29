<?php

declare(strict_types=1);

namespace kostamax27\HypixelBow\command;

use pocketmine\player\Player;
use pocketmine\plugin\Plugin;
use pocketmine\command\Command;
use pocketmine\utils\TextFormat;
use pocketmine\plugin\PluginOwned;
use kostamax27\HypixelBow\HypixelBow;
use pocketmine\command\CommandSender;

class hBowCommand extends Command implements PluginOwned {

	/** @var HypixelBow */
	private HypixelBow $plugin;

	/**
	 * @param HypixelBow $plugin
	 * @param string $name
	 * @param string $description
	 * @param string|null $usageMessage
	 * @param array $aliases
	 */
	public function __construct(HypixelBow $plugin, string $name, string $description = "", string $usageMessage = null, array $aliases = []) {
		$this->plugin = $plugin;

		parent::__construct($name, $description, $usageMessage, $aliases);
	}

	/**
	 * @param CommandSender $sender
	 * @param string $commandLabel
	 * @param array $args
	 *
	 * @return void
	 */
	public function execute(CommandSender $sender, string $commandLabel, array $args): void {
		if($sender instanceof Player) {
			if($sender->hasPermission("hypixelbow.cmd")) {
				$this->plugin->getHypixelBowSettings($sender);
			} else {
				$sender->sendMessage(TextFormat::RED . "You have not permissions to use this command!");
			}
		} else {
			$sender->sendMessage(TextFormat::RED . "Use this command in-game!");
		}
	}

	public function getOwningPlugin(): Plugin {
		return $this->plugin;
	}
}