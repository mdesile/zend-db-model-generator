<?php
require_once(APPLICATION_PATH.DIRECTORY_SEPARATOR.'models'.DIRECTORY_SEPARATOR.'User.php');
class IndexController extends Zend_Controller_Action
{

    public function init()
    {
        /* Initialize action controller here */
    }

    public function indexAction()
    {
    }

    public function paginator1Action() {
        
        $page = $this->getRequest()->getParam('page');

        if (!$page)
             $page=1;

        $zdmg = new Default_Model_User();
        $paginator=$zdmg->fetchAll2Paginator();
        $paginator->setCurrentPageNumber($page);
        $paginator->setDefaultItemCountPerPage(5);
        $paginator->setPageRange(4);
        $paginator->setDefaultScrollingStyle('Sliding');


        $this->view->paginator=$paginator;

    }

    public function paginator2Action() {

        $page = $this->getRequest()->getParam('page');

        if (!$page)
             $page=1;

        $zdmg = new Default_Model_User();

        /** example 1 **/

        /**
         * @var Zend_Db_Table_Select
         */
        $select=$zdmg->getSelect(true);        

        $paginator=$zdmg->select2Paginator($select->columns('name'));

        /** end of example 1 **/

        // example 2:
        // paginator=$zdmg->query2Paginator('select name from user');
        // end of example 2
        
        $paginator->setCurrentPageNumber($page);
        $paginator->setDefaultItemCountPerPage(5);
        $paginator->setPageRange(4);
        $paginator->setDefaultScrollingStyle('Sliding');


        $this->view->paginator=$paginator;

    }


}

