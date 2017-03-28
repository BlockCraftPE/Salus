<?php

namespace Driesboy\Salus;

use pocketmine\plugin\PluginBase;
use pocketmine\utils\TextFormat;
use pocketmine\command\CommandSender;
use pocketmine\command\Command;
use pocketmine\utils\Config;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\Player;
use pocketmine\Plugin;
use pocketmine\plugin\PluginLoader;
use Driesboy\Salus\EventListener;
use Driesboy\Salus\Observer;
use Driesboy\Salus\KickTask;

class Salus extends PluginBase
{
  public $Config;
  public $Logger;
  public $cl;
  public $PlayerObservers = array();
  public $PlayersToKick   = array();

  public function onEnable()
  {
    $this->getServer()->getScheduler()->scheduleRepeatingTask(new KickTask($this), 1);
    @mkdir($this->getDataFolder());
    $this->saveDefaultConfig();
    $cl              = $this->getConfig()->get("Color");

    $Config = $this->getConfig();
    $Logger = $this->getServer()->getLogger();
    $Server = $this->getServer();

    $this->getServer()->getPluginManager()->registerEvents(new EventListener($this), $this);
    $Logger->info(TextFormat::ESCAPE."$cl" . "[Salus] > SalusAntiCheat Activated"            );
    $Logger->info(TextFormat::ESCAPE."$cl" . "[Salus] > SalusAntiCheat v3.2.2 [Salus]");
    $Logger->info(TextFormat::ESCAPE."$cl" . "[Salus] > Loading Modules");
    if($Config->get("ForceOP"    )) $Logger->info(TextFormat::ESCAPE."$cl"."[Salus] > Enabling AntiForceOP"    );
    if($Config->get("NoClip"     )) $Logger->info(TextFormat::ESCAPE."$cl"."[Salus] > Enabling AntiNoClip"     );
    if($Config->get("Fly"        )) $Logger->info(TextFormat::ESCAPE."$cl"."[Salus] > Enabling AntiFly"        );
    if($Config->get("Glide"      )) $Logger->info(TextFormat::ESCAPE."$cl"."[Salus] > Enabling AntiGlide"      );
    if($Config->get("KillAura"   )) $Logger->info(TextFormat::ESCAPE."$cl"."[Salus] > Enabling AntiKillAura"   );
    if($Config->get("Reach"      )) $Logger->info(TextFormat::ESCAPE."$cl"."[Salus] > Enabling AntiReach"      );
    if($Config->get("Speed"      )) $Logger->info(TextFormat::ESCAPE."$cl"."[Salus] > Enabling AntiSpeed"      );
    if($Config->get("Regen"      )) $Logger->info(TextFormat::ESCAPE."$cl"."[Salus] > Enabling AntiRegen"      );

    if($Config->get("Config-Version") !== "3.5.4")
    {
      $Logger->warning(TextFormat::ESCAPE."$cl"."[Salus] > Your Config is out of date!");
    }
    if($Config->get("Plugin-Version") !== "3.2.2" and $Config->get("Plugin-Version") !== "3.2.1")
    {
      $Logger->error(TextFormat::ESCAPE."$cl"."[Salus] > Your Config is incompatible with this plugin version, please update immediately!");
      $Server->shutdown();
    }

    foreach($Server->getOnlinePlayers() as $player)
    {
      $hash     = spl_object_hash($player);
      $name     = $player->getName();
      $oldhash  = null;
      $observer = null;

      foreach ($this->PlayerObservers as $key=>$obs)
      {
        if ($obs->PlayerName == $name)
        {
          $oldhash  = $key;
          $observer = $obs;
          $observer->Player = $player;
        }
      }
      if ($oldhash != null)
      {
        unset($this->PlayerObservers[$oldhash]);
        $this->PlayerObservers[$hash] = $observer;
        $this->PlayerObservers[$hash]->PlayerRejoin();
      }
      else
      {
        $observer = new Observer($player, $this);
        $this->PlayerObservers[$hash] = $observer;
        $this->PlayerObservers[$hash]->PlayerJoin();
      }
    }
  }

  public function onDisable()
  {
    $cl              = $this->getConfig()->get("Color");
    $Logger = $this->getServer()->getLogger();
    $Server = $this->getServer();

    $Logger->info(TextFormat::ESCAPE."$cl"."[Salus] > You are no longer protected from cheats!");
    $Logger->info(TextFormat::ESCAPE."$cl"."[Salus] > SalusAntiCheat Deactivated");
    $Server->enablePlugin($this);
  }

  public function onCommand(CommandSender $sender, Command $cmd, $label, array $args)
  {
    $Logger = $this->getServer()->getLogger();
    $cl              = $this->getConfig()->get("Color");
    if ($this->getConfig()->get("ForceOP"))
    {
      if ($sender->isOp())
      {
        if (!$sender->hasPermission($this->getConfig()->get("ForceOP-Permission")))
        {
          if ($sender instanceof Player)
          {
            $sname = $sender->getName();
            $message  = "[Salus] > $sname used ForceOP!";
            $this->NotifyAdmins($message);
            $sender->getPlayer()->kick(TextFormat::ESCAPE."$cl"."[Salus] > ForceOP detected!");
          }
        }
      }
    }
    if ($cmd->getName() === "Salus" or $cmd->getName() === "Salusanticheat")
    {
      $sender->sendMessage(TextFormat::ESCAPE."$cl"."[Salus] > SalusAntiCheat v1.0 [Salus] (Driesboy)");
    }
  }

  public function NotifyAdmins($message)
  {
    $cl              = $this->getConfig()->get("Color");
    if($this->getConfig()->get("Verbose"))
    {
      foreach ($this->PlayerObservers as $observer)
      {
        $player = $observer->Player;
        if ($player != null and $player->hasPermission("rank.moderator"))
        {
          $player->sendMessage(TextFormat::ESCAPE."$cl" . $message);
        }
      }
    }
  }

}

//////////////////////////////////////////////////////
//                                                  //
//     Salus by Driesboy.                              //
//     Distributed under the AntiCheat License.     //
//     Do not redistribute in modyfied form!        //
//     All rights reserved.                         //
//                                                  //
//////////////////////////////////////////////////////
