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


/**
 * Modify palettes
 */

$GLOBALS['TL_DCA']['tl_iso_config']['palettes']['__selector__'][] = 'vatoutside';
$GLOBALS['TL_DCA']['tl_iso_config']['palettes']['default'] .= ';{eutax_legend:hide},pricenote,groupwithnetprices,groupwithvatid,vatoutside,eucountries';


/**
 * Add subpalette
 */

$GLOBALS['TL_DCA']['tl_iso_config']['subpalettes']['vatoutside'] = 'groupoutside';


/**
 * Add fields
 */

$GLOBALS['TL_DCA']['tl_iso_config']['fields']['pricenote'] = array
(
	'label'                   => &$GLOBALS['TL_LANG']['tl_iso_config']['pricenote'],
	'exclude'                 => true,
	'inputType'               => 'select',
	'options_callback'        => array('tl_iso_eutax', 'getArticleAlias'),
	'eval'                    => array('mandatory'=>false)
);

$GLOBALS['TL_DCA']['tl_iso_config']['fields']['groupwithnetprices'] = array
(
	'label'                   => &$GLOBALS['TL_LANG']['tl_iso_config']['groupwithnetprices'],
	'inputType'               => 'select',
	'foreignKey'			  => 'tl_member_group.name',
	'eval'                    => array('mandatory'=>false, 'includeBlankOption'=>true, 'tl_class'=>'w50')
);

$GLOBALS['TL_DCA']['tl_iso_config']['fields']['groupwithvatid'] = array
(
	'label'                   => &$GLOBALS['TL_LANG']['tl_iso_config']['groupwithvatid'],
	'inputType'               => 'select',
	'foreignKey'			  => 'tl_member_group.name',
	'eval'                    => array('mandatory'=>false, 'includeBlankOption'=>true, 'tl_class'=>'w50')
);

$GLOBALS['TL_DCA']['tl_iso_config']['fields']['vatoutside'] = array
(
	'label'                   => &$GLOBALS['TL_LANG']['tl_iso_config']['vatoutside'],
	'inputType'               => 'checkbox',
	'eval'                    => array('default'=>true, 'submitOnChange'=>true)
);

$GLOBALS['TL_DCA']['tl_iso_config']['fields']['groupoutside'] = array
(
	'label'                   => &$GLOBALS['TL_LANG']['tl_iso_config']['groupoutside'],
	'inputType'               => 'select',
	'foreignKey'			  => 'tl_member_group.name',
	'eval'                    => array('mandatory'=>false, 'includeBlankOption'=>true)
);

$GLOBALS['TL_DCA']['tl_iso_config']['fields']['eucountries'] = array
(
	'label'                   => &$GLOBALS['TL_LANG']['tl_iso_config']['eucountries'],
	'inputType'               => 'select',
	'options'				  => $this->getCountries(),
	'default'				  => array('be','bg','dk','de','ee','fi','fr','gr','gb','ie','it','lv','lt','lu','mt','nl','at','pl','pt','ro','se','sk','si','es','cz','hu','cy'),
	'eval'                    => array('mandatory'=>false, 'multiple'=>true)
);


/**
 * Additional methods
 */

class tl_iso_eutax extends Backend
{

	/**
	 * Import the back end user object
	 */
	public function __construct()
	{
		parent::__construct();
		$this->import('BackendUser', 'User');
	}

	/**
	 * Get all articles and return them as array (article alias)
	 * @param object
	 * @return array
	 */
	public function getArticleAlias(DataContainer $dc)
	{
		$arrPids = array();
		$arrAlias = array();

		if (!$this->User->isAdmin)
		{
			foreach ($this->User->pagemounts as $id)
			{
				$arrPids[] = $id;
				$arrPids = array_merge($arrPids, $this->getChildRecords($id, 'tl_page'));
			}

			if (empty($arrPids))
			{
				return $arrAlias;
			}

			$objAlias = $this->Database->prepare("SELECT a.id, a.pid, a.title, a.inColumn, p.title AS parent FROM tl_article a LEFT JOIN tl_page p ON p.id=a.pid WHERE a.pid IN(". implode(',', array_map('intval', array_unique($arrPids))) .") AND a.id!=(SELECT pid FROM tl_content WHERE id=?) ORDER BY parent, a.sorting")
									   ->execute($dc->id);
		}
		else
		{
			$objAlias = $this->Database->prepare("SELECT a.id, a.pid, a.title, a.inColumn, p.title AS parent FROM tl_article a LEFT JOIN tl_page p ON p.id=a.pid WHERE a.id!=(SELECT pid FROM tl_content WHERE id=?) ORDER BY parent, a.sorting")
									   ->execute($dc->id);
		}

		if ($objAlias->numRows)
		{
			$this->loadLanguageFile('tl_article');

			while ($objAlias->next())
			{
				$key = $objAlias->parent . ' (ID ' . $objAlias->pid . ')';
				$arrAlias[$key][$objAlias->id] = $objAlias->title . ' (' . (strlen($GLOBALS['TL_LANG']['tl_article'][$objAlias->inColumn]) ? $GLOBALS['TL_LANG']['tl_article'][$objAlias->inColumn] : $objAlias->inColumn) . ', ID ' . $objAlias->id . ')';
			}
		}

		return $arrAlias;
	}

}
?>