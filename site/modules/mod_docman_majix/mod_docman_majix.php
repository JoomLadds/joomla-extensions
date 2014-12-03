<?php
/**
 * @version		$Id: mod_docman_majix.php
 * @category	DOCman
 * @package		DOCman Majix Module
 * @copyright	Copyright (C) 2014 JoomLadds @ River Media. All rights reserved.
 * @license		GNU GPLv2 <http://www.gnu.org/licenses/old-licenses/gpl-2.0.html>
 * @link     	http://joomladds.com
 */

defined('_JEXEC') or die;

jimport('joomla.filesystem.file');
jimport('joomla.filesystem.folder');

$docman_path = JPATH_ADMINISTRATOR .'/components/com_docman';

// does docman exist?
if(!JFolder::exists($docman_path)){
	JError::raiseWarning( 404, JTEXT::_("MOD_DOCMAN_MAJIX_INSTALL_ERROR_1"));
	return false;
}
    
$docman_class = $docman_path . '/version.php';
if(!JFile::exists($docman_class))
{
	$docman_version = 'legacy';
	return JError::raiseWarning(404, JTEXT::_('MOD_DOCMAN_MAJIX_INSTALL_ERROR_5'));
}
else
{
$db = JFactory::getDbo();
$query = $db->getQuery(true);
$query
    ->select($db->quoteName(array('manifest_cache')))
    ->from($db->quoteName('#__extensions'))
    ->where($db->quoteName('name') . ' = '. $db->quote('com_docman'));

$db->setQuery($query);
$results = $db->loadAssoc();

$docman_manifest = json_decode($results['manifest_cache']);
	include_once($docman_class);
	$docman_version = $docman_manifest->version;
}

if($docman_version=='legacy')
{
	$docman_class = $docman_path . '/docman.class.php';
	if(!JFile::exists($docman_class))
	{
		return JError::raiseWarning(404, JTEXT::_('MOD_DOCMAN_MAJIX_INSTALL_ERROR_2'));
	}
	else
	{
		include_once($docman_class);

		global $_DOCMAN, $_DMUSER;
		if(!is_object($_DOCMAN))
		{
			$_DOCMAN = new dmMainFrame();
    		$_DMUSER = $_DOCMAN->getUser();
		}

		$_DOCMAN->setType(_DM_TYPE_MODULE);
		$_DOCMAN->loadLanguage('modules');
		
		require_once($_DOCMAN->getPath('classes', 'utils'));
		require_once($_DOCMAN->getPath('classes', 'file'));
		require_once($_DOCMAN->getPath('classes', 'model'));
	}
}

require JModuleHelper::getLayoutPath('mod_docman_majix', $params->get('layout','default'));
