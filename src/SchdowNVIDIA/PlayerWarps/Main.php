<?php

/* 
                         _____                           
     /\                 |  __ \                          
    /  \   ___ _ __ ___ | |__) |_      ____ _ _ __ _ __  
   / /\ \ / _ \ '__/ _ \|  ___/\ \ /\ / / _` | '__| '_ \ 
  / ____ \  __/ | | (_) | |     \ V  V / (_| | |  | |_) |
 /_/    \_\___|_|  \___/|_|      \_/\_/ \__,_|_|  | .__/ 
                                                  | |    
                                                  |_|    

                
    Copyright (C) 2019 SchdowNVIDIA

    This program is free software: you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation, either version 3 of the License, or
    (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program.  If not, see <https://www.gnu.org/licenses/>.
*/

namespace SchdowNVIDIA\PlayerWarps;

use onebone\economyapi\EconomyAPI;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\event\Listener;
use pocketmine\world\World;
use pocketmine\world\Position;
use pocketmine\math\Vector3;
use pocketmine\player\Player;
use pocketmine\Player;
use pocketmine\plugin\PluginBase;
use pocketmine\utils\Config;
use pocketmine\utils\TextFormat;

class Main extends PluginBase implements Listener {

    public function onEnable(): void
    {
        @mkdir($this->getDataFolder());
        $this->saveResource("pwarps.yml");
        $this->getServer()->getPluginManager()->registerEvents($this, $this);
        $this->saveDefaultConfig();
        $this->getLogger()->info("                         
                            _____                           
        /\                 |  __ \                          
       /  \   ___ _ __ ___ | |__) |_      ____ _ _ __ _ __  
      / /\ \ / _ \ '__/ _ \|  ___/\ \ /\ / / _` | '__| '_ \ 
     / ____ \  __/ | | (_) | |     \ V  V / (_| | |  | |_) |
    /_/    \_\___|_|  \___/|_|      \_/\_/ \__,_|_|  | .__/ 
                                                     | |    
                                                     |_|    
       ");

    }

    public function onCommand(CommandSender $sender, Command $command, string $label, array $args): bool
    {
        $createPrice = $this->getConfig()->getNested("price.create");
        $deletePrice = $this->getConfig()->getNested("price.delete");
        $newposPrice = $this->getConfig()->getNested("price.newpos");
        $name = strtolower($sender->getName());
        $economy = EconomyAPI::getInstance();
        $PWARPS = new Config($this->getDataFolder() . "/pwarps.yml", Config::YAML);
        if($command->getName() == "pwarp") {
            if(!isset($args[0])) {
                $sender->sendMessage("§8[§bAero§3Pwarp§8] §cPENGGUNAA SALAH: §fKetik /pwarp help untuk mengetahui command");
                return true;
            }
            switch($args[0]) {
                case "price":
                    $sender->sendMessage("§8[§bAero§3Pwarp§8] §fThe current prices of an PWarp are:");
                    $sender->sendMessage("§bCreate: §f" .$createPrice."$");
                    $sender->sendMessage("§bNewpos: §f" .$newposPrice."$");
                    $sender->sendMessage("§bDelete: §f" .$deletePrice."$");
                    return true;
                case "delete":
                    if(!isset($args[1])) {
                        $sender->sendMessage("§8[§bAero§3Pwarp§8] §cWRONG USAGE: §fRun /pwarp help to get help about PlayerWarps.");
                        return true;
                    }
                    $pwarpname = $args[1];
                    $money = $economy->myMoney($name);
                    if($money < $deletePrice) {
                        $sender->sendMessage("§8[§bAero§3Pwarp§8] §cKamu tidak memiliki cukup uang untuk menghapus warp, kamu butuh Rp." . $createPrice . " untuk menghapusnya");
                        return true;
                    }
                    if(!$PWARPS->exists($pwarpname)) {
                        $sender->sendMessage("§8[§bAero§3Pwarp§8] §cERROR: §fTidak ada warp dengan nama §b" . $pwarpname . "§f!");
                        return true;
                    }
                    if($PWARPS->getNested($pwarpname.".owner") != $name) {
                        $sender->sendMessage("§8[§bAero§3Pwarp§8] §cKamu tidak bisa menghapus warp ini, warp ini bukan milikmu!");
                        return true;
                    }
                    $PWARPS->remove($pwarpname);
                    $PWARPS->save();
                    $PWARPS->reload();
                    $sender->sendMessage("§8[§bAero§3Pwarp§8] §fPWarp §b".$pwarpname." §aBerhasil di Hapus!");
                    return true;

                case "create":
                    if(!isset($args[1])) {
                        $sender->sendMessage("§8[§bAero§3Pwarp§8] §cWRONG USAGE: §fRun /pwarp help to get help about PlayerWarps.");
                        return true;
                    }
                    $pwarpname = $args[1];
                    $money = $economy->myMoney($name);
                    if($money < $createPrice) {
                        $sender->sendMessage("§8[§bAero§3Pwarp§8] §cKamu tidak memiliki cukup uang untuk membuat warp, kamu butuh Rp." . $createPrice . " untuk membuat warp!");
                        return true;
                    }
                    if($PWARPS->exists($pwarpname)) {
                        $sender->sendMessage("§8[§bAero§3Pwarp§8] §cERROR: §fWarp sudah ada dengan nama" . $pwarpname . "!");
                        return true;
                    }
                    $economy->reduceMoney($name, $createPrice);
                    $x = $sender->getX();
                    $y = $sender->getY();
                    $z = $sender->getZ();
                    $world = $sender->getLevel()->getName();
                    $PWARPS->setNested($pwarpname . ".owner", $name);
                    $PWARPS->setNested($pwarpname. ".x", $x);
                    $PWARPS->setNested($pwarpname . ".y", $y);
                    $PWARPS->setNested($pwarpname.".z", $z);
                    $PWARPS->setNested($pwarpname.".world", $world);
                    $PWARPS->save();
                    $PWARPS->reload();
                    $sender->sendMessage("§8[§bAero§3Pwarp§8] §fPwarp §b" . $pwarpname ." §fBerhasil dibuat!");
                    return true;
                case "help":
                    $this->getMessage($sender, "help");
                    return true;
                case "info":
                    $pwarpname = $args[1];
                    if(!$PWARPS->exists($pwarpname)) {
                        $sender->sendMessage("§8[§bAero§3Pwarp§8] §cERROR: §fTidak ada pwarp dengan nama §b" . $pwarpname . "§f!"); 
                        return true;
                    }
                    /** Dev nya kagak update2 Bruh **/
                    $x = $PWARPS->getNested($pwarpname.".x");
                    $y = $PWARPS->getNested($pwarpname.".y");
                    $z = $PWARPS->getNested($pwarpname.".z");
                    $world = $PWARPS->getNested($pwarpname.".world");
                    $pwarpOwner = $PWARPS->getNested($pwarpname.".owner");
                    $sender->sendMessage("--- AeroPWarp Info ---");
                    $sender->sendMessage("Owner: " . $pwarpOwner);
                    $sender->sendMessage("Position: (X: " . $x. ", Y: " . $y . ", Z: " . $z. ")");
                    $sender->sendMessage("World: " . $world);
                    $sender->sendMessage("--- AeroPWarp Info ---");
                    return true;
                case "newpos":
                    $pwarpname = $args[1];
                    if(!$PWARPS->exists($pwarpname)) {
                        $sender->sendMessage("§8[§bAero§3Pwarp§8] §cERROR: §fTidak ada warp dengan nama §b" . $pwarpname . "§f!");
                        return true;
                    }
                    if($PWARPS->getNested($pwarpname.".owner") == $sender->getName()) {
                        $sender->sendMessage("§8[§bAero§3Pwarp§8] §cKamu tidak bisa edit warp ini, karena ini bukan milikmu!");
                        return true;
                    }
                    if($economy->myMoney($name) < $newposPrice) {
                        $sender->sendMessage("§8[§bAero§3Pwarp§8] §cKamu tidak memilik cukup uang untuk membuat posisi baru!");
                        return true;
                    }
                    $economy->reduceMoney($name, $newposPrice);
                    $x = $sender->getPosition()->getX();
                    $y = $sender->getPosition()->getY();
                    $z = $sender->getPosition()->getZ();
                    $world = $sender->getWorld()->getName();
                    $PWARPS->setNested($pwarpname. ".x", $x);
                    $PWARPS->setNested($pwarpname . ".y", $y);
                    $PWARPS->setNested($pwarpname.".z", $z);
                    $PWARPS->setNested($pwarpname.".world", $world);
                    $PWARPS->save();
                    $PWARPS->reload();
                    $sender->sendMessage("§8[§bAero§3Pwarp§8] §fPosisi baru berhasil disetting!.");
                    return true;
                case "list":
                    $pwarplist = array();
                    foreach ($PWARPS->getAll(true) as $pwarp) {
                        //$sender->sendMessage($pwarp);
                         array_push($pwarplist, $pwarp);
                    }
                    $sender->sendMessage("§bRegistered PlayerWarps:");
                    $sender->sendMessage(implode(", ", $pwarplist));
                    return true;


                default:
                    $pwarpname = $args[0];
                    if(!$PWARPS->exists($pwarpname)) {
                        $sender->sendMessage("§8[§bAero§3Pwarp§8] §cERROR: §fTidak ada pwarp dengan nama " . $pwarpname . "!");
                        return true;
                    }
                    $x = $PWARPS->getNested($pwarpname.".x");
                    $y = $PWARPS->getNested($pwarpname.".y");
                    $z = $PWARPS->getNested($pwarpname.".z");
                    $level = $this->getServer()->getLevelByName($PWARPS->getNested($pwarpname.".world"));
                    $sender->teleport(new Position($x, $y, $z, $level));
                    $sender->sendMessage("§8[§bAero§3Pwarp§8] §bKamu berhasil teleport ke §b" . $pwarpname . "§f!");
                    return true;
            }
        }
    }

    public function getMessage(Player $player, $type) {
        if($type == "help") {
            $player->sendMessage("--- AeroPwarps Help ---");
            $player->sendMessage("§f/pwarp <pwarp-name> §7- teleport to an pwarp");
            $player->sendMessage("§f/pwarp price §7- shows alls prices of an pwarp");
            $player->sendMessage("§f/pwarp create <pwarpname> §7- create an pwarp");
            $player->sendMessage("§f/pwarp delete <pwarpname> §7- delete an pwarp");
            $player->sendMessage("§f/pwarp newpos <pwarp-name> §7- change position of an pwarp");
            $player->sendMessage("§f/pwarp info <pwarp-name> §7- get info of an pwarp");
            $player->sendMessage("§f/pwarp list §7- get a list of all pwarps");
            $player->sendMessage("--- AeroPwarps Help ---");
        }
    }
}
