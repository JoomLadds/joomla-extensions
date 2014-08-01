<?php
/*------------------------------------------------------------------------
# mod_docman_majix
# ------------------------------------------------------------------------
# author    JoomLadds / River Media
# copyright Copyright (C) 2014 JoomLadds All Rights Reserved.
# @license - http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
# Websites: http://www.joomladds.com
# Technical Support:  Forum - http://www.joomladds.com/forum.html
-------------------------------------------------------------------------*/

defined('_JEXEC') or die('Restricted access');

// you can define the following parameters at administration:

// limits = number of downloads to display (default = 3);
// show_icon = displays a generic icon near the name of the document, using the theme defined (default = 1) No=0; Yes=1
// show_counter = displays the number of downloads (default = 1) No=0; Yes=1

$html = '';
$limits  		= $params->get( 'limits', 3 );
$show_icon 		= $params->get( 'show_icon', 1 );
$show_counter	= $params->get( 'show_counter', 1 );
$menuid			= $params->get( 'relatedMenu' );
$doc_ids		= $params->get( 'doc_ids' );
$order_by		= $params->get( 'order_by', 'd.created_on' );
$direction		= $params->get( 'direction', 'asc' );

if(version_compare($docman_version,'1.8.0','>'))
{
	$db=JFactory::getDbo();
	$query=$db->getQuery(true);
	$query->select(array('d.title', 'd.docman_document_id AS id','d.slug','dc.slug AS cat_slug','d.params'));
	$query->join('LEFT', '#__docman_categories AS dc ON d.docman_category_id=dc.docman_category_id');
	$query->from('#__docman_documents AS d');
	$query->where($db->quoteName('d.enabled') . ' = 1 ');
	$query->where('(d.docman_document_id' . ' IN ( '.$doc_ids.' ))');
	$query->order('d.'.$order_by .' ' .$direction);
	$db->setQuery($query);

$echoQuery = nl2br(str_replace('#__','tmqh_',$query));
#JFactory::getApplication()->enqueueMessage($echoQuery, 'notice');
#echo $echoQuery;
#die;

	$results = $db->loadAssocList();

	if (count($results))
	{
		$html .='<ul class="dm_mod_mostdown">';
		foreach ($results as $row)
		{
			if($row['params']!='')
			{
				$docParams = json_decode($row['params']);
			}

// index.php?view=document&alias=28-special-promotions-booking-fill-in-form-1&category_slug=special-promotion-downloads-1&layout=default&option=com_docman&Itemid=360

			$url = JRoute::_( 'index.php?option=com_docman&amp;view=document&amp;alias='.$row['id'].'-'.$row['slug'] . '&amp;category_slug=' . $row['cat_slug'] . '&amp;layout=default&amp;Itemid='.$menuid );

			$html.='<li>';
			$html .= '<a href="'.$url.'">';
				
			if($show_icon && isset($docParams->icon))
			{
				$html.='<img style="padding-right:4px;" class="icon" width="16px" height="16px" align="left" src="media/com_docman/images/icons/'.$docParams->icon.'" />';
			}
        		
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
	$menuid = $_DOCMAN->getMenuId();

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