<!--CONTENT CONTAINER-->
<!--===================================================-->
<div id="content-container">
	<div id="page-head">
		<!--Page Title-->
		<!--~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~-->
		<div id="page-title">
			<h1 class="page-header text-overflow">
				<i class="fa fa-mobile"></i> PhonePe Payment Gateway
			</h1>
		</div>
		<!--~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~-->
		<!--End page title-->
	</div>
	
	<!--Page content-->
	<!--===================================================-->
	<div id="page-content">
		<div class="row">
			<div class="col-md-6 col-md-offset-3">
				<div class="panel panel-primary">
					<div class="panel-heading">
						<h3 class="panel-title">
							<i class="fa fa-credit-card"></i> Complete Your Payment
						</h3>
					</div>
					<div class="panel-body text-center">
						<div style="padding: 30px;">
							<div style="margin-bottom: 30px;">
								<i class="fa fa-mobile" style="font-size: 80px; color: #5f259f;"></i>
							</div>
							
							<h3>Payment Details</h3>
							<hr>
							
							<div class="row" style="margin-bottom: 15px;">
								<div class="col-xs-6 text-left">
									<strong>Total Items:</strong>
								</div>
								<div class="col-xs-6 text-right">
									<?php 
									$payment_ids = json_decode($bulk_payment_data['payment_ids'], true);
									echo count($payment_ids); 
									?>
								</div>
							</div>
							
							<div class="row" style="margin-bottom: 20px;">
								<div class="col-xs-6 text-left">
									<strong>Total Amount:</strong>
								</div>
								<div class="col-xs-6 text-right">
									<h4 style="margin: 0; color: #28a745;">
										<strong><?php echo currency('', 'def') . number_format($bulk_payment_data['total_amount'], 2); ?></strong>
									</h4>
								</div>
							</div>
							
							<hr>
							
							<div id="payment_form_container">
								<!-- PhonePe Payment Form -->
								<form id="phonepe_payment_form" method="POST" action="<?php echo base_url('phonepe/initiate_payment'); ?>">
									<input type="hidden" name="amount" value="<?php echo $bulk_payment_data['total_amount']; ?>">
									<input type="hidden" name="payment_type" value="bulk_payment">
									<input type="hidden" name="payment_ids" value='<?php echo $bulk_payment_data['payment_ids']; ?>'>
									<input type="hidden" name="return_url" value="<?php echo base_url('admin/earnings/bulk_payment_success'); ?>">
									<input type="hidden" name="cancel_url" value="<?php echo base_url('admin/earnings/payment_cart'); ?>">
									<input type="hidden" name="<?php echo $this->security->get_csrf_token_name(); ?>" value="<?php echo $this->security->get_csrf_hash(); ?>">
									
									<button type="submit" class="btn btn-lg" style="background-color: #5f259f; color: white; padding: 15px 40px; font-size: 18px;">
										<i class="fa fa-mobile"></i> Pay with PhonePe
									</button>
								</form>
								
								<div style="margin-top: 20px;">
									<a href="<?php echo base_url('admin/earnings/payment_cart'); ?>" class="btn btn-default">
										<i class="fa fa-arrow-left"></i> Back to Cart
									</a>
								</div>
							</div>
							
							<div id="processing_container" style="display: none;">
								<div class="text-center">
									<i class="fa fa-spinner fa-spin" style="font-size: 60px; color: #5f259f;"></i>
									<h4 style="margin-top: 20px;">Processing your payment...</h4>
									<p>Please wait while we redirect you to PhonePe</p>
								</div>
							</div>
						</div>
					</div>
				</div>
				
				<!-- Security Info -->
				<div class="panel panel-success">
					<div class="panel-heading">
						<h3 class="panel-title">
							<i class="fa fa-shield"></i> Secure Payment
						</h3>
					</div>
					<div class="panel-body">
						<p><i class="fa fa-lock"></i> <strong>100% Secure Payment</strong></p>
						<p style="font-size: 12px; margin-bottom: 5px;">
							• Your payment information is encrypted and secure<br>
							• We do not store your card details<br>
							• PhonePe uses industry-standard security protocols
						</p>
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
		border-color: #5f259f;
	}
	.panel-primary > .panel-heading {
		background-color: #5f259f;
		border-color: #5f259f;
		color: white;
	}
	.panel-success > .panel-heading {
		background-color: #28a745;
		border-color: #28a745;
		color: white;
	}
</style>

<script>
	$(document).ready(function() {
		$('#phonepe_payment_form').submit(function() {
			$('#payment_form_container').hide();
			$('#processing_container').show();
		});
		
		// Alternative: If you want to handle PhonePe payment via AJAX
		// Uncomment below and modify according to your PhonePe integration
		
		/*
		$('#phonepe_payment_form').submit(function(e) {
			e.preventDefault();
			
			$('#payment_form_container').hide();
			$('#processing_container').show();
			
			var formData = $(this).serialize();
			
			$.ajax({
				url: "<?php echo base_url('phonepe/initiate_payment'); ?>",
				type: "POST",
				data: formData,
				dataType: 'json',
				success: function(response) {
					if (response.status === 'success' && response.payment_url) {
						// Redirect to PhonePe payment page
						window.location.href = response.payment_url;
					} else {
						alert('Error initiating payment: ' + (response.message || 'Unknown error'));
						$('#processing_container').hide();
						$('#payment_form_container').show();
					}
				},
				error: function() {
					alert('Error connecting to payment gateway!');
					$('#processing_container').hide();
					$('#payment_form_container').show();
				}
			});
		});
		*/
	});
</script>

<!-- 
NOTE: You need to integrate this with your existing PhonePe payment gateway.
The form submits to 'phonepe/initiate_payment' which should:
1. Create a PhonePe payment request
2. Generate payment URL
3. Redirect user to PhonePe payment page
4. Handle callback after payment completion

Example PhonePe Integration Flow:
1. Merchant initiates payment with amount and transaction ID
2. PhonePe generates payment link
3. User completes payment on PhonePe
4. PhonePe redirects back to return_url with payment status
5. Verify payment status and update database
-->