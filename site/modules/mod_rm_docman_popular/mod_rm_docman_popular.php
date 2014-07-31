<?php
/**
 * @version		$Id: mod_docman_mostdown.php 955 2009-10-14 20:38:38Z mathias $
 * @category	DOCman
 * @package		DOCman15
 * @copyright	Copyright (C) 2003 - 2009 Johan Janssens and Mathias Verraes. All rights reserved.
 * @license		GNU GPLv2 <http://www.gnu.org/licenses/old-licenses/gpl-2.0.html>
 * @link     	http://www.joomladocman.org
 */
defined('_JEXEC') or die('Restricted access');

jimport('joomla.filesystem.file');

// you can define the following parameters at administration:

// limits = number of downloads to display (default = 3);
// show_icon = displays a generic icon near the name of the document, using the theme defined (default = 1) No=0; Yes=1
// show_counter = displays the number of downloads (default = 1) No=0; Yes=1

$limits  		= $params->get( 'limits', 3 );
$show_icon 		= $params->get( 'show_icon', 1 );
$show_counter	= $params->get( 'show_counter', 1 );
$docman_version	= $params->get( 'docman_version', '2' );
$menuid			= $params->get( 'relatedMenu' );
$doc_ids		= $params->get( 'doc_ids' );
$order_by		= $params->get( 'order_by', 'd.created_on' );
$direction		= $params->get( 'direction', 'asc' );

if($docman_version=='2')
{
	$db=JFactory::getDbo();
	$query=$db->getQuery(true);
	$query->select(array('d.title', 'd.docman_document_id AS id','d.slug','dc.slug AS cat_slug'));
	$query->join('LEFT', '#__docman_categories AS dc ON d.docman_category_id=dc.docman_category_id');
	$query->from('#__docman_documents AS d');
	$query->where($db->quoteName('d.enabled') . ' = 1 ');
	$query->where('(d.docman_document_id' . ' IN ( '.$doc_ids.' ))');
	$query->order($order_by .' ' .$direction);
	$db->setQuery($query);

$echoQuery = nl2br(str_replace('#__','tmqh_',$query));
JFactory::getApplication()->enqueueMessage($echoQuery, 'notice');
#echo $echoQuery;
#die;

	$results = $db->loadAssocList();

	if (count($results))
	{
			$html .='<ul class="dm_mod_mostdown">';
			foreach ($results as $row)
			{
// index.php?view=document&alias=28-special-promotions-booking-fill-in-form-1&category_slug=special-promotion-downloads-1&layout=default&option=com_docman&Itemid=360

				$url = JRoute::_( 'index.php?option=com_docman&amp;view=document&amp;alias='.$row['id'].'-'.$row['slug'] . '&amp;category_slug=' . $row['cat_slug'] . '&amp;layout=default&amp;Itemid='.$menuid );

				$html .= '<li><a href="'.$url.'">';
        		
        		$html .= $row['title'];
				
    			$html .= '</a></li>';
    		}
    		$html .= '</ul>';
		}
		else
		{
			$html .= "<br />".'No records Found';
		}

		echo $html;

}
else
{
	$docman_class = JPATH_ADMINISTRATOR .'components/com_docman/docman.class.php';
	if(!JFile::exists($docman_class))
	{
		return JError::raiseWarning(404, 'DOCman 1.6 is not installed. Please disable the \'RM DOCman Popular\' Module');
	}
	else
	{
		include_once($docman_class);

		//DOCman core interaction API
		global $_DOCMAN, $_DMUSER;
		if(!is_object($_DOCMAN)) {
			$_DOCMAN = new dmMainFrame();
			$_DMUSER = $_DOCMAN->getUser();
		}

		$_DOCMAN->setType(_DM_TYPE_MODULE);
		$_DOCMAN->loadLanguage('modules');

		require_once($_DOCMAN->getPath('classes', 'utils'));
		require_once($_DOCMAN->getPath('classes', 'file'));
		require_once($_DOCMAN->getPath('classes', 'model'));

		// get the parameters

		$menuid = $_DOCMAN->getMenuId();

		$html = '';

		$rows = DOCMAN_Docs::getDocsByUserAccess(0, 'hits', 'DESC', $limits);

		if (count($rows))
		{
			$html .='<ul class="dm_mod_mostdown">';
			foreach ($rows as $row)
			{
				$doc = new DOCMAN_Document($row->id);

				$url = JRoute::_( "index.php?option=com_docman&task=cat_view&Itemid=$menuid&gid={$row->catid}" );
				$html .= '<li><a href="'.$url.'">';

				if ($show_icon)
				{
        			$html .= '<img border="0" src="'.$doc->getPath('icon', 1, '16x16').'" alt="file icon" />';
				}
        		
        		$html .= $doc->getData('dmname');

    			if ($show_counter)
    			{
    				$html .= ' ('.$doc->getData('dmcounter').')';
				}
				
    			$html .= '</a></li>';
    		}
    		$html .= '</ul>';
		}
		else
		{
			$html .= "<br />"._DML_MOD_NODOCUMENTS;
		}

		echo $html;
	}
}