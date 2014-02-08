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
class TlMemberCellEdit extends BackendModule
{

       //str Template
       protected $strTemplate = 'be_tl_member_main';


       /**
        * Import
        */
       public function compile()
       {

              // load language file
              \System::loadLanguageFile('tl_member');

              if (\Input::get('action') == 'update')
              {
                     $this->update();
                     exit();
              }
              if (\Input::get('action') == 'updateGroupmembership')
              {
                     $this->updateGroupmembership();
                     exit();
              }


              $arrAvailableGroups = array();
              $objGroups = \Database::getInstance()->execute("SELECT * FROM tl_member_group");
              while ($objGroups->next())
              {
                     $arrAvailableGroups[$objGroups->id] = array(
                            'id' => $objGroups->id,
                            'name' => $objGroups->name
                     );
              }

              $arrAvailableCustomers = array();
              $objCustomers = \Database::getInstance()->execute("SELECT * FROM tl_customer_group");
              while ($objCustomers->next())
              {
                     $arrAvailableCustomers[$objCustomers->id] = array(
                            'id' => $objCustomers->id,
                            'name' => $objCustomers->name
                     );
              }

              $objDb = \Database::getInstance()->execute("SELECT * FROM tl_member WHERE city='Entenhausen' ORDER BY username");

              //$objDb = \Database::getInstance()->execute("SELECT * FROM tl_member ORDER BY username");

              $html = '';
              while ($objDb->next())
              {
                     $objTemplate = new \BackendTemplate('be_tl_member_row');
                     $objTemplate->id = $objDb->id;
                     $objTemplate->username = $objDb->username;
                     $objTemplate->lastname = $objDb->lastname;
                     $objTemplate->firstname = $objDb->firstname;
                     $objTemplate->lastname = $objDb->lastname;

                     //member_groups
                     $arrGroups = strlen($objDb->groups != '') ? unserialize($objDb->groups) : array();
                     $arrGroupMembership = $arrAvailableGroups;
                     foreach ($arrGroupMembership as $k => $v)
                     {
                            if (in_array($v['id'], $arrGroups))
                            {
                                   $arrGroupMembership[$k]['checked'] = ' checked';
                            }
                            else
                            {
                                   $arrGroupMembership[$k]['checked'] = '';
                            }
                     }
                     $objTemplate->groups = $arrGroupMembership;

                     //customer_groups
                     $arrCustomers = strlen($objDb->customers != '') ? unserialize($objDb->customers) : array();
                     $arrCustomerMembership = $arrAvailableCustomers;
                     foreach ($arrCustomerMembership as $k => $v)
                     {
                            if (in_array($v['id'], $arrCustomers))
                            {
                                   $arrCustomerMembership[$k]['checked'] = ' checked';
                            }
                            else
                            {
                                   $arrCustomerMembership[$k]['checked'] = '';
                            }
                     }
                     $objTemplate->customers = $arrCustomerMembership;
                     $html .= $objTemplate->parse();

              }


              $this->Template = new BackendTemplate($this->strTemplate);
              $this->Template->groups = $arrAvailableGroups;
              $this->Template->customers = $arrAvailableCustomers;
              $this->Template->rows = $html;
       }


       /**
        * update data record
        */
       private function update()
       {

              $json = array();
              $doNotSave = false;

              $strTable = \Input::post('FORM_SUBMIT');
              $field = \Input::post('field');
              $value = \Input::post('value');
              $id = \Input::post('id');
              $set = array();


              // load language file
              \System::loadLanguageFile($strTable);

              // load dca
              $this->loadDataContainer($strTable);

              // get the DCA of the current field
              $arrDCA =  & $GLOBALS['TL_DCA'][$strTable]['fields'][$field];

              // Prepare FormWidget object !set inputType to "text" if there is no definition
              $inputType = $arrDCA['inputType'] != '' ? $arrDCA['inputType'] : 'text';

              // Map checkboxWizards to regular checkbox widgets
              if ($inputType == 'checkboxWizard')
              {
                     $inputType = 'checkbox';
              }
              $strClass = & $GLOBALS['TL_FFL'][$inputType];

              // Continue if the class does not exist
              // Use form widgets for input validation
              if (class_exists($strClass))
              {
                     $objWidget = new $strClass($strClass::getAttributesFromDca($arrDCA, $field, $value, '', '', $this));

                     $objWidget->storeValues = false;

                     if ($field == 'password')
                     {
                            \Input::setPost('password_confirm', $value);
                     }


                     // validate input
                     $objWidget->validate();
                     $value = $objWidget->value;

                     $rgxp = $arrDCA['eval']['rgxp'];

                     // Convert date formats into timestamps (check the eval setting first -> #3063)
                     if (($rgxp == 'date' || $rgxp == 'time' || $rgxp == 'datim') && $value != '')
                     {
                            try
                            {
                                   $strTimeFormat = $GLOBALS['TL_CONFIG'][$rgxp . 'Format'];
                                   $objDate = new \Date($value, $strTimeFormat);
                                   $value = $objDate->tstamp;
                            }
                            catch (\OutOfBoundsException $e)
                            {
                                   $objWidget->addError(sprintf($GLOBALS['TL_LANG']['ERR']['invalidDate'], $value));
                            }
                     }

                     // Make sure that unique fields are unique
                     if ($arrDCA['eval']['unique'] && $value != '' && !$this->Database->isUniqueValue($strTable, $field, $value, null))
                     {
                            $query = 'SELECT * FROM ' . $strTable . ' WHERE ' . $field . '=? AND id !=?';
                            $objDb = $this->Database->prepare($query)->execute($value, $id);
                            if ($objDb->numRows)
                            {
                                   $objWidget->addError(sprintf($GLOBALS['TL_LANG']['ERR']['unique'], $arrDCA['label'][0] ? : $field));
                            }
                     }

                     // Do not save the field if there are errors
                     if ($objWidget->hasErrors())
                     {
                            $doNotSave = true;
                            $errorMsg = sprintf('"%s" => <span class="errMsg">%s</span>', $value, $objWidget->getErrorsAsString());
                            $json['status'] = 'error';
                            $json['errorMsg'] = $errorMsg;
                     }
                     else
                     {
                            $json['status'] = 'success';
                            // Set the correct empty value
                            if ($value === '')
                            {
                                   $value = $objWidget->getEmptyValue();
                            }
                     }
              }

              if (!$doNotSave)
              {
                     $set[$field] = $value;
                     $query = 'UPDATE ' . $strTable . ' %s WHERE id=?';
                     $this->Database->prepare($query)->set($set)->execute($id);
              }

              // response
              $json['value'] = $value;
              echo json_encode($json);
              exit();
       }


       /**
        * update data record
        */
       private function updateGroupmembership()
       {

              $json = array();

              $strTable = \Input::post('FORM_SUBMIT');
              $memberId = \Input::post('memberId');
              $groupId = \Input::post('groupId');
              $checked = \Input::post('checked');
              $field = \Input::post('field');


              $objMember = $this->Database->prepare('SELECT * FROM ' . $strTable . ' WHERE id=?')->execute($memberId);
              if ($objMember->numRows)
              {

                     $arrGroups = $objMember->$field != '' ? unserialize($objMember->$field) : array();
                     if ($checked == 'true')
                     {

                            $arrGroups[] = $groupId;
                            $arrGroups = array_unique($arrGroups);
                            $json['value'] = 'checked';
                     }
                     if ($checked == 'false')
                     {
                            if (in_array($groupId, $arrGroups))
                            {
                                   $index = array_search($groupId, $arrGroups);
                                   unset($arrGroups[$index]);
                                   $arrGroups = array_values($arrGroups);
                            }
                            $json['value'] = '';

                     }

                     $set = array();
                     $set[$field] = serialize($arrGroups);
                     $query = 'UPDATE ' . $strTable . ' %s WHERE id=?';
                     $this->Database->prepare($query)->set($set)->execute($memberId);
              }

              // response
              $json['status'] = 'success';
              echo json_encode($json);
              exit();
       }

}

?>