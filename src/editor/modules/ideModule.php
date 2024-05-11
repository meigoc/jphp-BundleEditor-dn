<?php
namespace editor\modules;

use php\desktop\Robot;
use Exception;
use facade\Json, std, gui, framework, editor, scripts\tools;


class ideModule extends AbstractModule
{

    public $objForFile = [];
    public $count = 0;

    /**
     * @event action 
     */
    function doAction(ScriptEvent $e = null)
    {    
        $this->tabs->observer("width")->addListener(function ($old, $new){
            if($this->tabs->data('wf')!=1){
                $this->tabs->data('wf', 1);
            }else{
                foreach ($this->getContextForm()->tabsList as $tab){
                    $tab->content->width = $new;
                }
            }
        });
        $this->tabs->observer("height")->addListener(function ($old, $new){
            if($this->tabs->data('height')!=1){
                $this->tabs->data('height', 1);
            }else{
                foreach ($this->getContextForm()->tabsList as $tab){
                    $tab->content->height = $new-32;
                }
            }
        });
        
        $l = [];
        $contextMenu = new UXContextMenu();
        
        $new = new UXMenu("New              >");
        $icon = new UXImage("res://.data/img/New.png");
        $icon = new UXImageArea($icon);
        $icon->width = 16;
        $icon->height = 16;
        $new->graphic = $icon;
        
        $folder = new UXMenuItem("Folder");
        $folder->on("action", function (){
            $path = fs::abs($this->tree->focusedItem->graphic->data('path')) . '/';
            $this->block();
            $makefile = new makefile;
            $makefile->type = "dir";
            $makefile->path = $path;
            $makefile->id = $this->projectID;
            $makefile->update();
            $this->getContextForm()->activeForm = $makeFile;
            $makefile->showAndWait();
            $makefile->free();
            $this->block(true);
        });
        $icon = new UXImage("res://.data/img/folder-empty.png");
        $icon = new UXImageArea($icon);
        $icon->width = 16;
        $icon->height = 16;
        $folder->graphic = $icon;
        $new->items->add($folder);
        
        $new->items->add(UXMenu::createSeparator());
        
        $file = new UXMenuItem("File");
        $file->on("action", function (){
            $path = fs::abs($this->tree->focusedItem->graphic->data('path')) . '/';
            $this->block();
            $makefile = new makefile;
            $makefile->type = "file";
            $makefile->path = $path;
            $makefile->id = $this->projectID;
            $makefile->project = $this->project;
            $makefile->update();
            $this->getContextForm()->activeForm = $makeFile;
            $makefile->showAndWait();
            $makefile->free();
            $this->block(true);
        });
        $icon = new UXImage("res://.data/img/file.png");
        $icon = new UXImageArea($icon);
        $icon->width = 16;
        $icon->height = 16;
        $file->graphic = $icon;
        $new->items->add($file);
        
        $file = new UXMenuItem("PHP File");
        $file->on("action", function (){
            $path = fs::abs($this->tree->focusedItem->graphic->data('path')) . '/';
            $this->block();
            $makefile = new makefile;
            $makefile->type = "PHP File";
            $makefile->path = $path;
            $makefile->id = $this->projectID;
            $makefile->project = $this->project;
            $makefile->update();
            $this->getContextForm()->activeForm = $makeFile;
            $makefile->showAndWait();
            $makefile->free();
            $this->block(true);
        });
        $icon = new UXImage("res://.data/img/php-file.png");
        $icon = new UXImageArea($icon);
        $icon->width = 16;
        $icon->height = 16;
        $file->graphic = $icon;
        $new->items->add($file);
        
        $file = new UXMenuItem("PHP Class");
        $file->on("action", function (){
            $path = fs::abs($this->tree->focusedItem->graphic->data('path')) . '/';
            $this->block();
            $makefile = new makefile;
            $makefile->type = "PHP Class";
            $makefile->path = $path;
            $makefile->id = $this->projectID;
            $makefile->project = $this->project;
            $makefile->update();
            $this->getContextForm()->activeForm = $makeFile;
            $makefile->showAndWait();
            $makefile->free();
            $this->block(true);
        });
        $icon = new UXImage("res://.data/img/php-file.png");
        $icon = new UXImageArea($icon);
        $icon->width = 16;
        $icon->height = 16;
        $file->graphic = $icon;
        $new->items->add($file);
        
        $contextMenu->items->add($new);
        
        $edit = new UXMenuItem("Edit");
        $icon = new UXImage("res://.data/img/Edit.png");
        $icon = new UXImageArea($icon);
        $icon->width = 16;
        $icon->height = 16;
        $edit->graphic = $icon;
        $edit->on("action", function (){
            $graphic = $this->tree->focusedItem->graphic;
            $this->editFile($graphic->data('fp'));
        });
        $l[] = $edit;
        $contextMenu->items->add($edit);
        
        $openFileFolder = new UXMenuItem("Open file folder");
        $icon = new UXImage("res://.data/img/folder.png");
        $icon = new UXImageArea($icon);
        $icon->width = 16;
        $icon->height = 16;
        $openFileFolder->graphic = $icon;
        $openFileFolder->on("action", function (){
            $graphic = $this->tree->focusedItem->graphic;
            $path = $graphic->data('path');
            if($graphic->data('isDir')){
                $path = fs::parent($path);
            }
            (new UXDesktop)->open($path);
        });
        $contextMenu->items->add($openFileFolder);
        
        $del = new UXMenuItem("Delete");
        $del->on("action", function (){
            $graphic = $this->tree->focusedItem->graphic;
            if($graphic->data("isDir")){
                $path = $graphic->data("path");
            }else{
                $path = $graphic->data("file");
            }
            if(fs::isDir($path)){
                fs::clean($path);
            }
            fs::delete($path);
        });
        $icon = new UXImage("res://.data/img/Delete.png");
        $icon = new UXImageArea($icon);
        $icon->width = 16;
        $icon->height = 16;
        $del->graphic = $icon;
        $contextMenu->items->add($del);
        
        $this->tree->contextMenu = $contextMenu;
        $this->objForFile = $l;
        $this->tree->on('dragOver', function (UXDragEvent $e) {
            if ($e->dragboard->files) {
                $e->acceptTransferModes(['MOVE', 'COPY']);
                $e->consume();
            }
        });
        $this->tree->on('dragDrop', function (UXDragEvent $f){
            $path = $this->tree->focusedItem->graphic->data('path') . '/';
            $this->block();
            $ac = new ac;
            $ac->help1->text = "Copying files...";
            $ac->show();
            $ac->setH();
            $this->getContextForm()->activeForm = $ac;
            $l = $f->dragboard->files;
            (new Thread(function () use ($ac, $path, $l){
                $var = new varE;
                $var->file = $l[0];
                $t = new Thread(function () use ($path, $l, $var){
                    foreach ($l as $file){
                        $var->file = $file;
                        fs::copy($file, $path . fs::name($file));
                        $var->index++;
                    }
                });
                $t->start();
                while($t->isAlive()){
                    $size = fs::size($var->file);
                    $size2 = fs::size($path . fs::name($var->file));
                    $r1 = $size2*(100/$size);
                    $r2 = $var->index*(100/arr::count($l));
                    uiLaterAndWait(function () use ($ac, $r1, $r2, $var){
                        $ac->pb1->progress = $r2;
                        $ac->pb2->progress = $r1;
                        $ac->help2->text = './'.fs::name($var->file);
                    });
                }
                uiLaterAndWait(function () use ($ac){
                    $ac->hide();
                    $this->block(true);
                });
            }))->start();
        });
        $this->thread = new Thread(function (){
            while(!($this->isFree())){
                if($this->project!=null){
                    $l = $this->scan($this->project);
                    if(Json::encode($l)!=Json::encode($this->list)){
                        $this->list = $l;
                        $parent = new UXTreeItem;
                        $parent->value = fs::name($this->project);
                        $parent->path = fs::abs($this->project) . '/';
                        $icon = new UXImage("res://.data/img/Home.png");
                        $icon = new UXImageArea($icon);
                        $icon->width = 16;
                        $icon->height = 16;
                        $icon->data("path", $parent->path);
                        $icon->data("isDir", true);
                        $parent->graphic = $icon;
                        $this->saveExpands($this->tree);
                        if($this->expanded[$parent->path]){
                            $parent->expanded = true;
                        }
                        $this->makeTree($l, $parent, $this->tree);
                        uiLaterAndWait(function () use ($parent){
                            $this->tree->root = $parent;
                        });
                    }
                }
                sleep(1);
                uiLaterAndWait(function (){
                    if($this->count!=$this->tabs->tabs->count()){
                        $this->getContextForm()->updateTabs();
                        $this->count = $this->tabs->tabs->count();
                    }
                });
            }
        });
        $this->thread->start();
    }

    /**
     * @event update.action 
     */
    function doUpdateAction(ScriptEvent $e = null)
    {    
        $form = $this->getContextForm();
        $robot = new RobotScript;
        $v = 0;
        while($form->width+$v<=856){
            $v++;
        }
        $form->width += $v;
        $robot->x += $v;
        
        $v = 0;
        while($form->height+$v<=480){
            $v++;
        }
        $form->height += $v;
        $robot->y += $v;
    }

    
    function saveExpands(UXTreeView $root){
        $expands = $root->expandedItems;
        $l = [];
        foreach ($expands as $item){
            $l[$item->graphic->data("path")] = true;
        }
        $this->expanded = $l;
        
        $expands = $root->selectedItems;
        $l = [];
        foreach ($expands as $item){
            $l[$item->graphic->data("path")] = true;
        }
        $this->selected = $l;
    }
    
    function scan($path){
        $f = new File($path);
        $l = [];
        foreach ($f->findFiles() as $file){
            if(fs::isDir($file)){
                $arr = $this->scan($file);
                $l[] = ['dir' => $file, 'list' => $arr];
            }else{
                $l[] = $file;
            }
        }
        return $l;
    }
    
    function makeTree($arr, UXTreeItem $parent, $root = null){
        foreach ($arr as $item){
            $child = new UXTreeItem;
            if(gettype($item)=="array"){
                if(arr::count($item['list'])!=0){
                    $icon = new UXImage("res://.data/img/folder.png");
                }else{
                    $icon = new UXImage("res://.data/img/folder-empty.png");
                }
                $child->value = fs::name($item['dir']);
                $path = fs::abs($item['dir']);
                $child->isDir = true;
                $this->makeTree($item['list'], $child, $root);
            }else{
                $icon = tools::getIcon(fs::ext($item));
                $child->value = fs::name($item);
                $path = fs::parent(fs::abs($item));
            }
            $iconArea = new UXImageArea($icon);
            $iconArea->width = 16;
            $iconArea->height = 16;
            $iconArea->data('path', $path);
            $iconArea->data('isDir', $child->isDir);
            if(!$child->isDir){
                $iconArea->data('file', fs::abs($item));
                $iconArea->data('fp', str::replace(fs::abs($item), fs::abs($this->getContextForm()->project), ""));
            }
            $child->graphic = $iconArea;
            $this->saveExpands($root);
            if($this->expanded[$path]){
                $child->expanded = true;
            }
            $parent->children->add($child);
        }
    }

    /**
     * @var string
     */
    public $project;
    
    /**
     * @var File[]
     */
    public $list = [];
    
    /**
     * @var UXTreeItem[]
     */
    public $expanded = [];
    
    /**
     * @var UXTreeItem[]
     */
    public $selected = [];
    
    /**
     * @var Thread
     */
     public $thread;

}

class varE
{
    public $file;
    public $index = 0;
}
