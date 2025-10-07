<!--CONTENT CONTAINER-->
<!--===================================================-->
<div id="content-container">
	<div id="page-head">
		<!--Page Title-->
		<!--~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~-->
		<div id="page-title">
			<h1 class="page-header text-overflow">
				<i class="fa fa-shopping-cart"></i> <?php echo translate('payment_cart')?>
			</h1>
		</div>
		<!--~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~-->
		<!--End page title-->
		<!--Breadcrumb-->
		<!--~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~-->
		<ol class="breadcrumb">
			<li><a href="<?=base_url()?>admin"><?php echo translate('home')?></a></li>
			<li><a href="<?=base_url()?>admin/earnings"><?php echo translate('earnings')?></a></li>
			<li class="active"><?php echo translate('payment_cart')?></li>
		</ol>
		<!--~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~-->
		<!--End breadcrumb-->
	</div>
	
	<!--Page content-->
	<!--===================================================-->
	<div id="page-content">
		<div class="row">
			<!-- Cart Items -->
			<div class="col-md-8">
				<div class="panel">
					<div class="panel-heading">
						<h3 class="panel-title">
							<i class="fa fa-list"></i> Selected Payments 
							<span class="badge badge-primary"><?php echo count($cart_items); ?> Items</span>
						</h3>
					</div>
					<div class="panel-body">
						<div class="table-responsive">
							<table class="table table-striped table-bordered">
								<thead>
									<tr>
										<th width="5%">#</th>
										<th>Member Name</th>
										<th>Package</th>
										<th>Amount</th>
										<th width="10%">Action</th>
									</tr>
								</thead>
								<tbody id="cart_items_body">
									<?php 
									$sl = 1;
									$total = 0;
									foreach($cart_items as $item): 
										$total += $item['amount'];
									?>
									<tr data-item-id="<?php echo $item['id']; ?>">
										<td><?php echo $sl++; ?></td>
										<td><?php echo $item['memberName']; ?></td>
										<td><?php echo $item['packageName']; ?></td>
										<td class="item-amount">
											<strong><?php echo currency('', 'def') . number_format($item['amount'], 2); ?></strong>
										</td>
										<td>
											<button class="btn btn-danger btn-xs remove-item" data-id="<?php echo $item['id']; ?>">
												<i class="fa fa-trash"></i> Remove
											</button>
										</td>
									</tr>
									<?php endforeach; ?>
								</tbody>
							</table>
						</div>
					</div>
				</div>
			</div>
			
			<!-- Cart Summary -->
			<div class="col-md-4">
				<div class="panel panel-primary">
					<div class="panel-heading">
						<h3 class="panel-title">
							<i class="fa fa-calculator"></i> Payment Summary
						</h3>
					</div>
					<div class="panel-body">
						<div class="row">
							<div class="col-xs-12">
								<h4>Order Details</h4>
								<hr style="margin: 10px 0;">
							</div>
						</div>
						
						<div class="row" style="margin-bottom: 10px;">
							<div class="col-xs-6">
								<strong>Total Items:</strong>
							</div>
							<div class="col-xs-6 text-right">
								<span id="total_items"><?php echo count($cart_items); ?></span>
							</div>
						</div>
						
						<div class="row" style="margin-bottom: 10px;">
							<div class="col-xs-6">
								<strong>Subtotal:</strong>
							</div>
							<div class="col-xs-6 text-right">
								<span id="subtotal"><?php echo currency('', 'def') . number_format($total, 2); ?></span>
							</div>
						</div>
						
						<hr style="margin: 15px 0;">
						
						<div class="row" style="margin-bottom: 20px;">
							<div class="col-xs-6">
								<h4 style="margin: 0;"><strong>Total Amount:</strong></h4>
							</div>
							<div class="col-xs-6 text-right">
								<h4 style="margin: 0; color: #28a745;">
									<strong id="grand_total"><?php echo currency('', 'def') . number_format($total, 2); ?></strong>
								</h4>
							</div>
						</div>
						
						<div class="row">
							<div class="col-xs-12">
								<button class="btn btn-success btn-block btn-lg" id="proceed_to_payment">
									<i class="fa fa-credit-card"></i> Proceed to PhonePe Payment
								</button>
								<a href="<?php echo base_url('admin/earnings'); ?>" class="btn btn-default btn-block" style="margin-top: 10px;">
									<i class="fa fa-arrow-left"></i> Back to Earnings
								</a>
							</div>
						</div>
					</div>
				</div>
				
				<!-- Payment Info -->
				<div class="panel panel-info">
					<div class="panel-heading">
						<h3 class="panel-title">
							<i class="fa fa-info-circle"></i> Payment Information
						</h3>
					</div>
					<div class="panel-body">
						<p><i class="fa fa-check-circle text-success"></i> Secure Payment via PhonePe</p>
						<p><i class="fa fa-check-circle text-success"></i> All payments are encrypted</p>
						<p><i class="fa fa-check-circle text-success"></i> Instant payment confirmation</p>
					</div>
				</div>
			</div>
		</div>
	</div>
	<!--===================================================-->
	<!--End page content-->
</div>

<style>
	.panel-primary {
		border-color: #337ab7;
	}
	.panel-primary > .panel-heading {
		background-color: #337ab7;
		border-color: #337ab7;
		color: white;
	}
	.panel-info > .panel-heading {
		background-color: #5bc0de;
		border-color: #5bc0de;
		color: white;
	}
	.item-amount {
		font-size: 16px;
		color: #28a745;
	}
	#grand_total {
		font-size: 24px;
	}
</style>


<script>
    var cartData = <?php echo json_encode($cart_items); ?>;
    
    $(document).ready(function() {
        // Remove item from cart
        $(document).on('click', '.remove-item', function() {
            var itemId = $(this).data('id');
            
            if (confirm('Are you sure you want to remove this item from cart?')) {
                cartData = cartData.filter(function(item) {
                    return item.id !== itemId;
                });
                
                $.ajax({
                    url: "<?=base_url()?>admin/earnings/store_cart_items",
                    type: "POST",
                    data: {
                        items: JSON.stringify(cartData),
                        '<?php echo $this->security->get_csrf_token_name(); ?>': '<?php echo $this->security->get_csrf_hash(); ?>'
                    },
                    success: function(response) {
                        $('tr[data-item-id="' + itemId + '"]').fadeOut(300, function() {
                            $(this).remove();
                            updateCartSummary();
                            
                            if (cartData.length === 0) {
                                alert('Cart is empty!');
                                window.location.href = "<?=base_url()?>admin/earnings";
                            }
                        });
                    },
                    error: function(xhr, status, error) {
                        console.log('Error:', error);
                        console.log('Response:', xhr.responseText);
                        alert('Error removing item from cart!');
                    }
                });
            }
        });
        
        // Proceed to payment - Fixed version
        $(document).on('click', '#proceed_to_payment', function() {
            if (cartData.length === 0) {
                alert('Cart is empty!');
                return;
            }
            
            var btn = $(this);
            btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Processing...');
            
            $.ajax({
                url: "<?=base_url()?>admin/earnings/process_bulk_payment",
                type: "POST",
                data: {
                    '<?php echo $this->security->get_csrf_token_name(); ?>': '<?php echo $this->security->get_csrf_hash(); ?>'
                },
                dataType: 'json',
                success: function(response) {
                    console.log('Response:', response);
                    if (response.status === 'success') {
                        window.location.href = response.redirect_url;
                    } else {
                        alert(response.message || 'Payment processing failed!');
                        btn.prop('disabled', false).html('<i class="fa fa-credit-card"></i> Proceed to PhonePe Payment');
                    }
                },
                error: function(xhr, status, error) {
                    console.log('Error:', error);
                    console.log('Response:', xhr.responseText);
                    alert('Error processing payment! Check console for details.');
                    btn.prop('disabled', false).html('<i class="fa fa-credit-card"></i> Proceed to PhonePe Payment');
                }
            });
        });
    });
    
    function updateCartSummary() {
        var total = 0;
        var count = cartData.length;
        
        cartData.forEach(function(item) {
            total += parseFloat(item.amount);
        });
        
        $('#total_items').text(count);
        $('#subtotal').text('<?php echo currency("", "def"); ?>' + total.toFixed(2));
        $('#grand_total').text('<?php echo currency("", "def"); ?>' + total.toFixed(2));
        
        $('#cart_items_body tr').each(function(index) {
            $(this).find('td:first').text(index + 1);
        });
    }
</script>
