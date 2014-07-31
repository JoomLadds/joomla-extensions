<?php
/**
 * @version		$Id: mod_docman_catdown.php 1449 2011-05-25 18:58:32Z ercan $
 * @category	DOCman
 * @package		DOCman15
 * @copyright	Copyright (C) 2003 - 2009 Johan Janssens and Mathias Verraes. All rights reserved.
 * @license		GNU GPLv2 <http://www.gnu.org/licenses/old-licenses/gpl-2.0.html>
 * @link     	http://www.joomladocman.org
 */
defined('_JEXEC') or die('Restricted access');

$html = '';

#$html = '<div class="moduletable'.$params->get( 'moduleclass_sfx' ).'">';

$db=JFactory::getDbo();
$query=$db->getQuery(true);
/*
$db->setQuery( "SELECT * FROM #__docman_categories" .
					"\n WHERE enabled = 'com_docman'".
					"\n ORDER BY ordering "
                    );
*/
$query->select(array('dc.title', 'dc.docman_category_id AS id','dc.slug','dcr.ancestor_id','dcr.descendant_id','dcr.level'));
$query->join('LEFT', '#__docman_category_relations AS dcr ON dc.docman_category_id=dcr.ancestor_id');

$query->from('#__docman_categories AS dc');



$query->where($db->quoteName('dc.enabled') . ' = 1 ');
#$query->where($db->quoteName('dcr.level') . ' = 0 ');
$query->order('dc.title ASC');
$db->setQuery($query);

$echoQuery = nl2br(str_replace('#__','tmqh_',$query));
JFactory::getApplication()->enqueueMessage($echoQuery, 'notice');
#echo $echoQuery;
#die;

$results = $db->loadAssocList();

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

#print_r($testResults);
#print_r($newResults);
#die;    
    $html .='<ul class="menu'.$params->get( 'moduleclass_sfx' ).'">';
    foreach ($newResults as $row)
    {
		if(!in_array($row['id'],$testResults))
		{
		

// index.php?view=list&slug=training-1&option=com_docman&Itemid=360
       	$url = JRoute::_( 'index.php?option=com_docman&amp;view=list&amp;slug='.$row['slug']);//.'&amp;Itemid=360' );
       	$html.='<li class="item-'.$row['id'].'"><a href="'.$url.'">';


       	$html .= $row['title'];

        $html .= '</a></li>';
		}    	
    }
    $html .= '</ul>';
}
#$html .= '</div>';
echo $html;

