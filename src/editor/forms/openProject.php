<?php
namespace editor\forms;

use Exception, std, gui, framework, editor, scripts\tools;


class openProject extends AbstractForm
{

    /**
     * @event construct 
     */
    function doConstruct(UXEvent $e = null)
    {    
        $this->update();
        
        $icon = new UXImageView(new UXImage($GLOBALS['icons']['find']));
        $this->find->graphic = $icon;
        
        $icon = new UXImageView(new UXImage($GLOBALS['icons']['refresh']));
        $this->refresh->graphic = $icon;
    }

    /**
     * @event refresh.action 
     */
    function doRefreshAction(UXEvent $e = null)
    {    
        $this->update();
    }

    /**
     * @event find.action 
     */
    function doFindAction(UXEvent $e = null)
    {    
        $this->refind();
    }

    /**
     * @event findEdit.globalKeyPress-Enter 
     */
    function doFindEditGlobalKeyPressEnter(UXKeyEvent $e = null)
    {    
        $this->refind();
    }


    public $list;
    public $activeForm;
    public $allowDestroy = false;
    
    public function update(){
        $this->findEdit->enabled = false; 
        $this->find->enabled = false; 
        $this->refresh->hide();
        $this->container->hide();
        (new Thread(function (){
            $this->updateList();
            uiLaterAndWait(function (){
                $this->refind();
            });
        }))->start();
    }

    public function updateList(){
        $p = new File($GLOBALS['projectdir']);
        $l = [];
        foreach ($p->findFiles() as $file){
            if(fs::isDir($file)){
                if(fs::exists($file . '/.resource')){
                    $ini = new IniStorage($file . '/.resource');
                    $name = $ini->get('name');
                    $ID = $ini->get('ID');
                    if($name!=null and $ID!=null){
                        $l[] = ['name' => $name, 'ID' => $ID, 'path' => $file . '/'];
                    }
                }
                
            }
        }
        $this->list = $l;
    }
    
    public function refind(){
        $this->findEdit->enabled = false; 
        $this->find->enabled = false; 
        $this->refresh->hide();
        $this->container->hide();
        (new Thread(function (){
            $find = $this->findEdit->text;
            $content = new UXTilePane;
            $content->hgap = 5;
            $content->vgap = 5;
            foreach ($this->list as $p){
                if(str::contains($p['name'], $find)){
                    $btn = new UXButton($p['name']);
                    $btn->width = 88;
                    $btn->height = 88;
                    $btn->contentDisplay = "TOP";
                    try {
                        $graphic = new UXImage($p['path'].'src/.data/img/develnext/bundle/'.str::lower($p['ID']).'/'.str::lower($p['ID']).".png");
                    }catch (Exception $err){
                        echo $err->getMessage()."\n";
                        $graphic = new UXImage("res://.data/img/project-file-64x64.png");
                    }
                    $graphic = new UXImageArea($graphic);
                    $graphic->width = 64;
                    $graphic->height = 64;
                    $graphic->stretch = true;
                    $btn->graphic = $graphic;
                    $btn->on("action", function () use ($p){
                        $this->activeForm->hide();
                        if($this->allowDestroy){
                            $this->activeForm->free();
                        }
                        $this->hide();
                        tools::openIDE($p['path']);
                    });
                    $content->add($btn);
                }
            }
            uiLaterAndWait(function () use ($content){
                $this->container->content = $content;
                $this->findEdit->enabled = true; 
                $this->find->enabled = true; 
                $this->refresh->show();
                $this->container->show();
            });
        }))->start();
    }

}
