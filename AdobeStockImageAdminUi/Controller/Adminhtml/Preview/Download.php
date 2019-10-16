<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\AdobeStockImageAdminUi\Controller\Adminhtml\Preview;

use Magento\AdobeStockAssetApi\Api\GetAssetByIdInterface;
use Magento\AdobeStockImageApi\Api\SaveImageInterface;
use Magento\Backend\App\Action;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Exception\NotFoundException;
use Psr\Log\LoggerInterface;

/**
 * Class Download
 */
class Download extends Action
{
    /**
     * Successful image download result code.
     */
    const HTTP_OK = 200;

    /**
     * Download image failed response code.
     */
    const HTTP_BAD_REQUEST = 400;

    /**
     * Internal server error response code.
     */
    const HTTP_INTERNAL_ERROR = 500;

    /**
     * @see _isAllowed()
     */
    const ADMIN_RESOURCE = 'Magento_AdobeStockImageAdminUi::save_preview_images';

    /**
     * @var GetAssetByIdInterface
     */
    private $getAssetById;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var SaveImageInterface
     */
    private $saveImage;

    /**
     * Download constructor.
     *
     * @param Action\Context $context
     * @param SaveImageInterface $saveImage
     * @param LoggerInterface $logger
     * @param GetAssetByIdInterface $getAssetById
     */
    public function __construct(
        Action\Context $context,
        SaveImageInterface $saveImage,
        LoggerInterface $logger,
        GetAssetByIdInterface $getAssetById
    ) {
        parent::__construct($context);

        $this->saveImage = $saveImage;
        $this->getAssetById = $getAssetById;
        $this->logger = $logger;
    }

    /**
     * @inheritDoc
     */
    public function execute()
    {
        try {
            $params = $this->getRequest()->getParams();
            $mediaId = (int) $params['media_id'];
            $destinationPath = (string) $params['destination_path'];
            $asset = $this->getAssetById->execute($mediaId);
            $this->saveImage->execute($asset, $destinationPath);

            $responseCode = self::HTTP_OK;
            $responseContent = [
                'success' => true,
                'message' => __('You have successfully downloaded the image.'),
            ];
        } catch (NotFoundException $exception) {
            $responseCode = self::HTTP_BAD_REQUEST;
            $responseContent = [
                'success' => false,
                'message' => __('Image not found. Could not be saved.'),
            ];
        } catch (\Exception $exception) {
            $responseCode = self::HTTP_INTERNAL_ERROR;
            $logMessage = __('An error occurred during image download: %1', $exception->getMessage());
            $this->logger->critical($logMessage);
            $responseContent = [
                'success' => false,
                'message' => __('An error occurred while image download. Contact support.'),
            ];
        }

        /** @var \Magento\Framework\Controller\Result\Json $resultJson */
        $resultJson = $this->resultFactory->create(ResultFactory::TYPE_JSON);
        $resultJson->setHttpResponseCode($responseCode);
        $resultJson->setData($responseContent);

        return $resultJson;
    }
}
