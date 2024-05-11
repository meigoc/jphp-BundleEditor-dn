<?php
namespace editor\modules;

use std, gui, framework, editor, scripts\updater;


class AppModule extends AbstractModule
{
    /**
     * @event action 
     */
    function doAction(ScriptEvent $e = null)
    {
        $GLOBALS['version'] = "dev-v0.0.1.4";
        $ide = fs::name($GLOBALS['argv'][0])=="jphp-core.jar";
        $GLOBALS['progdir'] = fs::parent($GLOBALS['argv'][0]) . '/';
        if($ide) $GLOBALS['progdir'] = fs::abs('./').'/';
        $GLOBALS['projectdir'] = $GLOBALS['progdir'] . '/projects/';
        $GLOBALS['docdir'] = $GLOBALS['progdir'] . '/docs/';
        $GLOBALS['nickname'] = 'illa4257';
        $GLOBALS['repo'] = 'Bundle-Editor-for-Develnext';
        $GLOBALS['updater'] = new updater;
        $settings = app()->form('settings');
        $GLOBALS['styles'] = $settings->defColors;
        $GLOBALS['icons'] = $settings->defIcons;
        (new Thread(function () use ($settings){
            while(!$settings->builded){}
            uiLaterAndWait(function (){
                app()->showForm('projectManager');
            });
        }))->start();
    }
}
