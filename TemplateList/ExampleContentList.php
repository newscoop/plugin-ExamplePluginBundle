<?php
/**
 * @package Newscoop\ExamplePluginBundle
 * @author Paweł Mikołajczuk <pawel.mikolajczuk@sourcefabric.org>
 * @copyright 2014 Sourcefabric o.p.s.
 * @license http://www.gnu.org/licenses/gpl-3.0.txt
 */

namespace Newscoop\ExamplePluginBundle\TemplateList;

use Newscoop\ListResult;
use Newscoop\TemplateList\PaginatedBaseList;

/**
 * ExampleContent List
 */
class ExampleContentList extends PaginatedBaseList // we extend PaginatedBaseList to use build-in support for paginator
{
    protected function prepareList($criteria, $parameters)
    {
        // get query builder (or Query), use passed $criteria object to build query
        $target = $this->getListByCriteria($criteria);
        // paginate query builder, pagenumber is injected to paginatorService in list block, use max results from criteria.
        // get ListResults with paginated data

        // if you don't have records in database then you can uncomment this code (it will create dummy criteria objects):
        $target = array();
        for ($i=0; $i < 20 ; $i++) {
            $target[$i] = new \Newscoop\ExamplePluginBundle\Entity\Example();
            $target[$i]->setName('Name for '.$i.' example');
            $target[$i]->setDescription('Description for '.$i.' example');
            $target[$i]->getCreatedAt(new \DateTime());
        }


        $list = $this->paginateList($target, null, $criteria->maxResults);

        return $list;
    }

    /**
     * Get list for given criteria
     *
     * You can place this method also in Entity Repository.
     *
     * @param Newscoop\ExamplePluginBundle\TemplateList\ExampleContentCriteria $criteria
     *
     * @return Newscoop\ListResult
     */
    private function getListByCriteria(ExampleContentCriteria $criteria)
    {
        $em = \Zend_Registry::get('container')->get('em');
        $qb = $em->getRepository('Newscoop\ExamplePluginBundle\Entity\Example')
            ->createQueryBuilder('e');

        // use processed by list constraints from list block (template)
        foreach ($criteria->perametersOperators as $key => $operator) {
            $qb->andWhere('e.'.$key.' = :'.$key)
                ->setParameter($key, $criteria->$key);
        }

        // use processed by list order definitions from list block (template)
        $metadata = $em->getClassMetadata('Newscoop\ExamplePluginBundle\Entity\Example');
        foreach ($criteria->orderBy as $key => $order) {
            if (array_key_exists($key, $metadata->columnNames)) {
                $key = 'e.' . $key;
            }

            $qb->orderBy($key, $order);
        }

        return $qb;
    }
}
