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
        $GLOBALS['version'] = "1.0";
        $ide = fs::name($GLOBALS['argv'][0])=="jphp-core.jar";
        $GLOBALS['progdir'] = fs::parent($GLOBALS['argv'][0]) . '/';
        if($ide) $GLOBALS['progdir'] = fs::abs('./').'/';
        $GLOBALS['projectdir'] = $GLOBALS['progdir'] . '/projects/';
        $GLOBALS['docdir'] = $GLOBALS['progdir'] . '/docs/';
        $GLOBALS['nickname'] = 'meigoc';
        $GLOBALS['repo'] = 'jphp-BundleEditor-dn';
        $GLOBALS['updater'] = new updater;
        $settings = app()->form('settings');
        $GLOBALS['styles'] = $settings->darkColors;
        $GLOBALS['icons'] = $settings->darkIcons;
        $settings->settings['theme'] = "Dark";
        //$settings->applySettings();
        (new Thread(function () use ($settings){
            while(!$settings->builded){}
            uiLaterAndWait(function (){
                app()->showForm('projectManager');
            });
        }))->start();
    }
}
