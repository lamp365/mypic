<?php defined('SYSTEM_IN') or exit('Access Denied');?><?php  include page('header');?>
<style type="text/css">
	.order-times{
		width: 100%;
		overflow: hidden;
	}
	.order-times li{
		float: left;
		width: 25%;
		padding-left: 7px;
	    box-sizing: border-box;
	}
	.order-times ul{
		margin:0;
		padding:0;
	}
</style>
<h3 class="header smaller lighter blue">订单基本信息&nbsp;&nbsp; <span class="btn btn-xs btn-info return">返回列表</span></h3>

<form action="" method="post" enctype="multipart/form-data" class="form-horizontal" >
<div class="order-times">
	<ul>
		<li><label>下单时间:</label> <?php  echo date('Y-m-d H:i:s', $order['createtime'])?></li>
		<li><label>支付时间:</label> <?php if(!empty($order['paytime'])) { echo date('Y-m-d H:i:s', $order['paytime']); } ?></li>
		<li><label>发货时间:</label> <?php if(!empty($order['sendtime'])) { echo date('Y-m-d H:i:s', $order['sendtime']); }?></li>
		<li><label>收货时间:</label> <?php  if(!empty($order['completetime'])) { echo date('Y-m-d H:i:s', $order['completetime']); }?></li>
	</ul>
</div>
<input type="hidden" name="id" value="<?php  echo $order['id'];?>">
		<table class="table">
			<tr>
			<th style="width:150px"><label for="">订单编号:</label></th>
			<td style="width: 320px">
				<?php  echo $order['ordersn']?>
			</td>
			<th style="width:150px"><label for="">订单状态:</label></th>
				<td >
					<?php  if($order['status'] == 0) { ?><span class="label label-warning" >待付款</span><?php  } ?>
					<?php  if($order['status'] >=1) { ?><span class="label label-success" >已完成</span><?php  } ?>
					<?php  if($order['status'] == -1) { ?><span class="label label-success">已关闭</span><?php  } ?>
				</td>
			</tr>
			<tr>
			<th ><label for="">下单时间:</label></th>
				<td >
									<?php  echo date('Y-m-d H:i:s', $order['createtime'])?>
				</td>
				<th ><label for="" style="color: red">总金额:</label></th>
				<td >
					<font style="font-weight: bolder"><?php  echo $order['price']+$order['balance_sprice']+$order['freeorder_price']?></font>
					&nbsp;&nbsp;&nbsp;&nbsp;<span>现金支付：<?php  echo $order['price']?></span>
					&nbsp;&nbsp;&nbsp;&nbsp;<span>余额抵扣：<?php  echo $order['balance_sprice']+$order['freeorder_price']?></span>
				</td>
			</tr>
			<tr>
				<th ><label for="">支付方式:</label></th>
				<td >
					<?php  echo $order['paytypename'];?>
					&nbsp;&nbsp;
				</td>
				<th ><label for="">配送方式:</label></th>
				<td >
							<?php  echo $dispatchdata[$order['dispatch']]['dispatchname'];?>
				</td>
			</tr>
			<?php  if($order['hasbonus']>0) { ?>
			<tr>
				<th ><label for="">	优惠卷抵消金额：</label></th>
				<td >
						<?php  echo $order['bonusprice']?>
				</td>
				<th ><label for="">优惠卷编号：</label></th>
				<td>
				<?php  
				foreach ($bonuslist as $bonus) {
				echo $bonus['type_name']."(".$bonus['bonus_sn'].")<br/>";
			}?>
				</td>
			</tr>
			<?php  } ?>
			
						<?php  if($order['status']>=2) { ?>
			<tr>
				<th ><label for="">	寄送方式：</label></th>
				<td >
						<?php echo $order['expresscom'];?>
				</td>
				<th ><label for="">快递编号：</label></th>
				<td>
						<?php  echo $order['expresssn']?>
						<?php  if($order['express'] != 'beihai'){?>
							[<a target="_blank" href="http://m.kuaidi100.com/index_all.html?type=<?php  echo $order['express']?>&postid=<?php  echo $order['expresssn']?>#input"  >查看物流信息</a>]
						<?php }else{ ?>
							[<a target="_blank" href="http://www.xlobo.com/Public/QueryBill.aspx?code=<?php  echo $order['expresssn']?>"  >查看物流信息</a>]
						<?php } ?>
				</td>
			</tr>
			<?php  } ?>
			</table>
			<h3 class="header smaller lighter blue">收货人信息&nbsp;&nbsp;<span style="font-size:14px;" class="btn btn-xs btn-danger" data-toggle="modal" data-target="#modal-address">修改信息</span></h3>
		
			<table class="table ">
					<tr>
				<th style="width:150px"><label for="">收货人姓名:</label></th>
				<td  style="width:320px">
					<?php  echo $order['address_realname'];
							echo '  【'.$order['address_mobile'].'】';
					?>
				</td>
				<th style="width: 150px;"><label for="">收货地址:</label></th>
				<td >
		<?php  echo $order['address_province'];?><?php  echo $order['address_city'];?><?php  echo $order['address_area'];?><?php  echo $order['address_address'];?>
				</td>
			</tr>
				<tr>
						<th  style="width:150px"><label for="">下单人姓名:</label></th>
				<td >
						<?php $m_member = member_get($order['openid'],'mobile,nickname');
						   echo $m_member['nickname'];
							echo '  【'.$m_member['mobile'].'】';
						?>
				</td>
				<th ><label for="">订单备注:</label></th>
				<td >
					<?php if(!empty($order['remark'])){ ?>
					<textarea readonly="readonly" style='width:300px;border: none;' type="text"><?php  echo $order['remark'];?></textarea>
					<?php  } ?>
				</td>
			</tr>
		</table>

	<?php
	if(!empty($order['retag'])){
		$retag = json_decode($order['retag'],true);
		if(!empty($retag['recoder'])){
			$retagArr = explode(';',$retag['recoder']);

	 ?>
		<table class="table table-striped table-bordered table-hover" style="width: 60%">
			<thead>
			<tr>
				<th>管理员</th>
				<th>订单操作日志</th>
				<th>操作时间</th>
			</tr>
			</thead>
			<?php  foreach($retagArr as $onetag) {
						echo '<tr>';
						$onetagArr = explode('-',$onetag);
						$admin = getAdminName($onetagArr[0]);
						$time  = date("Y-m-d H:i:s",$onetagArr[2]);
						echo "<td style='width:150px'>{$admin}</td><td>{$onetagArr[1]}</td><td style='width:200px'>{$time}</td>";
						echo '</tr>';
			       }
			?>
		</table>

	<?php }} ?>



<table class="table table-striped table-bordered table-hover">
			<thead>
			<tr>
				<th style="width:50px;">序号</th>
				<th >商品标题</th>
				<th style="min-width: 160px;">商品规格</th>
				<th >货号</th>
				<th style="color:red;width: 75px;">成交价</th>
				<th style="width: 50px;">数量</th>
				<th style="width: 100px;">状态</th>
				<th style="width: 100px;">操作</th>

			</tr>
			</thead>
			<?php  $i=1;?>
			<?php  if(is_array($order['goods'])) { foreach($order['goods'] as $goods) { ?>
			<tr>
				<td><?php  echo $i;$i++?></td>
				<td><?php  echo $goods['title'];?>【<?php  echo $goods['dish_number'];?>】</td>
				<td> <?php
						if(!empty($goods['spec_key_name'])) {
							$spec_key_name = json_decode($goods['spec_key_name'], true);
							$str = '';
							foreach($spec_key_name as $spec_key => $spec_name){
								$str .= $spec_key.'：'.$spec_name.' | ';
							}
							echo rtrim($str,'|');
						}
					?>
					</td>
				<td><?php  echo $goods['productsn'];?></td>

         <td style='color:red;font-weight:bold;'><?php  echo $goods['orderprice'];?></td>
				<td><?php  echo $goods['total'];?></td>
				<td>
					 <span style="color: red">
					   <?php
					   if($goods['order_type'] == 1 && $goods['order_status'] == 1)  echo "退货申请中";
					   if($goods['order_type'] == 1 && $goods['order_status'] == 2)  echo "退货审核通过";
					   if($goods['order_type'] == 1 && $goods['order_status'] == 3)  echo "买家退货中";
					   if($goods['order_type'] == 1 && $goods['order_status'] == 4)  echo "退货成功";
					   if($goods['order_type'] == 1 && $goods['order_status'] == -1)  echo "退货审核驳回";
					   if($goods['order_type'] == 1 && $goods['order_status'] == -2)  echo "买家撤销退货";
					   if($goods['order_type'] == 3 && $goods['order_status'] == 1)  echo "退款申请中";
					   if($goods['order_type'] == 3 && $goods['order_status'] == 2)  echo "退款审核通过";
					   if($goods['order_type'] == 3 && $goods['order_status'] == 4)  echo "退款成功";
					   if($goods['order_type'] == 3 && $goods['order_status'] == -1)  echo "退款审核驳回";
					   if($goods['order_type'] == 3 && $goods['order_status'] == -2)  echo "买家撤销退款";

					   ?>
				     </span>
				</td>
				<td>
					<?php
						if($goods['order_type'] ==3){
							$url = web_url('order',array('op'=>'aftersale_detail','order_good_id'=>$goods['order_id'],'type'=>'money','orderid'=>$order['id']));
							echo "<a href='{$url}' class='label label-warning'>退款详情</a>";
						}else if($goods['order_type'] ==1){
							$url = web_url('order',array('op'=>'aftersale_detail','order_good_id'=>$goods['order_id'],'type'=>'good','orderid'=>$order['id']));
							echo "<a href='{$url}' class='label label-warning'>退货详情</a>";
						}else{
							echo '无';
						}
					?>
				</td>
				
			</tr>
			<?php  } } ?>
		</table>
				<table class="table table-hover">
		<tr>
		     <td>订单标记:</td>
			 <td>
			    <?php for ( $i = 0; $i <=5 ; $i++ ){ $checked = ($order['tag'] == $i)?"checked":"";?>
			       <label class="radio-inline">
                     <input <?php echo  $checked; ?> type="radio" name="tag" id="tag<?php echo $i; ?>" value="<?php echo $i; ?>"><img src="images/tag<?php echo $i; ?>.png" />
                   </label> 
				 <?php } ?>
			 </td>
		</tr>
		<tr>

				<td >订单备注：</td>
				<td>
					<textarea style="height:150px;" class="span7" name="retag" cols="70"><?php  if(!empty($order['retag'])){ $retag = json_decode($order['retag'],true); echo $retag['beizhu']; }?></textarea>
				</td>	
					</table>
		<table class="table" >
			<tr>
				<th  style="width:50px"></th>
				<td>
				    <button type="submit" class="btn btn-danger span2" onclick="return confirm('确认更新此订单备注吗？'); return false;" name="reset" value="reset">确认备注</button>

					<?php  if($order['status']==0 && isHasPowerToShow('shop','order','confrimpay')) { ?>
						<a href="javascript:;" class="btn btn-danger span2" data-toggle="modal" data-target="#modal-surepay">确认付款</a>
					<?php  } ?>

					<?php  if($order['status']==1 && isHasPowerToShow('shop','order','return_order')) { ?>
						<a href="<?php echo web_url('order',array('op'=>'return_order','id'=>$_GP['id']));?>" class="btn btn-danger span2">订单退货</a>
					<?php  } ?>

					<?php if($order['status'] == 1 && isHasPowerToShow('shop','order','confirmsend') && $ishowSendBtn) { ?>
						<a type="button" class="btn btn-primary span2" name="confirmsend" data-toggle="modal" data-target="#modal-confirmsend" value="confirmsend">确认发货</a>
					
					<?php  } ?>

					
					<?php  if($order['status'] ==2 && isHasPowerToShow('shop','order','finish')) { ?>
					<a href="<?php echo web_url('order',array('op'=>'finish','id'=>$order['id']));?>" class="btn btn-success span2" onclick="return confirm('确认完成此订单吗？'); return false;" name="finish">完成订单</a>
				   <?php  } ?>


					<?php  if(($order['status']==0||($order['status']<-1&&$order['status']>-5)) && isHasPowerToShow('shop','order','close')) { ?>
					<a href="<?php echo web_url('order',array('op'=>'close','id'=>$order['id']));?>" class="btn btn-danger span2" name="close" onclick="return confirm('永久关闭此订单吗？'); return false;" >关闭订单</a>
					<?php  } ?>

					<?php  if($order['status'] ==-1 && isHasPowerToShow('shop','order','open')) { ?>
						<a href="<?php echo web_url('order',array('op'=>'open','id'=>$order['id']));?>" class="btn btn-success span2" onclick="return confirm('确认开启此订单吗？'); return false;" name="open">开启订单</a>
					<?php  } ?>

				</td>
			</tr>
		</table>
	</form>

<!--确认发货-->
     <form action="<?php echo web_url('order',array('op'=>'confirmsend','id'=>$order['id']));?>" method="post" enctype="multipart/form-data" class="form-horizontal" >
		<div id="modal-confirmsend" class="modal  fade">
		  	<div class="modal-dialog">
				<div class="modal-content">
			  		<div class="modal-header">
						<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
						<h4 class="modal-title">快递信息</h4>
			  		</div>
				    <div class="modal-body">
      	
						  <div class="form-group">
							<label class="col-sm-3 control-label no-padding-left" > 快递公司：</label>

							<div class="col-sm-5">
								<select name="express" id='express' class="form-control">
										<option value="-1" data-name="">请选择快递</option>
										<?php   foreach($dispatchlist as $dispatchitem) { ?>
										<option value="<?php echo $dispatchitem['code'];?>" data-name="<?php echo $dispatchitem['name'];?>"><?php echo $dispatchitem['name'];?></option>
										<?php   } ?>
								</select>
								 <input type='hidden' class='input span3' name='expresscom' id='expresscom'  />
							</div>
						  </div>
      	
						  <div class="form-group">
								<label class="col-sm-3 control-label no-padding-left" > 快递单号：</label>
								<div class="col-sm-5">
									<input type="text" name="expresssn" class="span5 form-control" />
								</div>
						  </div>
      	
					</div>
				   <div class="modal-footer">
					 <button type="submit" class="btn btn-primary" name="confirmsend" value="yes">确认发货</button>

					 <button type="button" class="btn btn-default" data-dismiss="modal">关闭窗口</button>
				   </div>
    			</div><!-- /.modal-content -->
  			</div><!-- /.modal-dialog -->
		</div>
	</form>

<!--确认支付-->
<?php  if($order['status']==0 && isHasPowerToShow('shop','order','confrimpay')) { ?>
<form action="<?php echo web_url('order',array('op'=>'confrimpay','id'=>$order['id']));?>" method="post" enctype="multipart/form-data" class="form-horizontal" >
	<div id="modal-surepay" class="modal  fade">
		<div class="modal-dialog">
			<div class="modal-content">
				<div class="modal-header">
					<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
					<h4 class="modal-title">操作理由</h4>
				</div>
				<div class="modal-body">
					<div class="form-group">
						<label class="col-sm-3 control-label no-padding-left" > 操作理由：</label>
						<div class="col-sm-5">
							<input type="text" name="payreason" class="span5 form-control" value="测试订单"/>
						</div>
					</div>

				</div>
				<div class="modal-footer">
					<button type="submit" class="btn btn-primary" name="surepay" value="yes">确认支付</button>

					<button type="button" class="btn btn-default" data-dismiss="modal">关闭窗口</button>
				</div>
			</div><!-- /.modal-content -->
		</div><!-- /.modal-dialog -->
	</div>
</form>
<?php } ?>

<!--修改收货人信息-->
<form action="<?php echo web_url('order',array('op'=>'modifyaddress','id'=>$order['id']));?>" method="post" enctype="multipart/form-data" class="form-horizontal" >
	<div id="modal-address" class="modal  fade">
		<div class="modal-dialog">
			<div class="modal-content">
				<div class="modal-header">
					<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
					<h4 class="modal-title">修改收货人信息</h4>
				</div>
				<div class="modal-body">
					<div class="form-group">
						<label class="col-sm-3 control-label no-padding-left" > 姓名：</label>
						<div class="col-sm-5">
							<input type="text" name="address_realname" class="form-control span5" value="<?php echo $order['address_realname'];?>" />
						</div>
					</div>

					<div class="form-group">
						<label class="col-sm-3 control-label no-padding-left" > 联系电话：</label>
						<div class="col-sm-5">
							<input type="text" name="address_mobile" class="form-control span5" value="<?php echo $order['address_mobile'];?>"/>
						</div>
					</div>
					<div class="form-group">
						<label class="col-sm-3 control-label no-padding-left" > 省份：</label>
						<div class="col-sm-5">
							<input type="text" name="address_province" class="form-control span5"  value="<?php echo $order['address_province'];?>"/>
						</div>
					</div>
					<div class="form-group">
						<label class="col-sm-3 control-label no-padding-left" > 城市：</label>
						<div class="col-sm-5">
							<input type="text" name="address_city" class="form-control span5"  value="<?php echo $order['address_city'];?>"/>
						</div>
					</div>
					<div class="form-group">
						<label class="col-sm-3 control-label no-padding-left" > 地区：</label>
						<div class="col-sm-5">
							<input type="text" name="address_area" class="form-control span5"  value="<?php echo $order['address_area'];?>"/>
						</div>
					</div>
					<div class="form-group">
						<label class="col-sm-3 control-label no-padding-left" > 地址：</label>
						<div class="col-sm-5">
							<input type="text" name="address_address" class="form-control span5"  value="<?php echo $order['address_address'];?>"/>
						</div>
					</div>
				</div>
				<div class="modal-footer">
					<button type="submit" class="btn btn-primary" name="" value="yes">确认修改</button>

					<button type="button" class="btn btn-default" data-dismiss="modal">关闭窗口</button>
				</div>
			</div><!-- /.modal-content -->
		</div><!-- /.modal-dialog -->
	</div>
</form>

<script language='javascript'>
	function jsSelectIsExitItem(objSelect,objItemValue)   
{   
     var isExit = false ;   
     for ( var i=0;objSelect.length>i;i++)   
     {   
         if (objSelect.options[i].value == objItemValue)   
         {   
             isExit = true ;   
             break ;   
         }   
     }        
     return isExit;   
}
				if(jsSelectIsExitItem(document.getElementById("express"),"<?php  echo $order['dispatchexpress'];?>"))
				{
			document.getElementById("express").value='<?php  echo $order['dispatchexpress'];?>';	
				}
     $(function(){
         <?php  if(!empty($express)) { ?>
             $("#express").val("<?php  echo $express['express_url'];?>");
             $("#expresscom").val(  $("#express").find("option:selected").attr("data-name"));
         <?php  } ?>
             
        $("#express").change(function(){
          
            var obj = $(this);  
            var sel =obj.find("option:selected").attr("data-name");
            $("#expresscom").val(sel);
        });
      
    })
    $(".return").click(function(){
		window.history.back();
	})
</script>