<!--Magic Checkbox [ OPTIONAL ]-->
<link href="<?=base_url()?>template/back/plugins/magic-check/css/magic-check.min.css" rel="stylesheet">

<!--CONTENT CONTAINER-->
<!--===================================================-->
<div id="content-container">
	<div id="page-head">
		<!--Page Title-->
		<!--~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~-->
		<div id="page-title">
			<h1 class="page-header text-overflow"><?php echo translate('edit_staff');?></h1>
			<!--Searchbox-->
			<div class="searchbox">
				<div class="pull-right">
					<a href="<?=base_url()?>admin/admins" class="btn btn-danger btn-sm btn-labeled fa fa-step-backward" type="submit"><?php echo translate('go_back')?></a>
				</div>
			</div>
		</div>
		<!--~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~-->
		<!--End page title-->
		<!--Breadcrumb-->
		<!--~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~-->
		<ol class="breadcrumb">
			<li><a href="#"><?php echo translate('home')?></a></li>
			<li><a href="#"><?php echo translate('staff_panel')?></a></li>
			<li><a href="#"><?php echo translate('manage_staff')?></a></li>
			<li class="active"><?php echo translate('edit_staff')?></li>
		</ol>
		<!--~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~-->
		<!--End breadcrumb-->
	</div>
	<div id="page-content">
		<div class="panel">
			<?php if (!empty($success_alert)): ?>
				<div class="alert alert-success" id="success_alert" style="display: block">
	                <button class="close" data-dismiss="alert"><i class="pci-cross pci-circle"></i></button>
	                <?=$success_alert?>
	            </div>
			<?php endif ?>
			<?php if (!empty($danger_alert)): ?>
				<div class="alert alert-danger" id="danger_alert" style="display: block">
	                <button class="close" data-dismiss="alert"><i class="pci-cross pci-circle"></i></button>

	                <?=$danger_alert?>
	                 <?=validation_errors()?>
	            </div>
			<?php endif ?>
			
		    <div class="panel-heading">
		        <h3 class="panel-title"><?= translate('edit_staff')?></h3>
		    </div>
		    <div class="panel-body">
		    	<?php
        			foreach ($admin_data as $row) {
        		?>
		    		<form class="form-horizontal" id="manage_details_form" method="POST" action="<?=base_url()?>admin/admins/update/<?=$row['admin_id']?>">
						<div class="form-group">
							<label class="col-sm-3 control-label" for="name"><b><?= translate('name')?> <span class="text-danger">*</span></b></label>
							<div class="col-sm-8">
								<input type="text" class="form-control" value="<?php if(!empty($form_contents)){echo $form_contents['name'];}else{ echo $row['name']; }?>" name="name" placeholder="<?= translate('staff_name')?>" >
							</div>
						</div>
						<div class="form-group">
							<label class="col-sm-3 control-label" for="description"><b><?= translate('email')?> <span class="text-danger">*</span></b></label>
							<div class="col-sm-8">
								<input type="text" class="form-control" value="<?php if(!empty($form_contents)){echo $form_contents['email'];}else{ echo $row['email']; }?>" name="email" placeholder="<?= translate('staff_email')?>" >
								
							</div>
						</div>
						<div class="form-group">
							<label class="col-sm-3 control-label" for="description"><b><?= translate('phone_no.')?> <span class="text-danger">*</span></b></label>
							<div class="col-sm-8">
								<input type="text" class="form-control" value="<?php if(!empty($form_contents)){echo $form_contents['phone'];}else{ echo $row['phone']; }?>" name="phone" placeholder="<?= translate('staff_phone_no.')?>" >
								
							</div>
						</div>
						<div class="form-group">
							<label class="col-sm-3 control-label" for="description"><b><?= translate('address')?> </span></b></label>
							<div class="col-sm-8">
								<textarea class="form-control" name="address" placeholder="<?= translate('staff_address')?>" ><?php if(!empty($form_contents)){echo $form_contents['address'];}else{ echo $row['address']; }?></textarea>
								
							</div>
						</div>
						<div class="form-group">
							<label class="col-sm-3 control-label" for="role"><b><?= translate('role')?> <span class="text-danger">*</span></b></label>
							<div class="col-sm-8">
								<?php if (!empty($form_contents)) {
										echo $this->Crud_model->select_html('role', 'role', 'name', 'edit', 'form-control form-control-sm selectpicker', $form_contents['role'], '', '', '');
									}else{
										echo $this->Crud_model->select_html('role', 'role', 'name', 'edit', 'form-control form-control-sm selectpicker', $row['role'], '', '', '');
									}
								?>
							</div>
						</div>
						
						<?php $selected_area = !empty($form_contents['area']) ? $form_contents['area'] : (isset($existing_area_id) ? $existing_area_id : ''); ?>
						<div class="form-group">
							<label class="col-sm-3 control-label"><b>Area <span class="text-danger">*</span></b></label>
							<div class="col-sm-8">
								<select id="area" name="area" class="form-control" onchange="getLegions(this.value)">
									<option value="">Select Area</option>
									<?php foreach($areas as $area): ?>
										<option value="<?= $area['id'] ?>" <?= ($area['id'] == $selected_area) ? 'selected' : '' ?>>
											<?= $area['name'] ?>
										</option>
									<?php endforeach; ?>
								</select>
							</div>
						</div>

						<input type="hidden" id="existing_legion_id" value="<?= !empty($form_contents['legion_id']) ? $form_contents['legion_id'] : (isset($existing_legion_id) ? $existing_legion_id : '') ?>">

						<!-- Legion dropdown -->
						<div class="form-group">
							<label class="col-sm-3 control-label"><b>Legion <span class="text-danger">*</span></b></label>
							<div class="col-sm-8">
								<select id="legion" name="legion_id" class="form-control">
									<option value="">Select Legion</option>
									<!-- Legions will be loaded dynamically -->
								</select>
							</div>
						</div>
						
						<div class="form-group">
							<div class="col-sm-offset-3 col-sm-8 text-right">
								<button type="submit" class="btn btn-primary btn-sm btn-labeled fa fa-save">Save</button>
							</div>
						</div>
					</form>
					<?php } ?>								
		    </div>	
		</div>
	</div>
</div>
<script>
	setTimeout(function() {
	    $('#success_alert').fadeOut('fast');
	    $('#danger_alert').fadeOut('fast');
	}, 5000); // <-- time in milliseconds
	
	function getLegions(areaId, selectLegionId = null) {
		if (areaId === '') {
			document.getElementById('legion').innerHTML = '<option value="">Select Legion</option>';
			return;
		}

		fetch("<?= base_url('admin/get_legions_of_area/') ?>" + areaId)
			.then(response => response.json())
			.then(data => {
				console.log("Fetched legions:", data); 

				let options = '<option value="">Select Legion</option>';
				let selected = selectLegionId ? selectLegionId : document.getElementById('existing_legion_id').value;
				
				data.forEach(function (legion) {
					let isSelected = (legion.id == selected) ? 'selected' : '';
					options += `<option value="${legion.id}" ${isSelected}>${legion.name}</option>`;
				});
				document.getElementById('legion').innerHTML = options;
			})
			.catch(error => {
				console.error('Error fetching legions:', error);
			});
	}

    // Load legions on page load if area is selected
    window.addEventListener('load', function() {
        var areaVal = document.getElementById('area').value;
        if(areaVal) {
            getLegions(areaVal);
        }
    });
</script>
