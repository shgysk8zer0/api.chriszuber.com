<?php
require './autoloader.php';

class Git
{
	use \shgysk8zer0\Traits\Git;
}

$git = new Git();

if ($git->isMaster()) {
	echo $git->fetch() . PHP_EOL;
	echo $git->pull() . PHP_EOL;
	echo $git->updateSubmodules() . PHP_EOL;
	echo $git->status() . PHP_EOL;
}
echo $git->isClean() ? 'Clean' : 'Not clean';
echo PHP_EOL;
