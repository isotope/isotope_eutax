--
-- Table `tl_iso_setup`
--

CREATE TABLE `tl_iso_config` (
  `taxnotevatid` int(10) unsigned NOT NULL default '0',
  `taxnoteoutside` int(10) unsigned NOT NULL default '0',
  `groupwithnetprices` int(10) unsigned NOT NULL default '0',
  `groupwithvatid` int(10) unsigned NOT NULL default '0',
  `groupoutside` int(10) unsigned NOT NULL default '0',
  `vatoutside` char(1) NOT NULL default '',
  `eucountries` blob NULL,
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- Table `tl_iso_tax_rate`
--

CREATE TABLE `tl_iso_tax_rate` (
  `isvat` char(1) NOT NULL default '',
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- Table `tl_member`
--

CREATE TABLE `tl_member` (
  `isoeuvatid` varchar(255) NOT NULL default '',
) ENGINE=MyISAM DEFAULT CHARSET=utf8;