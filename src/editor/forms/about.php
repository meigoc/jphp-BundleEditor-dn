<?php
namespace editor\forms;

use std, gui, framework, editor;


class about extends AbstractForm
{

    /**
     * @event checkUpdate.action 
     */
    function doCheckUpdateAction(UXEvent $e = null)
    {    
        $e->sender->enabled = false;
        $e->sender->text = "Checking update...";
        (new Thread(function (){
            $GLOBALS['updater']->checkUpdate();
            uiLaterAndWait(function (){
                $this->checkUpdate->enabled = true;
                $this->checkUpdate->text = "Check update";
            });
        }))->start();
    }

    /**
     * @event link.action 
     */
    function doLinkAction(UXEvent $e = null)
    {    
        $this->toast("Link opens in browser");
        browse("https://github.com/illa4257/Bundle-Editor-for-Develnext");
    }

    /**
     * @event show 
     */
    function doShow(UXWindowEvent $e = null)
    {    
        $this->width -= 8;
        $this->height -= 8;
    }


}
