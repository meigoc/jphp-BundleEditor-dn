<?php
namespace editor\modules;

use std, gui, framework, editor;


class aboutModule extends AbstractModule
{

    /**
     * @event timer.action 
     */
    function doTimerAction(ScriptEvent $e = null)
    {    
        $data = 'Version: '.$GLOBALS['version']."\n";
        if($GLOBALS['updater']->checked){
            if($GLOBALS['updater']->availableUpdate){
                $data .= "Available update: ".$GLOBALS['updater']->lastVersion;
                $labelEx = new UXLabelEx($GLOBALS['updater']->description);
                $this->container->content = $labelEx;
            }else{
                $data .= "This is last version!";
            }
        }
        $this->info->text = $data;
    }

}
