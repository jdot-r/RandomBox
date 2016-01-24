<?php 

namespace Laeng;

//Base
use pocketmine\Server;
use pocketmine\Player;
use pocketmine\plugin\PluginBase;

//Command
use pocketmine\command\Command;
use pocketmine\command\CommandSender;

//Event
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\event\player\PlayerInteractEvent;

//Entity
use pocketmine\entity\Effect;

//Item
use pocketmine\item\Item;

//Utils
use pocketmine\utils\Config;
use pocketmine\utils\TextFormat;



class RandomBox extends PluginBase implements Listener{
	
	private $lang;
	private $setting;
	private $load01 = 0;
	
	private $rbASC = 0;
	private $notice = array();
	
	//API ---------------------
	private static $instance;
	
	public function onLoad(){
		self::$instance = $this;
	}
	
	public static function getInstance(){
		return self::$instance;
	}
	//-------------------------
	
	public function onEnable(){
		if(!file_exists($this->getDataFolder() . "setting.yml")){
			$this->makeSetting();
		}
		
		$this->setting = new Config($this->getDataFolder() . "setting.yml", Config::YAML);
		
		$language = 0;
		if(is_file($this->getDataFolder() .  "language/" . $this->setting->get("language"))) $language = 1;
		if(is_file($this->getResource("language/". $this->setting->get("language")). ".yml")) $language = 2;
		
		switch($language){
			case 0:
				$this->getServer()->getLogger()->critical("[RandomBox] Can not find language (" . $this->setting->get("language") . ") file.");
				$this->getServer()->shutdown();
				break;
			
			case 1: 
				$this->lang = new Config($this->getDataFolder() .  "language/" . $this->setting->get("language"), Config::YAML);
				break;
			
			case 2 :
				$this->lang = new Config($this->getResource("language/". $this->setting->get("language")), Config::YAML);
				break;
		}
		
		$this->lang = new Config($this->getDataFolder() .  "language/" . $this->setting->get("language"), Config::YAML);
		
		if($this->getServer()->getPluginManager()->getPlugin("EconomyAPI")) $this->load01 = 1;
		if($this->getServer()->getPluginManager()->getPlugin("MassiveEconomy")) $this->load01 = 2;
		if($this->load01 == 0){
			$this->getServer()->getLogger()->critical("[RandomBox] Can not find Economy Plugin.");
			$this->getServer()->getLogger()->info("[RandomBox] This plugin need to 'EconomyAPI' or 'MassiveEconomy'.");
			$this->getServer()->shutdown();
		}
		
		if($this->setting->get("rb-auto-stop"))
			$this->rbASC = $this->setting->get("rb-auto-stop-endDay") - date("ymd");
		
		$this->getServer()->getLogger()->info(TextFormat::YELLOW . "Plugin Developer: LAENG");
	}
	
	public function pLogin(PlayerJoinEvent $event){
		$laeng = $event->getPlayer();
		$rLaeng = strtolower($laeng);
		
		$set = $this->setting;
		
		if($set->get("rb-auto-stop") && $this->rbASC <= 5){
			if(empty($this->notice[$rLaeng])){
				if($this->rbASC > 0){
					$this->popup($laeng, $this->lang->get("message-auto-stop-01"));
				}else{
					$this->popup($laeng, str_replace("%1", $this->rbASC, $this->lang->get("message-auto-stop-02")));
				}
			}
		}
		
		if($setting->get("rb-daily-offer")){
			$end = $set->get("rb-daily-offer-endDay");
			if(strlen($end) != 6 || $end >= date("ymd"))
				$event->setCancelled(true);
			
			$log = new Config($this->getDataFolder() . "RandomBox.log", Config::JSON);
			if($log->get($rLaeng) === ""){
				$log->set($rLaeng, date("ymd") - 1);
				$log->save();
			}
			
			if($log->get($rLaeng) == date("ymd")) return;
			$this->giveRB($laeng, $set->get("rb-daily-offer-unit"));
			
			$log->set($rLaneg, date("ymd"));
			$log->save();
			
			$this->message($laeng, $this->lang->get("message-free-offer-01"));
		}
	}
	
	public function pHand(PlayerInteractEvent $event){
		$laeng = $event->getPlayer();
		$rLaeng = strtolower($laeng->getName());
		
		$item = $laeng->getInventory()->getItemInHand();
		$rBox = $this->getBoxItem();
		
		if($item->getId() == $rBox[0] && $itme->getDamage() == $rBox[1]){
			$this->openRB($laeng);
		}
	}
	
	/*
	 * RandomBox API : RB API
	 */
	
	public function addMoney(Player $laeng, $value){
		switch($this->load01){
			case 0: return "ERROR";
			
			case 1:
				$plugin = $this->getServer()->getPluginManager()->getPlugin("EconomyAPI");
				$plugin->addMoney($laeng, $value);
				return true;
			
			case 2: 
				$plugin = $this->getServer()->getPluginManager()->getPlugin("MassiveEconomy");
				$plugin->payPlayer($laeng->getName(), $value);
				return true;
		}
	}
	
	public function takeMoney(Player $laeng, $value){
		switch($this->load01){
			case 0: return "ERROR";
			
			case 1:
				$plugin = $this->getServer()->getPluginManager()->getPlugin("EconomyAPI");
				$plugin->reduceMoney($laeng, $value);
				return true;
			
			case 2: 
				$plugin = $this->getServer()->getPluginManager()->getPlugin("MassiveEconomy");
				$plugin->takeMoney($laeng->getName(), $value);
				return true;
		}
	}
	
	public function giveRB(Player $laeng, $unit = 1){
		$item = $this->getBoxItem();		
		if($unit == "" || $unit < 1) $unit = 1;
		
		$laeng->getInventory()->addItem(Item::get($item[0], $item[1], $unit));
	}
	
	public function openRB(Player $laeng){
		$rb = $laeng->getInventory()->getItemInHand();
		
		if(!$laeng->getInventory()->canAddItem()){
			$this->popup($laeng, $this->lang->get("message-open-rb-01"));
			return;
		}
		
		$rb->setCount($rb->getCount() - 1);
		$laeng->getInventory()->setItemInHand($rb->getCount() > 0 ? $rb : Item::get(Item::AIR));
		
		if($this->setting->get("rb-auto-stop") && $this->rbASC <= 0){
			$this->popup($laeng, $this->lang->get("message-end-rb-01"));
			return;
		}
			
		
		switch(mt_rand(0,3)){
			case 0: //BAM
				$this->popup($laeng, $this->lang->get("message-open-rb-02"));
				break;
				
			case 1: //ITEM
				$pi = $this->qPI2TEXTp($this->dRAND2IMb(0));
				$laeng->getInventory()->addItem(Item::get($pi[0], $pi[1], $pi[2]));
				
				$this->popup($laeng, str_replace("%1", $pi[0].":".$pi[1]." ".$pi[2], $this->lang->get("message-open-rb-03")));
				break;
			
			case 2: //MONEY
				$pm = $this->dRAND2IMb(1);
				$this->addMoney($laeng, $pm);
				
				$this->popup($laeng, str_replace("%1", $pm, $this->lang->get("message-open-rb-04")));
				break;
			
			case 3: //ALL
				$pi = $this->qPI2TEXTp($this->dRAND2IMb(0));
				$laeng->getInventory()->addItem(Item::get($pi[0], $pi[1], $pi[2]));
				
				$pm = $this->dRAND2IMb(1);
				$this->addMoney($laeng, $pm);
				
				$this->popup($laeng, $this->lang->get("message-open-rb-05"));
				break;
		}
		
		if($this->setting->get("rb-effect-offer")){
			$this->onEffect($laeng);
		}
	}
	
	/*
	 * Non API
	 */
	private function onEffect(Player $laeng){
		$g = 0;
		
		while($g > 0) switch(mt_rand(1,14)){
			case 1: $g = 1; break;
			case 3: $g = 3; break;
			case 5: $g = 5; break;
			case 8: $g = 8; break;
			case 10: $g = 10; break;
			case 11: $g = 11; break;
			case 12: $g = 12; break;
			case 13: $g = 13; break;
			case 14: $g = 14; break;
			default: $g = 0; break;
		}
		
		$laeng->addEffect(Effect::getEffect($g)->setDuration($this->setting->get("rb-effect-timer"))->setAmplifier(1));
	}
	
	
	private function getBoxItem(){
		$item = $this->setting->get("rb-item-value");
		
		if($item == "" || $item == 0 || empty($itme)) $item = 246;
		
		if(strpos($item, ":")){
			$code = explode(":", $item);
		}else{
			$code[0] = $item;
			$code[1] = 0;
		}
		
		return $code;
	}
	
	private function qPI2TEXTp($pi){
		return explode(":", $pi);
	}
	
	private function dRAND2IMb($mo){
		switch($mo){
			case 0:
				$va = $this->setting->get("rb-prize-item");
				return $va[mt_rand(0, count($va))];
			
			case 1:
				$va = $this->setting->get("rb-prize-money");
				return $va[mt_rand(0, count($va))];
		}
	}
	
	private function makeSetting(){
		$path = $this->getDataFolder();
		
		@mkdir($path);
		@mkdir($path . "language");
		
		file_put_contents($path . "setting.yml", $this->getResource("setting.yml"));
		file_put_contents($path . "language/English", $this->getResource("/language/English.yml"));
	}
	
	private function message(Player $laeng, $message){
		$laeng->sendMessage($this->lang->get("message-prefix") . " " . $message);
	}
	
	private function popup(Player $laeng, $message){
		for($i = 0; $i > 3; ++$i) 
			$laeng->sendPopup($this->lang->get("message-prefix") . " " . $message);
	}
}
?>