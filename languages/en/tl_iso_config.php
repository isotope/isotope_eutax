<?php if (!defined('TL_ROOT')) die('You cannot access this file directly!');

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


/**
 * Fields
 */

$GLOBALS['TL_LANG']['tl_iso_config']['pricenote']   		= array('Article with price info', 'Select the Contao article that provides the additional price info.');
$GLOBALS['TL_LANG']['tl_iso_config']['groupwithnetprices'] 	= array('Member group with net prices', 'Select the member group that sees net prices without VAT.');
$GLOBALS['TL_LANG']['tl_iso_config']['groupwithvatid']   	= array('Member group with VAT-Id', 'Select the member group granting that the member has a verfied VAT-Id.');
$GLOBALS['TL_LANG']['tl_iso_config']['vatoutside']   		= array('Calculate VAT outside the EU', 'Check, if VAT outside the EU shall not be dropped automatically.');
$GLOBALS['TL_LANG']['tl_iso_config']['groupoutside']   		= array('Member group without VAT', 'Select the member group for that shall be calculated without VAT outside the EU.');
$GLOBALS['TL_LANG']['tl_iso_config']['eucountries']   		= array('EU countries', 'Specify the EU countries.');


/**
 * Legend
 */

$GLOBALS['TL_LANG']['tl_iso_config']['eutax_legend']   		= 'EU Tax settings';
?>