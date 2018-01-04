<?php

namespace Inchoo\TicketMail\Plugin;

/**
 * Class SendMail
 * @package Inchoo\TicketMail\Plugin
 */
class SendMail
{

    const XML_PATH_EMAIL_TEMPLATE_FIELD = 'customer_ticket_configuration/ticket_mail_notification/notification_mail_template';

    /**
     * @var \Magento\Framework\App\Request\DataPersistorInterface
     */
    protected $persistor;

    /**
     * @var \Magento\Framework\Mail\Template\TransportBuilder
     */
    protected $transportBuilder;

    /**
     * @var \Magento\Framework\Translate\Inline\StateInterface
     */
    protected $inlineTranslation;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $customerSession;

    /**
     * @var \Magento\User\Model\ResourceModel\User\Collection
     */
    protected $adminCollection;

    /**
     * @var \Inchoo\CustomerTicket\Api\TicketRepositoryInterface
     */
    protected $ticketRepository;

    /**
     * @var \Inchoo\TicketMail\Api\ConfigurationInterface
     */
    protected $configuration;

    /**
     * SendMail constructor.
     * @param \Magento\Framework\App\Request\DataPersistorInterface $persistor
     * @param \Magento\Framework\Mail\Template\TransportBuilder $transportBuilder
     * @param \Magento\Framework\Translate\Inline\StateInterface $inlineTranslation
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Magento\User\Model\ResourceModel\User\Collection $adminCollection
     * @param \Inchoo\CustomerTicket\Api\TicketRepositoryInterface $ticketRepository
     * @param \Inchoo\TicketMail\Api\ConfigurationInterface $configuration
     */
    public function __construct(
        \Magento\Framework\App\Request\DataPersistorInterface $persistor,
        \Magento\Framework\Mail\Template\TransportBuilder $transportBuilder,
        \Magento\Framework\Translate\Inline\StateInterface $inlineTranslation,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\User\Model\ResourceModel\User\Collection $adminCollection,
        \Inchoo\CustomerTicket\Api\TicketRepositoryInterface $ticketRepository,
        \Inchoo\TicketMail\Api\ConfigurationInterface $configuration
    )
    {
        $this->persistor = $persistor;
        $this->transportBuilder = $transportBuilder;
        $this->inlineTranslation = $inlineTranslation;
        $this->scopeConfig = $scopeConfig;
        $this->storeManager = $storeManager;
        $this->customerSession = $customerSession;
        $this->adminCollection = $adminCollection;
        $this->ticketRepository = $ticketRepository;
        $this->configuration = $configuration;
    }

    public function afterExecute($subject, $result)
    {
        if($this->configuration->isEnabled()) {
            $id = $this->persistor->get('ticket_id');
            $ticketData = $this->ticketRepository->getById($id);
            $storeScope = \Magento\Store\Model\ScopeInterface::SCOPE_STORE;
            $storeId = $this->storeManager->getStore()->getId();
            $templateId = $this->scopeConfig->getValue(self::XML_PATH_EMAIL_TEMPLATE_FIELD, $storeScope, $storeId);
            $administrators = $this->adminCollection->getData();
            $customer = $this->customerSession->getCustomer();

            $adminMails = [];

            foreach ($administrators as $admin) {
                $adminMails[$admin['username']] = $admin['email'];
            }

            $this->inlineTranslation->suspend();
            try {
                $transport = $this->transportBuilder
                    ->setTemplateIdentifier($templateId)
                    ->setTemplateOptions(
                        [
                            'area' => \Magento\Framework\App\Area::AREA_ADMINHTML,
                            'store' => $storeId
                        ]
                    )
                    ->setTemplateVars(['ticket' => $ticketData, 'name' => $customer->getName(), 'mail' => $customer->getEmail()])
                    ->setFrom(['name' => $customer->getName(), 'email' => $customer->getEmail()])
                    ->addTo($adminMails)
                    ->getTransport();

                $transport->sendMessage();
            } finally {
                $this->inlineTranslation->resume();
            }

            return $result;
        }
    }

}