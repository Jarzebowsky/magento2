<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Controller\Adminhtml\Product\Widget;

use Magento\Framework\Exception\NotFoundException;

/**
 * Chooser Product container Action.
 */
class Chooser extends \Magento\Backend\App\Action
{
    /**
     * Authorization level of a basic admin session
     */
    const ADMIN_RESOURCE = 'Magento_Widget::widget_instance';

    /**
     * @var \Magento\Framework\Controller\Result\RawFactory
     */
    protected $resultRawFactory;

    /**
     * @var \Magento\Framework\View\LayoutFactory
     */
    protected $layoutFactory;

    /**
     * @var \Magento\Framework\Escaper
     */
    private $escaper;

    /**
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Framework\Controller\Result\RawFactory $resultRawFactory
     * @param \Magento\Framework\View\LayoutFactory $layoutFactory
     * @param \Magento\Framework\Escaper $escaper
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\Controller\Result\RawFactory $resultRawFactory,
        \Magento\Framework\View\LayoutFactory $layoutFactory,
        \Magento\Framework\Escaper $escaper
    ) {
        parent::__construct($context);
        $this->resultRawFactory = $resultRawFactory;
        $this->layoutFactory = $layoutFactory;
        $this->escaper = $escaper;
    }

    /**
     * Chooser Source action.
     *
     * @return \Magento\Framework\Controller\Result\Raw
     * @throws NotFoundException
     */
    public function execute()
    {
        if (!$this->getRequest()->isPost()) {
            throw new NotFoundException(__('Page not found.'));
        }

        $uniqId = $this->getRequest()->getParam('uniq_id');
        $massAction = $this->getRequest()->getParam('use_massaction', false);
        $productTypeId = $this->getRequest()->getParam('product_type_id', null);

        $layout = $this->layoutFactory->create();
        $productsGrid = $layout->createBlock(
            \Magento\Catalog\Block\Adminhtml\Product\Widget\Chooser::class,
            '',
            [
                'data' => [
                    'id' => $this->escaper->escapeHtml($uniqId),
                    'use_massaction' => $massAction,
                    'product_type_id' => $productTypeId,
                    'category_id' => $this->getRequest()->getParam('category_id'),
                ]
            ]
        );

        $html = $productsGrid->toHtml();

        if (!$this->getRequest()->getParam('products_grid')) {
            $categoriesTree = $layout->createBlock(
                \Magento\Catalog\Block\Adminhtml\Category\Widget\Chooser::class,
                '',
                [
                    'data' => [
                        'id' => $this->escaper->escapeHtml($uniqId) . 'Tree',
                        'node_click_listener' => $productsGrid->getCategoryClickListenerJs(),
                        'with_empty_node' => true,
                    ]
                ]
            );

            $html = $layout->createBlock(\Magento\Catalog\Block\Adminhtml\Product\Widget\Chooser\Container::class)
                ->setTreeHtml($categoriesTree->toHtml())
                ->setGridHtml($html)
                ->toHtml();
        }

        /** @var \Magento\Framework\Controller\Result\Raw $resultRaw */
        $resultRaw = $this->resultRawFactory->create();

        return $resultRaw->setContents($html);
    }
}
