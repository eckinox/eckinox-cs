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

	public function handleExistingFile(string $packageFilename, string $projectFilename, ?string $currentlyInstalledFilename = null)
	{
		$this->io->debug("Handling existing file:\n\tpackageFilename: $packageFilename\n\tprojectFilename: $projectFilename\n\tcurrentlyInstalledFilename: $currentlyInstalledFilename");

		$filename = basename($projectFilename);

		if (!$this->filesAreDifferent($packageFilename, $projectFilename)) {
			$this->io->debug(sprintf("%s is already up-to-date.", $projectFilename));
			return;
		}

		if ($currentlyInstalledFilename && !$this->filesAreDifferent($packageFilename, $currentlyInstalledFilename)) {
			$this->io->debug(sprintf("%s hasn't changed since previous version - already up-to-date.", $projectFilename));
			return;
		}

		switch ($filename) {
			case 'package.json':
				$packageJsConfigs = json_decode(file_get_contents($packageFilename), true);
				$projectJsConfigs = json_decode(file_get_contents($projectFilename), true);
				$changed = false;

				foreach ($packageJsConfigs as $section => $configs) {
					if (!is_array($configs)) {
						if (!isset($projectJsConfigs[$section]) || $projectJsConfigs[$section] != $configs) {
							$changed = true;
							$projectJsConfigs[$section] = $configs;
						}
					} else {
						foreach ($configs as $key => $value) {
							if (!isset($projectJsConfigs[$section], $projectJsConfigs[$section][$key]) || $projectJsConfigs[$section][$key] != $value) {
								$changed = true;
								$projectJsConfigs[$section][$key] = $value;
							}
						}
					}
				}

				if (!$changed) {
					$this->io->info(sprintf("%s is already up-to-date.", $filename));
				} else {
					$json = json_encode($projectJsConfigs, JSON_PRETTY_PRINT);
					$formattedJson = preg_replace_callback('/^ +/m', function ($m) {
						return str_repeat("\t", strlen($m[0]) / 4);
					}, $json);
					$formattedJson .= "\n";

					$this->filesystem->filePutContentsIfModified($projectFilename, $formattedJson);
					$this->io->info(sprintf("%s has been updated to match the version provided in eckinox/eckinox-cs.", $filename));
				}

				break;

			default:
				if ($currentlyInstalledFilename && $this->canUseGit()) {
					$this->io->info(sprintf("Updating %s with the changes from eckinox/eckinox-cs.", $filename));
					$this->mergeEditedFileWithGit($packageFilename, $projectFilename, $currentlyInstalledFilename);
				} else {
					$this->io->info(sprintf("Overwriting %s with the version from eckinox/eckinox-cs. (to enable automatic merges, allow exec and install git)", $filename));
					$this->filesystem->copy($packageFilename, $projectFilename);
				}
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
			if (is_link($existingFilename)) {
				$this->io->info("The symbolic link for your pre-commit hook (\".git/hooks/pre-commit\") is broken. Deleting it and recreating it...");
				unlink($existingFilename);
			}
			
			symlink($projectFilename, $existingFilename);
			$this->io->info("A symbolic link has been created from \".git/hooks/pre-commit\" to \"DEV/hooks/pre-commit\".");
		}
	}

	protected function canUseGit(): bool
	{
		static $cachedResult = null;

		if ($cachedResult !== null) {
			return $cachedResult;
		}
		
		if (@exec('echo EXEC') != 'EXEC') {
			return $cachedResult = false;
		}

		return $cachedResult = preg_match("~^.*[0-9]\.[0-9].*$~", @exec('git --version') ?: "");
	}

	protected function mergeEditedFileWithGit(string $packageFilename, string $projectFilename, string $currentlyInstalledFilename)
	{
		$originalWorkingDirectory = getcwd();
		
		$tmpDir = rtrim(sys_get_temp_dir(), "/") . "/" . "merge" . uniqid();
		mkdir($tmpDir);
		chdir($tmpDir);

		$filename = $tmpDir . "/" . basename($packageFilename);

		file_put_contents($filename, file_get_contents($currentlyInstalledFilename));
		exec("git init 2> /dev/null && git config user.email 'dev@eckinox.ca' && git config user.name 'EckinoxCS'");
		exec("git checkout -b source 2> /dev/null && git add $filename && git commit -m 'original source file'");
		
		exec("git checkout -b user 2> /dev/null");
		file_put_contents($filename, file_get_contents($projectFilename));
		exec("git add $filename && git commit -m 'user changes'");
		
		exec("git checkout source 2> /dev/null");
		file_put_contents($filename, file_get_contents($packageFilename));
		exec("git add $filename && git commit -m 'source package update'");
		
		exec("git checkout user 2> /dev/null");
		exec("git merge source", $mergeOutput);
		
		$mergeHasConflict = strpos(implode("\n", $mergeOutput), "CONFLICT") !== false;
		$updatedContent = file_get_contents($filename);
		
		if ($mergeHasConflict) {
			$this->io->warning(sprintf("%s has been updated by both you and eckinox-cs. You must check the file and fix the conflicts.", $projectFilename));

			// Update conflicts to use more readable names
			$updatedContent = str_replace(
				[
					"<<<<<<< HEAD",
					">>>>>>> source"
				], [
					"<<<<<<< HEAD (your changes)",
					">>>>>>> source (eckinox-cs changes)"
				], 
				$updatedContent
			);
		}

		$this->filesystem->filePutContentsIfModified($projectFilename, $updatedContent);

		exec("rm -rf $tmpDir");
		chdir($originalWorkingDirectory);
	}

	protected function filesAreDifferent(string $filename1, string $filename2)
	{
		if (filetype($filename1) !== filetype($filename2)) {
			$this->io->debug("Files have different types:\n\t" . filetype($filename1) . " - $filename1\n\t" . filetype($filename2) . " - $filename2");
			return true;
		}

		if (filesize($filename1) !== filesize($filename2)) {
			$this->io->debug("Files have different size:\n\t" . filesize($filename1) . " - $filename1\n\t" . filesize($filename2) . " - $filename2");
			return true;
		}

		$file1 = fopen($filename1, 'rb');

		if (!$file1) {
			$this->io->debug("Files are different:\n\tCould not open $filename1");
			return true;
		}

		$file2 = fopen($filename2, 'rb');

		if (!$file2) {
			fclose($file1);
			$this->io->debug("Files are different:\n\tCould not open $filename2");
			return true;
		}

		$same = true;

		while (!feof($file1) and !feof($file2)) {
			if (fread($file1, 4096) !== fread($file2, 4096)) {
				$this->io->debug("Files are different:\n\tEncountered difference in content");
				$same = false;
				break;
			}
		}

		if (feof($file1) !== feof($file2)) {
			$this->io->debug("Files are different:\n\tEncountered different EOF");
			$same = false;
		}

		fclose($file1);
		fclose($file2);

		return !$same;
	}
}