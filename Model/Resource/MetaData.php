<?php

namespace BigBridge\ProductImport\Model\Resource;

use BigBridge\ProductImport\Model\Db\Magento2DbConnection;

/**
 * @author Patrick van Bergen
 */
class MetaData
{
    const ENTITY_TYPE_TABLE = 'eav_entity_type';
    const PRODUCT_ENTITY_TABLE = 'catalog_product_entity';
    const CATEGORY_ENTITY_TABLE = 'catalog_category_entity';
    const URL_REWRITE_TABLE = 'url_rewrite';
    const CATEGORY_PRODUCT_TABLE = 'catalog_category_product';
    const CONFIG_DATA_TABLE = 'core_config_data';
    const ATTRIBUTE_SET_TABLE = 'eav_attribute_set';
    const ATTRIBUTE_TABLE = 'eav_attribute';
    const ATTRIBUTE_OPTION_TABLE = 'eav_attribute_option';
    const ATTRIBUTE_OPTION_VALUE_TABLE = 'eav_attribute_option_value';
    const STORE_TABLE = 'store';
    const WEBSITE_TABLE = 'store_website';
    const TAX_CLASS_TABLE = 'tax_class';

    const TYPE_DATETIME = 'datetime';
    const TYPE_DECIMAL = 'decimal';
    const TYPE_INTEGER = 'int';
    const TYPE_VARCHAR = 'varchar';
    const TYPE_TEXT = 'text';

    const FRONTEND_SELECT = 'select';

    /** @var  Magento2DbConnection */
    protected $db;

    /** @var  string  */
    public $productEntityTable;

    /** @var string */
    public $categoryEntityTable;

    /** @var string */
    public $urlRewriteTable;

    /** @var  string */
    public $categoryProductTable;

    /** @var  string */
    public $configDataTable;

    /** @var  int */
    public $defaultCategoryAttributeSetId;

    /** @var array Maps attribute set name to id */
    public $productAttributeSetMap;

    /** @var array Maps tax class name to id */
    public $taxClassMap;

    /** @var  array Maps store view code to id */
    public $storeViewMap;

    /** @var  array Maps website code to id */
    public $websiteMap;

    /** @var int  */
    public $productEntityTypeId;

    /** @var int  */
    public $categoryEntityTypeId;

    /** @var  EavAttributeInfo[] */
    public $productEavAttributeInfo;

    /** @var array */
    public $categoryAttributeMap;

    /** @var  array */
    public $categoryUrlSuffix;

    public function __construct(Magento2DbConnection $db)
    {
        $this->db = $db;

        $this->productEntityTable = $db->getFullTableName(self::PRODUCT_ENTITY_TABLE);
        $this->categoryEntityTable = $db->getFullTableName(self::CATEGORY_ENTITY_TABLE);
        $this->urlRewriteTable = $db->getFullTableName(self::URL_REWRITE_TABLE);
        $this->categoryProductTable = $db->getFullTableName(self::CATEGORY_PRODUCT_TABLE);
        $this->configDataTable = $db->getFullTableName(self::CONFIG_DATA_TABLE);

        $this->productEntityTypeId = $this->getProductEntityTypeId();
        $this->categoryEntityTypeId = $this->getCategoryEntityTypeId();

        $this->defaultCategoryAttributeSetId = $this->getDefaultCategoryAttributeSetId();

        $this->categoryAttributeMap = $this->getCategoryAttributeMap();
        $this->productAttributeSetMap = $this->getProductAttributeSetMap();
        $this->productEavAttributeInfo = $this->getProductEavAttributeInfo();

        $this->storeViewMap = $this->getStoreViewMap();
        $this->websiteMap = $this->getWebsiteMap();
        $this->taxClassMap = $this->getTaxClassMap();

        $this->categoryUrlSuffix = $this->getCategoryUrlSuffix();
    }

    /**
     * Returns the id of the default category attribute set id.
     *
     * @return int
     */
    protected function getDefaultCategoryAttributeSetId()
    {
        $entityTypeTable = $this->db->getFullTableName(self::ENTITY_TYPE_TABLE);
        $attributeSetId = $this->db->fetchSingleCell("SELECT `default_attribute_set_id` FROM {$entityTypeTable} WHERE `entity_type_code` = 'catalog_category'");
        return $attributeSetId;
    }

    /**
     * Returns the id of the product entity type.
     *
     * @return int
     */
    protected function getProductEntityTypeId()
    {
        $entityTypeTable = $this->db->getFullTableName(self::ENTITY_TYPE_TABLE);
        $productEntityTypeId = $this->db->fetchSingleCell("SELECT `entity_type_id` FROM {$entityTypeTable} WHERE `entity_type_code` = 'catalog_product'");
        return $productEntityTypeId;
    }

    /**
     * Returns the id of the category entity type.
     *
     * @return int
     */
    protected function getCategoryEntityTypeId()
    {
        $entityTypeTable = $this->db->getFullTableName(self::ENTITY_TYPE_TABLE);
        $categoryEntityTypeId = $this->db->fetchSingleCell("SELECT `entity_type_id` FROM {$entityTypeTable} WHERE `entity_type_code` = 'catalog_category'");
        return $categoryEntityTypeId;
    }

    /**
     * Returns a name => id map for product attribute sets.
     *
     * @return array
     */
    protected function getProductAttributeSetMap()
    {
        $attributeSetTable = $this->db->getFullTableName(self::ATTRIBUTE_SET_TABLE);
        $map = $this->db->fetchMap("SELECT `attribute_set_name`, `attribute_set_id` FROM {$attributeSetTable} WHERE `entity_type_id` = {$this->productEntityTypeId}");
        return $map;
    }

    /**
     * Returns a code => id map for store views.
     *
     * @return array
     */
    protected function getStoreViewMap()
    {
        $storeTable = $this->db->getFullTableName(self::STORE_TABLE);
        $map = $this->db->fetchMap("SELECT `code`, `store_id` FROM {$storeTable}");
        return $map;
    }

    /**
     * Returns a code => id map for websites.
     *
     * @return array
     */
    protected function getWebsiteMap()
    {
        $websiteTable = $this->db->getFullTableName(self::WEBSITE_TABLE);
        $map = $this->db->fetchMap("SELECT `code`, `website_id` FROM {$websiteTable}");
        return $map;
    }

    /**
     * Returns a code => id map for tax classes.
     *
     * @return array
     */
    protected function getTaxClassMap()
    {
        $taxClassTable = $this->db->getFullTableName(self::TAX_CLASS_TABLE);
        $map = $this->db->fetchMap("SELECT `class_name`, `class_id` FROM {$taxClassTable}");
        return $map;
    }


    /**
     * Returns a name => id map for category attributes.
     *
     * @return array
     */
    protected function getCategoryAttributeMap()
    {
        $attributeTable = $this->db->getFullTableName(self::ATTRIBUTE_TABLE);
        $map = $this->db->fetchMap("SELECT `attribute_code`, `attribute_id` FROM {$attributeTable} WHERE `entity_type_id` = {$this->categoryEntityTypeId}");
        return $map;
    }
    
    /**
     * @return array An attribute code indexed array of AttributeInfo
     */
    protected function getProductEavAttributeInfo()
    {
        $attributeTable = $this->db->getFullTableName(self::ATTRIBUTE_TABLE);
        $attributeOptionTable = $this->db->getFullTableName(self::ATTRIBUTE_OPTION_TABLE);
        $attributeOptionValueTable = $this->db->getFullTableName(self::ATTRIBUTE_OPTION_VALUE_TABLE);

        $optionValueRows = $this->db->fetchAll("
            SELECT A.`attribute_code`, O.`option_id`, V.`value`
            FROM {$attributeTable} A
            INNER JOIN {$attributeOptionTable} O ON O.attribute_id = A.attribute_id
            INNER JOIN {$attributeOptionValueTable} V ON V.option_id = O.option_id
            WHERE A.`entity_type_id` = {$this->productEntityTypeId} AND A.frontend_input IN ('select', 'multiselect') AND V.store_id = 0
        ");

        $allOptionValues = [];
        foreach ($optionValueRows as $row) {
            $allOptionValues[$row['attribute_code']][$row['value']] = $row['option_id'];
        }

        $rows = $this->db->fetchAll("
            SELECT `attribute_id`, `attribute_code`, `is_required`, `backend_type`, `frontend_input` 
            FROM {$attributeTable} 
            WHERE `entity_type_id` = {$this->productEntityTypeId} AND backend_type != 'static'");

        $info = [];
        foreach ($rows as $row) {

            $optionValues = array_key_exists($row['attribute_code'], $allOptionValues) ? $allOptionValues[$row['attribute_code']] : [];

            $info[$row['attribute_code']] = new EavAttributeInfo(
                $row['attribute_code'],
                (int)$row['attribute_id'],
                (bool)$row['is_required'],
                $row['backend_type'],
                $this->productEntityTable . '_' . $row['backend_type'],
                $row['frontend_input'],
                $optionValues);
        }

        return $info;
    }

    public function getCategoryUrlSuffix()
    {
        $value = $this->db->fetchSingleCell("
            SELECT `value`
            FROM `{$this->configDataTable}`
            WHERE
                `scope` = 'default' AND
                `scope_id` = 0 AND
                `path` = 'catalog/seo/category_url_suffix'
        ");

        return is_null($value) ? ".html" : $value;
    }
}