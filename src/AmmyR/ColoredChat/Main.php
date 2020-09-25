<?php

namespace AmmyR\ColoredChat;

use pocketmine\plugin\PluginBase;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerChatEvent;
use pocketmine\utils\Config;

class Main extends PluginBase {

	private const CONFIG_VERSION = 1.0;
	private static $instance = null;

	public static function getInstance()
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
			$colorsList = Main::getSettings()->get("Colors"); //Array
			$message = $event->getMessage();
			$splitedString = str_split($message);

			foreach($splitedString as $coloredMessage)
			{
				$randomColor = array_rand($colorsList);
				$splitedString[array_search($coloredMessage, $splitedString)] = "§" . $randomColor . $coloredMessage;
			}

			$newMessage = implode($splitedString);
			$event->setMessage($newMessage);
		}
	}
}