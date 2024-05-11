<?php
namespace editor\forms;

use Exception;
use std, gui, framework, editor, scripts\pmgr;


class newproject extends AbstractForm
{

    /**
     * @event close 
     */
    function doClose(UXWindowEvent $e = null)
    {    
        if($this->close->enabled){
            $this->hide();
            $this->pform->block(true);
        }else{
            $e->consume();
        }
    }

    /**
     * @event close.action 
     */
    function doCloseAction(UXEvent $e = null)
    {    
        $this->doClose();
    }

    /**
     * @event make.action 
     */
    function doMakeAction(UXEvent $e = null)
    {    
        if($this->check()){
            $this->hide();
            $this->pform->block(true);
            $this->pform->hide();
            if($this->allowDestroy){
                $this->pform->free();
            }
            $arr = ['name' => $this->name->text,
            'icon' => $this->icon,
            'desc' => $this->desc->text,
            'author' => $this->author->text,
            'version' => $this->version->text,
            'groupI' => $this->group->selectedIndex,
            'aecl' => $this->aecl->selected,
            'ID' => $this->identifer->text];
            if(!$arr['aecl']){
                $arr['cl'] = $this->cl;
            }
            pmgr::makeProject($this->type->selectedIndex, $arr);
        }else{
            $this->toast('Fill in all the fields!');
        }
    }

    /**
     * @event name.keyUp 
     */
    function doNameKeyUp(UXKeyEvent $e = null)
    {    
        $this->update();
    }

    /**
     * @event type.action 
     */
    function doTypeAction(UXEvent $e = null)
    {    
        $this->tabPane->selectedIndex = $this->type->selectedIndex;
    }

    /**
     * @event choose.action 
     */
    function doChooseAction(UXEvent $e = null)
    {    
        $this->selectIcon();
    }

    /**
     * @event icon.click 
     */
    function doIconClick(UXMouseEvent $e = null)
    {    
        $this->selectIcon();
    }

    /**
     * @event desc.keyUp 
     */
    function doDescKeyUp(UXKeyEvent $e = null)
    {    
        $this->update();
    }

    /**
     * @event author.keyUp 
     */
    function doAuthorKeyUp(UXKeyEvent $e = null)
    {    
        $this->update();
    }

    /**
     * @event version.keyUp 
     */
    function doVersionKeyUp(UXKeyEvent $e = null)
    {    
        $this->update();
    }

    /**
     * @event aecl.step 
     */
    function doAeclStep(UXEvent $e = null)
    {    
        $en = !$this->aecl->selected;
        $this->open->enabled = $en;
    }

    /**
     * @event open.action 
     */
    function doOpenAction(UXEvent $e = null)
    {    
        $this->block();
        $desc = new desc;
        $desc->showAndWait();
        $this->cl = $desc->getText();
        $this->block(true);
    }

    /**
     * @event make.mouseExit 
     */
    function doMakeMouseExit(UXMouseEvent $e = null)
    {    
        $this->update();
    }

    /**
     * @var UXForm
     */
    public $pform;
    
    /**
     * @var FileChooserScript
     */
    public $iconChooser;
    
    public $allowDestroy = false;
    
    public $cl;

    function block($en = false){
        $this->name->enabled = $en;
        $this->desc->enabled = $en;
        $this->author->enabled = $en;
        $this->identifer->enabled = $en;
        $this->aecl->enabled = $en;
        $this->open->enabled = $en;
        $this->icon->enabled = $en;
        $this->choose->enabled = $en;
        $this->version->enabled = $en;
        $this->group->enabled = $en;
        $this->type->enabled = $en;
        $this->tabPane->enabled = $en;
        $this->make->enabled = $en;
        $this->close->enabled = $en;
    }

    function selectIcon(){
        $this->block();
        if($this->iconChooser->execute()){
            try {
                $img = new UXImage($this->iconChooser->file);
                $gc = $this->icon->gc();
                $gc->clearRect(0, 0, $this->icon->width, $this->icon->height);
                $gc->drawImage($img, 0, 0, $this->icon->width, $this->icon->height);
                $GLOBALS['selectedIcon'] = true;
                $this->update();
            }catch (Exception $err){
                alert('Error');
            }
        }
        $this->block(true);
    }

    function setting($form){
        $this->pform = $form;
        $this->type->selectedIndex = 0;
        $this->group->selectedIndex = 4;
        $gc = $this->icon->gc();
        $gc->fillColor = "#949494";
        $gc->fillRect(0, 0, $this->icon->width, $this->icon->height);
        $GLOBALS['selectedIcon'] = false;
        $this->iconChooser = new FileChooserScript;
        $this->iconChooser->filterExtensions = '*.png, *.jpg, *.jpeg';
        $this->name->text = "";
        $this->desc->text = "";
        $this->author->text = "";
        $this->version->text = "1.0";
        $this->identifer->text = "";
        $this->update();
    }
    
    function update(){
        $a = $this->check();
        if($a){
            $this->make->color = $GLOBALS['styles']["btn1"];
            $this->make->hoverColor = $GLOBALS['styles']["btn1-hover"];
            $this->make->textColor = $GLOBALS['styles']["btn1-text"];
        }else{
            $this->make->color = $GLOBALS['styles']["btn1-hover"];
            $this->make->hoverColor = $GLOBALS['styles']["btn1-hover"];
            $this->make->textColor = $GLOBALS['styles']["btn1-text"];
        }
        $this->make->borderRadius = 3;
    }
    
    function check(){
        if($this->name->text!=null
        and $this->desc->text!=null
        and $this->author->text!=null
        and $this->version->text!=null
        and $GLOBALS['selectedIcon']){
            return true;
        }
        return false;
    }
}
