<?php

namespace AmmyR\ColoredChat;

use pocketmine\plugin\PluginBase;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerChatEvent;
use pocketmine\utils\Config;

class Main extends PluginBase {

	private const CONFIG_VERSION = 1.0;
	private static $instance = null;
	
	/**
	 * @return self
	 */
	public static function getInstance() : self
	{
		return self::$instance;
	}

	/**
	 * @return Config
	 */
	public static function getSettings() : Config
	{
		return new Config(self::getInstance()->getDataFolder() . "settings.yml", Config::YAML);
	}

	public function onEnable() : void
	{
		self::$instance = $this;
		if(!is_dir($this->getDataFolder())) @mkdir($this->getDataFolder());
		if(!is_file($this->getDataFolder() . "settings.yml")) $this->saveResource("settings.yml");

		if(self::getSettings()->get("Config-Version") !== self::CONFIG_VERSION)
		{
			@unlink($this->getDataFolder() . "settings.yml");
			$this->saveResource("settings.yml");
			$this->getServer()->getLogger()->debug("ColoredChat > Settings file updated to version \"" . self::CONFIG_VERSION . "\" successfully.");
		}

		new EventListener($this);
	}

}

class EventListener implements Listener {

	private $ccMain;

	public function __construct(Main $ccMain)
	{
		Main::getInstance()->getServer()->getPluginManager()->registerEvents($this, $ccMain);
	}

	/**
	 * @priority NORMAL
	 * @param PlayerChatEvent $event
	 * @return void
	 */
	public function onChat(PlayerChatEvent $event) : void
	{
		$player = $event->getPlayer();

		if($player->hasPermission("coloredchat"))
		{
			$colorList = Main::getSettings()->get("Colors"); //Array
			$message = $ev->getMessage();

			$storedColors = [];
			for($i = 0; $i < mb_strlen($message); $i++)
			{
				$splitedString = mb_substr($message, $i, 1);
				$rand = array_rand($colorList);

				$randomColor = $colorList[$rand];

				$coloredString[$i] = "§" . $randomColor . $splitedString;
			}

			$newMessage = implode($coloredString);
			$ev->setMessage($newMessage);
		}
	}
}
