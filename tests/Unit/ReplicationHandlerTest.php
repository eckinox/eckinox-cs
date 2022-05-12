<?php

namespace Eckinox\CodingStandards\Tests\Unit;

use Composer\IO\IOInterface;
use Composer\Package\PackageInterface;
use Composer\Util\Filesystem;
use Eckinox\CodingStandards\ReplicationHandler;
use Eckinox\CodingStandards\Tests\Mock\MockFilesystem;
use PHPUnit\Framework\TestCase;

class ReplicationHandlerTest extends TestCase
{
	private string $testDir;
	private PackageInterface $packageStub;
	private IOInterface $mockIo;
	private Filesystem $mockFilesystem;
	private ReplicationHandler $replicationHandler;
	
	public function setUp(): void
	{
		$this->testDir = __DIR__ . "/../";
		$this->mockIo = $this->createMock(IOInterface::class);
		$this->mockFilesystem = $this->createMock(Filesystem::class);
		$this->packageStub = $this->createStub(PackageInterface::class);
		$this->replicationHandler = new ReplicationHandler($this->packageStub,  $this->mockFilesystem, $this->mockIo);
	}

	public function testFileWithNoChangesRemainsTheSame(): void
	{
		$this->mockFilesystem->expects($this->never())
			->method("filePutContentsIfModified");

		$this->mockFilesystem->expects($this->never())
			->method("copy");

		$this->replicationHandler->handleExistingFile(
			$this->testDir . "fixtures/source/unchanged.txt",
			$this->testDir . "fixtures/destination/unchanged.txt"
		);
	}

	public function testPackageJsonUpdatesCorrectly(): void
	{
		$expectedContent = file_get_contents($this->testDir . "expectations/package.json");

		$this->mockFilesystem->expects($this->once())
			->method("filePutContentsIfModified")
			->with(
				$this->testDir . "fixtures/destination/package.json",
				$expectedContent
			);

		$this->replicationHandler->handleExistingFile(
			$this->testDir . "fixtures/source/package.json",
			$this->testDir . "fixtures/destination/package.json"
		);
	}

	public function testFileIsUpdatedOnlyIfContentChangedSinceCurrentlyInstalledVersion(): void
	{
		$this->mockFilesystem->expects($this->never())->method("copy");
		$this->mockFilesystem->expects($this->never())->method("filePutContentsIfModified");
		$this->mockIo->expects($this->once())
			->method("debug")
			->with($this->stringContains("hasn't changed since previous version"));

		$this->replicationHandler->handleExistingFile(
			$this->testDir . "fixtures/source/phpstan.neon",
			$this->testDir . "fixtures/destination/phpstan.neon",
			$this->testDir . "fixtures/installed_source/phpstan.neon"
		);
	}
}