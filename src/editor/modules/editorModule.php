<?php
namespace editor\modules;

use Exception;
use std, gui, framework, editor;


class editorModule extends AbstractModule
{

    public $docs = [];

    /**
     * @event script.action 
     */
    function doScriptAction(ScriptEvent $e = null)
    {    
        try{
            while(true){
                if($this->getContextForm()->saveFile){
                    $this->getContextForm()->saveFile = false;
                    file_put_contents($this->getContextForm()->file, $this->getContextForm()->getText());
                    uiLaterAndWait(function (){
                        $this->info->text = "Saved!";
                        $this->getContextForm()->tab->text = $this->getContextForm()->text;
                    });
                }else{
                    sleep(1);
                }
            }
        }catch(Exception $err){
            uiLaterAndWait(function () use ($err){
                Logger::error("Error:");
                echo "File: ".$err->getFile()."\n";
                echo "Line: ".$err->getLine()."\n";
                echo "Message:\n";
                var_dump($err->getMessage());
                echo "--------------------\n";
            });
        }
    }

    /**
     * @event script2.action 
     */
    function doScript2Action(ScriptEvent $e = null)
    {    
        try{
            while(true){
                if($this->getContextForm()->updateCode or $GLOBALS["updateCode"]){
                    $this->getContextForm()->updateCode = false;
                    $GLOBALS["updateCode"] = false;
                    $text = str::lines($this->getContextForm()->getText()." ");
                    $ct = arr::count($text);
                    $code = $this->code->items;
                    $cc = $code->count();
                    
                    // Lines
                    uiLater(function () use ($y){
                        $this->info->text = "Loading... (1/2)";
                    });
                    while($ct!=$cc){
                        if($ct>=$cc){
                            $cc++;
                            $UXPanel = new UXPanel;
                            $UXPanel->borderWidth = 0;
                            $c = new UXLabelEx($cc);
                            //$c->textColor = $GLOBALS['styles']['text-color'];
                            //$c->classesString = "";
                            $UXPanel->children->add($c);
                            uiLaterAndWait(function () use ($code, $UXPanel){
                                $code->add($UXPanel);
                            });
                        }else{
                            $cc--;
                            uiLaterAndWait(function () use ($code, $cc){
                                $code->removeByIndex($cc);
                            });
                        }
                    }
                    
                    // Colored text (y)
                    $y = 0;
                    $oldWidth = $gwidth;
                    $gwidth = 0;
                    $commented = false;
                    while($y!=$ct){
                        // x
                        $posx = 0;
                        $line = str::replace($text[$y], ' ', "\n \n");
                        $line = str::replace($line, '    ', "\n    \n");
                        $line = str::replace($line, '/', "\n/\n");
                        $line = str::replace($line, '\\', "\n\\\n");
                        $line = str::replace($line, '$', "\n$\n");
                        $line = str::replace($line, '?', "\n?\n");
                        $line = str::replace($line, '<', "\n<\n");
                        $line = str::replace($line, '>', "\n>\n");
                        $line = str::replace($line, '(', "\n(\n");
                        $line = str::replace($line, ')', "\n)\n");
                        $line = str::replace($line, '[', "\n[\n");
                        $line = str::replace($line, ']', "\n]\n");
                        $line = str::replace($line, '{', "\n{\n");
                        $line = str::replace($line, '}', "\n}\n");
                        $line = str::replace($line, '-', "\n-\n");
                        $line = str::replace($line, '+', "\n+\n");
                        $line = str::replace($line, '*', "\n*\n");
                        $line = str::replace($line, '=', "\n=\n");
                        $line = str::replace($line, '@', "\n@\n");
                        $line = str::replace($line, '\'', "\n\'\n");
                        $line = str::replace($line, '"', "\n\"\n");
                        $line = str::replace($line, '&', "\n\&\n");
                        $line = str::replace($line, '^', "\n\^\n");
                        $line = str::replace($line, '%', "\n\%\n");
                        $line = str::replace($line, '#', "\n\#\n");
                        $line = str::replace($line, '!', "\n\!\n");
                        $line = str::replace($line, ':', "\n\:\n");
                        $line = str::replace($line, ';', "\n\;\n");
                        $line = str::replace($line, '.', "\n\.\n");
                        $line = str::replace($line, ',', "\n\,\n");
                        $line = str::replace($line, '|', "\n|\n");
                        
                        if($this->docs!=[]){
                            $line = str::replace($line, $this->getLnOperator($this->docs["comment"]), "\n".$this->docs["comment"]."\n");
                            $line = str::replace($line, $this->getLnOperator($this->docs["comment-begin"]), "\n".$this->docs["comment-begin"]."\n");
                            $line = str::replace($line, $this->getLnOperator($this->docs["comment-end"]), "\n".$this->docs["comment-end"]."\n");
                        }
                        $line = str::lines("0\n".$line."\n");
                        $cl = arr::count($line);
                        $obj = $code->offsetGet($y)->children;
                        $cc = $obj->count()-1;
                        while($cl!=$cc){
                            if($cl>=$cc){
                                $cc++;
                                $UXLabelEx = new UXLabelEx;
                                $UXLabelEx->classesString = "label";
                                uiLaterAndWait(function () use ($obj, $UXLabelEx){
                                    $obj->add($UXLabelEx);
                                });
                            }else{
                                $cc--;
                                uiLaterAndWait(function () use ($obj, $cc){
                                $obj->removeByIndex($cc);
                            });
                            }
                        }
                        
                        // Colored text (x)
                        $x = 0;
                        $commentedLine = false;
                        $isVar = false;
                        while($x!=$cl){
                            if($x!=0){
                                $UXLabelEx = $obj->offsetGet($x+1);
                                $value = $line[$x];
                                $color = $GLOBALS['styles']['text-color'];
                                if($this->docs!=[]){
                                    $type = $this->getType($value);
                                    if($type=="class"){
                                        $color = $GLOBALS['styles']['class-color'];
                                    }
                                    if($type=="function"){
                                        $color = $GLOBALS['styles']['func-color'];
                                    }
                                    if($type=="var" or $type=="startvar"){
                                        $color = $GLOBALS['styles']['var-color'];
                                    }
                                    if($type=="operator"){
                                        $color = $GLOBALS['styles']['operator-color'];
                                    }
                                    if($type=="comment"){
                                        $color = $GLOBALS['styles']['comment-color'];
                                        $commentedLine = true;
                                    }
                                    if($type=="comment-begin"){
                                        $color = $GLOBALS['styles']['comment-color'];
                                        $commented = true;
                                    }
                                    if($type=="comment-end"){
                                        $color = $GLOBALS['styles']['comment-color'];
                                        $commented = false;
                                    }
                                    if($type=="ssymb"){
                                        $color = $GLOBALS['styles']['ssymb-color'];
                                    }else{
                                        if($isVar){
                                            $isVar = false;
                                            $color = $GLOBALS['styles']['var-color'];
                                        }
                                    }
                                    if($type=="startvar"){
                                        $isVar = true;
                                    }
                                    if($commentedLine or $commented){
                                        $color = $GLOBALS['styles']['comment-color'];
                                    }
                                }
                                uiLaterAndWait(function () use ($UXLabelEx, $value, $color, $posx){
                                    $UXLabelEx->text = $value;
                                    $UXLabelEx->x = $posx;
                                    $UXLabelEx->textColor = $color;
                                });
                                $width = $UXLabelEx->font->calculateTextWidth($value);
                                $posx += $width;
                            }else{
                                $UXLabelEx = $obj->offsetGet($x);
                                $width = $UXLabelEx->font->calculateTextWidth($y)+8;
                                $width += 8;
                                if($width>=$gwidth){
                                    $gwidth = $width;
                                }
                                uiLaterAndWait(function () use ($UXLabelEx, $oldWidth){
                                    $UXLabelEx->width = $oldWidth;
                                    $this->textArea->x = $oldWidth;
                                });
                                $posx += $oldWidth;
                            }
                            $x++;
                        }
                        $y++;
                        uiLater(function () use ($y, $ct){
                            $this->info->text = "Loading... (".$y."/".$ct.") (2/2)";
                        });
                    }
                    uiLaterAndWait(function () use ($oldWidth){
                        $this->textArea->x = $oldWidth;
                        $this->code->data('offset', $oldWidth);
                    });
                    if($gwidth!=$oldWidth){
                        $this->getContextForm()->updateCode = true;
                        $this->code->data('update', true);
                        $oldArr = [];
                    }elseif($this->code->data('update')){
                        uiLaterAndWait(function (){
                            $this->code->data('update', false);
                            $this->getContextForm()->updateWH($this->textArea->text);
                            if($this->code->data('loaded')==null){
                                $this->code->data('loaded', true);
                                $this->info->text = "Loaded!";
                                $this->getContextForm()->tab->text = $this->getContextForm()->text;
                                $this->textArea->enabled = true;
                                $this->code->show();
                                
                            }
                        });
                    }
                }else{
                    if(!$this->textArea->focused)
                        sleep(1);
                }
            }
        }catch(Exception $err){
            uiLaterAndWait(function () use ($err){
                Logger::error("Error:");
                echo "File: ".$err->getFile()."\n";
                echo "Line: ".$err->getLine()."\n";
                echo "Message:\n";
                var_dump($err->getMessage());
                echo "--------------------\n";
            });
        }
    }

    /**
     * @event script3.action 
     */
    function doScript3Action(ScriptEvent $e = null)
    {    
        while(true){
            if($this->getContextForm()->updateHint){
                $this->getContextForm()->updateHint = false;
                $arr = [$this->getLineFromPos($this->textArea->text, $this->textArea->selection["start"])];
                $i = $arr[0];
                $end = $this->getLineFromPos($this->textArea->text, $this->textArea->selection["end"]);
                while($end!=$arr[0]){
                    $i++;
                    $arr[] = $i;
                    $end--;
                }
                $this->code->selectedIndexes = $arr;
                
                if($this->docs!=[]){
                    $text = $this->getText();
                    $y = $this->getLineFromPos($text, $this->textArea->selection["start"]);
                    $text = str::lines($text);
                    $x = $this->textArea->font->calculateTextWidth($text[$y]);
                    $x += $this->code->data('offset')+20;
                    $y *= 17;
                    $this->help->x = $x;
                    $this->help->y = $y;
                    
                    uiLaterAndWait(function (){
                        $this->help->items->clear();
                    });
                    $width = 0;
                    foreach ($this->docs['classes'] as $class){
                        $item = new UXHBox;
                        $UXLabelEx = new UXLabelEx($class["name"]);
                        $w = $UXLabelEx->font->calculateTextWidth($UXLabelEx->text);
                        if($w>=$width){
                            $width = $w;
                        }
                        $UXLabelEx->classesString = "";
                        $UXLabelEx->textColor = $GLOBALS['styles']['class-color'];
                        $item->add($UXLabelEx);
                        uiLaterAndWait(function () use ($item){
                            $this->help->items->add($item);
                        });
                    }
                    foreach ($this->docs['funcs'] as $func){
                        $item = new UXHBox;
                        $funcName = $func["func"];
                        $funcName .= $this->docs["funcargstart"];
                        $added = false;
                        foreach ($func["args"] as $arg){
                            if($added){
                                $funcName .= $this->docs["funcargnext"];
                            }else{
                                $added = true;
                            }
                            $funcName .= $this->docs["startvar"].$arg["name"];
                        }
                        $funcName .= $this->docs["funcargend"].$this->docs["end"];
                        $UXLabelEx = new UXLabelEx($funcName);
                        $w = $UXLabelEx->font->calculateTextWidth($UXLabelEx->text);
                        if($w>=$width){
                            $width = $w;
                        }
                        $UXLabelEx->classesString = "";
                        $UXLabelEx->textColor = $GLOBALS['styles']['func-color'];
                        $item->add($UXLabelEx);
                        uiLaterAndWait(function () use ($item){
                            $this->help->items->add($item);
                        });
                    }
                    foreach ($this->docs['vars'] as $var){
                        $item = new UXHBox;
                        $UXLabelEx = new UXLabelEx($this->docs["startvar"].$var["name"]);
                        $w = $UXLabelEx->font->calculateTextWidth($UXLabelEx->text);
                        if($w>=$width){
                            $width = $w;
                        }
                        $UXLabelEx->classesString = "";
                        $UXLabelEx->textColor = $GLOBALS['styles']['var-color'];
                        $item->add($UXLabelEx);
                        uiLaterAndWait(function () use ($item){
                            $this->help->items->add($item);
                        });
                    }
                    foreach ($this->docs['operators'] as $operator){
                        $item = new UXHBox;
                        $UXLabelEx = new UXLabelEx($operator["name"]);
                        $w = $UXLabelEx->font->calculateTextWidth($UXLabelEx->text);
                        if($w>=$width){
                            $width = $w;
                        }
                        $UXLabelEx->classesString = "";
                        $UXLabelEx->textColor = $GLOBALS['styles']['operator-color'];
                        $item->add($UXLabelEx);
                        uiLaterAndWait(function () use ($item){
                            $this->help->items->add($item);
                        });
                    }
                    $width += 32;
                    $this->help->width = $width;
                }
            }
            if(!$this->textArea->focused)
            sleep(1);
        }
    }

    
    function getType($text){
        $r = "none";
        foreach ($this->docs["classes"] as $class){
            if($class["name"]==$text){
                $r = "class";
            }
        }
        if($r=="none"){
            foreach ($this->docs["funcs"] as $func){
                if($func["func"]==$text){
                    $r = "function";
                }
            }
        }
        if($r=="none" and $text==$this->docs["startvar"]){
            $r = "startvar";
        }
        if($r=="none"){
            foreach ($this->docs["vars"] as $var){
                if($var["name"]==$text){
                    $r = "var";
                }
            }
        }
        if($r=="none"){
            foreach ($this->docs["operators"] as $operator){
                if($operator["name"]==$text){
                    $r = "operator";
                }
            }
        }
        if($r=="none"){
            if($this->docs["comment"]==$text){
                $r = "comment";
            }
        }
        if($r=="none"){
            if($this->docs["comment-begin"]==$text){
                $r = "comment-begin";
            }
        }
        if($r=="none"){
            if($this->docs["comment-end"]==$text){
                $r = "comment-end";
            }
        }
        if($r=="none"){
            foreach ($this->docs["ssymb"] as $ss){
                if($ss==$text){
                    $r = "ssymb";
                }
            }
        }
        return $r;
    }
    
    function getLnOperator($text){
        $r = "";
        $s = 0;
        while($text[$s]!=null){
            $r .= "\n".$text[$s]."\n";
            $s++;
        }
        return $r;
    }
}
