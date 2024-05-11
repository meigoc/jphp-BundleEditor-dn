<?php
namespace editor\forms;

use std, gui, framework, editor;


class editor extends AbstractForm
{

    
    function updateWH($new){
        $arr = str::lines($new);
        foreach ($arr as $one){
            $l = $this->textArea->font->calculateTextWidth($one."a");
            if($l>=$r){
                $r = $l;
            }
        }
        $r+=128;
        $this->textArea->width = $r;
        $this->code->width = $r+$this->code->data('offset');
        
        $height = arr::count($arr)*17;
        $height+=32;
        $this->textArea->height = $height+3;
        $this->code->height = $height;
    }
    
    public $file;
    public $saveFile = false;
    public $updateCode = false;
    public $updateHint = false;
    public $tab;
    public $text;
    public $docs = [];

    public function openFile($file, $tab, $docs){
        $this->module('editorModule')->docs = $docs;
        $this->docs = $docs;
        $this->file = $file;
        $data = file_get_contents($file);
        $this->textArea->text = $data;
        $this->updateWH($data);
        $this->tab = $tab;
        $this->text = $tab->text;
        $this->updateCode = true;
        $tab->text .= ' (-)';
        
        $this->textArea->observer("text")->addListener(function ($old, $new){
            $this->updateWH($new);
            $this->tab->text = $this->text."*";
            $this->info->text = "Waiting...";
            $this->saveFile = true;
            $this->updateCode = true;
        });
        $this->textArea->observer("selection")->addListener(function ($old, $new){
            $this->updateHint = true;
        });
        
        $this->script->callAsync();
        $this->script2->callAsync();
        //$this->script3->callAsync();
    }
    
    public function getText(){
        return $this->textArea->text;
    }
    
    function getLineFromPos($text, $pos){
        $text = str::lines($text);
        $ct = arr::count($text);
        $y = 0;
        while($y!=$ct and $pos>=0){
            $pos -= str::length($text[$y])+1;
            $y++;
        }
        return $y-1;
    }
    
}
