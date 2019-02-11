<?php

namespace PavelLeonidov\WebApiOrderInvoice\Model\Order;

/*******************************************************************
 *  Copyright notice
 *
 *  (c) 2018 Pavel Leonidov <info@pavel-leonidov.de>
 *
 *  All rights reserved
 *
 *  This script is part of the Magento 2 project. The Magento 2 project is
 *  free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as
 *  published by the Free Software Foundation; either version 2 of
 *  the License, or (at your option) any later version.
 *
 *  The GNU General Public License can be found at
 *  http://www.gnu.org/copyleft/gpl.html.
 *
 *  This script is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  This copyright notice MUST APPEAR in all copies of the script!
 ******************************************************************/

use Magento\Framework\App\ResourceConnection;
use Magento\Sales\Api\Data\InvoiceCommentCreationInterface;
use Magento\Sales\Api\Data\InvoiceCreationArgumentsInterface;
use Magento\Sales\Api\Data\InvoiceInterface;
use Magento\Sales\Api\InvoiceOrderInterface;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Sales\Model\Order\Config as OrderConfig;
use Magento\Sales\Model\Order\Invoice;
use Magento\Sales\Model\Order\Invoice\NotifierInterface;
use Magento\Sales\Model\Order\InvoiceDocumentFactory;
use Magento\Sales\Model\Order\InvoiceRepository;
use Magento\Sales\Model\Order\OrderStateResolverInterface;
use Magento\Sales\Model\Order\PaymentAdapterInterface;
use Magento\Sales\Model\Order\Validation\InvoiceOrderInterface as InvoiceOrderValidator;
use Magento\Sales\Model\Service\InvoiceService;
use PavelLeonidov\WebApiOrderInvoice\Api\OrderInvoiceInterface;
use Psr\Log\LoggerInterface;

class OrderInvoice implements OrderInvoiceInterface {
	/**
	 * @var ResourceConnection
	 */
	private $resourceConnection;

	/**
	 * @var OrderRepositoryInterface
	 */
	private $orderRepository;

	/**
	 * @var InvoiceDocumentFactory
	 */
	private $invoiceDocumentFactory;

	/**
	 * @var PaymentAdapterInterface
	 */
	private $paymentAdapter;

	/**
	 * @var OrderStateResolverInterface
	 */
	private $orderStateResolver;

	/**
	 * @var OrderConfig
	 */
	private $config;

	/**
	 * @var InvoiceRepository
	 */
	private $invoiceRepository;

	/**
	 * @var InvoiceOrderValidator
	 */
	private $invoiceOrderValidator;

	/**
	 * @var NotifierInterface
	 */
	private $notifierInterface;

	/**
	 * @var LoggerInterface
	 */
	private $logger;

	/**
	 * @var InvoiceService
	 */
	private $invoiceService;

	/**
	 * InvoiceOrder constructor.
	 * @param ResourceConnection $resourceConnection
	 * @param OrderRepositoryInterface $orderRepository
	 * @param InvoiceDocumentFactory $invoiceDocumentFactory
	 * @param PaymentAdapterInterface $paymentAdapter
	 * @param OrderStateResolverInterface $orderStateResolver
	 * @param OrderConfig $config
	 * @param InvoiceRepository $invoiceRepository
	 * @param InvoiceOrderValidator $invoiceOrderValidator
	 * @param NotifierInterface $notifierInterface
	 * @param LoggerInterface $logger
	 * @param InvoiceService $invoiceService
	 * @SuppressWarnings(PHPMD.ExcessiveParameterList)
	 */
	public function __construct(
		ResourceConnection $resourceConnection,
		OrderRepositoryInterface $orderRepository,
		InvoiceDocumentFactory $invoiceDocumentFactory,
		PaymentAdapterInterface $paymentAdapter,
		OrderStateResolverInterface $orderStateResolver,
		OrderConfig $config,
		InvoiceRepository $invoiceRepository,
		InvoiceOrderValidator $invoiceOrderValidator,
		NotifierInterface $notifierInterface,
		LoggerInterface $logger,
		InvoiceService $invoiceService
	) {
		$this->resourceConnection = $resourceConnection;
		$this->orderRepository = $orderRepository;
		$this->invoiceDocumentFactory = $invoiceDocumentFactory;
		$this->paymentAdapter = $paymentAdapter;
		$this->orderStateResolver = $orderStateResolver;
		$this->config = $config;
		$this->invoiceRepository = $invoiceRepository;
		$this->invoiceOrderValidator = $invoiceOrderValidator;
		$this->notifierInterface = $notifierInterface;
		$this->logger = $logger;
		$this->invoiceService = $invoiceService;
	}

	/**
	 * @param int $orderId
	 * @return \Magento\Sales\Api\Data\InvoiceInterface
	 * @throws \Magento\Sales\Api\Exception\DocumentValidationExceptionInterface
	 * @throws \Magento\Sales\Api\Exception\CouldNotInvoiceExceptionInterface
	 * @throws \Magento\Framework\Exception\InputException
	 * @throws \Magento\Framework\Exception\NoSuchEntityException
	 * @throws \DomainException
	 * @api
	 */
	public function execute(
		$orderId
	) {
		$order = $this->orderRepository->get($orderId);
		$invoice =  NULL;

		try{
			if($order->hasInvoices()) {
				$invoice = $order->getInvoiceCollection()->getFirstItem();
			} else {
				if(!$order->canInvoice()) {
					return;
				}
				$invoice  = $this->invoiceService->prepareInvoice($order);
				$invoice->register();

				$order->addStatusHistoryComment('Invoice Created', false);
			}



		}catch(\Exception $e)
		{
			die($e->getMessage());
			$order->addStatusHistoryComment('Exception message: '.$e->getMessage(), false);
			$order->save();
		}
		return $invoice;

	}


}
?>
