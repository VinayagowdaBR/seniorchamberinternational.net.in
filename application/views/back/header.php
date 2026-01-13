<style>
/* for desktop */
.whatsapp_float {
	position:fixed;
	width:60px;
	height:60px;
	bottom:40px;
	right:40px;
	background-color:#25d366;
	color:#FFF;
	border-radius:50px;
	text-align:center;
        font-size:30px;
	box-shadow: 2px 2px 3px #999;
        z-index:100;
}

.whatsapp-icon {
	margin-top:16px;
}
/* for mobile */
@media screen and (max-width: 767px){
     .whatsapp-icon {
	 margin-top:10px;
     }
    .whatsapp_float {
        width: 40px;
        height: 40px;
        bottom: 20px;
        right: 10px;
        font-size: 22px;
    }
}
</style>
<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/font-awesome/4.5.0/css/font-awesome.min.css">
<a href="https://wa.me/919902406387" class="whatsapp_float" target="_blank"> <i class="fa fa-whatsapp whatsapp-icon"></i></a>



<!--NAVBAR-->
<!--===================================================-->
<header id="navbar">
<div id="navbar-container" class="boxed">
	<!--Brand logo & name-->
	<!--================================-->
	<div class="navbar-header">
		<a href="<?=base_url()?>admin" class="navbar-brand">
		<?php
            $favicon = $this->db->get_where('frontend_settings', array('type' => 'favicon'))->row()->value;
            $favicon = json_decode($favicon, true);
            if (!empty($favicon) && file_exists('uploads/favicon/'.$favicon[0]['image'])) {
        ?>
        		<img src="<?=base_url()?>uploads/favicon/<?=$favicon[0]['image']?>" alt="Active Matrimony Logo" class="brand-icon" style="padding: 5px">
                <!-- <link href="<?=base_url()?>uploads/favicon/<?=$favicon[0]['image']?>" rel="icon" type="image/png"> -->
        <?php
            }
            else {
        ?>
        		<img src="<?=base_url()?>uploads/favicon/default_image.png" alt="Active Matrimony Logo" class="brand-icon" style="padding: 5px">
                <!-- <link href="<?=base_url()?>uploads/favicon/default_image.png" rel="icon" type="image/png"> -->
        <?php
            }
        ?>

		<div class="brand-title hidden-sm hidden-xs">
			<span class=""><?=$this->system_name?></span>
		</div>
		</a>
	</div>
	<!--================================-->
	<!--End brand logo & name-->
	<!--Navbar Dropdown-->
	<!--================================-->
	<div class="navbar-content clearfix">
		<ul class="nav navbar-top-links pull-left">
			<!--Navigation toogle button-->
			<!--~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~-->
			<li class="tgl-menu-btn">
			<a class="mainnav-toggle" href="#">
			<i class="fa fa-bars" aria-hidden="true"></i>
			</a>
			</li>
			
			<!------ (19-3-21) ------->
			<?php
    $role_id = $this->session->userdata('role_id');
	log_message('info', 'User role_id: ' . $role_id . ', roleName: ' . ($roleName ?? 'Unknown'));
    if ($role_id === 2) {
        $roleName = 'President';
    } elseif ($role_id === 8) {
        $roleName = 'Secretary';
    } else {
        $roleName = null;
    }
?>

<?php if (!empty($roleName)): ?>
    <li style="margin-left: 25px; display: flex; align-items: center;">
        <span style="font-size: 16px; font-weight: 600; color: #fff;">
            <i class="fa fa-user-circle" style="margin-right: 6px;"></i> <?= $roleName ?>
        </span>
    </li>
<?php endif; ?>
			<!------ (19-3-21) ------->
			<!--~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~-->
			<!--End Navigation toogle button-->
		</ul>
		<?php if(demo()){ ?>
			<ul class="nav navbar-top-links pull-left">
				<p style="margin-top: 20px;margin-left: 20px;"><i class="text-danger blink_me fa fa-exclamation-triangle" style="font-size: 16px"></i> For Demo purpose some functionality &amp; all image uploads are DISABLED</p>
			</ul>
		<?php } ?>

		<ul class="nav navbar-top-links pull-right">
			<!--Language selector-->
			<!--~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~-->
			<li>
			<div class="lang-selected" style="margin-top:12px;">
				<a href="<?=base_url()?>home" target="_blank" class="btn btn-dark btn-sm" style="border-radius: 3px;"><i class="fa fa-desktop"></i> <?php echo translate('visit_home_page')?></a>
			</div>
			</li>
			<li id="dropdown-user" class="dropdown">
			<a href="#" data-toggle="dropdown" class="dropdown-toggle text-right">
			<span class="ic-user pull-right">
			<!--<img class="img-circle img-user media-object" src="<?=base_url()?>template/back/img/profile-photos/1.png" alt="Profile Picture">-->
			<i class="psi-administrator"></i>
			</span>
			<div class="username hidden-xs">
				<?=$this->session->userdata('admin_name')?>
			</div>
			</a>
			<div class="dropdown-menu dropdown-menu-md dropdown-menu-right panel-default">
				<!-- Dropdown heading  -->

				<!-- User dropdown menu -->
				<ul class="head-list">
					<li>
					<a href="<?=base_url()?>admin/manage_admin">
					<i class="demo-pli-gear icon-lg icon-fw"></i><?php echo translate('manage_profile')?></a>
					</li>
					<li>
					<a href="<?=base_url()?>admin/logout">
					<i class="demo-pli-unlock icon-lg icon-fw"></i> <?php echo translate('logout')?> </a>
					</li>
				</ul>
			</div>
			</li>
			<!--~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~-->
			<!--End user dropdown-->
		</ul>
	</div>
	<!--================================-->
	<!--End Navbar Dropdown-->
</div>
<style>
	.blink_me {
		animation: blinker 1.5s linear infinite;
	}
	@keyframes blinker {
		50% {
			opacity: 0;
		}
	}
</style>

<script>
	var myVar = setInterval(myTimer ,1000);
	function myTimer() {
	  var d = new Date();
	  var demo = document.getElementById("demo");
	  if (demo) {
	  	demo.innerHTML = d.toLocaleTimeString();
	  }
	}
</script>
</header>
<!--===================================================-->
<!--END NAVBAR-->
