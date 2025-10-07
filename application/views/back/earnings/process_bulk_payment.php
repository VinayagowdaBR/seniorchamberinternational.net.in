<div id="page-head">
    <!--Page Title-->
    <div id="page-title">
        <h1 class="page-header text-overflow">
            <i class="fa fa-mobile"></i> <?php echo translate('phonepe_payment_gateway'); ?>
        </h1>
    </div>
    
    <!--Breadcrumb-->
    <ol class="breadcrumb">
        <li><a href="<?=base_url()?>admin"><?php echo translate('home'); ?></a></li>
        <li><a href="<?=base_url()?>admin/earnings"><?php echo translate('earnings'); ?></a></li>
        <li><a href="<?=base_url()?>admin/earnings/payment_cart"><?php echo translate('payment_cart'); ?></a></li>
        <li class="active"><?php echo translate('phonepe_payment'); ?></li>
    </ol>
</div>

<!--Page content-->
<div id="page-content">
    <div class="row">
        <div class="col-md-8 col-md-offset-2">
            <div class="panel panel-primary">
                <div class="panel-heading">
                    <h3 class="panel-title">
                        <i class="fa fa-credit-card"></i> Complete Your Payment
                    </h3>
                </div>
                <div class="panel-body">
                    <div class="text-center" style="padding: 30px;">
                        <!-- PhonePe Logo -->
                        <div style="margin-bottom: 30px;">
                            <i class="fa fa-mobile" style="font-size: 80px; color: #5f259f;"></i>
                        </div>
                        
                        <h3 style="margin-bottom: 30px;">Payment Details</h3>
                        
                        <!-- Payment Summary -->
                        <div class="well" style="margin-bottom: 30px; background-color: #f9f9f9;">
                            <div class="row" style="margin-bottom: 15px;">
                                <div class="col-xs-6 text-left">
                                    <strong style="font-size: 16px;">Total Items:</strong>
                                </div>
                                <div class="col-xs-6 text-right">
                                    <span style="font-size: 16px;">
                                        <?php 
                                        $payment_ids = json_decode($bulk_payment_data['payment_ids'], true);
                                        echo count($payment_ids); 
                                        ?>
                                    </span>
                                </div>
                            </div>
                            
                            <hr style="margin: 15px 0; border-color: #ddd;">
                            
                            <div class="row">
                                <div class="col-xs-6 text-left">
                                    <strong style="font-size: 18px;">Total Amount:</strong>
                                </div>
                                <div class="col-xs-6 text-right">
                                    <h3 style="margin: 0; color: #28a745; font-weight: bold;">
                                        <?php echo currency('', 'def') . number_format($bulk_payment_data['total_amount'], 2); ?>
                                    </h3>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Payment Form -->
                        <div id="payment_form_container">
                            <form id="phonepe_payment_form" method="POST" action="<?php echo base_url('phonepe/initiate_payment'); ?>">
                                <input type="hidden" name="amount" value="<?php echo $bulk_payment_data['total_amount']; ?>">
                                <input type="hidden" name="payment_type" value="bulk_payment">
                                <input type="hidden" name="payment_ids" value='<?php echo $bulk_payment_data['payment_ids']; ?>'>
                                <input type="hidden" name="return_url" value="<?php echo base_url('admin/earnings/bulk_payment_success'); ?>">
                                <input type="hidden" name="cancel_url" value="<?php echo base_url('admin/earnings/payment_cart'); ?>">
                                <input type="hidden" name="<?php echo $this->security->get_csrf_token_name(); ?>" value="<?php echo $this->security->get_csrf_hash(); ?>">
                                
                                <button type="submit" class="btn btn-lg btn-phonepe">
                                    <i class="fa fa-mobile"></i> Pay with PhonePe
                                </button>
                            </form>
                            
                            <div style="margin-top: 25px;">
                                <a href="<?php echo base_url('admin/earnings/payment_cart'); ?>" class="btn btn-default btn-lg">
                                    <i class="fa fa-arrow-left"></i> Back to Cart
                                </a>
                            </div>
                        </div>
                        
                        <!-- Processing Spinner -->
                        <div id="processing_container" style="display: none;">
                            <div class="text-center">
                                <i class="fa fa-spinner fa-spin" style="font-size: 60px; color: #5f259f;"></i>
                                <h4 style="margin-top: 20px; color: #5f259f;">Processing your payment...</h4>
                                <p class="text-muted">Please wait while we redirect you to PhonePe</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Security Information -->
            <div class="panel panel-success">
                <div class="panel-heading">
                    <h3 class="panel-title">
                        <i class="fa fa-shield"></i> Secure Payment
                    </h3>
                </div>
                <div class="panel-body">
                    <div class="media">
                        <div class="media-left">
                            <i class="fa fa-lock fa-3x text-success"></i>
                        </div>
                        <div class="media-body">
                            <h4 class="media-heading">100% Secure Payment</h4>
                            <ul class="list-unstyled" style="margin-top: 10px;">
                                <li><i class="fa fa-check text-success"></i> Your payment information is encrypted and secure</li>
                                <li><i class="fa fa-check text-success"></i> We do not store your card details</li>
                                <li><i class="fa fa-check text-success"></i> PhonePe uses industry-standard security protocols</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Payment Instructions -->
            <div class="alert alert-info">
                <i class="fa fa-info-circle"></i> 
                <strong>Note:</strong> After clicking "Pay with PhonePe", you will be redirected to the PhonePe payment gateway to complete your transaction securely.
            </div>
        </div>
    </div>
</div>

<style>
    .panel-primary {
        border-color: #5f259f;
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    }
    .panel-primary > .panel-heading {
        background-color: #5f259f;
        border-color: #5f259f;
        color: white;
    }
    .panel-success {
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    }
    .panel-success > .panel-heading {
        background-color: #28a745;
        border-color: #28a745;
        color: white;
    }
    .btn-phonepe {
        background-color: #5f259f;
        color: white;
        padding: 15px 50px;
        font-size: 18px;
        font-weight: bold;
        border: none;
        border-radius: 5px;
        transition: all 0.3s ease;
    }
    .btn-phonepe:hover {
        background-color: #4a1d7a;
        color: white;
        transform: translateY(-2px);
        box-shadow: 0 4px 8px rgba(95, 37, 159, 0.3);
    }
    .btn-phonepe:active {
        transform: translateY(0);
    }
    .well {
        border: 1px solid #ddd;
        border-radius: 5px;
    }
</style>

<script>
    $(document).ready(function() {
        // Handle form submission with loading state
        $('#phonepe_payment_form').submit(function(e) {
            // Hide form and show processing
            $('#payment_form_container').fadeOut(300, function() {
                $('#processing_container').fadeIn(300);
            });
            
            // Allow form to submit normally
            return true;
        });
        
        /* 
        ============================================
        ALTERNATIVE: AJAX-based Payment Initiation
        ============================================
        Uncomment this section if your PhonePe controller 
        returns JSON response instead of direct redirect
        
        $('#phonepe_payment_form').submit(function(e) {
            e.preventDefault();
            
            // Show processing state
            $('#payment_form_container').fadeOut(300, function() {
                $('#processing_container').fadeIn(300);
            });
            
            var formData = $(this).serialize();
            
            $.ajax({
                url: "<?php echo base_url('phonepe/initiate_payment'); ?>",
                type: "POST",
                data: formData,
                dataType: 'json',
                timeout: 30000, // 30 second timeout
                success: function(response) {
                    console.log('PhonePe Response:', response);
                    
                    if (response.status === 'success' && response.payment_url) {
                        // Redirect to PhonePe payment page
                        window.location.href = response.payment_url;
                    } else {
                        // Show error message
                        alert('Payment Error: ' + (response.message || 'Unable to initiate payment'));
                        
                        // Reset form
                        $('#processing_container').fadeOut(300, function() {
                            $('#payment_form_container').fadeIn(300);
                        });
                    }
                },
                error: function(xhr, status, error) {
                    console.error('Payment Gateway Error:', {
                        status: status,
                        error: error,
                        response: xhr.responseText
                    });
                    
                    alert('Error connecting to payment gateway! Please try again or contact support.');
                    
                    // Reset form
                    $('#processing_container').fadeOut(300, function() {
                        $('#payment_form_container').fadeIn(300);
                    });
                }
            });
            
            return false;
        });
        */
    });
</script>

<!-- 
=============================================
PHONEPE INTEGRATION NOTES
=============================================

This form submits to 'phonepe/initiate_payment' which should:

1. Create a PhonePe payment request with transaction ID
2. Generate payment URL from PhonePe API
3. Redirect user to PhonePe payment page
4. Handle callback after payment completion

PhonePe Integration Flow:
- Merchant initiates payment with amount and unique transaction ID
- PhonePe generates secure payment link
- User completes payment on PhonePe platform
- PhonePe redirects back to return_url with payment status
- Verify payment status via PhonePe API
- Update database with payment confirmation

Required in your PhonePe controller:
- Merchant ID
- Salt Key/Salt Index
- Transaction ID generation
- Callback URL handling
- Payment verification
-->
