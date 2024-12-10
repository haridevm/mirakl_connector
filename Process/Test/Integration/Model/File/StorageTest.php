<?php

declare(strict_types=1);

namespace Mirakl\Process\Test\Integration\Model\File;

use Magento\Framework\Filesystem\Driver\File;
use Mirakl\Process\Model\File\Storage;
use Mirakl\Process\Test\Integration\TestCase;

/**
 * @group process
 * @group model
 * @coversDefaultClass \Mirakl\Process\Model\File\Storage
 * @magentoDbIsolation enabled
 * @magentoAppIsolation enabled
 */
class StorageTest extends TestCase
{
    /**
     * @var Storage
     */
    private $storage;

    /**
     * @var File
     */
    private $driver;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->storage = $this->objectManager->create(Storage::class);
        $this->driver = $this->objectManager->create(File::class);
    }

    /**
     * @covers ::getBaseDir
     */
    public function testGetBaseDir()
    {
        $baseDir = $this->storage->getBaseDir();

        $this->assertStringEndsWith('pub/media', $baseDir);
    }

    /**
     * @covers ::getDir
     */
    public function testGetDir()
    {
        $dir = $this->storage->getDir();

        $this->assertStringEndsWith('pub/media/mirakl/process', $dir);
    }

    /**
     * @covers ::removeFile
     */
    public function testRemoveFile()
    {
        $this->driver->createDirectory($this->storage->getDir());

        $this->assertTrue($this->driver->isExists($this->storage->getDir()));

        $file = $this->storage->getDir() . '/test.txt';

        $this->driver->filePutContents($file, 'test');

        $this->assertFileExists($file);

        $this->storage->removeFile('mirakl/process/test.txt');

        $this->assertFileDoesNotExist($file);
        $this->assertFalse($this->storage->removeFile('mirakl/process/test.txt'));
    }

    /**
     * @covers ::clear
     */
    public function testClear()
    {
        $this->driver->createDirectory($this->storage->getDir());

        $this->assertTrue($this->driver->isExists($this->storage->getDir()));

        $file = $this->storage->getDir() . '/test.txt';

        $this->driver->filePutContents($file, 'test');

        $this->assertFileExists($file);

        $this->storage->clear();

        $this->assertFileDoesNotExist($file);
        $this->assertDirectoryExists($this->storage->getBaseDir());
        $this->assertDirectoryDoesNotExist($this->storage->getDir());
    }

    /**
     * @covers ::cleanUp
     */
    public function testCleanUp()
    {
        /**
         * mirakl/
         *   process/
         *     foo/ <= should be kept
         *       test.txt
         *     bar/ <= should be removed
         */
        $fooDir = $this->storage->getDir() . '/foo';
        $barDir = $this->storage->getDir() . '/bar';

        $this->driver->createDirectory($fooDir);
        $this->driver->createDirectory($barDir);

        $this->assertTrue($this->driver->isExists($fooDir));
        $this->assertTrue($this->driver->isExists($barDir));

        $file = $fooDir . '/test.txt';

        $this->driver->filePutContents($file, 'test');

        $this->assertFileExists($file);

        $this->storage->cleanUp();

        $this->assertFileExists($file);
        $this->assertDirectoryDoesNotExist($barDir);
    }
}
