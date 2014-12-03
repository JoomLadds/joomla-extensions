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

// no direct access
defined( '_JEXEC' ) or die( 'Restricted access' );

$menuid 		= $params->get( 'relatedMenu' );
$order_by		= $params->get( 'order_by', 'dc.created_on' );
$direction		= $params->get( 'direction', 'asc' );
$show_icon		= $params->get( 'show_icon', 0 );

$html = '';

if(version_compare($docman_version,'1.8.0','>'))
{

$db=JFactory::getDbo();
$query=$db->getQuery(true);
$query->select(array('dc.title', 'dc.docman_category_id AS id','dc.slug','dcr.ancestor_id','dcr.descendant_id','dcr.level','dc.image'));
$query->join('LEFT', '#__docman_category_relations AS dcr ON dc.docman_category_id=dcr.ancestor_id');
$query->from('#__docman_categories AS dc');
$query->where($db->quoteName('dc.enabled') . ' = 1 ');
$query->order('dc.'.$order_by .' ' .$direction);
$db->setQuery($query);

$echoQuery = nl2br(str_replace('#__','tmqh_',$query));
#JFactory::getApplication()->enqueueMessage($echoQuery, 'notice');
#echo $echoQuery;
#die;

$results = $db->loadAssocList();

$testResults = array();
$newResults = array();

#echo '<pre>';
#print_r($results);

if (count($results))
{
	$i=0;
    foreach ($results as $process)
    {
    	if($process['ancestor_id']!=$process['descendant_id'])
    	{
    		$testResults[] = $process['descendant_id'];
#    		$newResults[$i]['match'] = 1;
    	}
    	else
    	{
    		$newResults[] = $process;
#    		$newResults[$i]['match'] = 0;
    	}
    	$i++;
    }
/*
echo '<pre>';
print_r($testResults);
print_r($newResults);
die;
*/   
    $html .='<ul class="menu'.$params->get( 'moduleclass_sfx' ).'">';
    foreach ($newResults as $row)
    {
		if(!in_array($row['id'],$testResults))
		{
		
			if($row['image']!='')
			{
				print_r($row['image']);
			}
// media/com_docman/images/icons/pdf.png
// index.php?view=list&slug=training-1&option=com_docman&Itemid=360
       		$url = JRoute::_( 'index.php?option=com_docman&amp;view=list&amp;slug='.$row['slug'] .'&amp;Itemid=' . $menuid );
       		$html.='<li class="item-'.$row['id'].'"><a href="'.$url.'">';

/*			if($show_icon)
			{
				$html.='<img src="media/com_docman/icons/pdf.png" />&nbsp;';
			}
*/
       		$html .= $row['title'];

			$html .= '</a></li>';
		}    	
    }
    $html .= '</ul>';
}
#$html .= '</div>';
echo $html;

}
else
{
	$menuid = $_DOCMAN->getMenuId();

	$html = '<div class="mod_docman_catdown'.$params->get( 'moduleclass_sfx' ).'">';

	$rows = DOCMAN_Docs::getChildsByUserAccess(0,'ordering ASC',$_DMUSER->id);

	if (count($rows))
	{
    	$html .='<ul class="mod_docman_catdown'.$params->get( 'moduleclass_sfx' ).'">';
    	foreach ($rows as $row)
    	{
     		$doc = new DOCMAN_Document($row->id);

       		$url = JRoute::_( "index.php?option=com_docman&amp;task=cat_view&amp;Itemid=$menuid&amp;gid=".$doc->getData('catid')."&amp;orderby=dmdate_published&amp;ascdesc=DESC" );
       		$html.='<li><a href="'.$url.'">';

        	if ($show_icon)
        	{
        		$html .= '<img src="'.$doc->getPath('icon', 1, '16x16').'" alt="file icon" border="0" />';
			}
			
       		$html .= $doc->getData('dmname');

        	$html .= '</a></li>';
    	}
    	
    	$html .= '</ul>';
	}
	else
	{
		$html .= '<br />'._DML_MOD_NODOCUMENTS;
	}
	
	$html .= '</div>';
	echo $html;
}