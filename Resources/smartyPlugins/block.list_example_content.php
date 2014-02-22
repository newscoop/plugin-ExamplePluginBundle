<?php
/**
 * @package Newscoop\ExamplePluginBundle
 * @author Paweł Mikołajczuk <pawel.mikolajczuk@sourcefabric.org>
 * @copyright 2014 Sourcefabric o.p.s.
 * @license http://www.gnu.org/licenses/gpl-3.0.txt
 */

/**
 * list_example_content block plugin
 *
 * Type:     block
 * Name:     list_example_content
 *
 * @param array $params
 * @param mixed $content
 * @param object $smarty
 * @param bool $repeat
 * @return string
 */
function smarty_block_list_example_content($params, $content, &$smarty, &$repeat)
{
    $context = $smarty->getTemplateVars('gimme');
    // get paginator service
    $paginatorService = \Zend_Registry::get('container')->get('newscoop.listpaginator.service');

    if (!isset($content)) { // init
        $start = $context->next_list_start('\Newscoop\ExamplePluginBundle\TemplateList\ExampleContentList');
        // initiate list object, pass new criteria object and paginatorService
        $list = new \Newscoop\ExamplePluginBundle\TemplateList\ExampleContentList(
            new \Newscoop\ExamplePluginBundle\TemplateList\ExampleContentCriteria(),
            $paginatorService
        );

        // inject page parameter name to paginatorService, every list have own name used for pagination
        $list->setPageParameterName($context->next_list_id($context->getListName($list)));
        // inject requested page number (get from request value of list page parameter name)
        $list->setPageNumber(\Zend_Registry::get('container')->get('request')->get($list->getPageParameterName(), 1));

        // get list
        $list->getList($start, $params);
        if ($list->isEmpty()) {
            $context->setCurrentList($list, array());
            $context->resetCurrentList();
            $repeat = false;

            return null;
        }

        // set current list and connect used in list properties
        $context->setCurrentList($list, array('content', 'pagination'));
        // assign current list element to context
        // how we get current_example_content_list name? Our list class have name "ExampleContentList"
        // so we add "current_" and replace all big letters to "_"
        $context->content = $context->current_example_content_list->current;
        $repeat = true;
    } else { // next
        $context->current_example_content_list->defaultIterator()->next();
        if (!is_null($context->current_example_content_list->current)) {
            // assign current list element to context
            $context->content = $context->current_example_content_list->current;
            $repeat = true;
        } else {
            $context->resetCurrentList();
            $repeat = false;
        }
    }

    return $content;
}
