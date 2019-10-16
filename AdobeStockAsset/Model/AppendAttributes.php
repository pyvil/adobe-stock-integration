<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\AdobeStockAsset\Model;

use Magento\AdobeStockAsset\Model\ResourceModel\Asset\LoadByIds;
use Magento\Framework\Api\AttributeValueFactory;
use Magento\Framework\Api\Search\DocumentInterface;
use Magento\Framework\Api\Search\SearchResultInterface;
use Magento\Framework\App\ResourceConnection;

/**
 * Class AddIsDownloadedToSearchResult
 */
class AppendAttributes
{
    const ATTRIBUTE_CODE_IS_DOWNLOADED = 'is_downloaded';
    const ATTRIBUTE_CODE_PATH = 'path';

    /**
     * @var AttributeValueFactory
     */
    private $attributeValueFactory;

    /**
     * @var ResourceConnection
     */
    private $resourceConnection;

    /**
     * @var LoadByIds
     */
    private $loadByIds;

    /**
     * AppendAttributes constructor.
     * @param ResourceConnection $resourceConnection
     * @param AttributeValueFactory $attributeValueFactory
     * @param LoadByIds $loadByIds
     */
    public function __construct(
        ResourceConnection $resourceConnection,
        AttributeValueFactory $attributeValueFactory,
        LoadByIds $loadByIds
    ) {
        $this->resourceConnection = $resourceConnection;
        $this->attributeValueFactory = $attributeValueFactory;
        $this->loadByIds = $loadByIds;
    }

    /**
     * Add additional asset attributes
     *
     * @param SearchResultInterface $searchResult
     * @return SearchResultInterface
     */
    public function execute(SearchResultInterface $searchResult): SearchResultInterface
    {
        $items = $searchResult->getItems();

        if (empty($items)) {
            return $searchResult;
        }

        $ids = array_map(
            function ($item) {
                return $item->getId();
            },
            $items
        );

        $assets = $this->loadByIds->execute($ids);

        foreach ($items as $key => $item) {
            if (!isset($assets[$item->getId()])) {
                $this->addAttributes(
                    $item,
                    [
                        self::ATTRIBUTE_CODE_IS_DOWNLOADED => 0,
                        self::ATTRIBUTE_CODE_PATH => ''
                    ]
                );
                continue;
            }

            $this->addAttributes(
                $item,
                [
                    self::ATTRIBUTE_CODE_IS_DOWNLOADED => 1,
                    self::ATTRIBUTE_CODE_PATH => $assets[$item->getId()]->getPath()
                ]
            );
        }

        return $searchResult;
    }

    /**
     * Add attributes to document
     *
     * @param DocumentInterface $document
     * @param array $attributes [code => value]
     * @return DocumentInterface
     */
    private function addAttributes(DocumentInterface $document, array $attributes): DocumentInterface
    {
        $customAttributes = $document->getCustomAttributes();

        foreach ($attributes as $code => $value) {
            $attribute = $this->attributeValueFactory->create();
            $attribute->setAttributeCode($code);
            $attribute->setValue($value);
            $customAttributes[$code] = $attribute;
        }

        $document->setCustomAttributes($customAttributes);

        return $document;
    }
}
