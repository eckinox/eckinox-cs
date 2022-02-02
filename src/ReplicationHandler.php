<?php

namespace Eckinox\CodingStandards;

use Composer\Package\PackageInterface;
use Composer\IO\IOInterface;
use Composer\Util\Filesystem;
use Eckinox\Composer\HandlerInterface;

class ReplicationHandler implements HandlerInterface
{
	/** @var PackageInterface */
	protected $package;

	/** @var Filesystem */
	protected $filesystem;

	/** @var IOInterface */
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

		if (!$this->filesAreDifferent($packageFilename, $projectFilename)) {
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
					file_put_contents($projectFilename, json_encode($projectJsConfigs, JSON_PRETTY_PRINT));
					$this->io->info(sprintf("%s has been updated to match the version provided in eckinox/eckinox-cs.", $filename));
				}

				break;

			default:
				$this->io->info(sprintf("Overwriting %s with the version from eckinox/eckinox-cs.", $filename));
				$this->filesystem->copy($packageFilename, $projectFilename);
		}
	}

	public function postFileCreationCallback(string $projectFilename)
	{
		$filename = basename($projectFilename);

		if ($filename != 'pre-commit') {
			return;
		}

		$rootDir = str_replace("DEV/hooks/pre-commit", "", $projectFilename);
		$existingFilename = $rootDir . ".git/hooks/pre-commit";

		if (file_exists($existingFilename)) {
			if ($this->filesAreDifferent($projectFilename, $existingFilename)) {
				$this->io->warning("Pre-commit hook already exists in your project. You should make sure it includes the execution of coding standards tools provided by eckinox/eckinox-cs.");
			}
		} else {
			symlink($projectFilename, $existingFilename);
			$this->io->info("A symbolic link has been created from \".git/hooks/pre-commit\" to \"DEV/hooks/pre-commit\".");
		}
	}

	protected function filesAreDifferent(string $file1, string $file2)
	{
		return md5_file($file1) == md5_file($file2);
	}
}