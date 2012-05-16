<?php if (!defined('TL_ROOT')) die('You can not access this file directly!');

/**
 * Contao Open Source CMS
 * Copyright (C) 2005-2010 Leo Feyer
 *
 * Formerly known as TYPOlight Open Source CMS.
 *
 * This program is free software: you can redistribute it and/or
 * modify it under the terms of the GNU Lesser General Public
 * License as published by the Free Software Foundation, either
 * version 3 of the License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU
 * Lesser General Public License for more details.
 *
 * You should have received a copy of the GNU Lesser General Public
 * License along with this program. If not, please visit the Free
 * Software Foundation website at <http://www.gnu.org/licenses/>.
 *
 * PHP version 5
 * @copyright  Isotope eCommerce Workgroup 2009-2012
 * @author     Christian de la Haye <service@delahaye.de>
 * @license    http://opensource.org/licenses/lgpl-3.0.html
 */


/**
 * Class IsotopeEuTax
 * 
 * Provide methods to handle EU taxes in Isotope.
 * @copyright  Isotope eCommerce Workgroup 2009-2012
 * @author     Christian de la Haye <service@delahaye.de>
 */
class IsotopeEuTax extends IsotopeFrontend
{

	/**
	 * Decide if an EU VAT is applied or not.
	 * @param object
	 * @param float
	 * @param array
	 * @return boolean
	 */
	public function applyVat($objRate, $fltPrice, $arrAddresses)
	{
		// only check for logged in members with net prices
		if(!$_SESSION['TL_USER_LOGGED_IN'] || !in_array($this->Isotope->Config->groupwithnetprices,$this->Isotope->Cart->User->groups))
			return true;

		if (is_array($objRate->address) && count($objRate->address) && $objRate->isvat && !$this->addressForcingToVat($arrAddresses))
		{
			return false;
		}

		// default
		return true;
	}


	/**
	 * Check if tax has to be applied or not
	 * @param array
	 * @return boolean
	 */
	public function addressForcingToVat($arrAddresses)
	{
		// check for each address, if the tax has to be payed
		foreach( $arrAddresses as $name => $arrAddress )
		{
			if( // 1 of the addresses is own country
				$arrAddress['country']==$this->Isotope->Config->country
				|| // 1 of the addresses is eu-country, but member is not in group without eu-tax or has no VAT-Id
					(
					in_array($arrAddress['country'], $this->Isotope->Config->eucountries)
					&& (!in_array($this->Isotope->Config->groupwithvatid,$this->Isotope->Cart->User->groups) || !$this->Isotope->Cart->User->isoeuvatid)
					)
				|| // 1 of the addresses is non-eu-country, but sale without VAT in non-eu is active, maybe only for one group
					(
					!in_array($arrAddress['country'], $this->Isotope->Config->eucountries)
					&& (
						($this->Isotope->Config->vatoutside && !$this->Isotope->Config->groupoutside)
						|| ($this->Isotope->Config->vatoutside && !in_array($this->Isotope->Config->groupoutside,$this->Isotope->Cart->User->groups))
						)
					)
				|| // 1 of the addresses is non-eu-country, but member is in group without eu-tax (wrong setting)
					(
					!in_array($arrAddress['country'], $this->Isotope->Config->eucountries)
					&& in_array($this->Isotope->Config->groupwithvatid,$this->Isotope->Cart->User->groups)
					)
				)
				return true;
		}

		// either member in group without eu-tax and eu, or non-eu-country for all addresses
		return false;
	}


	/**
	 * Inject a contao article in the default templates to provide in the EU required information.
	 * @param string
	 * @param string
	 * @return string
	 */
	public function injectNotes($strBuffer, $strTemplate)
	{
		// note in the main cart
		if(strpos($strBuffer,'isotopeEuTax::taxNote')===false && $strTemplate == 'iso_cart_full')
		{
			$strBuffer = str_replace('<div class="submit_container">',$this->isotopeEuTaxInsertTags('isotopeEuTax::taxNote').'<div class="submit_container">',$strBuffer);
		}

		// note in the checkout
		if(strpos($strBuffer,'isotopeEuTax::taxNote')===false && ($strTemplate == 'iso_checkout_order_review' || $strTemplate == 'mod_iso_orderdetails'))
		{
			$strBuffer = str_replace('</table>','</table>'.$this->isotopeEuTaxInsertTags('isotopeEuTax::taxNote'),$strBuffer);
		}

		// note in the checkout
		if(strpos($strBuffer,'isotopeEuTax::taxNote')===false && $strTemplate == 'iso_products_html')
		{
			$strBuffer .= $this->isotopeEuTaxInsertTags('isotopeEuTax::taxNote');
		}

		// note in the checkout
		if(strpos($strBuffer,'isotopeEuTax::taxNote')===false && $strTemplate == 'iso_products_text')
		{
			$strBuffer .= '

'.$this->isotopeEuTaxInsertTags('isotopeEuTax::taxNote::txt');
		}

		return $strBuffer;
	}


	/**
	 * Inject notes via insert tags
	 * @param string
	 * @return string
	 */
	public function isotopeEuTaxInsertTags($strTag)
	{
		$arrTag = trimsplit('::', $strTag);

		if($arrTag[0] == 'isotopeEuTax' && $_SESSION['TL_USER_LOGGED_IN'] && is_array($this->Isotope->Cart->User->groups) && in_array($this->Isotope->Config->groupwithnetprices,$this->Isotope->Cart->User->groups))
		{
			$arrAddresses = array('billing'=>$this->Isotope->Cart->billingAddress, 'shipping'=>$this->Isotope->Cart->shippingAddress);

			if(!$this->addressForcingToVat($arrAddresses))
			{

				switch($arrTag[1])
				{
					case 'taxNote':
						if($this->Isotope->Config->taxnotevatid && in_array($this->Isotope->Config->groupwithvatid,$this->Isotope->Cart->User->groups))
						{
							$return = $this->replaceInsertTags('{{insert_article::'.$this->Isotope->Config->taxnotevatid.'}}');
						}
						elseif($this->Isotope->Config->taxnoteoutside && (in_array($this->Isotope->Config->groupoutside,$this->Isotope->Cart->User->groups) || !$this->Isotope->Config->vatoutside))
						{
							$return = $this->replaceInsertTags('{{insert_article::'.$this->Isotope->Config->taxnoteoutside.'}}');
						}
						break;

				}

				// optional parameter txt for text only
				if($arrTag[2] == 'txt')
				{
					$return = trim(strip_tags($return));
				}

				return $return;
			}
		}

		return false;
	}


	/**
	 * Switch to net store configuration on login
	 * @param object
	 */
	public function switchConfig($objThis)
	{
		$objConfig = $this->Database->prepare("SELECT id FROM tl_iso_config WHERE (id!=? AND store_id=?)")
			->limit(1)
			->execute($this->Isotope->Config->id,$this->Isotope->Config->store_id);

		if(in_array($this->Isotope->Config->groupwithnetprices,$objThis->groups) && $objConfig->numRows)
		{
			$_SESSION['ISOTOPE']['config_id'] = $objConfig->id;
			$this->redirect(preg_replace(('@[?|&]config='.$objConfig->id.'@'), '', $this->Environment->request));
		}

	}

}