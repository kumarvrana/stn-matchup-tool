<?php
// no direct access
defined( '_VALID_MOS' ) or die( 'Restricted access' );

        mosMenuBar::startTable();
        //mosMenuBar::addNewX();
        mosMenuBar::spacer();
        //mosMenuBar::editList();
        mosMenuBar::spacer();
        //mosMenuBar::deleteList();
        mosMenuBar::spacer();
        mosMenuBar::divider();
        mosMenuBar::spacer();
        mosMenuBar::custom('import', 'import.png', 'import.png', 'Import', false);
        mosMenuBar::spacer();
        mosMenuBar::divider();
        mosMenuBar::spacer();
        //mosMenuBar::custom('config', 'config.png', 'config.png', 'Configuration', false);
        mosMenuBar::spacer();
        mosMenuBar::help( 'stn.import.tool' );
        mosMenuBar::endTable();
      
