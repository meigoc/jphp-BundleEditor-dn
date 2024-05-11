<?php
namespace editor\forms;

use std, gui, framework, editor;


class ready extends AbstractForm
{

    public $path;

    /**
     * @event close.action 
     */
    function doCloseAction(UXEvent $e = null)
    {    
        $this->hide();
    }

    /**
     * @event openFileFolder.action 
     */
    function doOpenFileFolderAction(UXEvent $e = null)
    {    
        (new UXDesktop)->open($this->path);
    }

}
