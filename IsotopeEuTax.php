<?php if (!defined('TL_ROOT')) die('You can not access this file directly!');

/**
 * Contao Open Source CMS
 * Copyright (C) 2005-2011 Leo Feyer
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
 * @copyright  de la Haye Kommunikationsdesign 2011
 * @author     Christian de la Haye <http://www.delahaye.de>
 * @package    Isotope EU tax handling
 * @license    LGPL
 * @filesource
 */


class IsotopeEuTax extends Frontend
{

	/**
	 * Decide, if a member is granted to pay withot any EU VAT.
	 * This is only suitable, if the member is manually set so after providing a VAT-Id within the EU
	 * or if the member is in a non-EU country AND all addresses fit those needs. Additionally VAT-free
	 * sales to non-EU countries can be limited to a certain group.
	 */

	public function noVat($objRate, $fltPrice, $arrAddresses)
	{
		global $objIndex;
		$this->import('Isotope');

		// only check for logged in members with net prices
		if(!$_SESSION['TL_USER_LOGGED_IN'] || !in_array($this->Isotope->Config->groupwithnetprices,$objIndex->User->groups))
			return true;

		if (is_array($objRate->address) && count($objRate->address) && $objRate->isvat)
		{
			// check for each address, if the tax has to be payed
			foreach( $arrAddresses as $name => $arrAddress )
			{
				if( // 1 of the addresses is own country
					$arrAddress['country']==$this->Isotope->Config->country
					|| // 1 of the addresses is eu-country, but member is not in group without eu-tax or has no VAT-Id
						(
						in_array($arrAddress['country'], $this->Isotope->Config->eucountries)
						&& (!in_array($this->Isotope->Config->groupwithvatid,$objIndex->User->groups) || !$objIndex->User->isoeuvatid)
						)
					|| // 1 of the addresses is non-eu-country, but sale without VAT in non-eu is active, maybe only for one group
						(
						!in_array($arrAddress['country'], $this->Isotope->Config->eucountries)
						&& (
							($this->Isotope->Config->vatoutside && !$this->Isotope->Config->groupoutside)
							|| ($this->Isotope->Config->vatoutside && !in_array($this->Isotope->Config->groupoutside,$objIndex->User->groups))
							)
						)
					|| // 1 of the addresses is non-eu-country, but member is in group without eu-tax (wrong setting)
						(
						!in_array($arrAddress['country'], $this->Isotope->Config->eucountries)
						&& in_array($this->Isotope->Config->groupwithvatid,$objIndex->User->groups)
						)
					)
					return true;
			}

			// either member in group without eu-tax and eu, or non-eu-country for all addresses
			return false;
		}

		// default
		return true;
	}


	/**
	 * Inject a contao article in the default templates after the price field to provide in the EU required information.
	 */

	public function injectPricenotes($strBuffer, $strTemplate)
	{
		$this->import('Isotope');

		if(
			// article is defined
			$this->Isotope->Config->pricenote
			// default templates are used
			&& ($strTemplate == 'iso_list_default' || $strTemplate == 'iso_list_variants' || $strTemplate == 'iso_reader_default')
			)
		{
			$strBuffer = preg_replace('#\<div class="price">(.*)\</div>(.*)#Uis', '<div class="price">\1</div>{{insert_article::'.$this->Isotope->Config->pricenote.'}}\2', $strBuffer);
		}
		return $strBuffer;
	}

}


?>