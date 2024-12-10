<?php
namespace Mirakl\Mci\Helper\Product;

use GuzzleHttp\Exception\RequestException;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\Product\Action as ProductAction;
use Magento\Catalog\Model\Product\Gallery\Processor as MediaGalleryProcessor;
use Magento\Catalog\Model\ResourceModel\Eav\Attribute as EavAttribute;
use Magento\Catalog\Model\ResourceModel\Product as ProductResource;
use Magento\Catalog\Model\ResourceModel\Product\Collection as ProductCollection;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory as ProductCollectionFactory;
use Magento\Catalog\Model\ResourceModel\ProductFactory as ProductResourceFactory;
use Magento\ConfigurableProduct\Model\Product\ReadHandler;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Filesystem;
use Magento\Framework\Filesystem\Directory\WriteInterface as DirectoryWriteInterface;
use Mirakl\Api\Helper\Config as ApiConfigHelper;
use Mirakl\Core\Helper\Data as CoreHelper;
use Mirakl\Mci\Helper\Data as MciHelper;
use Mirakl\Mci\Model\Image\Converter as ImageConverter;
use Mirakl\Mci\Model\Image\Downloader as ImageDownloader;
use Mirakl\Process\Model\Process;
use Psr\Http\Message\ResponseInterface;

class Image extends AbstractHelper
{
    const IMAGES_IMPORT_STATUS_PENDING    = 1;
    const IMAGES_IMPORT_STATUS_PROCESSING = 2;
    const IMAGES_IMPORT_STATUS_PROCESSED  = 3;

    const DELETED_IMAGE_URL = 'http://delete.image?processed=false';

    /**
     * @var ApiConfigHelper
     */
    protected $apiConfigHelper;

    /**
     * @var CoreHelper
     */
    protected $coreHelper;

    /**
     * @var MciHelper
     */
    protected $mciHelper;

    /**
     * @var ImageDownloader
     */
    protected $imageDownloader;

    /**
     * @var ProductCollectionFactory
     */
    protected $productCollectionFactory;

    /**
     * @var Filesystem
     */
    protected $filesystem;

    /**
     * @var DirectoryWriteInterface
     */
    protected $tmpDirectory;

    /**
     * @var DirectoryWriteInterface
     */
    protected $mediaDirectory;

    /**
     * @var MediaGalleryProcessor
     */
    protected $mediaGalleryProcessor;

    /**
     * @var ReadHandler
     */
    protected $configurableReadHandler;

    /**
     * @var ProductResourceFactory
     */
    protected $productResourceFactory;

    /**
     * @var ProductResource
     */
    protected $productResource;

    /**
     * @var ImageConverter
     */
    protected $imageConverter;

    /**
     * @var ProductAction
     */
    protected $productAction;

    /**
     * @param   Context                     $context
     * @param   ApiConfigHelper             $apiConfigHelper
     * @param   CoreHelper                  $coreHelper
     * @param   MciHelper                   $mciHelper
     * @param   ImageDownloader             $imageDownloader
     * @param   ProductCollectionFactory    $productCollectionFactory
     * @param   Filesystem                  $filesystem
     * @param   MediaGalleryProcessor       $mediaGalleryProcessor
     * @param   ReadHandler                 $configurableReadHandler
     * @param   ProductResourceFactory      $productResourceFactory
     * @param   ImageConverter              $imageConverter
     * @param   ProductAction               $productAction
     */
    public function __construct(
        Context $context,
        ApiConfigHelper $apiConfigHelper,
        CoreHelper $coreHelper,
        MciHelper $mciHelper,
        ImageDownloader $imageDownloader,
        ProductCollectionFactory $productCollectionFactory,
        Filesystem $filesystem,
        MediaGalleryProcessor $mediaGalleryProcessor,
        ReadHandler $configurableReadHandler,
        ProductResourceFactory $productResourceFactory,
        ImageConverter $imageConverter,
        ProductAction $productAction
    ) {
        parent::__construct($context);
        $this->apiConfigHelper          = $apiConfigHelper;
        $this->coreHelper               = $coreHelper;
        $this->mciHelper                = $mciHelper;
        $this->imageDownloader          = $imageDownloader;
        $this->productCollectionFactory = $productCollectionFactory;
        $this->filesystem               = $filesystem;
        $this->tmpDirectory             = $filesystem->getDirectoryWrite(DirectoryList::SYS_TMP);
        $this->mediaDirectory           = $filesystem->getDirectoryWrite(DirectoryList::MEDIA);
        $this->mediaGalleryProcessor    = $mediaGalleryProcessor;
        $this->configurableReadHandler  = $configurableReadHandler;
        $this->productResourceFactory   = $productResourceFactory;
        $this->productResource          = $productResourceFactory->create();
        $this->imageConverter           = $imageConverter;
        $this->productAction            = $productAction;
    }

    /**
     * Returns dir where images will be downloaded temporarily
     *
     * @return  string
     */
    public function getDownloadDir()
    {
        $path = implode(DIRECTORY_SEPARATOR, ['mirakl', 'images', date('Y'), date('m'), date('d')]);
        $this->tmpDirectory->create($path);
        $this->mediaDirectory->create($path);

        return $path;
    }

    /**
     * @return  string
     */
    public function generateImageFileName()
    {
        return substr(sha1(uniqid()), 0, 16);
    }

    /**
     * @param   string  $file
     * @return  string
     */
    public function getFilePath($file)
    {
        return $this->getDownloadDir() . DIRECTORY_SEPARATOR . $file;
    }

    /**
     * @return  EavAttribute[]
     */
    public function getImagesAttributes()
    {
        return $this->mciHelper->getImagesAttributes();
    }

    /**
     * @param   Product $product
     * @return  array
     */
    public function getProductImageAttributeList($product)
    {
        $attributeList = [];

        foreach ($product->getMediaAttributes() as $imageAttribute) {
            /** @var EavAttribute $imageAttribute */
            $attributeList[] = $imageAttribute->getAttributeCode();
        }

        return $attributeList;
    }

    /**
     * @return  ProductCollection
     */
    public function getProductsToProcess()
    {
        return $this->getProductsByImagesStatus(self::IMAGES_IMPORT_STATUS_PENDING);
    }

    /**
     * @param   string $status
     * @return  ProductCollection
     */
    public function getProductsByImagesStatus($status)
    {
        $collection = $this->productCollectionFactory->create();
        $collection->addFieldToFilter(MciHelper::ATTRIBUTE_IMAGES_STATUS, (string) $status);

        return $collection;
    }

    /**
     * @return  ProductCollection
     * @deprecated Use getProductsToProcess() instead
     */
    public function getProductsToProcessByQueryParam()
    {
        $collection = $this->productCollectionFactory->create();

        // Retrieve mirakl_image_* attributes
        $attributes = $this->getImagesAttributes();

        if (empty($attributes)) {
            return $collection->addIdFilter(0); // no product to process
        }

        $conds = [];
        foreach ($attributes as $attribute) {
            /** @var EavAttribute $attribute */
            $conds[] = [
                'attribute' => $attribute->getAttributeCode(),
                'like'      => 'http%processed=false%',
            ];
        }
        $collection->addAttributeToFilter($conds, null, 'left');

        return $collection;
    }

    /**
     * @param   ProductCollection   $collection
     * @return  int
     */
    public function markProductsImagesAsPending(ProductCollection $collection)
    {
        return $this->updateProductsImagesStatus($collection, self::IMAGES_IMPORT_STATUS_PENDING);
    }

    /**
     * @param   ProductCollection   $collection
     * @return  int
     */
    public function markProductsImagesAsProcessing(ProductCollection $collection)
    {
        return $this->updateProductsImagesStatus($collection, self::IMAGES_IMPORT_STATUS_PROCESSING);
    }

    /**
     * @param   ProductCollection   $collection
     * @return  int
     */
    public function markProductsImagesAsProcessed(ProductCollection $collection)
    {
        return $this->updateProductsImagesStatus($collection, self::IMAGES_IMPORT_STATUS_PROCESSED);
    }

    /**
     * @param   ProductCollection   $collection
     * @param   int                 $status
     * @return  int
     */
    public function updateProductsImagesStatus(ProductCollection $collection, $status)
    {
        $productIds = $collection->getAllIds();

        if (empty($productIds)) {
            return 0;
        }

        return $collection->getConnection()->update(
            $collection->getMainTable(),
            [MciHelper::ATTRIBUTE_IMAGES_STATUS => $status],
            [$collection->getEntity()->getEntityIdField() . ' IN (?)' => $productIds]
        );
    }

    /**
     * Synchronizes Mirakl images (mirakl_image_* attributes) :
     *
     * 1. Download images if URL is specified
     * 2. Add images to product
     * 3. Define images as default if no image where present
     *
     * @param   Process             $process
     * @param   ProductCollection   $collection
     * @return  $this
     */
    public function importProductsImages(Process $process, ProductCollection $collection)
    {
        try {
            set_time_limit(0); // Script may take a while

            // Retrieve mirakl_image_* attributes
            $attributes = $this->getImagesAttributes();

            if (empty($attributes)) {
                $process->output(__('No image attribute found.'));

                return $this;
            }

            $process->output(__(
                'Found %1 image attribute%2 (%3).',
                count($attributes),
                count($attributes) > 1 ? 's' : '',
                implode(', ', array_keys($attributes))
            ), true);

            $collection->setStoreId(0);
            $collection->addAttributeToSelect('*');
            $collection->addMediaGalleryData(); // /!\ Loads collection implicitly

            $process->output(__(
                'Found %1 product%2 to process.',
                count($collection),
                count($collection) > 1 ? 's' : ''
            ));

            $this->apiConfigHelper->disable();

            $client = $this->imageDownloader->getHttpClient();
            $downloadTime = 0;

            /** @var \Magento\Catalog\Model\Product $product */
            foreach ($collection as $product) {
                $process->hr();

                if ($this->areProductImagesProcessed($product)) {
                    $process->output(__('Images for product %1 have already been processed', $product->getId()));
                    continue;
                }

                $this->configurableReadHandler->execute($product);
                $process->output(__('Processing images for product %1...', $product->getId()));

                $product->setStoreId(0); // force admin area on product

                $urls = [];
                $images = [];
                $deletedImages = false;

                foreach ($attributes as $attribute) {
                    /** @var EavAttribute $attribute */
                    if (!$url = $product->getData($attribute->getAttributeCode())) {
                        continue;
                    }

                    if ($url == self::DELETED_IMAGE_URL) {
                        $product->setData($attribute->getAttributeCode(), '');
                        $deletedImages = true;
                        continue;
                    }

                    $url = $this->prepareUrl($url);
                    $urls[$attribute->getAttributeCode()] = $url;
                }

                if (empty($urls)) {
                    $process->output(__('Nothing to download for this product'));
                    if (!$deletedImages) {
                        continue; // Continue to next product because no URL to download and no image to delete
                    }
                } else {
                    $process->output(__('Downloading images...'));
                    $start = microtime(true);

                    try {
                        $this->imageDownloader->downloadMultiple($client, $urls,
                            function (ResponseInterface $response, $attrCode) use ($process, &$images) {
                                $file = $this->onImageFulfilled($response, $process);
                                $images[$attrCode] = $file;
                            },
                            function (\Exception $reason, $attrCode) use ($process, $product) {
                                $this->onImageRejected($reason, $process, $product, $attrCode);
                            }
                        );
                    } catch (\Exception $e) {
                        $process->output(__('ERROR: %1', $e->getMessage()));
                        continue; // Try next product
                    }

                    $time = round(microtime(true) - $start, 2);
                    $process->output(__('Download time: %1 file(s), %2s', count($urls), $time));
                    $downloadTime += $time;
                }

                if (empty($images) && !$deletedImages) {
                    continue; // No valid image to save or delete, continue to next product
                }

                // Remove old images
                if ($product->getMediaGalleryEntries()) {
                    /** @var \Magento\Catalog\Model\Product\Gallery\Entry $entry */
                    foreach ($product->getMediaGalleryEntries() as $entry) {
                        $this->mediaGalleryProcessor->removeImage($product, $entry->getFile());
                    }
                }

                // Sort images by attribute name to restore the order
                ksort($images);

                $i = 0;
                foreach ($images as $file) {
                    try {
                        $imageAttributeList = ($i === 0) ? $this->getProductImageAttributeList($product) : null;
                        $this->mediaGalleryProcessor->addImage($product, $file, $imageAttributeList, false, false);
                        $i++; // Increment only if image has been correctly added
                        if ($this->mediaDirectory->isFile($file)) {
                            $this->mediaDirectory->delete($file);
                        }
                    } catch (\Exception $e) {
                        $process->output(__('ERROR: %1', $e->getMessage()));
                    }
                }

                $product->setData(MciHelper::ATTRIBUTE_IMAGES_STATUS, self::IMAGES_IMPORT_STATUS_PROCESSED);

                $start = microtime(true);
                $process->output(__('Saving images for product %1...', $product->getId()));

                try {
                    $this->productResource->save($product);
                } catch (\Exception $e) {
                    $process->output(__('ERROR: %1', $e->getMessage()));
                }

                $time = round(microtime(true) - $start, 2);
                $process->output(__('Saved! (%1s)', $time));
            }

            $process->hr();
            $process->output(__('Total download time: %1s', $downloadTime));

            $this->apiConfigHelper->enable();
        } catch (\Exception $e) {
            $process->fail(__('ERROR: %1', $e->getMessage()));
            throw $e;
        }

        return $this;
    }

    /**
     * Method called when an image is downloaded successfully.
     * Must return the image file path.
     *
     * @param   ResponseInterface   $response
     * @param   Process             $process
     * @return  string
     */
    public function onImageFulfilled(ResponseInterface $response, Process $process)
    {
        $url = $response->getHeaderLine(ImageDownloader::HEADER_MIRAKL_IMAGE_URL);

        $process->output($url);

        $pathParts = pathinfo(basename(parse_url($url, PHP_URL_PATH)));
        $filePath = $this->getFilePath($this->generateImageFileName());

        if (isset($pathParts['extension'])) {
            $filePath .= '.' . $pathParts['extension'];
        }
        $mediaFilePath = $filePath;

        $this->tmpDirectory->writeFile($filePath, (string) $response->getBody());

        try {
            $fileStat = $this->tmpDirectory->stat($filePath);
            $fileSize = $fileStat['size'];

            if (!$fileSize) {
                throw new LocalizedException(__('Image file is empty after download'));
            }

            switch (exif_imagetype($this->tmpDirectory->getAbsolutePath($filePath))) {
                case IMAGETYPE_GIF:
                    $ext = '.gif';
                    break;
                case IMAGETYPE_JPEG:
                    $ext = '.jpg';
                    break;
                case IMAGETYPE_PNG:
                    $ext = '.png';
                    break;
                case IMAGETYPE_WEBP:
                    $process->output(__('Converting webp image to jpeg...'));

                    // Try to convert webp image to jpeg
                    $this->imageConverter->convertWebpToJpeg($this->tmpDirectory->getAbsolutePath($filePath));

                    unset($pathParts['extension']);
                    $ext = '.jpg';
                    break;
                default:
                    throw new LocalizedException(__('Could not detect image type'));
            }

            if (!isset($pathParts['extension'])) {
                $mediaFilePath .= $ext;
            }
            $this->tmpDirectory->renameFile($filePath, $mediaFilePath, $this->mediaDirectory);

            if ($this->mediaDirectory->isFile($mediaFilePath)) {
                $process->output(__('OK (%1)', $this->coreHelper->formatSize($fileSize)));
            }
        } catch (\Exception $e) {
            $process->output(__('ERROR: %1', $e->getMessage()));
            if ($this->tmpDirectory->isFile($filePath)) {
                $this->tmpDirectory->delete($filePath);
            }
        }

        return $mediaFilePath;
    }

    /**
     * Method called when an error is encountered during an image download.
     *
     * @param   \Exception  $reason
     * @param   Process     $process
     * @param   Product     $product
     * @param   string      $attrCode
     */
    public function onImageRejected(\Exception $reason, Process $process, Product $product, $attrCode)
    {
        if ($reason instanceof RequestException && $response = $reason->getResponse()) {
            $url = $response->getHeaderLine(ImageDownloader::HEADER_MIRAKL_IMAGE_URL);
            $url = $this->coreHelper->addQueryParamToUrl($url, 'processed', 'error');
            $url = $this->coreHelper->addQueryParamToUrl($url, 'process_id', $process->getId());

            $product->setData($attrCode, $url);

            $this->productAction->updateAttributes([$product->getId()], [$attrCode => $url], $product->getStoreId());
        }

        $process->output(__('ERROR: %1', $reason->getMessage()));
    }

    /**
     * @param   Product $product
     * @return  bool
     */
    public function areProductImagesProcessed(Product $product)
    {
        return intval($product->getData(MciHelper::ATTRIBUTE_IMAGES_STATUS)) === self::IMAGES_IMPORT_STATUS_PROCESSED;
    }

    /**
     * Synchronizes Mirakl images (mirakl_image_* attributes) :
     *
     * 1. Download images if URL is specified
     * 2. Add images to product
     * 3. Define images as default if no image where present
     *
     * @param   Process $process
     * @param   int     $limit
     * @return  $this
     * @deprecated Use importProductsImages() instead
     */
    public function run(Process $process, $limit = 100)
    {
        $collection = $this->getProductsToProcess();

        if ($limit) {
            $collection->getSelect()->limit($limit);
        }

        return $this->importProductsImages($process, $collection);
    }

    /**
     * @param   Process $process
     * @param   array   $productIds
     * @return  $this
     */
    public function runByProductIds(Process $process, array $productIds)
    {
        $collection = $this->productCollectionFactory->create();
        $collection->addIdFilter($productIds);

        return $this->importProductsImages($process, $collection);
    }

    /**
     * Prepares URL before being downloaded
     * (remove some query parameters that have been added by the connector).
     *
     * @param   string  $url
     * @return  string
     */
    protected function prepareUrl($url)
    {
        $urlParts = parse_url($url);

        if (isset($urlParts['query'])) {
            $queryParams = [];
            parse_str($urlParts['query'], $queryParams);
            unset($queryParams['processed']);
            unset($queryParams['process_id']);
            $urlParts['query'] = http_build_query($queryParams) ?: null;
            $url = $this->coreHelper->buildUrl($urlParts);
        }

        return $url;
    }
}
