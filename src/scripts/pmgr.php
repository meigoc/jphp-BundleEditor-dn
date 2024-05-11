<?php
namespace scripts;

use editor;
use bundle\zip\ZipFileScript;
use httpclient;
use framework, std, gui;
use scripts\tools;

class pmgr 
{

    public static function makeProject($type, $args = []){
        $group = [
            'game',
            'network',
            'database',
            'system',
            'other'
        ];
        $args['group'] = $group[$args['groupI']];
        $GLOBALS['project'] = $GLOBALS['projectdir'] . $args['name'] . '/';
        $tools = $GLOBALS['progdir'] . 'tools/';
        $s = 2;
        $ac = new \editor\forms\ac;
        $ac->help1->text = "Making project...";
        $ac->pb1->progress = 0;
        $ac->show();
        if($type==0){
            // Without everything
            
            (new Thread(function () use ($args, $ac){
                $project = $GLOBALS['project'];
                fs::makeDir($project);
                fs::clean($project);
                fs::makeDir($project.'src/.data/img/develnext/bundle/'.str::lower($args['ID']));
                uiLaterAndWait(function () use ($args, $project, $ac){
                    $args['icon']->save($project . 'src/.data/img/develnext/bundle/'.str::lower($args['ID']).'/'.str::lower($args['ID']).'.png');
                    $ac->pb1->progress = 50;
                });
                $bundle = $project.'src/develnext/bundle/'.str::lower($args['ID']).'/';
                fs::makeDir($bundle);
                
                if($args['aecl']){
                    $data = "<ul>\n";
                    $data .= "</ul>";
                }else{
                    $data = $args['cl'];
                }
                file_put_contents($bundle.'description.html', $data);
                
                $data = "<?php\n";
                $data .= "namespace develnext\\bundle\\".str::lower($args['ID']).";\n\n";
                $data .= "use ide\\bundle\\AbstractBundle;\n";
                $data .= "use ide\\bundle\\AbstractJarBundle;\n";
                $data .= "use ide\\project\\Project;\n";
                $data .= "use ide\\library\\IdeLibraryBundleResource;\n\n";
                $data .= "/**\n";
                $data .= " * Class ".$args['ID']."Bundle\n";
                $data .= " * @package develnext\\bundle\\".str::lower($args['ID'])."\n";
                $data .= " */\n";
                $data .= "class ".$args['ID']."Bundle extends AbstractJarBundle\n{\n\n";
                $data .= "    /**\n     * @param Project \$project\n     * @param AbstractBundle|null \$owner\n     */\n";
                $data .= "    public function onAdd(Project \$project, AbstractBundle \$owner = null)\n    {\n";
                $data .= "       parent::onAdd(\$project, \$owner);\n";
                $data .= "    }\n\n";
                $data .= "    /**\n     * @param Project \$project\n     * @param AbstractBundle|null \$owner\n     */\n";
                $data .= "    public function onRemove(Project \$project, AbstractBundle \$owner = null)\n    {\n";
                $data .= "       parent::onRemove(\$project, \$owner);\n";
                $data .= "    }\n\n";
                $data .= "    /**\n     * @param IdeLibraryBundleResource \$resource\n     */\n";
                $data .= "    public function onRegister(IdeLibraryBundleResource \$resource)\n    {\n";
                $data .= "       parent::onRegister(\$resource);\n";
                $data .= "    }\n";
                $data .= "}";
                file_put_contents($bundle.$args['ID'].'Bundle.php', $data);
                
                fs::makeDir($project.'src/vendor/develnext.bundle.'.str::lower($args['ID']).'.'.$args['ID'].'Bundle/bundle/'.str::lower($args['ID']));
                
                $ini = new IniStorage($project.'.resource');
                $ini->set('name', $args['name']);
                $ini->set('description', str::replace($args['desc'], "\n", "\\n"));
                $ini->set('descriptionFile', 'develnext/bundle/'.str::lower($args['ID']).'/description.html');
                $ini->set('group', $args['group']);
                $ini->set('icon', "develnext/bundle/".str::lower($args['ID']).'/'.str::lower($args['ID']).".png");
                $ini->set('class', "develnext\\\\bundle\\\\".str::lower($args['ID'])."\\\\".$args['ID']."Bundle");
                $ini->set('author', $args['author']);
                $ini->set('version', $args['version']);
                $ini->set('type', 'without');
                $ini->set('ID', $args['ID']);
                $ini->set('tabs', '[{"type":"Welcome"}]');
                if($args['aecl']){
                    $ini->set('automaticallyEnterClassList', 'true');
                }else{
                    $ini->set('automaticallyEnterClassList', 'false');
                }
                $ini->free();
                
                uiLaterAndWait(function () use ($ac, $project){
                    $ac->pb1->progress = 100;
                    $ac->hide();
                    tools::openIDE($project);
                });
            }))->start();
        }
    }
    
    public static function compile($activeform){
        $activeform->block();
        $time = Time::millis();
        $ac = new ac;
        $ac->help1->text = "Building project...";
        $ac->show();
        $project = $activeform->project;
        $type = $activeform->projectType;
        $id = $activeform->projectID;
        $name = $activeform->projectName;
        $activeform->activeForm = $ac;
        if($type=="without"){
            
            // Without everything compile
            
            (new Thread(function () use ($project, $name, $id, $type, $ac, $time, $activeform){
                
                fs::makeDir($project.'build');
                fs::clean($project.'build');
                
                $zipmgr = new ZipFileScript;
                
                $bundle = $project.'build/temp/bundle/dn-'.str::lower($id).'-bundle.jar';
                
                fs::makeFile($bundle);
                
                $zipmgr->path = $bundle;
                
                $zipmgr->createFile();
                
                fs::makeDir($project.'build/temp/f');
                
                tools::copyDir($project.'src', $project.'build/temp/f');
                
                $ini2 = new IniStorage($project.'.resource');
                
                if($ini2->get('automaticallyEnterClassList')=="true"){
                    $cl = self::getClassList($project.'src/vendor/develnext.bundle.'.str::lower($id).'.'.$id.'Bundle/bundle/'.str::lower($id));
                    $text = "<ul>\n";
                    foreach ($cl as $item){
                        $text .= "    <li>".$item['className'];
                        $list = self::getVarsAndFuncsList($item['file'],$item['className']);
                        if(arr::count($list)!=0){
                            $text .= "<ul>";
                            foreach ($list as $var){
                                $text .= "<li>".$var['call'].$var['name'];
                                if($var['isFunc']){
                                    $text .= "(";
                                    foreach ($var['args'] as $arg){
                                        $text .= $arg;
                                        if($arg[str::length($arg)-1]==',') $text .= " ";
                                    }
                                    $text .= ")";
                                }
                                if($var['result']!=null){
                                    $text .= " : ".$var['result'];
                                }
                                $text .= "</li>";
                            }
                            $text .= "</ul>";
                        }
                        $text .= "</li>\n";
                    }
                    $text .= "<ul>";
                    file_put_contents($project.'build/temp/f/develnext/bundle/'.str::lower($id).'/description.html', $text);
                }
                
                $mf = $project.'build/temp/f/META-INF/MANIFEST.MF';
                fs::makeFile($mf);
                file_put_contents($mf, "Manifest-Version: 1.0\n\n");
                $zipmgr->addDirectory($project."build/temp/f");
                fs::clean($project."build/temp/f");
                
                fs::delete($project."build/temp/f");
                
                $ini = new IniStorage($project.'build/temp/.resource');
                $ini->set("name", $ini2->get("name"));
                $ini->set("description", $ini2->get("description"));
                $ini->set("descriptionFile", $ini2->get("descriptionFile"));
                $ini->set("group", $ini2->get("group"));
                $ini->set("icon", $ini2->get("icon"));
                $ini->set("class", $ini2->get("class"));
                $ini->set("author", $ini2->get("author"));
                $ini->set("version", $ini2->get("version"));
                
                $zipmgr->path = $project."build/".$name.".dnbundle";
                
                $zipmgr->addDirectory($project."build/temp");
                
                fs::clean($project."build/temp");
                fs::delete($project."build/temp");
                
                $time = Time::millis()-$time;
                uiLaterAndWait(function () use ($ac, $time, $activeform, $project){
                    $ac->hide();
                    $ready = new ready;
                    $ready->label->text = "Compiled in ".$time."ms!";
                    $activeform->activeForm = $ready;
                    $ready->path = $project."build";
                    $ready->showAndWait();
                    $activeform->block(true);
                });
            }))->start();
        }
    }
    
    public static function getClassList($path){
        $path = fs::abs($path);
        $f = new File($path);
        $l = [];
        foreach ($f->findFiles() as $file){
            if(fs::isDir($file)){
                $arr = self::getClassList($file);
                foreach ($arr as $item){
                    $l[] = $item;
                }
            }else{
                if(fs::ext($file)=='php'){
                    $className = fs::nameNoExt($file);
                    $isClassFile = str::contains(file_get_contents($file), 'class '.$className);
                    if($isClassFile){
                        $l[] = ['file' => $file, 'className'=>$className];
                    }
                }
            }
        }
        return $l;
    }
    
    public static function getVarsAndFuncsList($file, $className){
        $data = str::replace(file_get_contents($file), '{', ' { ');
        $data = str::replace($data, '}', ' } ');
        $data = str::replace($data, '    ', ' ');
        $data = str::replace($data, ';', ' ; ');
        $data = str::replace($data, '(', ' ( ');
        $data = str::replace($data, ')', ' ) ');
        $lines = str::lines($data, true);
        $l = [];
        $fc = true;
        $sk = 0;
        $sk2 = false;
        $type = "";
        foreach ($lines as $line){
            if($fc){
                if(str::contains($line, 'class '.$className)){
                    $fc = false;
                }
            }
            if(!$fc){
                $line = explode(' ', $line);
                $nl = 0;
                $read = false;
                $isFunc = false;
                $call = "";
                $name = "";
                $args = [];
                foreach ($line as $text){
                    if($text=='@var' or $text=='@return'){
                        $type = $line[$nl+1];
                    }
                    if($text=="("){
                        $sk2 = true;
                    }
                    if($text==")"){
                        $sk2 = false;
                    }
                    if($name=="" and $read==true and $text!='function' and $text!='static'){
                        $name = str::replace($text, '$', '');
                    }
                    if($read and $sk2 and $text!="(" and $text!=""){
                        $args[] = $text;
                    }
                    if($text=='public'){
                        $read = true;
                        $call = '->';
                    }
                    if($text=='static' and $read){
                        $call = '::';
                    }
                    if($text=='function' and $read){
                        $isFunc = true;
                    }
                    if($text=="{"){
                        $sk++;
                    }
                    if($text=="}"){
                        $sk--;
                        if($sk==0){
                            $fc = true;
                        }
                    }
                    $nl++;
                }
                if($read){
                    $l[] = ['result'=>$type,'call'=>$call, 'name'=>$name,'isFunc'=>$isFunc,'args'=>$args];
                    $type = "";
                }
            }
        }
        return $l;
    }
    
}