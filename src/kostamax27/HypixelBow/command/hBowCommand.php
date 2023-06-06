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

    /**
     * @param HypixelBow $plugin
     * @param string $name
     * @param string $description
     * @param string|null $usageMessage
     * @param array $aliases
     */
    public function __construct(private readonly HypixelBow $plugin, string $name, string $description = "", string $usageMessage = null, array $aliases = []) {
        parent::__construct($name, $description, $usageMessage, $aliases);

        $this->setPermission("hypixelbow.cmd");
    }

    /**
     * @param CommandSender $sender
     * @param string $commandLabel
     * @param array $args
     *
     * @return void
     */
    public function execute(CommandSender $sender, string $commandLabel, array $args): void {
        if(!($sender instanceof Player)) {
            $sender->sendMessage(TextFormat::RED . "Use this command in-game!");
            return;
        }

        if(!$this->testPermission($sender)) {
            return;
        }

        $this->plugin->getHypixelBowSettings($sender);
    }

    /**
     * @return Plugin
     */
    public function getOwningPlugin(): Plugin {
        return $this->plugin;
    }
}