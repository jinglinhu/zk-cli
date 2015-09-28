<?php
/**
 * 基于zookeeper的分布式命令管理 
 */
define('ZKHOST' , 'xx.xxx.xxx.xx:2181,xx.xxx.xxx.xx:2181,xx.xxx.xxx.xx:2181') ;

class ZookeeperController extends Controller {
    public $layout ='//layouts/column2';

    public function actionIndex()
    {	

    	$zk = new ComZookeeper(ZKHOST);

    	$data['modules'] = $zk->getChildren('/adep');

        $data['m'] = Yii::app()->request->getParam('m','');

        if(!in_array($data['m'],$data['modules'])){
            $data['m'] = '';
        }

        $this->render("index",$data);
    }

    public function actionReq(){
        $is_post = Yii::app()->request->isPostRequest ;
        if($is_post) {
            $params = $_POST ;
        } else {
            $params = $_GET ;
        }
        $act = $params['act'] ;
        if($act =='add'){

            if(isset($params['module_name']) && $params['module_name']){

                $zk = new ComZookeeper(ZKHOST);
                $path_cli = '/adep/'.trim(trim($params['module_name'],'/')).'/cli';
                $path_res = '/adep/'.trim(trim($params['module_name'],'/')).'/res';
                $zk->set($path_cli,'');
                $zk->set($path_res,'');
            }

        }elseif($act == 'exc'){

             if(isset($params['cmd']) && $params['cmd'] && isset($params['cmd_module']) && $params['cmd_module']){

                $zk = new ComZookeeper(ZKHOST);
                $path = '/adep/'.trim(trim($params['cmd_module'],'/')).'/cli';
                $zk->set($path,trim($params['cmd']));
             }

        }elseif($act == 'res'){

             if(isset($params['module_name']) && $params['module_name']){

                $zk = new ComZookeeper(ZKHOST);
                $path = '/adep/'.trim(trim($params['module_name'],'/')).'/res';
                $ips = $zk->getChildren($path);

                if($ips){
                    $data = array();
                    foreach ($ips as $k => $ip) {
                        $res_path = $path."/".$ip;
                        $data[$ip] = json_decode($zk->get($res_path),true);
                    }
                    echo json_encode($data);
                }else{
                    echo -1;
                }
                return;
             }

        }
        echo 0;
    }
 

}   //end of class