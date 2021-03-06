<?php
namespace Driesboy\Salus;

use pocketmine\scheduler\PluginTask;
use pocketmine\utils\TextFormat;
use pocketmine\command\ConsoleCommandSender;
use Driesboy\Salus\Observer;

class KickTask extends PluginTask
{

  public function __construct($plugin)
  {
    parent::__construct($plugin);
    $this->plugin = $plugin;
  }

  public function onRun($currentTick)
  {
    $cl = $this->plugin->getConfig()->get("Color");
    foreach($this->plugin->PlayersToKick as $key=>$obs)
    {
      $obs->PlayerBanCounter++;
      if ($obs->PlayerBanCounter > 0 and $obs->PlayerBanCounter == $this->plugin->getConfig()->get("Max-Hacking-Times"))
      {
        foreach($this->plugin->getConfig()->get("MaxHackingExceededCommands") as $command)
        {
          $send = $obs->ScanMessage($command);
          $this->plugin->getServer()->dispatchCommand(new ConsoleCommandSender(), $send);
          if($this->plugin->getConfig()->get("BanPlayerMessageBool"))
          {
            $bmsg = $this->plugin->getConfig()->get("BanPlayerMessage");
            $sbmsg = $obs->ScanMessage($bmsg);
            $this->plugin->getServer()->broadcastMessage(TextFormat::ESCAPE."$cl" . $sbmsg);
          }
        }
        $obs->PlayerBanCounter = 0;
      }
      if ($obs->Player != null && $obs->Player->isOnline())
      {
        $obs->Player->kick(TextFormat::ESCAPE."$cl" . $obs->KickMessage);
        if($this->plugin->getConfig()->get("KickPlayerMessageBool"))
        {
          $msg = $this->plugin->getConfig()->get("KickPlayerMessage");
          $smsg = $obs->ScanMessage($msg);
          $this->plugin->getServer()->broadcastMessage(TextFormat::ESCAPE."$cl" . $smsg);
        }
      }   
      unset ($this->plugin->PlayersToKick[$key]);
    }  
  }
  
}