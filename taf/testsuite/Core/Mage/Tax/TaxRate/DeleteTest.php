<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @category    tests
 * @package     selenium
 * @subpackage  tests
 * @author      Magento Core Team <core@magentocommerce.com>
 * @copyright   Copyright (c) 2013 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Tax Rate deletion tests
 *
 * @package     selenium
 * @subpackage  tests
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Core_Mage_Tax_TaxRate_DeleteTest extends Mage_Selenium_TestCase
{
    /**
     * <p>Save rule name for clean up</p>
     */
    protected $_ruleToBeDeleted = array();

    /**
     * <p>Preconditions:</p>
     * <p>Navigate to Sales->Tax->Manage Tax Zones&Rates</p>
     */
    protected function assertPreConditions()
    {
        $this->loginAdminUser();
        $this->navigate('manage_tax_zones_and_rates');
    }

    protected function tearDownAfterTest()
    {
        //Remove Tax rule after test
        if (!empty($this->_ruleToBeDeleted)) {
            $this->loginAdminUser();
            $this->navigate('manage_tax_rule');
            $this->taxHelper()->deleteTaxItem($this->_ruleToBeDeleted, 'rule');
            $this->_ruleToBeDeleted = array();
        }
    }

    /**
     * <p>Delete a Tax Rate</p>
     * <p>Steps:</p>
     * <p>1. Create a new Tax Rate</p>
     * <p>2. Open the Tax Rate</p>
     * <p>3. Delete the Tax Rate</p>
     * <p>Expected result:</p>
     * <p>Received the message that the Tax Rate has been deleted.</p>
     *
     * @test
     */
    public function notUsedInRule()
    {
        //Data
        $taxRate = $this->loadDataSet('Tax', 'tax_rate_create_test_zip_no');
        $search = $this->loadDataSet('Tax', 'search_tax_rate', array('filter_tax_id' => $taxRate['tax_identifier']));
        //Steps
        $this->taxHelper()->createTaxItem($taxRate, 'rate');
        //Verifying
        $this->assertMessagePresent('success', 'success_saved_tax_rate');
        //Steps
        $this->taxHelper()->deleteTaxItem($search, 'rate');
        //Verifying
        $this->assertMessagePresent('success', 'success_deleted_tax_rate');
    }

    /**
     * <p>Delete a Tax Rate that used</p>
     * <p>Steps:</p>
     * <p>1. Create a new Tax Rate</p>
     * <p>2. Create a new Tax Rule that use Tax Rate from previous step</p>
     * <p>2. Open the Tax Rate</p>
     * <p>3. Delete the Tax Rate</p>
     * <p>Expected result:</p>
     * <p>Received the message that the Tax Rate could not be deleted.</p>
     *
     * @test
     */
    public function usedInRule()
    {
        //Data
        $rate = $this->loadDataSet('Tax', 'tax_rate_create_test_zip_no');
        $searchRate = $this->loadDataSet('Tax', 'search_tax_rate', array('filter_tax_id' => $rate['tax_identifier']));
        $rule = $this->loadDataSet('Tax', 'new_tax_rule_required', array('tax_rate' => $rate['tax_identifier']));
        $searchRule = $this->loadDataSet('Tax', 'search_tax_rule', array('filter_name' => $rule['name']));
        //Steps
        $this->taxHelper()->createTaxItem($rate, 'rate');
        //Verifying
        $this->assertMessagePresent('success', 'success_saved_tax_rate');
        //Steps
        $this->navigate('manage_tax_rule');
        $this->taxHelper()->createTaxItem($rule, 'rule');
        //Verifying
        $this->assertMessagePresent('success', 'success_saved_tax_rule');
        $this->_ruleToBeDeleted = $searchRule;
        //Steps
        $this->navigate('manage_tax_zones_and_rates');
        $this->taxHelper()->deleteTaxItem($searchRate, 'rate');
        //Verifying
        $this->assertMessagePresent('error', 'error_delete_tax_rate');
    }
}