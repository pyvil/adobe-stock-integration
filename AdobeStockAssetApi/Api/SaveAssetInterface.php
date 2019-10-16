<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\AdobeStockAssetApi\Api;

/**
 * Save asset and all it's relations
 *
 * @api
 */
interface SaveAssetInterface
{
    /**
     * Save asset and all it's relations
     *
     * @param \Magento\AdobeStockAssetApi\Api\Data\AssetInterface $asset
     * @throws \Magento\Framework\Exception\AlreadyExistsException
     */
    public function execute(\Magento\AdobeStockAssetApi\Api\Data\AssetInterface $asset): void;
}
