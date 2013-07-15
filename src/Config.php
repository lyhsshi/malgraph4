<?php
class Config extends Singleton
{
	static $userQueuePath;
	static $mirrorPath;
	static $mirrorEnabled;
	static $cachePath;
	static $cacheEnabled;
	static $cacheTimeToLive;
	static $dbPath;
	static $debugCron;

	public static function doInit()
	{
		$rootDir = join(DIRECTORY_SEPARATOR, [__DIR__, '..', 'data', '']);
		self::$userQueuePath = $rootDir . 'users.lst';
		self::$mirrorPath = $rootDir . 'mirror';
		self::$cachePath = $rootDir . 'cache';
		self::$dbPath = $rootDir . 'db.sqlite';
		self::$debugCron = true;
		self::$mirrorEnabled = false;
		self::$cacheEnabled = true;
		self::$cacheTimeToLive = 24 * 60 * 60;
	}
}

Config::init();
