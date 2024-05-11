<?php
namespace editor\forms;

use std, gui, framework, editor;


class settings extends AbstractForm
{

    public $builded = false;
    
    public $funcsMenu = [];
    public $oldSettings = [];
    public $settings = [
        "theme" => "Light"
    ];
    
    /**
     * @var IniStorage
     */
    public $ini;
    public $from = null;
    
    public $defColors = [
        'btn1'=>'#e6e6e6',
        'btn1-text' => '#333',
        'btn1-hover' => '#ccc',
        'class-color' => '#1b6da8',
        'func-color' => '#3264a8',
        'var-color' => '#4632a8',
        'operator-color' => '#20a81b',
        'comment-color' => '#787878',
        'text-color' => '#333',
        'ssymb-color' => '#2176de'
    ];
    public $defIcons = [
        'settings'=>'res://.data/img/settings-20x20.png',
        'find'=>'res://.data/img/find.png',
        'refresh'=>'res://.data/img/Refresh.png'
    ];
    
    public $darkColors = [
        'btn1'=>'#4d4d4d',
        'btn1-text'=>'#e6e6e6',
        'btn1-hover'=>'#666666',
        'class-color' => '#1b6da8',
        'func-color' => '#20a81b',
        'var-color' => '#4632a8',
        'operator-color'=>'#debb21',
        'comment-color' => '#787878',
        'text-color' => '#e6e6e6',
        'ssymb-color' => '#2176de'
    ];
    public $darkIcons = [
        'settings'=>'res://.data/img/dark-theme/settings-20x20.png',
        'find'=>'res://.data/img/dark-theme/find.png',
        'refresh'=>'res://.data/img/dark-theme/Refresh.png'
    ];
    
    /**
     * @event construct 
     */
    function doConstruct(UXEvent $e = null)
    {    
        $this->ini = new IniStorage($GLOBALS['progdir'].'config.ini');
        $theme = $this->ini->get('theme');
        $at = true;
        if($theme=='Dark' or $theme=='Light') $at = false;
        if($at){
            $theme = 'Light';
            $this->ini->set('theme', $theme);
        }
        $this->settings['theme'] = $theme;
        $this->applySettings();
        $root = new UXTreeItem('Bundle Editor for DevelNext');
        
        // interface
        $interface = new UXTreeItem('interface');
        $this->funcsMenu[$interface->value] = function () use ($interface){
            $UXVBox = new UXVBox;
            $this->addLinksForChildrens($UXVBox, $interface);
            $this->content->content = $UXVBox;
        };
            $general = new UXTreeItem('General');
            $this->funcsMenu[$general->value] = function () use ($general){
                $UXVBox = new UXVBox;
                $UXVBox->add(new UXLabelEx('Theme:'));
                $langList = new UXComboBox;
                $langList->itemsText = "Light\nDark";
                $langList->value = $this->settings['theme'];
                $langList->on('action', function ($e){
                    $this->settings['theme'] = $e->sender->value;
                });
                $UXVBox->add($langList);
                $this->content->content = $UXVBox;
            };
            $interface->children->add($general);
        $root->children->add($interface);
        
        $this->menu->root = $root;
        
        $this->menu->selectedItems = [$interface];
        $this->update($interface);
        $this->oldSettings = $this->settings;
        
        $this->builded = true;
    }

    /**
     * @event menu.click 
     */
    function doMenuClick(UXMouseEvent $e = null)
    {    
        if($e->clickCount==1){
            if($this->menu->focusedItem!=null){
                $this->update($this->menu->focusedItem);
            }
        }
    }

    /**
     * @event apply.action 
     */
    function doApplyAction(UXEvent $e = null)
    {    
        $this->applySettings();
    }

    /**
     * @event show 
     */
    function doShow(UXWindowEvent $e = null)
    {    
        if($this->menu->root!=null)
        if($this->menu->root->children->count()!=0){
            $item = $this->menu->root->children->offsetGet(0);
            $this->update($item);
            $this->menu->selectedItems = [$item];
        }
    }

    /**
     * @event close 
     */
    function doClose(UXWindowEvent $e = null)
    {    
        $this->from = null;
        $this->settings = $this->oldSettings;
    }
    
    function addLinksForChildrens($UXVBox, UXTreeItem $tree){
        $childs = $tree->children->toArray();
        foreach ($childs as $child){
            $link = new UXHyperlink($child->value);
            $link->on('action', function () use ($child){
                $this->menu->selectedItems = [$child];
                $this->update($child);
            });
            $UXVBox->children->add($link);
        }
    }
    
    function update(UXTreeItem $obj){
        if($this->funcsMenu[$obj->value]!=null)
        $this->funcsMenu[$obj->value]();
    }
    
    function setColorAllFlatButtons($arr){
        $GLOBALS['styles'] = $arr;
        app()->form('projectManager')->about->color = $arr['btn1'];
        app()->form('projectManager')->about->hoverColor = $arr['btn1-hover'];
        app()->form('projectManager')->about->textColor = $arr['btn1-text'];
        app()->form('projectManager')->op->color = $arr['btn1'];
        app()->form('projectManager')->op->hoverColor = $arr['btn1-hover'];
        app()->form('projectManager')->op->textColor = $arr['btn1-text'];
        app()->form('projectManager')->settings->color = $arr['btn1'];
        app()->form('projectManager')->settings->hoverColor = $arr['btn1-hover'];
        app()->form('projectManager')->settings->textColor = $arr['btn1-text'];
        app()->form('about')->checkUpdate->color = $arr['btn1'];
        app()->form('about')->checkUpdate->hoverColor = $arr['btn1-hover'];
        app()->form('about')->checkUpdate->textColor = $arr['btn1-text'];
        app()->form('newproject')->close->color = $arr['btn1'];
        app()->form('newproject')->close->hoverColor = $arr['btn1-hover'];
        app()->form('newproject')->close->textColor = $arr['btn1-text'];
        app()->form('newproject')->make->color = $arr['btn1'];
        app()->form('newproject')->make->hoverColor = $arr['btn1-hover'];
        app()->form('newproject')->make->textColor = $arr['btn1-text'];
        if($this->from!=null){
            $this->from->settings->color = $arr['btn1'];
            $this->from->settings->hoverColor = $arr['btn1-hover'];
            $this->from->settings->textColor = $arr['btn1-text'];
            $GLOBALS["updateCode"] = true;
        }
    }
    
    function updateIcons($arr){
        $GLOBALS['icons'] = $arr;
        $icon = new UXImage($arr['settings']);
        $icon = new UXImageView($icon);
        app()->form('projectManager')->settings->graphic = $icon;
        if($this->from!=null){
            $this->from->settings->graphic = $icon;
        }
    }
    
    function applySettings(){
        $theme = "Light";
        $themes = app()->getStyles();
        foreach ($themes as $one){
            if($one=="/.theme/dark-theme.css"){
                $theme = "Dark";
            }
        }
        if($theme!=$this->settings['theme']){
            if($this->settings['theme']=="Dark"){
                $this->addStylesheet("/.theme/dark-theme.css");
                app()->form('projectManager')->addStylesheet("/.theme/dark-theme.css");
                app()->form('newproject')->addStylesheet("/.theme/dark-theme.css");
                app()->form('about')->addStylesheet("/.theme/dark-theme.css");
                if($this->from!=null)
                    $this->from->addStylesheet("/.theme/dark-theme.css");
                $this->setColorAllFlatButtons($this->darkColors);
                $this->updateIcons($this->darkIcons);
                app()->addStyle("/.theme/dark-theme.css");
            }else{
                $this->removeStylesheet("/.theme/dark-theme.css");
                app()->form('projectManager')->removeStylesheet("/.theme/dark-theme.css");
                app()->form('newproject')->removeStylesheet("/.theme/dark-theme.css");
                app()->form('about')->removeStylesheet("/.theme/dark-theme.css");
                if($this->from!=null)
                    $this->from->removeStylesheet("/.theme/dark-theme.css");
                $this->setColorAllFlatButtons($this->defColors);
                $this->updateIcons($this->defIcons);
                app()->removeStyle("/.theme/dark-theme.css");
            }
            $this->ini->set('theme', $this->settings['theme']);
        }
        $this->oldSettings = $this->settings;
    }

}
