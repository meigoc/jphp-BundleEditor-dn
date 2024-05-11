<?php
namespace editor\forms;

use std, gui, framework, editor;


class desc extends AbstractForm
{

    public $UXHtmlEditor;
    public $UXTextArea;

    /**
     * @event construct 
     */
    function doConstruct(UXEvent $e = null)
    {    
        $UXHtmlEditor = new UXHtmlEditor;
        $UXHtmlEditor->on("keyUp", function (){
            $this->UXTextArea->text = str::replace($this->UXHtmlEditor->htmlText, '><', ">\n<");
        });
        $UXHtmlEditor->on("click", function (){
            $this->UXTextArea->text = str::replace($this->UXHtmlEditor->htmlText, '><', ">\n<");
        });
        $tab = new UXTab("UXHtmlEditor");
        $tab->content = $UXHtmlEditor;
        $this->UXHtmlEditor = $UXHtmlEditor;
        $this->tabPane->tabs->add($tab);
        
        $UXTextArea = new UXTextArea;
        $UXTextArea->on("keyUp", function (){
            $this->UXHtmlEditor->htmlText = $this->UXTextArea->text;
        });
        $tab = new UXTab("UXTextArea");
        $tab->content = $UXTextArea;
        $this->UXTextArea = $UXTextArea;
        $this->tabPane->tabs->add($tab);
        $this->UXTextArea->text = str::replace($this->UXHtmlEditor->htmlText, '><', ">\n<");
    }

    public function getText(){
        return $this->UXTextArea->text;
    }
}
