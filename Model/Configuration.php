<?php

namespace Inchoo\TicketMail\Model;

use Inchoo\TicketMail\Api\ConfigurationInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;

/**
 * Class Configuration
 * @package Inchoo\TicketMail\Model
 */
class Configuration implements ConfigurationInterface
{

    /**
     * @var ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * Configuration constructor.
     * @param ScopeConfigInterface $scopeConfig
     */
    public function __construct(ScopeConfigInterface $scopeConfig)
    {
        $this->scopeConfig = $scopeConfig;
    }

    /**
     * @return mixed
     */
    public function isEnabled()
    {
        return $this->scopeConfig->getValue(
            ConfigurationInterface::XML_EMAIL_CONFIGURATION_TEMPLATE,
            ScopeInterface::SCOPE_STORE
        );
    }

}