<?php

namespace Inchoo\TicketMail\Api;

/**
 * Interface ConfigurationInterface
 * @package Inchoo\TicketMail\Api
 */
interface ConfigurationInterface
{

    const XML_EMAIL_CONFIGURATION_TEMPLATE = 'customer_ticket_configuration/ticket_mail_notification/notification_enable';

    public function isEnabled();

}