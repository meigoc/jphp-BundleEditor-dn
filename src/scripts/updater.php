<?php
namespace scripts;

use gui;
use framework;
use Exception, httpclient, std, facade\Json;

class updater 
{

    /**
     * @var bool
     */
    public $isChecking = false;
    
    /**
     * @var bool
     */
    public $availableUpdate = false;
    
    /**
     * @var bool
     */
    public $checked = false;
    
    /**
     * @var string
     */
    public $version;
    
    /**
     * @var string
     */
    public $lastVersion;
    
    /**
     * @var string
     */
    public $author;
    
    /**
     * @var string
     */
    public $repo;

    /**
     * @var string
     */
    public $description;
    
    /**
     * @var string
     */
    public $assets = [];

    function __construct(){
        $this->version = $GLOBALS['version'];
        $this->author = $GLOBALS['nickname'];
        $this->repo = $GLOBALS['repo'];
    }
    
    public function checkUpdate(){
        if(!$this->isChecking){
            $this->isChecking = true;
            $hc = new HttpClient;
            $r = $hc->get('https://api.github.com/repos/'.$this->author.'/'.$this->repo.'/releases');
            $hc->free();
            $code = $r->statusCode();
            if($code==200){
                $text = $r->body();
                try {
                    $jsonR = Json::decode($text);
                    $json = $jsonR[0];
                    $this->lastVersion = $json['tag_name'];
                    $this->description = $json['tag_name'].":\n".$json['body'];
                    $l = [];
                    foreach ($json['assets'] as $asset){
                        if(fs::ext($asset['name'])=="zip"){
                            $l[] = ['name' => $asset['name'], 'url' => $asset['browser_download_url']];
                        }
                    }
                    $this->assets = $l;
                    if($this->lastVersion!=null and $this->version!=$this->lastVersion){
                        $this->availableUpdate = true;
                        $i = 1;
                        while($jsonR[$i]!=null){
                            $json = $jsonR[$i];
                            if($json['tag_name']!=$this->version){
                                $this->description .= "\n\n".$json['tag_name'].":\n".$json['body'];
                                $i++;
                            }else{
                                $i = -1;
                            }
                        }
                    }
                    $this->checked = true;
                }catch (Exception $err){
                    return "error";
                }
            }
            $this->isChecking = false;
            return $code;
        }
        return 0;
    }
    
    public function update(){
        
    }

}