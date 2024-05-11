<?php
namespace editor\forms;

use facade\Json;
use Exception, bundle\zip\ZipFileScript, facade\Json, std, gui, framework, editor, scripts\pmgr;


class ide extends AbstractForm
{

    /**
     * @event close 
     */
    function doClose(UXWindowEvent $e = null)
    {
        $this->activeForm = null;
        $this->block();
        $dialog = UXDialog::confirm("Do you really want to close the project \"$this->projectName\"?");
        if($dialog){
            $this->hide();
            $this->free();
            app()->shutdown();
            sleep(1);
            exit();
        }else{
            $e->consume();
            $this->block(true);
        }
    }

    /**
     * @event tree.click-Right 
     */
    function doTreeClickRight(UXMouseEvent $e = null)
    {    
        if($e->sender->contextMenu!=null){
            $en = !$this->tree->focusedItem->graphic->data('isDir');
            if($en){
                $en = false;
                $ext = fs::ext($this->tree->focusedItem->graphic->data('file'));
                foreach ($this->exts as $aext){
                    if($ext==$aext){
                        $en = true;
                    }
                }
            }
            foreach ($this->module('ideModule')->objForFile as $obj){
                $obj->enabled = $en;
            }
            $this->tree->contextMenu->showByNode($e->sender, $e->x, $e->y);
        }
    }

    /**
     * @event build.action 
     */
    function doBuildAction(UXEvent $e = null)
    {    
        pmgr::compile($this);
    }


    /**
     * @event tree.click-Left 
     */
    function doTreeClickLeft(UXMouseEvent $e = null)
    {    
        if($e->clickCount>=2){
            if($this->tree->focusedItem->graphic->data('isDir')!=true){
                $file = fs::abs(fs::abs($GLOBALS['projectdir']).$this->tree->focusedItem->graphic->data('fp'));
                $graphic = $this->tree->focusedItem->graphic->snapshot();
                $ext = fs::ext($file);
                $bool = false;
                foreach ($this->exts as $ext1){
                    if($ext==$ext1){
                        $bool = true;
                    }
                }
                if($bool){
                    $this->editFile($this->tree->focusedItem->graphic->data('fp'));
                }
            }
        }
    }

    /**
     * @event tabs.change 
     */
    function doTabsChange(UXEvent $e = null)
    {    
        $this->updateTabs();
    }

    /**
     * @event separator.mouseDrag 
     */
    function doSeparatorMouseDrag(UXMouseEvent $e = null)
    {    
        $x = $e->x;
        $x2 = $this->separator->x+$x;
        while($x2<=100){
            $x2++;
            $x++;
        }
        while($x2>=$this->w-200){
            $x2--;
            $x--;
        }
        
        $this->tree->width += $x;
        $this->separator->x += $x;
        $this->tabs->x += $x;
        $this->tabs->width -= $x;
        $this->label->x += $x;
    }

    /**
     * @event construct 
     */
    function doConstruct(UXEvent $e = null)
    {    
        $e->sender->observer('width')->addListener(function ($old, $new){
            $v = $new - $old;
            $this->tabs->width += $v;
            $this->w += $v;
        });
        $this->tree->minWidth = 100;
        $this->tabs->minWidth = 200;
        
        $toolbar = new UXMenuBar;
        $toolbar->width = 856;
        $toolbar->height = 24;
        $toolbar->leftAnchor = true;
        $toolbar->rightAnchor = true;
        
        $project = new UXMenu('Project');
        
        $item = new UXMenuItem('New project');
        $item->on('action', function (){
            $this->block();
            app()->showForm('newproject')->show();
            app()->form('newproject')->allowDestroy = true;
            app()->form('newproject')->setting($this);
            $this->activeForm = app()->form('newproject');
        });
        $project->items->add($item);
        
        $item = new UXMenuItem('Load project');
        $item->on('action', function (){
            $this->block();
            $op = new openProject;
            $this->activeForm = $op;
            $op->activeForm = $this;
            $op->allowDestroy = true;
            $op->showAndWait();
            $this->block(true);
        });
        $project->items->add($item);
        
        $item = new UXMenuItem('Close project');
        $item->on('action', function (){
            $this->block();
            app()->showForm('projectManager');
            $this->hide();
            $this->free();
        });
        $project->items->add($item);
        
        $toolbar->menus->add($project);
        
        
        $about = new UXMenu("About");
        
        $item = new UXMenuItem('Check updates');
        $item->on("action", function (){
            app()->showForm('about');
            app()->restoreForm('about');
            app()->form('about')->requestFocus();
        });
        $about->items->add($item);
        
        $toolbar->menus->add($about);
        
        $this->add($toolbar);
        $this->toolbar = $toolbar;
    }

    /**
     * @event show 
     */
    function doShow(UXWindowEvent $e = null)
    {    
        $this->w = $this->width;
        $this->settings->color = $GLOBALS['styles']['btn1'];
        $this->settings->hoverColor = $GLOBALS['styles']['btn1-hover'];
        $this->settings->textColor = $GLOBALS['styles']['btn1-text'];
        $icon = new UXImage($GLOBALS['icons']['settings']);
        $icon = new UXImageView($icon);
        $this->settings->graphic = $icon;
    }
    
    /**
     * @event click 
     */
    function doClick(UXMouseEvent $e = null)
    {    
        if(!$this->tree->enabled)
        if($this->activeForm!=null)
        $this->activeForm->requestFocus();
    }


    /**
     * @event tabs.closeRequest 
     */
    function doTabsCloseRequest(UXEvent $e = null)
    {    
        if(get_class($e->target->content)=='php\\gui\\layout\\UXFragmentPane'){
            $e->target->content->content->free();
        }
        $e->target->content->free();
        $this->updateTabs();
    }

    /**
     * @event settings.action 
     */
    function doSettingsAction(UXEvent $e = null)
    {    
        $this->block();
        $settings = app()->form('settings');
        $this->activeForm = $settings;
        $settings->from = $this;
        $settings->showAndWait();
        $this->block(true);
    }
    
    public $exts = ["php", "txt", "html", "resource", "ini", "json", "java", "js", "css", "bat", "cmd", "vbs", "cs"];
    public $docs = [];
    public $project;
    public $projectName;
    public $projectID;
    public $projectType;
    public $tabsList = [];
    public $openedFiles = [];
    public $w = 480;
    public $activeForm;
    public $toolbar;
    
    /**
     * @var IniStorage
     */
    public $ini;
    
    public function loadProject($path){
        $this->block();
        $path = fs::abs($path) . '/';
        $this->project = $path;
        $this->module('ideModule')->project = $path;
        $ac = new ac;
        $ac->help1->text = 'Opening project...';
        $ac->show();
        $this->activeForm = $ac;
        (new Thread(function () use ($path, $ac){
            $ini = new IniStorage($path . '.resource');
            $this->ini = $ini;
            $name = $ini->get('name');
            $this->projectName = $name;
            $title = $name.' - ['.fs::abs($path).']';
            uiLaterAndWait(function () use ($title, $ac){
                $this->title = $title;
                $ac->pb1->progress = 10;
            });
            $dir = new File($GLOBALS['docdir']);
            foreach ($dir->findFiles() as $file){
                if(fs::isFile($file)){
                    try {
                        $json = file_get_contents($file);
                        $json = Json::decode($json);
                        $this->docs[] = $json;
                    }catch (Exception $err){
                        uiLaterAndWait(function () use ($err, $file){
                            alert($file.":\n".$err->getMessage());
                        });
                    }
                }
            }
            uiLaterAndWait(function () use ($ac){
                $ac->pb1->progress = 50;
            });
            $this->projectID = $ini->get("ID");
            $this->projectType = $ini->get("type");
            $tabs = $ini->get("tabs");
            $tabs = Json::decode($tabs);
            foreach ($tabs as $tabArr){
                if($tabArr['type']=="Welcome"){
                    $tab = new UXTab;
                    $panel = new UXPanel;
                    $panel->classesString = "flat-panel";
                    $panel->borderWidth = 0;
                    $tab->text = "Welcome";
                    $tab->data('data', "{\"type\":\"Welcome\"}");
                    $labelEx = new UXLabelEx("Welcome to Bundle Editor!\n      This is editor bundles for DevelNext...");
                    $labelEx->font = UXFont::of("System", 30);
                    $labelEx->x = 100;
                    $labelEx->y = 50;
                    $panel->add($labelEx);
                    $tab->content = $panel;
                    uiLaterAndWait(function () use ($tab){
                        $this->tabs->tabs->add($tab);
                    });
                }elseif($tabArr['type']=="File"){
                    uiLaterAndWait(function () use ($tabArr){
                        $this->editFile($tabArr['file']);
                    });
                }
            }
            uiLaterAndWait(function () use ($ac){
                $ac->free();
                $this->block(true);
            });
        }))->start();
    }
    
    public function block($en = false){
        if($this->toolbar!=null)
        $this->toolbar->enabled = $en;
        $this->panel->enabled = $en;
        $this->tree->enabled = $en;
        $this->tabs->enabled = $en;
        if($en){
            $this->label->opacity = 1;
        }else{
            $this->label->opacity = 0;
        }
    }
    
    public function editFile($fp){
        $file = fs::abs(fs::abs($this->project).$fp);
        if(fs::exists($file)){
            $bool = true;
            $i = 0;
            while($i!=arr::count($this->openedFiles) and $bool){
                if($this->openedFiles[$i]==$file){
                    $bool = false;
                }
                $i++;
            }
            if($bool){
                $tab = new UXTab;
                $tab->data("tab", true);
                $tab->data("file", $file);
                $tab->data('data', "{\"type\":\"File\",\"file\":\"".str::replace($fp, '\\', '\\\\')."\"}");
                
                $ext = fs::ext($file);
                $docs = [];
                foreach ($this->docs as $doc){
                    foreach ($doc['exts'] as $ext1){
                        if($ext==$ext1){
                            $docs = $doc;
                        }
                    }
                }
                $tab->text = fs::name($file);
                $graphic = tools::getIcon(fs::ext($file));
                $graphic = new UXImageArea($graphic);
                $graphic->width = 16;
                $graphic->height = 16;
                $tab->graphic = $graphic;
                $frag = new UXFragmentPane;
                $editor = new editor;
                $editor->showInFragment($frag);
                $tab->content = $frag;
                $this->tabsList[] = $tab;
                $this->openedFiles[] = $file;
                $this->tabs->tabs->add($tab);
                $this->tabs->selectTab($tab);
                $this->updateTabs();
                $editor->openFile($file, $tab, $docs);
            }else{
                $this->tabs->selectTab($this->tabsList[$i-1]);
            }
        }
    }
    
    function updateTabs(){
        $tabs = $this->tabs->tabs->toArray();
        $l = "[";
        foreach ($tabs as $tab){
            if($l!="[") $l .= ",";
            $l .= $tab->data("data");
        }
        $l .= "]";
        $this->ini->set("tabs", $l);
        
        $l = [];
        $l2 = [];
        if($this->tabs->tabs->count()!=0){
            $arr = $this->tabs->tabs->toArray();
            foreach ($arr as $tab){
                if($tab->data("tab")){
                    $l2[] = $tab;
                    $l[] = $tab->data("file");
                }
            }
        }
        $this->openedFiles = $l;
        $this->tabsList = $l2;
    }

}
