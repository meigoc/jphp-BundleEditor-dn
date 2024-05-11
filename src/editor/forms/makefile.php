<?php
namespace editor\forms;

use std, gui, framework, editor;


class makefile extends AbstractForm
{

    /**
     * @event cancel.action 
     */
    function doCancelAction(UXEvent $e = null)
    {    
        $this->hide();
    }

    /**
     * @event make.action 
     */
    function doMakeAction(UXEvent $e = null)
    {    
        $path = fs::abs($this->path.$this->edit->text);
        $path2 = fs::abs($this->path);
        if($this->type=="dir"){
            fs::makeDir($path);
            mkdir($path);
        }else{
            fs::makeFile($this->path.$this->edit->text);
            $data = "";
            $gl = $this->project.'src/vendor/develnext.bundle.'.str::lower($this->id).'.'.$this->id.'Bundle/';
            $gl = str::replace(fs::abs($gl).'\\', '/', '\\');
            $path2 = str::replace($path2, '/', '\\');
            $gln = str::replace($path2, $gl, "");
            if($path2!=$gln)
            $namespace = "namespace " . $gln.";\n\n";
            if($this->type=="PHP File"){
                $data = "<?php\n";
                $path.=".php";
            }
            if($this->type=="PHP Class"){
                $data = "<?php\n".$namespace."class ".$this->edit->text."\n{\n    \n}";
                $path.=".php";
            }
            file_put_contents($path, $data);
        }
        $this->hide();
    }

    public $type;
    public $path;
    public $id;
    public $project;
    
    function update(){
        if($this->type=="dir"){
            $this->label->text .= "folder...";
        }else{
            $this->label->text .= "file...";
        }
    }

}
