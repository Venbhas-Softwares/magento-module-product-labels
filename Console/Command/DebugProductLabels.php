<?php
declare(strict_types=1);

namespace Venbhas\ProductLabels\Console\Command;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\Product;
use Magento\Customer\Model\Session as CustomerSession;
use Magento\Framework\App\Area;
use Magento\Framework\App\Http\Context as HttpContext;
use Magento\Framework\App\State;
use Magento\Customer\Model\Context as CustomerContext;
use Magento\Store\Model\StoreManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Venbhas\ProductLabels\Model\Config;
use Venbhas\ProductLabels\Model\Label as LabelModel;
use Venbhas\ProductLabels\Model\Label\IdListFormatter;
use Venbhas\ProductLabels\Model\Label\ProductValidator;
use Venbhas\ProductLabels\Model\Label\Resolver;
use Venbhas\ProductLabels\Model\ResourceModel\Label\CollectionFactory;

/**
 * Debug command to show resolved labels for a SKU.
 */
class DebugProductLabels extends Command
{
    private const ARG_SKU = 'sku';
    private const OPT_STORE = 'store';

    /** @var ProductRepositoryInterface */
    private $productRepository;

    /** @var Resolver */
    private $resolver;

    /** @var Config */
    private $config;

    /** @var CollectionFactory */
    private $collectionFactory;

    /** @var StoreManagerInterface */
    private $storeManager;

    /** @var CustomerSession */
    private $customerSession;

    /** @var HttpContext */
    private $httpContext;

    /** @var IdListFormatter */
    private $idListFormatter;

    /** @var ProductValidator */
    private $productValidator;

    /** @var State */
    private $appState;

    /**
     * @param ProductRepositoryInterface $productRepository
     * @param Resolver $resolver
     * @param Config $config
     * @param CollectionFactory $collectionFactory
     * @param StoreManagerInterface $storeManager
     * @param CustomerSession $customerSession
     * @param HttpContext $httpContext
     * @param IdListFormatter $idListFormatter
     * @param ProductValidator $productValidator
     * @param State $appState
     * @param string|null $name
     */
    public function __construct(
        ProductRepositoryInterface $productRepository,
        Resolver $resolver,
        Config $config,
        CollectionFactory $collectionFactory,
        StoreManagerInterface $storeManager,
        CustomerSession $customerSession,
        HttpContext $httpContext,
        IdListFormatter $idListFormatter,
        ProductValidator $productValidator,
        State $appState,
        ?string $name = null
    ) {
        parent::__construct($name);
        $this->productRepository = $productRepository;
        $this->resolver = $resolver;
        $this->config = $config;
        $this->collectionFactory = $collectionFactory;
        $this->storeManager = $storeManager;
        $this->customerSession = $customerSession;
        $this->httpContext = $httpContext;
        $this->idListFormatter = $idListFormatter;
        $this->productValidator = $productValidator;
        $this->appState = $appState;
    }

    /**
     * @inheritdoc
     */
    protected function configure(): void
    {
        $this->setName('venbhas:product-labels:debug')
            ->setDescription('Show resolved product labels for a SKU')
            ->addArgument(self::ARG_SKU, InputArgument::REQUIRED, 'Product SKU')
            ->addOption(
                self::OPT_STORE,
                null,
                InputOption::VALUE_OPTIONAL,
                'Store ID to emulate frontend scope',
                '1'
            );
    }

    /**
     * @inheritdoc
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->ensureAreaCode();

        $sku = (string) $input->getArgument(self::ARG_SKU);
        $storeId = (int) $input->getOption(self::OPT_STORE);
        $this->storeManager->setCurrentStore($storeId);

        $product = $this->productRepository->get($sku, false, $storeId);
        if ($product instanceof Product) {
            $product->setStoreId($storeId);
        }

        $groupId = (int) ($this->httpContext->getValue(CustomerContext::CONTEXT_GROUP)
            ?? $this->customerSession->getCustomerGroupId());

        $output->writeln(sprintf(
            'Module enabled: %s | Store: %d | Customer group: %d | Product ID: %d',
            $this->config->isEnabled($storeId) ? 'yes' : 'no',
            $storeId,
            $groupId,
            (int) $product->getId()
        ));

        $labels = $this->resolver->getLabelsForProduct($product);

        if ($labels === []) {
            $output->writeln('<info>No labels matched for SKU ' . $sku . '</info>');
            $this->writeDiagnostics($output, $product, $storeId, $groupId);
            return Command::SUCCESS;
        }

        foreach ($labels as $label) {
            $output->writeln(sprintf(
                '[%d] %s (%s) at %s',
                $label['label_id'],
                $label['display_text'],
                $label['label_type'],
                $label['position']
            ));
        }

        return Command::SUCCESS;
    }

    /**
     * Print why active labels did not match.
     *
     * @param OutputInterface $output
     * @param \Magento\Catalog\Api\Data\ProductInterface $product
     * @param int $storeId
     * @param int $groupId
     * @return void
     */
    private function writeDiagnostics(
        OutputInterface $output,
        $product,
        int $storeId,
        int $groupId
    ): void {
        $collection = $this->collectionFactory->create();
        $collection->addActiveFilter($storeId);

        if ($collection->getSize() === 0) {
            $output->writeln('<comment>No active labels found for this store and date.</comment>');
            return;
        }

        $output->writeln('<comment>Active labels in scope:</comment>');
        foreach ($collection as $label) {
            /** @var LabelModel $label */
            $allowedGroups = (string) $label->getCustomerGroupIds();
            $groupMatch = $allowedGroups === ''
                || in_array((string) $groupId, $this->idListFormatter->explodeIds($allowedGroups), true);
            $catalogProduct = $product instanceof Product ? $product : null;
            $ruleMatch = $catalogProduct
                && $this->productValidator->validate($label, $catalogProduct, $storeId);

            $output->writeln(sprintf(
                '- [%d] %s | group:%s | rule:%s | type:%s | text:%s',
                (int) $label->getId(),
                (string) $label->getName(),
                $groupMatch ? 'ok' : 'blocked',
                $ruleMatch ? 'ok' : 'blocked',
                (string) $label->getLabelType(),
                (string) $label->getTextContent()
            ));

            if ($allowedGroups !== '') {
                $output->writeln('    allowed groups: ' . $allowedGroups);
            }

            $conditionSummary = $this->describeConditions($label);
            $output->writeln('    conditions: ' . $conditionSummary);

            if ($catalogProduct && $conditionSummary !== 'none') {
                $output->writeln('    product values: ' . $this->describeProductValues($label, $catalogProduct));
            }
        }
    }

    /**
     * Summarize configured label conditions.
     *
     * @param LabelModel $label
     * @return string
     */
    private function describeConditions(LabelModel $label): string
    {
        $conditions = $label->getConditions();
        if (!$conditions || !$conditions->getConditions()) {
            return 'none';
        }

        $parts = [];
        foreach ($conditions->getConditions() as $condition) {
            $parts[] = sprintf(
                '%s %s %s',
                (string) $condition->getAttribute(),
                (string) $condition->getOperator(),
                (string) $condition->getValue()
            );
        }

        return implode('; ', $parts);
    }

    /**
     * Show product attribute values referenced by label conditions.
     *
     * @param LabelModel $label
     * @param Product $product
     * @return string
     */
    private function describeProductValues(LabelModel $label, Product $product): string
    {
        $conditions = $label->getConditions();
        if (!$conditions || !$conditions->getConditions()) {
            return 'n/a';
        }

        $parts = [];
        foreach ($conditions->getConditions() as $condition) {
            $attribute = (string) $condition->getAttribute();
            if ($attribute === '') {
                continue;
            }

            $value = $product->getData($attribute);
            if (is_array($value)) {
                $value = implode(',', $value);
            }

            $parts[] = $attribute . '=' . (string) $value;
        }

        return $parts !== [] ? implode('; ', $parts) : 'n/a';
    }

    /**
     * Set the frontend area code required by store-scoped services in CLI.
     *
     * @return void
     */
    private function ensureAreaCode(): void
    {
        try {
            $this->appState->setAreaCode(Area::AREA_FRONTEND);
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            // Area code is already set by another command bootstrap.
        }
    }
}
