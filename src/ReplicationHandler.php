<?php

namespace Eckinox\CodingStandards;

use Composer\Package\PackageInterface;
use Composer\IO\IOInterface;
use Composer\Util\Filesystem;
use Eckinox\Composer\HandlerInterface;

class ReplicationHandler implements HandlerInterface
{
	protected $package;
	protected $filesystem;
	protected $io;

	public function __construct(PackageInterface $package, Filesystem $filesystem, IOInterface $io)
	{
		$this->package = $package;
		$this->filesystem = $filesystem;
		$this->io = $io;
	}

	public function handleExistingFile(string $packageFilename, string $projectFilename)
	{
		$filename = basename($projectFilename);

		if (!$this->fileChanged($packageFilename, $projectFilename)) {
			$this->io->debug(sprintf("%s is already up-to-date.", $projectFilename));
			return;
		}

		switch ($filename) {
			case 'package.json':
				$packageJsConfigs = json_decode(file_get_contents($packageFilename), true);
				$projectJsConfigs = json_decode(file_get_contents($projectFilename), true);
				$changed = false;

				foreach ($packageJsConfigs as $section => $configs) {
					foreach ($configs as $key => $value) {
						if (!isset($projectJsConfigs[$section], $projectJsConfigs[$section][$key]) || $projectJsConfigs[$section][$key] != $value) {
							$changed = true;
							$projectJsConfigs[$section][$key] = $value;
						}
					}
				}

				if (!$changed) {
					$this->io->info(sprintf("%s is already up-to-date.", $filename));
				} else {
					$this->io->info(sprintf("%s has been updated to match the version provided in eckinox/eckinox-cs.", $filename));
					file_put_contents($projectFilename, json_encode($projectJsConfigs, JSON_PRETTY_PRINT));
				}

				break;

			default:
				$this->io->info(sprintf("Overwriting %s with the version from eckinox/eckinox-cs.", $filename));
				$this->filesystem->copy($packageFilename, $projectFilename);
		}
	}

	protected function fileChanged(string $packageFilename, string $projectFilename)
	{
		return md5_file($projectFilename) == md5_file($packageFilename);
	}
}