### HypixelBow
**Play sound when hit player with bow**


### Features
* Sound will be played when player hit a player with bow


### Settings
To edit settings, open config.yml in plugin folder

```yml
---
# HypixelBow Configuration File

# will send to launched player and hit the projectile
# not to send, set to false.
#
# Replacement:
#
# {hp}      : player's health
# {max_hp}  : player's max health
# {damage}  : amount of damage
# {name}    : player's name
# {display} : player's display name

sound:
  enable: true
  volume: 1
  pitch: 1
  name: random.orb
message:
  enable: true
  message: "§c{name} §7(§aHP: §e{hp}§7)"
popup:
  enable: true
  message: "§c{name} §7(§aHP: §e{hp}§7)"
tip:
  enable: true
  message: "§c{name} §7(§aHP: §e{hp}§7)"
...
```
