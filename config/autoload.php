<?php

/**
 * Contao Open Source CMS
 *
 * Copyright (c) 2005-2014 Leo Feyer
 *
 * @package Tl_member_cell_edit
 * @link    https://contao.org
 * @license http://www.gnu.org/licenses/lgpl-3.0.html LGPL
 */


/**
 * Register the classes
 */
ClassLoader::addClasses(array
(
	'TlMemberCellEdit' => 'system/modules/tl_member_cell_edit/classes/TlMemberCellEdit.php',
));


/**
 * Register the templates
 */
TemplateLoader::addFiles(array
(
	'be_tl_member_row'  => 'system/modules/tl_member_cell_edit/templates',
	'be_tl_member_main' => 'system/modules/tl_member_cell_edit/templates',
));
