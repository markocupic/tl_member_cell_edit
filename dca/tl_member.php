<?php

/**
 * Contao Open Source CMS
 *
 * Copyright (c) 2005-2014 Leo Feyer
 *
 * @package Core
 * @link    https://contao.org
 * @license http://www.gnu.org/licenses/lgpl-3.0.html LGPL
 */


/**
 * Table tl_member
 */
$GLOBALS['TL_DCA']['tl_member']['fields']['customers'] = array(
       'label' => &$GLOBALS['TL_LANG']['tl_member']['customers'],
       'exclude' => true,
       'filter' => true,
       'inputType' => 'checkboxWizard',
       'foreignKey' => 'tl_customer_group.name',
       'eval' => array(
              'multiple' => true,
              'feEditable' => true,
              'feGroup' => 'login'
       ),
       'sql' => "blob NULL",
       'relation' => array(
              'type' => 'belongsToMany',
              'load' => 'lazy'
       )
);
