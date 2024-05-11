<?php
namespace editor\forms;

use std, gui, framework, editor;


class ac extends AbstractForm
{

    /**
     * @event close 
     */
    function doClose(UXWindowEvent $e = null)
    {    
        $e->consume();
    }
    
    function setH($m = 1){
        if($m==0){
            $height = 93;
        }
        if($m==1){
            $height = 149;
        }
        $this->height = $height;
    }

}
