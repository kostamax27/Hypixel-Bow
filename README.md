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
# Replacement
#
# {hp}      : player's health
# {max_hp}  : player's max health
# {damage}  : amount of damage
# {name}    : player's name
# {display} : player's display name

enable-sound: true # Enable sound ?
sound-volume: 1
sound-pitch: 1
sound-name: "random.orb"

enable-message: true # Enable message ?
hit-message: "§c{name} §7(§aHP: §e{hp}§7)"

enable-popup: false  # Enable popup ?
hit-popup: "§c{name} §7(§aHP: §e{hp}§7)"

enable-tip: false  # Enable tip ?
hit-tip: "§c{name} §7(§aHP: §e{hp}§7)"
...
```
