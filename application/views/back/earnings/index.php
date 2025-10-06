<!--CONTENT CONTAINER-->
<!--===================================================-->
<div id="content-container">
	<div id="page-head">
		<!--Page Title-->
		<!--~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~-->
		<div id="page-title">
			<h1 class="page-header text-overflow"><?php echo translate('earnings')?></h1>
		</div>
		<!--~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~-->
		<!--End page title-->
		<!--Breadcrumb-->
		<!--~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~-->
		<ol class="breadcrumb">
			<li><a href="<?=base_url()?>admin"><?php echo translate('home')?></a></li>
			<li class="active"><a href="#"><?php echo translate('earnings')?></a></li>
		</ol>
		<!--~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~-->
		<!--End breadcrumb-->
	</div>
	<!--Page content-->
	<!--===================================================-->
	<div id="page-content">
		<!-- Basic Data Tables -->
		<!--===================================================-->
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
	            </div>
			<?php endif ?>
			<div class="panel-heading">
				<h3 class="panel-title"><?php echo translate('earnings_list')?></h3>
			</div>
			<form class="form-group" method="POST" action="<?php echo base_url('admin/earnings')?>">
				<div class="row">
					<div class="col-md-4 col-md-4 col-md-4">
						<input type="radio" name="earningStatus" class="container_radio" id="container_all" value="all" <?php if(!empty($this->session->userdata('earning_status'))) { if($this->session->userdata('earning_status') == 'all') {echo 'checked'; }} ?>>All
					</div>
				
					<div class="col-md-4 col-md-4 col-md-4" style="margin-left: -159px; margin-top: 23px;">
						<input type="radio" name="earningStatus" class="container_radio" id="container_yes" value="paid" <?php if(!empty($this->session->userdata('earning_status'))) { if($this->session->userdata('earning_status') == 'paid') {echo 'checked'; }} ?>>Paid
					</div>
				
					<div class="col-md-4 col-md-4 col-md-4" style="margin-left: -184px; margin-top: 23px;">
						<input type="radio" name="earningStatus" class="container_radio" id="container_no" value="due" <?php if(!empty($this->session->userdata('earning_status'))) { if($this->session->userdata('earning_status') == 'due') {echo 'checked'; }} ?>>Due
					</div>
				</div>
			</form>
			
			<!-- Bulk Action Section -->
			<div class="panel-body">
				<div class="row" style="margin-bottom: 15px;">
					<div class="col-md-12">
						<button type="button" class="btn btn-primary" id="proceed_to_cart" disabled>
							<i class="fa fa-shopping-cart"></i> Proceed to Payment Cart (<span id="selected_count">0</span>)
						</button>
						<button type="button" class="btn btn-default" id="clear_selection" disabled>
							<i class="fa fa-times"></i> Clear Selection
						</button>
					</div>
				</div>
				
				<div class="row">
					<table id="earnings_table" class="table table-striped table-bordered" cellspacing="0" width="100%">
						<thead>
						<tr>
							<th width="5%">
								<input type="checkbox" id="select_all">
							</th>
							<th width="10%">#</th>
							<th><?php echo translate('earning_name')?></th>
							<th><?php echo translate('date')?></th>
							<th><?php echo translate('payment_type')?></th>
							<th><?php echo translate('amount')?></th>
							<th><?php echo translate('package')?></th>
							<th><?php echo translate('status')?></th>
							<th width="20%" data-sortable="false"><?php echo translate('options')?></th>
						</tr>
						</thead>
					</table>
				</div>
			</div>
		</div>
		<!--===================================================-->
		<!-- End Striped Table -->
	</div>
	<!--===================================================-->
	<!--End page content-->
</div>

<style>
	#validation_info p {
		margin: 0px;
		color: #DE1B1B;
	}
	#container_all{
		margin-left: 22px;
		margin-top: 28px;
	}
	#button{
		margin-left: 29px;
		margin-top: 16px;
	}
	.earning-checkbox {
		cursor: pointer;
		width: 18px;
		height: 18px;
	}
	#select_all {
		cursor: pointer;
		width: 18px;
		height: 18px;
	}
</style>

<!--Default Bootstrap Modal-->
<!--===================================================-->
<div class="modal fade" id="earnings_modal" role="dialog" tabindex="-1" aria-labelledby="earnings_modal" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <!--Modal header-->
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal"><i class="pci-cross pci-circle"></i></button>
                <h4 class="modal-title" id="modal_title"></h4>
            </div>
            <!--Modal body-->
			<div id="modal_body"></div>
        </div>
    </div>
</div>

<div class="modal fade" id="delete_modal" role="dialog" tabindex="-1" aria-labelledby="delete_modal" aria-hidden="true">
    <div class="modal-dialog" style="width: 400px;">
        <div class="modal-content">
            <!--Modal header-->
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal"><i class="pci-cross pci-circle"></i></button>
                <h4 class="modal-title"><?php echo translate('confirm_delete')?></h4>
            </div>
           	<!--Modal body-->
            <div class="modal-body">
            	<p><?php echo translate('are_you_sure_you_want_to_delete_this_data?')?></p>
            	<div class="text-right">
            		<button data-dismiss="modal" class="btn btn-default btn-sm" type="button" id="modal_close"><?php echo translate('close')?></button>
                	<button class="btn btn-danger btn-sm" id="delete_earning" value=""><?php echo translate('delete')?></button>
            	</div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="accept_payment_modal" role="dialog" tabindex="-1" aria-labelledby="delete_modal" aria-hidden="true">
    <div class="modal-dialog" style="width: 400px;">
        <div class="modal-content">
            <!--Modal header-->
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal"><i class="pci-cross pci-circle"></i></button>
                <h4 class="modal-title"><?php echo translate('accept_manual_payment')?></h4>
            </div>
           	<!--Modal body-->
            <div class="modal-body">
            	<p><?php echo translate('are_you_sure_you_want_to_accept_this_payment?')?></p>
            	<div class="text-right">
					<button class="btn btn-success btn-sm" id="package_payment_id" value=""><?php echo translate('yes')?></button>
            		<button data-dismiss="modal" class="btn btn-default btn-sm" type="button" id="modal_close"><?php echo translate('close')?></button>
            	</div>
            </div>
        </div>
    </div>
</div>

<!--===================================================-->
<script>
	setTimeout(function() {
	    $('#success_alert').fadeOut('fast');
	    $('#danger_alert').fadeOut('fast');
	}, 5000);
</script>

<script>
	// Store selected items
	var selectedItems = [];
	
	$(document).ready(function () {
		$('.container_radio').on('click',function(){
			$(this).closest("form").submit();
        });
    });

	$(document).ready(function () {
		$('#earnings_table').DataTable({
			"processing": true,
			"serverSide": true,
			"ajax":{
				"url": "<?php echo base_url('admin/earnings/list_data') ?>",
				"dataType": "json",
				"type": "POST",
				"data":{'<?php echo $this->security->get_csrf_token_name(); ?>' : '<?php echo $this->security->get_csrf_hash(); ?>' }
			},
			"columns": [
				{ "data": "checkbox", "orderable": false },
				{ "data": "#" },
				{ "data": "member_name" },
				{ "data": "date" },
				{ "data": "payment_type" },
				{ "data": "amount" },
				{ "data": "package" },
				{ "data": "status" },
				{ "data": "options" },
			],
			"drawCallback": function( settings ) {
				$('.add-tooltip').tooltip();
				updateCheckboxStates();
			}
		});
	});

	// Select All Checkbox
	$(document).on('change', '#select_all', function() {
		var isChecked = $(this).is(':checked');
		$('.earning-checkbox:visible').prop('checked', isChecked);
		
		if (isChecked) {
			$('.earning-checkbox:visible').each(function() {
				addToSelection($(this));
			});
		} else {
			selectedItems = [];
		}
		updateButtonStates();
	});

	// Individual Checkbox
	$(document).on('change', '.earning-checkbox', function() {
		if ($(this).is(':checked')) {
			addToSelection($(this));
		} else {
			removeFromSelection($(this).data('id'));
		}
		updateButtonStates();
	});

	function addToSelection(checkbox) {
		var id = checkbox.data('id');
		var amount = checkbox.data('amount');
		var memberName = checkbox.data('member');
		var packageName = checkbox.data('package');
		var status = checkbox.data('status');
		
		// Only add due payments
		if (status !== 'due') {
			checkbox.prop('checked', false);
			alert('Only due payments can be selected for bulk payment!');
			return;
		}
		
		// Check if already exists
		var exists = selectedItems.find(item => item.id === id);
		if (!exists) {
			selectedItems.push({
				id: id,
				amount: parseFloat(amount),
				memberName: memberName,
				packageName: packageName
			});
		}
	}

	function removeFromSelection(id) {
		selectedItems = selectedItems.filter(item => item.id !== id);
	}

	function updateButtonStates() {
		var count = selectedItems.length;
		$('#selected_count').text(count);
		
		if (count > 0) {
			$('#proceed_to_cart').prop('disabled', false);
			$('#clear_selection').prop('disabled', false);
		} else {
			$('#proceed_to_cart').prop('disabled', true);
			$('#clear_selection').prop('disabled', true);
		}
	}

	function updateCheckboxStates() {
		selectedItems.forEach(function(item) {
			$('.earning-checkbox[data-id="' + item.id + '"]').prop('checked', true);
		});
	}

	// Clear Selection
	$('#clear_selection').click(function() {
		selectedItems = [];
		$('.earning-checkbox').prop('checked', false);
		$('#select_all').prop('checked', false);
		updateButtonStates();
	});

	// Proceed to Cart
	$('#proceed_to_cart').click(function() {
		if (selectedItems.length === 0) {
			alert('Please select at least one payment!');
			return;
		}
		
		// Store in session and redirect
		$.ajax({
			url: "<?=base_url()?>admin/earnings/store_cart_items",
			type: "POST",
			data: {
				items: JSON.stringify(selectedItems),
				'<?php echo $this->security->get_csrf_token_name(); ?>': '<?php echo $this->security->get_csrf_hash(); ?>'
			},
			success: function(response) {
				window.location.href = "<?=base_url()?>admin/earnings/payment_cart";
			},
			error: function(error) {
				alert('Error storing cart items!');
			}
		});
	});
</script>

<script>
	function get_detail(id) {
		$("#modal_title").html("<?=translate('payment_details')?>");
		$("#modal_body").html("<div class='text-center'><i class='fa fa-refresh fa-5x fa-spin'></i></div>");
		$.ajax({
			url: "<?=base_url()?>admin/earnings/view_detail/"+id,
			success: function(response) {
				$("#modal_body").html(response);
			},
			fail: function (error) {
				alert(error);
			}
		});
	}

	function delete_earning(id){
		$("#delete_earning").val(id);
	}
	
	$("#delete_earning").click(function(){
		$.ajax({
			url: "<?=base_url()?>admin/earnings/delete/"+$("#delete_earning").val(),
			success: function(response) {
				window.location.href = "<?=base_url()?>admin/earnings";
			},
			fail: function (error) {
				alert(error);
			}
		});
	})

	$("#package_payment_id").click(function(){
		$.ajax({
			url: "<?=base_url()?>admin/earnings/accept_payment/"+$("#package_payment_id").val(),
			success: function(response) {
				window.location.href = "<?=base_url()?>admin/earnings";
			},
			fail: function (error) {
				alert(error);
			}
		});
	})
</script>