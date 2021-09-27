<?php

namespace kostamax27\HypixelBow;

// Network
use pocketmine\network\mcpe\protocol\PlaySoundPacket;
// Entity
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\event\entity\ProjectileHitEntityEvent;
use pocketmine\event\entity\ProjectileHitEvent;
use pocketmine\event\entity\EntityDamageEvent;
// Plugin
use pocketmine\plugin\PluginBase;
// Event
use pocketmine\event\Listener;
// Utils
use pocketmine\utils\Config;
// Base
use pocketmine\Player;
use pocketmine\Server;

class Main extends PluginBase implements Listener {
    public function onEnable(){
        $this->saveDefaultConfig();
        $this->getServer()->getPluginManager()->registerEvents($this, $this);
    }
    
    public function onProjectileHit(ProjectileHitEvent $event){
        $projectile = $event->getEntity();
        $entity = $projectile->getOwningEntity();
        if($entity instanceof Player && $event instanceof ProjectileHitEntityEvent){
            $target = $event->getEntityHit();
            if($target instanceof Player){
                $pk = new PlaySoundPacket();
                $pk->x = $entity->getX();
                $pk->y = $entity->getY();
                $pk->z = $entity->getZ();
                $pk->volume = 1;
                $pk->pitch = 1;
                $pk->soundName = 'random.orb';
                $entity->dataPacket($pk);
                $message = $this->config->get("hit-message");
                if($this->getConfig()->getNested("message-enable", true)){
                    $entity->sendMessage(str_replace(['{hp}', '{damage}', '{rawname}', '{name}'], [$target->getHealth(), $projectile->getResultDamage(), $entity->getName(), $entity->getDisplayName()], $message));
                }
                $popup = $this->config->get("hit-popup");
                if($this->getConfig()->getNested("popup-enable", true)){
                    $entity->sendPopup(str_replace(['{hp}', '{damage}', '{rawname}', '{name}'], [$target->getHealth(), $projectile->getResultDamage(), $entity->getName(), $entity->getDisplayName()], $popup));
                }
            }
        }
    }
}
