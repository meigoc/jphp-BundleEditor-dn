<?php
namespace scripts;

use gui, std, editor;

class tools 
{

    public static function copyDir($from, $to){
        $from .= "/";
        $to .= "/";
        $f = new File($from);
        foreach ($f->findFiles() as $file){
            $t = $to . fs::name($file);
            if(fs::isDir($file)){
                fs::makeDir($t);
                tools::copyDir($file, $t);
            }else{
                fs::makeFile($file);
                fs::copy($file, $t);
            }
        }
    }
    
    public static function openIDE($path){
        $ide = new ide;
        $ide->show();
        $ide->loadProject($path);
    }
    
    public static function size($num, $symb = false, $a = false){
        $t = 'B';
        if($num>=1024){
            $num /= 1024;
            $t = 'KB';
            if($num>=1024){
                $num /= 1024;
                $t = 'MB';
                if($num>=1024){
                    $num /= 1024;
                    $t = 'GB';
                    if($num>=1024){
                        $num /= 1024;
                        $t = 'TB';
                        if($num>=1024){
                            $num /= 1024;
                        }
                    }
                }
            }
        }
        $arr = explode('.', $num);
        if($a){
            $num = $arr[0] . '.' . $arr[1][0] . $arr[1][1];
        }else{
            $num = $arr[0];
        }
        $num .= $t;
        return $num;
    }
    
    public static function getIcon($ext){
        if($ext=="bat" or $ext=="cmd"){
            $icon = new UXImage("res://.data/img/bat-file.png");
        }elseif($ext=="dnbundle"){
            $icon = new UXImage("res://.data/img/bundle-file.png");
        }elseif($ext=="cs"){
            $icon = new UXImage("res://.data/img/cs-file.png");
        }elseif($ext=="jar"){
            $icon = new UXImage("res://.data/img/jar-file.png");
        }elseif($ext=="jpg" or $ext=="jpeg"){
            $icon = new UXImage("res://.data/img/jpg-file.png");
        }elseif($ext=="png"){
            $icon = new UXImage("res://.data/img/png-file.png");
        }elseif($ext=="js"){
            $icon = new UXImage("res://.data/img/js-file.png");
        }elseif($ext=="txt"){
            $icon = new UXImage("res://.data/img/txt-file.png");
        }elseif($ext=="java"){
            $icon = new UXImage("res://.data/img/java-file.png");
        }elseif($ext=="vbs"){
            $icon = new UXImage("res://.data/img/vbs-file.png");
        }elseif($ext=="resource"){
            $icon = new UXImage("res://.data/img/project-file.png");
        }elseif($ext=="ini"){
            $icon = new UXImage("res://.data/img/ini-file.png");
        }elseif($ext=="html"){
            $icon = new UXImage("res://.data/img/html-file.png");
        }elseif($ext=="php"){
            $icon = new UXImage("res://.data/img/php-file.png");
        }elseif($ext=="css"){
            $icon = new UXImage("res://.data/img/css-file.png");
        }else
            $icon = new UXImage("res://.data/img/file.png");
        
        return $icon;
    }

}