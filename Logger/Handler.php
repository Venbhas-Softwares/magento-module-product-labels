<?php
declare(strict_types=1);

namespace Venbhas\ProductLabels\Logger;

use Magento\Framework\Logger\Handler\Base;
use Monolog\Logger;

/**
 * Product labels log handler.
 */
class Handler extends Base
{
    /** @var string */
    protected $fileName = '/var/log/venbhas_product_labels.log';

    /** @var int */
    protected $loggerType = Logger::DEBUG;
}
