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

              if (\Input::get('action') == 'update')
              {
                     $this->update();
                     exit();
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
                     $html .= $objTemplate->parse();

              }
              $this->Template = new BackendTemplate($this->strTemplate);
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

}

?>