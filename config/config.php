<?php

/**
 * FrontendUserImport
 *
 * Copyright (C) 2008-2013 Christian Barkowsky
 *
 * @package FrontendUserImport
 * @author  Christian Barkowsky <http://www.christianbarkowsky.de>
 * @link    http://www.christianbarkowsky.de
 * @license LGPL
 */


$GLOBALS['BE_MOD']['accounts']['tl_member_cell_edit'] = array(
       //'icon' => 'system/modules/tl_member_cell_edit/html/styles.css',
       'callback' => 'TlMemberCellEdit'
);
if ($_GET['do'] == 'tl_member_cell_edit')
{
       $GLOBALS['TL_CSS'][] = 'system/modules/tl_member_cell_edit/assets/css/tl_member_cell_edit.css';
       $GLOBALS['TL_JAVASCRIPT'][] = 'system/modules/tl_member_cell_edit/assets/js/tl_member_cell_edit.js';
}