<?php
namespace Mirakl\Process\Helper;

use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\Exception\FileSystemException;
use Magento\Framework\Filesystem;
use Magento\Framework\Filesystem\Directory\ReadInterface;
use Magento\Framework\Filesystem\Directory\WriteInterface;
use Mirakl\Core\Domain\FileWrapper;
use Mirakl\Core\Helper\Data as CoreHelper;
use Mirakl\Process\Model\Process;
use Mirakl\Process\Model\ProcessFactory;
use Mirakl\Process\Model\ResourceModel\ProcessFactory as ResourceFactory;
use Mirakl\Process\Model\ResourceModel\Process\Collection;
use Mirakl\Process\Model\ResourceModel\Process\CollectionFactory;

class Data extends AbstractHelper
{
    /**
     * @var CoreHelper
     */
    protected $coreHelper;

    /**
     * @var Config
     */
    protected $config;

    /**
     * @var CollectionFactory
     */
    protected $collectionFactory;

    /**
     * @var Filesystem
     */
    protected $filesystem;

    /**
     * @var WriteInterface
     */
    protected $mediaDirectory;

    /**
     * @var ProcessFactory
     */
    protected $processFactory;

    /**
     * @var ResourceFactory
     */
    protected $resourceFactory;

    /**
     * @param Context           $context
     * @param CoreHelper        $coreHelper
     * @param Config            $config
     * @param CollectionFactory $collectionFactory
     * @param Filesystem        $filesystem
     * @param ProcessFactory    $processFactory
     * @param ResourceFactory   $resourceFactory
     */
    public function __construct(
        Context $context,
        CoreHelper $coreHelper,
        Config $config,
        CollectionFactory $collectionFactory,
        Filesystem $filesystem,
        ProcessFactory $processFactory,
        ResourceFactory $resourceFactory
    ) {
        parent::__construct($context);
        $this->coreHelper = $coreHelper;
        $this->config = $config;
        $this->collectionFactory = $collectionFactory;
        $this->filesystem = $filesystem;
        $this->mediaDirectory = $filesystem->getDirectoryWrite(DirectoryList::MEDIA);
        $this->processFactory = $processFactory;
        $this->resourceFactory = $resourceFactory;
    }

    /**
     * @param ...$values
     * @return  string
     */
    public function generateHash(...$values)
    {
        return md5(implode(' ', $values));
    }

    /**
     * @return string|false
     */
    public function getArchiveDir()
    {
        return implode(DIRECTORY_SEPARATOR, ['mirakl', 'process', date('Y'), date('m'), date('d')]);
    }

    /**
     * Returns URL to the specified file
     *
     * @param string $filePath
     * @return string
     */
    public function getFileUrl($filePath)
    {
        $relativePath = $this->getRelativePath($filePath);
        $baseUrl = $this->coreHelper->getBaseUrl();

        return $baseUrl . $relativePath;
    }

    /**
     * Removes base dir from specified file path
     *
     * @param string             $path
     * @param ReadInterface|null $directory
     * @return string
     */
    public function getRelativePath($path, ReadInterface $directory = null)
    {
        if ($directory === null) {
            $directory = $this->filesystem->getDirectoryRead(DirectoryList::ROOT);
        }

        if (strpos($path, $directory->getAbsolutePath()) === 0) {
            return substr($path, strlen($directory->getAbsolutePath()));
        }

        return $path;
    }

    /**
     * Returns the processes for which we want to check the Mirakl status
     *
     * @return Collection
     */
    public function getMiraklStatusToCheckProcesses()
    {
        // Retrieve processing processes to exclude them afterwards
        $processing = $this->collectionFactory->create()
            ->addProcessingFilter();

        // Retrieve completed processes
        $completed = $this->collectionFactory->create()
            ->addCompletedFilter()
            ->addMiraklPendingFilter()
            ->addApiTypeFilter()
            ->addExcludeHashFilter($processing->getColumnValues('hash'))
            ->setOrder('id', 'ASC'); // oldest first

        return $completed;
    }

    /**
     * Returns the oldest pending process
     *
     * @return Process|null
     */
    public function getPendingProcess()
    {
        $pendingCollection = $this->getPendingProcessCollection();
        $pendingCollection->getSelect()->limit(1);

        if ($pendingCollection->count()) {
            return $pendingCollection->getFirstItem();
        }

        return null;
    }

    /**
     * Returns a collection all pending processes, oldest first
     *
     * @return  ProcessCollection
     */
    public function getPendingProcessCollection()
    {
        // Retrieve processing processes
        $processingCollection = $this->collectionFactory->create();
        $processingCollection->addProcessingFilter();

        // Retrieve pending processes
        $pendingCollection = $this->collectionFactory->create();
        $pendingCollection->addPendingFilter()
                          ->addExcludeHashFilter($processingCollection->getColumnValues('hash'))
                          ->setOrder('id', 'ASC'); // oldest first

        return $pendingCollection;
    }

    /**
     * @param string|null $hash
     * @return Collection
     */
    public function getRunningProcesses($hash = null)
    {
        $collection = $this->collectionFactory->create();
        $collection->addProcessingFilter();

        if ($hash) {
            $collection->addFieldToFilter('hash', $hash);
        }

        return $collection;
    }

    /**
     * @param string $file
     * @return Filesystem\File\ReadInterface
     */
    public function openFile($file)
    {
        return $this->filesystem
            ->getDirectoryRead(DirectoryList::MEDIA)
            ->openFile($file);
    }

    /**
     * Archives specified file in media/ folder
     *
     * @param string|\SplFileObject|FileWrapper $file
     * @param string|null                       $extension
     * @return string|false
     */
    public function saveFile($file, $extension = null)
    {
        if (is_string($file)) {
            $file = new \SplFileObject($file, 'r');
        }

        if ($file instanceof FileWrapper) {
            $file = $file->getFile();
        }

        if (null === $extension) {
            $extension = $file->getFlags() & \SplFileObject::READ_CSV ? 'csv' : 'txt';
        }

        [$micro, $time] = explode(' ', microtime());
        $filename = sprintf('%s_%s.%s', date('Ymd_His', $time), $micro, $extension);
        $filepath = $this->getArchiveDir() . DIRECTORY_SEPARATOR . $filename;

        try {
            $fh = $this->filesystem
                ->getDirectoryWrite(DirectoryList::MEDIA)
                ->openFile($filepath, 'w+');

            $file->rewind();
            while (!$file->eof()) {
                $fh->write($file->fgets());
            }
            $fh->close();

            return $filepath;
        } catch (FileSystemException $e) {
            return false;
        }
    }

    /**
     * @param int    $size
     * @param string $separator
     * @return string
     */
    public function formatSize($size, $separator = ' ')
    {
        return $this->coreHelper->formatSize($size, $separator);
    }

    /**
     * @param string $path
     * @return int
     */
    public function getFileSize($path)
    {
        $directory = $this->filesystem->getDirectoryRead(DirectoryList::MEDIA);

        if (!$directory->isFile($path)) {
            return 0;
        }

        $stat =  $directory->stat($path);

        return $stat['size'];
    }
}
