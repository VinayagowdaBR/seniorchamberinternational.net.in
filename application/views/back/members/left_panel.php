<?php                                                                   
	$member_id = $value->member_id;
	$member_data = $this->db->select('*')->from('member')->where(array('member_id'=>$member_id))->get()->row_array();
?> 

<div class="fixed-sm-200 fixed-lg-250 pull-sm-left">
	<div class="panel">
		
		<!-- Simple profile -->
		<div class="text-center pad-all bord-btm">
			<div class="pad-ver">
				<img src="<?=base_url()?>uploads/profile_image/<?=$image[0]['profile_image']?>" class="img-lg img-border img-circle" alt="Profile Picture">
			</div>
			<h4 class="text-lg text-overflow mar-no"><?=$value->first_name." ".$value->last_name?></h4>
			<p class="text-sm">
				<?=$education_and_career[0]['occupation']?>
			</p>
			<p class="text-sm">
				<?=$this->Crud_model->get_type_name_by_id('state', $present_address[0]['state']);?>, <?=$this->Crud_model->get_type_name_by_id('country', $present_address[0]['country']);?>
			</p>
			<div class="pad-ver btn-groups">
			   			    
			    <!--left_panel kundali and video start by Pooja-->
      <!--  <?php if($member_data['membership']=='2'): ?>-->
			   <!--<a href="#" id="demo-dt-delete-btn" data-target='#kundali_modal' data-toggle='modal' class="btn btn-primary btn-sm add-tooltip" data-toggle="tooltip" data-placement="top" title="Kundali" onclick='view_kundali(<?=$value->member_id?>)'><i class="fa fa-bullseye"></i></a>-->
			   <!-- <a href="#" id="demo-dt-delete-btn" data-target='#video_modal' data-toggle='modal' class="btn btn-success btn-sm add-tooltip" data-toggle="tooltip" data-placement="top" title="Video" onclick='view_video(<?=$value->member_id?>)'><i class="fa fa-play-circle-o"></i></a>-->
      <!--  <?php endif;?>-->
			  
			    <!--left_panel kundali and video stop--> 
			    <!--left_panel kundali and video stop--> 
				<a href="#" id="demo-dt-delete-btn" data-target='#package_modal' data-toggle='modal' class="btn btn-info btn-sm add-tooltip" data-toggle="tooltip" data-placement="top" title="Packages" onclick='view_package(<?=$value->member_id?>)'><i class="fa fa-object-ungroup"></i></a>
				<a href="<?=base_url()?>admin/members/<?=$parameter?>/id_card/<?=$value->member_id?>" class="btn btn-warning btn-sm btn-labeled fa fa-id-card" target="_blank">ID Card</a>
				<?php
				if ($value->is_blocked == 'no') {
				?>
				<a href="#" id="demo-dt-block-btn" data-target='#block_modal' data-toggle='modal' class="btn btn-dark btn-sm add-tooltip" data-toggle="tooltip" data-placement="top" title="<?=translate('block_user')?>" onclick="block('<?=$value->is_blocked?>', <?=$value->member_id?>)"><i class="fa fa-ban"></i></a>
				<?php
				}
				else {
				?>
				<a href="#" id="demo-dt-block-btn" data-target='#block_modal' data-toggle='modal' class="btn btn-success btn-sm add-tooltip" data-toggle="tooltip" data-placement="top" title="<?=translate('unblock_user')?>" onclick="block('<?=$value->is_blocked?>', <?=$value->member_id?>)"><i class="fa fa-check"></i></a>
				<?php
				}
				?>
			</div>
			<div class="profile-stats clearfix" style="padding: 15px;">
        <div class="stats-entry col-sm-12 text-center">
          <span class="stats-count"><?=$value->follower?></span><br>          
          <span class="stats-label text-uppercase"><?php echo translate('follower(s)')?> <?=$this->lang->line('followers'); ?></span>
        </div>
    	</div>

    	<div class="text-center">
    		<?php if ($value->is_closed == "yes")
					{
						echo "<span class='badge badge-danger' >".translate('closed_account')."</span>";
					}
				elseif ($value->is_closed == "no") 
					{
						echo "<span class='badge badge-success' >".translate('active_account')."</span>";
					} ?>
      </div>
	
		</div>
		<!--<div class="text-center">-->
		<!--<?php if($parameter=='free_members'): ?>-->
		<!--	<a  href="<?=base_url()?>admin/members/<?=$parameter?>/starmatching/<?=$value->member_id?>" class="btn btn-primary btn-sm btn-labeled fa fa-star" type="button" target="_blank">Check Star Compatibility<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;ನಕ್ಷತ್ರ ಹೊಂದಾಣಿಕೆ</a>-->
  <!--      <?php endif;?>-->
		<!--<?php if($parameter=='premium_members'): ?>-->
		<!--	<a  href="<?=base_url()?>admin/members/<?=$parameter?>/starmatching/<?=$value->member_id?>" class="btn btn-primary btn-sm btn-labeled fa fa-star" type="button" target="_blank">Check Star Compatibility<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;ನಕ್ಷತ್ರ ಹೊಂದಾಣಿಕೆ</a>-->
  <!--     <br><br>	<a  href="http://familytree.edigamatchmaker.com/" class="btn btn-primary btn-sm btn-labeled fa fa-star" type="button" target="_blank">Family Tree<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;ವಂಶ ವೃಕ್ಷ</a>-->
  <!--      <?php endif;?>-->
	
	  <!-- <button class="btn btn-primary" style="width:200px"><i class="fa fa-star"></i> Check Star Compatibility<br>ನಕ್ಷತ್ರ ಹೊಂದಾಣಿಕೆ</button>	 -->
  <!--    </div>-->

		<!--view video and kundali start(by Pooja)--->

		<?php if($member_data['membership']==2):?>
            
    <!--<div class="panel-heading">-->
    <!--  <h3 class="panel-title text-center"><?php echo translate('Birth Kundali and Video')?></h3>-->
    <!--  <p class="text-center" style="margin-top:-14px;"><?=$this->lang->line('birth_kundali_video'); ?></p>-->
    <!--</div>-->

    <div class="panel-body">
    	<table class="table">
    		<?php if(!empty($member_data['image'])){?>
    			<b><?php echo translate('Kundali')?></b>
  			<?php 
  				$path_info = pathinfo($member_data['image']);
          $extension = $path_info['extension']; 
        ?>

        <div class="pad-ver">
        	<?php if($extension == 'pdf') {?>
        	
	        	<div class="iframe">
	        		<a href="<?php echo base_url()?>uploads/kundali_image/<?php echo $member_data['image'];?>" target="imgbox">
	        			<iframe src="https://docs.google.com/gview?url=<?php echo base_url()?>uploads/kundali_image/<?php echo $member_data['image'];?>&embedded=true" alt="image" width="200px"  height="250px" scrolling="no" style="overflow: hidden"></iframe>
	        		</a>
	        		<a href="<?php echo base_url()?>uploads/kundali_image/<?php echo $member_data['image'];?>">Link to PDF (fallback)</a>                            
	          </div>

	        <?php } else {?>
	          <div class="iframe">
	          	<a href="<?php echo base_url()?>uploads/kundali_image/<?php echo $member_data['image'];?>" target="imgbox">
	          		<img src="<?php echo base_url()?>uploads/kundali_image/<?php echo $member_data['image'];?>" alt="image" width="200px" height="250px" scrolling="no" style="overflow: hidden">
	          	</a>
	          	</div>
	        
	        <?php } ?>
	        <a href="<?php echo base_url()?>uploads/kundali_image/<?php echo $member_data['image'];?>" download ><br>
	        	<button class="btn1" style="width:200px"><i class="fa fa-download"></i> Download</button>
	        </a>
	        </div>
	      <?php } ?>

	      <?php if(!empty($member_data['video'])){?>
	      	<b><?php echo translate('Video')?></b>
					<div class="pad-ver">
						<video width="200" height="150" controls>
							<source src="<?php echo base_url()?>uploads/video/<?php echo $member_data['video']?>">
						</video>
					</div>
				<?php }?>
			</table>
			
				<!--<a href="http://familytree.edigamatchmaker.com/" class="btn btn-info" role="button">Family Tree</a>-->
			</div>
		
		<?php endif;?>      
		<!--view video and kundali stop--->
	</div>
</div>

<style>
	.btn1 {
  	background-color: DodgerBlue;
	  border: none;
	  color: white;
	  padding: 12px 30px;
	  cursor: pointer;
	  font-size: 10px;
	}	
</style>

<script type="text/javascript">
	function image(img) {
    var src = img.src;
    window.open(src);
	}
</script>


