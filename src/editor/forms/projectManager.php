<?php
namespace editor\forms;

use std, gui, framework, editor;


class projectManager extends AbstractForm
{

    /**
     * @event mp.action 
     */
    function doMpAction(UXEvent $e = null)
    {    
        $this->block();
        app()->showForm('newproject')->show();
        app()->form('newproject')->allowDestroy = false;
        app()->form('newproject')->setting($this);
        $this->activeForm = app()->form('newproject');
    }

    /**
     * @event op.action 
     */
    function doOpAction(UXEvent $e = null)
    {    
        $this->block();
        $op = new openProject;
        $this->activeForm = $op;
        $op->activeForm = $this;
        $op->showAndWait();
        $this->block(true);
    }

    /**
     * @event construct 
     */
    function doConstruct(UXEvent $e = null)
    {    
        $this->info->text = "Version: " . $GLOBALS['version'];
    }

    /**
     * @event close 
     */
    function doClose(UXWindowEvent $e = null)
    {    
        if(!$this->mp->enabled){
            $e->consume();
        }else{
            exit();
        }
    }

    /**
     * @event about.action 
     */
    function doAboutAction(UXEvent $e = null)
    {    
        app()->form('about')->show();
        app()->form('about')->requestFocus();
    }

    /**
     * @event click 
     */
    function doClick(UXMouseEvent $e = null)
    {    
        if(!$this->mp->enabled)
        $this->activeForm->requestFocus();
    }

    /**
     * @event settings.action 
     */
    function doSettingsAction(UXEvent $e = null)
    {    
        $this->block();
        $settings = app()->form('settings');
        $this->activeForm = $settings;
        $settings->showAndWait();
        $this->block(true);
    }

    /**
     * @event show 
     */
    function doShow(UXWindowEvent $e = null)
    {    
        $this->width -= 8;
        $this->height -= 8;
        $icon = new UXImage($GLOBALS['icons']['settings']);
        $icon = new UXImageView($icon);
        $this->settings->graphic = $icon;
    }
    
    public $activeForm;
    
    function block($en = false){
        $this->mp->enabled = $en;
        $this->op->enabled = $en;
        $this->settings->enabled = $en;
    }
}
