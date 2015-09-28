<?php
$this->breadcrumbs=array(
    '分布式命令管理任务列表',
);
if(!empty($modules)) {
    Yii::app()->controller->widget('bootstrap.widgets.TbButton', array(
                                'label' => '新增模块',
                                'size'=>'small',
                                'type'=>'danger',
                                'htmlOptions' => array('id' =>'add_cmd','style'=>'float:right;')
                            )) ;
    echo "<br \>";
    foreach($modules as $k => $v) {

        if($m == $v){
            $btn_class = "btn-danger";
        }else{
            $btn_class = "btn-info btn-small";
        }
        ?>
        <a href="?m=<?php echo $v;?>" class="btn <?php echo $btn_class;?>"><?php echo $v;?></a>
        <?php
    }
    ?>
    <hr>
    <div class="control-group">
        <div class="controls">
            <span class="label label-important">[<?php echo Yii::app()->user->name."@".$m?> ~]#</span>
            <textarea rows="10" class="span8" name="cmd" id="cmd"></textarea>
            <a id="exc_cmd" class="btn btn-danger">执行</a>
            <input type="hidden" id='cmd_module' name='cmd_module' value = "<?php echo $m?>">

        </div>
    </div>
    <hr>
    <div id = "res"></div>
    
    <?php
}

?>

<?php $this->beginWidget('bootstrap.widgets.TbModal', array('id'=>'cron_modal')); ?>
 
<div class="modal-header">
    <a class="close" data-dismiss="modal">&times;</a>
    <h4 id="pop_title">添加模块</h4>
</div>
<div class="modal-body">
    <form class="form-horizontal">
        <div class="control-group">
            <label class="control-label" for="module_name">模块名称</label>
            <div class="controls">
                <input type="text" class="span4" id="module_name" name="module_name" placeholder="请填写模块名称">
            </div>
        </div>
        <div class="control-group">
            <div class="controls">
                <a id="add_new_cmd" class="btn btn-info btn-small">添加</a>
                <a class="btn btn-default btn-small" data-dismiss='modal'>取消</a>
            </div>
        </div>
    </form>
</div>
 
<?php $this->endWidget(); ?>
<script type="text/javascript">
$(function (){

    function show(){

        var module_name = '<?php echo $m;?>' ;

        if(module_name.length == 0) {
            return ;
        }else{
             var params  = {
                act:'res' ,
                module_name:encodeURIComponent(module_name),
             } ;
           $.ajax({
            type:'POST' ,
            url: '/zookeeper/req' ,
            dataType:'JSON',
            data:params ,
            success:function (data) {
                if(data == -1) {
                    
                }else{
                    var d = eval(data);
                    var html = "";
                    $.each(d,function(i,v){
                        html+='<div class="alert alert-success alert-block">';
                        html+='<span class="label label-warning">'+v.time+'</span>&nbsp;';
                        html+='<span class="label label-warning">'+i+'</span><br>';
                        html+='<span class="label label-info">'+v.cmd+'</span><br>';
                        html+=v.data;
                        html+="</div>";
                        //console.log(v);
                    })
                    if($("#res").html() != html){
                        $("#res").html(html);
                    }
                }
            }
         }) ; 
        }
    }
    setInterval(show,1000);  
        
    //新增任务
    $("a[id^=add_cmd]").click(function (){
        $("#cron_modal").css({'width':'800px','left':'45%','height':'auto'}).modal('show');
    }) ;
    $("#add_new_cmd").click(function (){
        //空判断
        var module_name = $.trim($("#module_name").val()) ;
        if(module_name.length == 0) {
            alert('模块名称不为空！') ;
            return ;
        }
        var params  = {
            act:'add' ,
            module_name:encodeURIComponent(module_name),
        } ;
        
        $.ajax({
            type:'POST' ,
            url: '/zookeeper/req' ,
            dataType:'JSON',
            data:params ,
            success:function (data) {
                if(data == 0) {
                    window.location.reload() ;
                }
            }
        }) ;
    }) ;

     $("#exc_cmd").click(function (){
        //空判断
        var cmd = $.trim($("#cmd").val()) ;
        var cmd_module = $.trim($("#cmd_module").val()) ;
        if(cmd_module.length == 0) {
            alert('请先选择执行模块') ;
            return ;
        }
        if(cmd.length == 0) {
            alert('命令不能为空！') ;
            return ;
        }
        var params  = {
            act:'exc' ,
            cmd:cmd,
            cmd_module:encodeURIComponent(cmd_module),
        } ;
        
        $.ajax({
            type:'POST' ,
            url: '/zookeeper/req' ,
            dataType:'JSON',
            data:params ,
            success:function (data) {
                if(data == 0) {

                }
            }
        }) ;
    }) ;
}) ;
</script>

