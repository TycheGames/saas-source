<?php
use yii\helpers\Url;
$this->shownav('index', 'my_menu_collection');
?>

<style>
    .container{width:100% !important;}
    .label a{color: #ffffff !important;text-decoration:none !important;cursor:pointer}
    .jump a{color: #65cea7 !important}
</style>

<link rel="stylesheet" href="<?php echo $this->baseUrl ?>/bootstrap/css/bootstrap.min.css">
<link rel="stylesheet" type="text/css" media="all" href="<?php echo $this->baseUrl ?>/css/daterangepicker.css" />
<!-- jQuery文件。务必在bootstrap.min.js 之前引入 -->
<script src="<?php echo $this->baseUrl; ?>/js/jquery.min.js"></script>
<script src="<?php echo $this->baseUrl; ?>/bootstrap/js/bootstrap.min.js"></script>

<section class="wrapper">
    <div class="row">
        <div class="col-lg-12">
            <section class="panel">
                <header class="panel-heading">
                    我的收藏
                    <span class="tools pull-right">
                        <a class="btn btn-success" href="#myModal" data-toggle="modal">添加收藏</a>
                    </span>
                </header>
                <div class="panel-body">
                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th>主菜单</th>
                                <th>子菜单</th>
                                <th>地址</th>
                                <th>操作</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if(!empty($collection)) :?>
                                <?php foreach($collection as $k => $v) :?>
                                    <tr>
                                        <td><?=$v['parent_menu_name']?></td>
                                        <td class="jump"><a href="<?=$host . $menu[$v['parent_menu_node']][$v['child_menu_node']][1]?>"><?=$v['child_menu_name']?></a></td>
                                        <td class="jump"><a href="<?=$host . $menu[$v['parent_menu_node']][$v['child_menu_node']][1]?>"><?=$host . $menu[$v['parent_menu_node']][$v['child_menu_node']][1]?></a></td>
                                        <td>
                                            <span class="label label-warning label-mini"><a href="<?=$host . $menu[$v['parent_menu_node']][$v['child_menu_node']][1]?>">跳转</a></span>
                                            <span class="label label-danger label-mini del-menu" menu-id="<?=$v['id']?>"><a>删除</a></span>
                                        </td>
                                    </tr>
                                <?php endforeach;?>
                            <?php else :?>
                                <tr class="text-center">
                                    <td colspan="4">你还没有收藏菜单，赶快去收藏吧！</td>
                                </tr>
                            <?php endif;?>
                        </tbody>
                    </table>
                </div>
            </section>
        </div>
    </div>
</section>

<div aria-hidden="true" aria-labelledby="myModalLabel" role="dialog" tabindex="-1" id="myModal" class="modal fade" style="display: none;">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button aria-hidden="true" data-dismiss="modal" class="close" type="button">×</button>
                <h4 class="modal-title">选择菜单</h4>
            </div>
            <div class="modal-body">

                <form class="form-horizontal adminex-form" method="get">
                    <div class="form-group">
                        <label class="col-sm-4 control-label col-lg-4" for="inputSuccess">主菜单</label>
                        <div class="col-lg-8">
                            <select class="form-control input-sm m-bot15 topmenu">
                                <?php if(!empty($topmenu)) :?>
                                    <?php foreach($topmenu as $k => $v):?>
                                        <option value="<?=$k?>"><?=$v[0]?></option>
                                    <?php endforeach;?>
                                <?php endif;?>
                            </select>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-sm-4 control-label col-lg-4" for="inputSuccess">子菜单</label>
                        <div class="col-lg-8">
                            <select class="form-control input-sm m-bot15 menu">
                                <?php foreach($menu['index'] as $k => $v):?>
                                    <option value="<?=$k?>"><?=$v[0]?></option>
                                <?php endforeach;?>
                            </select>
                        </div>
                    </div>
                </form>

            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">取消</button>
                <button type="button" class="btn btn-success save">保存</button>
            </div>
        </div>
    </div>
</div>

<script>
    var menu = <?php echo json_encode($menu)?>;
    var topmenu = '';
    var html = '';
    var csrf = $("meta[name=csrf-token]").attr('content');

    $('.topmenu').change(function () {
        $('.menu').html('');
        html = '';
        topmenu = $(this).val();
        var childmenu = menu[topmenu];

        $.each(childmenu,function(k,v){
            html += '<option value="'+ k +'">'+ v[0] +'</option>'
        })

        $('.menu').append(html);
    })

    $('.save').click(function () {
        var parent_menu = $('.topmenu').val();
        var child_menu = $('.menu').val();
        send_data({parent_menu:parent_menu, child_menu:child_menu, action:'add', _csrf:csrf})
    });

    $('.del-menu').click(function () {
        var id = $(this).attr('menu-id');
        send_data({id:id, action:'del', _csrf:csrf})
    });

    function send_data(data) {
        $.ajax({
            type: "POST",
            url: "<?php echo $this->baseUrl; ?>/index.php?r=main/menu-collection",
            data: data,
            dataType: 'json',
            success: function(msg){console.log(data);
                if(msg.code == 1) {
                    alert(msg.msg);
                    window.location = window.location;
                } else if(msg.code == 0) {
                    alert(msg.msg);
                } else {
                    alert('操作失败');
                }
            }
        });
    }
</script>