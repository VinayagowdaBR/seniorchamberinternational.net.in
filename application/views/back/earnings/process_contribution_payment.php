<div id="page-head">
    <!--Page Title-->
    <div id="page-title">
        <h1 class="page-header text-overflow">
            <i class="fa fa-credit-card"></i> <?php echo translate('contribution_payment_confirmation'); ?>
        </h1>
    </div>
    
    <!--Breadcrumb-->
    <ol class="breadcrumb">
        <li><a href="<?=base_url()?>admin"><?php echo translate('home'); ?></a></li>
        <li><a href="<?=base_url()?>admin/contributionpayment"><?php echo translate('contribution_payment'); ?></a></li>
        <li class="active"><?php echo translate('payment_confirmation'); ?></li>
    </ol>
</div>

<!--Page content-->
<div id="page-content">
    <div class="row">
        <div class="col-md-8 col-md-offset-2">
            
            <!-- Main Payment Panel -->
            <div class="panel panel-primary">
                <div class="panel-heading">
                    <h3 class="panel-title">
                        <i class="fa fa-credit-card"></i> Complete Contribution Payment
                    </h3>
                </div>
                <div class="panel-body">
                    
                    <!-- PhonePe Logo -->
                    <div class="text-center" style="margin-bottom: 20px;">
                        <i class="fa fa-mobile" style="font-size: 60px; color: #5f259f;"></i>
                        <h3 style="margin-top: 15px;">Payment Summary</h3>
                    </div>
                    
                    <!-- Payment Summary -->
                    <div class="well" style="background-color: #f9f9f9; border: 1px solid #ddd;">
                        <?php 
                        $payment_ids = json_decode($bulk_payment_data['payment_ids'], true);
                        $selected_plan = null;
                        if ($this->session->userdata('selected_package_id')) {
                            $selected_plan = $this->db->get_where('plan', [
                                'plan_id' => $this->session->userdata('selected_package_id')
                            ])->row();
                        }
                        ?>
                        
                        <div class="row" style="margin-bottom: 15px;">
                            <div class="col-xs-6 text-left">
                                <strong style="font-size: 16px;">Total Members:</strong>
                            </div>
                            <div class="col-xs-6 text-right">
                                <span style="font-size: 16px;">
                                    <?php echo count($payment_ids); ?>
                                </span>
                            </div>
                        </div>
                        
                        <?php if ($selected_plan): ?>
                        <div class="row" style="margin-bottom: 15px;">
                            <div class="col-xs-6 text-left">
                                <strong style="font-size: 16px;">Package:</strong>
                            </div>
                            <div class="col-xs-6 text-right">
                                <span style="font-size: 16px; color: #5f259f;">
                                    <?php echo htmlspecialchars($selected_plan->name); ?>
                                </span>
                            </div>
                        </div>
                        <?php endif; ?>
                        
                        <hr style="margin: 15px 0; border-color: #ddd;">
                        
                        <div class="row">
                            <div class="col-xs-6 text-left">
                                <strong style="font-size: 20px;">Total Amount:</strong>
                            </div>
                            <div class="col-xs-6 text-right">
                                <h3 style="margin: 0; color: #28a745; font-weight: bold;">
                                    <?php echo currency('', 'def') . number_format($bulk_payment_data['total_amount'], 2); ?>
                                </h3>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Members List -->
                    <div class="panel panel-info" style="margin-top: 20px;">
                        <div class="panel-heading">
                            <h4 class="panel-title">
                                <i class="fa fa-users"></i> Selected Members 
                                <span class="badge" style="background: white; color: #31b0d5;">
                                    <?php echo count($payment_ids); ?>
                                </span>
                            </h4>
                        </div>
                        <div class="panel-body" style="max-height: 300px; overflow-y: auto; padding: 0;">
                            <table class="table table-striped table-bordered" style="margin-bottom: 0;">
                                <thead>
                                    <tr style="background: #31b0d5; color: white;">
                                        <th width="8%">#</th>
                                        <th width="25%">Member Code</th>
                                        <th width="40%">Member Name</th>
                                        <th width="27%">Amount</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php 
                                    $serial = 1;
                                    $per_member = count($payment_ids) > 0 ? $bulk_payment_data['total_amount'] / count($payment_ids) : 0;
                                    
                                    foreach ($payment_ids as $member_id): 
                                        $member = $this->db->get_where('member', ['member_id' => $member_id])->row();
                                        if (!$member) continue;
                                    ?>
                                    <tr>
                                        <td class="text-center"><?php echo $serial++; ?></td>
                                        <td><?php echo htmlspecialchars($member->member_profile_id ?? 'N/A'); ?></td>
                                        <td><?php echo htmlspecialchars($member->first_name . ' ' . $member->last_name); ?></td>
                                        <td class="text-right">
                                            <strong><?php echo currency('', 'def') . number_format($per_member, 2); ?></strong>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                    
                    <!-- Payment Form -->
                    <div id="payment_form_container" class="text-center" style="margin-top: 30px;">
                        <form id="phonepe_payment_form" method="POST" action="<?php echo base_url('phonepe_contribution/initiate_payment'); ?>">
                            <input type="hidden" name="amount" value="<?php echo $bulk_payment_data['total_amount']; ?>">
                            <input type="hidden" name="payment_type" value="contribution_payment">
                            <input type="hidden" name="payment_ids" value='<?php echo $bulk_payment_data['payment_ids']; ?>'>
                            <input type="hidden" name="plan_id" value="<?php echo $this->session->userdata('selected_package_id'); ?>">
                            <input type="hidden" name="return_url" value="<?php echo base_url('phonepe_contribution/payment_return'); ?>">
                            <input type="hidden" name="callback_url" value="<?php echo base_url('phonepe_contribution/payment_callback'); ?>">
                            <input type="hidden" name="cancel_url" value="<?php echo base_url('admin/contributionpayment'); ?>">
                            <input type="hidden" name="<?php echo $this->security->get_csrf_token_name(); ?>" value="<?php echo $this->security->get_csrf_hash(); ?>">
                            
                            <button type="submit" class="btn btn-lg btn-phonepe">
                                <i class="fa fa-mobile"></i> Pay <?php echo currency('', 'def') . number_format($bulk_payment_data['total_amount'], 2); ?> with PhonePe
                            </button>
                            
                            <div style="margin-top: 20px;">
                                <a href="<?php echo base_url('admin/contributionpayment'); ?>" class="btn btn-default btn-lg">
                                    <i class="fa fa-arrow-left"></i> Back to Member Selection
                                </a>
                            </div>
                        </form>
                    </div>
                    
                    <!-- Processing Spinner -->
                    <div id="processing_container" class="text-center" style="display: none; padding: 30px;">
                        <i class="fa fa-spinner fa-spin" style="font-size: 60px; color: #5f259f;"></i>
                        <h4 style="margin-top: 20px; color: #5f259f;">Processing your contribution payment...</h4>
                        <p class="text-muted">Please wait while we redirect you to PhonePe</p>
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
    .panel-success, .panel-info {
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    }
    .panel-success > .panel-heading {
        background-color: #28a745;
        border-color: #28a745;
        color: white;
    }
    .panel-info > .panel-heading {
        background-color: #31b0d5;
        border-color: #31b0d5;
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
        border-radius: 5px;
    }
</style>

<script>
    $(document).ready(function() {
        // Handle form submission with loading state
        $('#phonepe_payment_form').submit(function(e) {
            // Confirmation dialog
            var totalAmount = <?php echo $bulk_payment_data['total_amount']; ?>;
            var memberCount = <?php echo count($payment_ids); ?>;
            
            if (!confirm('Confirm payment of ' + currency('', 'def') + totalAmount.toFixed(2) + ' for ' + memberCount + ' members?')) {
                e.preventDefault();
                return false;
            }
            
            // Hide form and show processing
            $('#payment_form_container').fadeOut(300, function() {
                $('#processing_container').fadeIn(300);
            });
            
            // Allow form to submit normally
            return true;
        });
    });
</script>
