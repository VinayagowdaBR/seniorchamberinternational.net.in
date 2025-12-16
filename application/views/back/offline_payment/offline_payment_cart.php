<div id="content-container">
    <div id="page-head">
        <div id="page-title">
            <h1 class="page-header text-overflow">Offline Payment Cart</h1>
            <div class="searchbox">
                <div class="pull-right">
                    <a href="<?= base_url('offline_payment/make') ?>" class="btn btn-default btn-sm btn-labeled">
                        <span class="btn-label"><i class="fa fa-arrow-left"></i></span>
                        Back to Members
                    </a>
                </div>
            </div>
        </div>
        <ol class="breadcrumb">
            <li><a href="<?= base_url('admin') ?>">Home</a></li>
            <li><a href="<?= base_url('offline_payment/make') ?>">Offline Payment</a></li>
            <li class="active">Payment Cart</li>
        </ol>
    </div>
    
    <div id="page-content">
        <?php if (!empty($success_alert)): ?>
            <div class="alert alert-success alert-dismissible">
                <button class="close" data-dismiss="alert"><i class="pci-cross pci-circle"></i></button>
                <?= $success_alert ?>
            </div>
        <?php endif ?>
        
        <?php if (!empty($danger_alert)): ?>
            <div class="alert alert-danger alert-dismissible">
                <button class="close" data-dismiss="alert"><i class="pci-cross pci-circle"></i></button>
                <?= $danger_alert ?>
            </div>
        <?php endif ?>

        <?php if(empty($cart_members)): ?>
            <div class="panel">
                <div class="panel-body text-center" style="padding: 60px;">
                    <i class="fa fa-shopping-cart" style="font-size: 80px; color: #ccc; margin-bottom: 20px;"></i>
                    <h3>Your Payment Cart is Empty</h3>
                    <p class="text-muted">Please add members to cart from the member list.</p>
                    <a href="<?= base_url('offline_payment/make') ?>" class="btn btn-primary btn-lg" style="margin-top: 20px;">
                        <i class="fa fa-users"></i> Go to Member List
                    </a>
                </div>
            </div>
        <?php else: ?>
            <form method="POST" action="<?= base_url('offline_payment/offline_process_payment') ?>" id="offline_payment_form">
                <div class="row">
                    <!-- Left: Members List -->
                    <div class="col-md-8">
                        <div class="panel">
                            <div class="panel-heading">
                                <h3 class="panel-title">
                                    Members in Cart (<?= count($cart_members) ?>)
                                </h3>
                                <div class="panel-control">
                                    <button type="button" class="btn btn-danger btn-sm" id="offline_clear_cart_btn">
                                        <i class="fa fa-trash"></i> Clear Cart
                                    </button>
                                </div>
                            </div>
                            <div class="panel-body">
                                <div class="table-responsive">
                                    <table class="table table-bordered table-hover">
                                        <thead>
                                            <tr>
                                                <th width="40">#</th>
                                                <th>Member ID</th>
                                                <th>Name</th>
                                                <th>Area</th>
                                                <th>Legion</th>
                                                <th width="80">Action</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php $i = 1; foreach($cart_members as $member): ?>
                                                <tr>
                                                    <td><?= $i++ ?></td>
                                                    <td><strong><?= $member['member_profile_id'] ?></strong></td>
                                                    <td>
                                                        <?= $member['first_name'] . ' ' . $member['last_name'] ?>
                                                        <br><small class="text-muted"><?= $member['email'] ?></small>
                                                    </td>
                                                    <td><?= $member['area_name'] ?? 'N/A' ?></td>
                                                    <td><?= $member['legion_name'] ?? 'N/A' ?></td>
                                                    <td>
                                                        <button type="button" class="btn btn-xs btn-danger offline_remove_btn" 
                                                                data-member-id="<?= $member['member_id'] ?>" 
                                                                data-member-name="<?= $member['first_name'] . ' ' . $member['last_name'] ?>">
                                                            <i class="fa fa-times"></i>
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
                    
                    <!-- Right: Payment Details -->
                    <div class="col-md-4">
                        <div class="panel panel-primary">
                            <div class="panel-heading">
                                <h3 class="panel-title">Payment Details</h3>
                            </div>
                            <div class="panel-body">
                                <!-- Scanner Image Section -->
                                <div class="text-center pad-btm" style="padding-bottom: 20px; border-bottom: 1px solid #eee; margin-bottom: 20px;">
                                    <h4 class="text-thin mar-no">Scan to Pay</h4>
                                    <small class="text-muted">Use UPI / Bank App</small>
                                    <br><br>
                                    <img src="<?= base_url('uploads/UPI/Senior_chamber_International_Account.jpg') ?>" 
                                         alt="Payment Scanner QR" 
                                         style="width: 300px; height: 300px; object-fit: contain; border: 1px solid #ddd; padding: 5px; border-radius: 4px;"
                                         onerror="this.src="this.onerror=null;this.src='https://via.placeholder.com/150?text=QR+Not+Found';">
                                    <p class="mar-top text-sm text-info">
                                        <i class="fa fa-info-circle"></i> UPI ID: seniorchamber2494@sbi
                                </div>

                                <div class="form-group">
                                    <label>Select Plan <span class="text-danger">*</span></label>
                                    <select name="plan_id" id="offline_plan_select" class="form-control" required>
                                        <option value="">-- Select Plan --</option>
                                        <?php foreach($plans as $plan): ?>
                                            <option value="<?= $plan['plan_id'] ?>" 
                                                    data-amount="<?= $plan['amount'] ?>" 
                                                    data-gst="<?= isset($plan['gst']) ? $plan['gst'] : 0 ?>"
                                                    data-name="<?= $plan['name'] ?>">
                                                <?= $plan['name'] ?> - ₹<?= number_format($plan['amount'], 2) ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                
                                <div class="form-group">
                                    <label>Payment Method <span class="text-danger">*</span></label>
                                    <select name="payment_method" class="form-control" required>
                                        <option value="">-- Select Method --</option>
                                        <option value="cash">Cash</option>
                                        <option value="cheque">Cheque</option>
                                        <option value="bank_transfer">Bank Transfer</option>
                                        <option value="upi">UPI</option>
                                        <option value="neft_rtgs">NEFT/RTGS</option>
                                        <option value="other">Other</option>
                                    </select>
                                </div>
                                
                                <div class="form-group">
                                    <label>Transaction ID / Reference</label>
                                    <input type="text" name="transaction_id" class="form-control" 
                                           placeholder="Optional - Cheque No./UTR/Ref No.">
                                </div>
                                
                                <div class="form-group">
                                    <label>Payment Date</label>
                                    <input type="date" name="payment_date" class="form-control" 
                                           value="<?= date('Y-m-d') ?>" max="<?= date('Y-m-d') ?>">
                                </div>
                                
                                <div class="form-group">
                                    <label>Notes / Remarks</label>
                                    <textarea name="notes" class="form-control" rows="3" 
                                              placeholder="Optional notes about this payment"></textarea>
                                </div>
                                
                                <hr>
                                
                                <!-- Amount Summary -->
                                <div id="offline_amount_summary">
                                    <h4 class="mar-no pad-btm">Payment Summary</h4>
                                    
                                    <table class="table table-condensed" style="margin-bottom: 0;">
                                        <tbody>
                                            <tr>
                                                <td><strong>Total Members:</strong></td>
                                                <td class="text-right" id="offline_total_members"><?= count($cart_members) ?></td>
                                            </tr>
                                            <tr>
                                                <td><strong>Rate per Member:</strong></td>
                                                <td class="text-right" id="offline_rate_per_member">₹0.00</td>
                                            </tr>
                                            <tr>
                                                <td><strong>Base Amount:</strong></td>
                                                <td class="text-right" id="offline_base_amount">₹0.00</td>
                                            </tr>
                                            <tr>
                                                <td><strong>GST (<span id="offline_gst_percent">0</span>%):</strong></td>
                                                <td class="text-right" id="offline_gst_amount">₹0.00</td>
                                            </tr>
                                            <tr class="active">
                                                <td><strong>Total Amount:</strong></td>
                                                <td class="text-right">
                                                    <h4 class="text-primary" style="margin: 0;" id="offline_total_amount">₹0.00</h4>
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                                
                                <button type="submit" class="btn btn-success btn-block btn-lg" 
                                        id="offline_process_payment_btn" disabled style="margin-top: 20px;">
                                    <i class="fa fa-check-circle"></i> Process Payment
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        <?php endif; ?>
    </div>
</div>

<script>
$(document).ready(function(){
    // Plan selection change
    $('#offline_plan_select').on('change', function(){
        calculateOfflineTotal();
        var hasValue = $(this).val() !== '';
        $('#offline_process_payment_btn').prop('disabled', !hasValue);
    });
    
    // Remove member from cart
    $('.offline_remove_btn').on('click', function(){
        var member_id = $(this).data('member-id');
        var member_name = $(this).data('member-name');
        
        if(confirm('Remove ' + member_name + ' from cart?')){
            $.ajax({
                url: '<?= base_url("offline_payment/offline_remove_from_cart") ?>',
                type: 'POST',
                data: {member_id: member_id},
                dataType: 'json',
                success: function(response){
                    if(response.success){
                        location.reload();
                    } else {
                        alert(response.message);
                    }
                },
                error: function(){
                    alert('Failed to remove member from cart');
                }
            });
        }
    });
    
    // Clear entire cart
    $('#offline_clear_cart_btn').on('click', function(){
        if(confirm('Are you sure you want to clear entire cart?')){
            $.ajax({
                url: '<?= base_url("offline_payment/offline_clear_cart") ?>',
                type: 'POST',
                dataType: 'json',
                success: function(response){
                    if(response.success){
                        location.reload();
                    }
                },
                error: function(){
                    alert('Failed to clear cart');
                }
            });
        }
    });
    
    // Form submission
    $('#offline_payment_form').on('submit', function(e){
        var plan = $('#offline_plan_select').val();
        var method = $('select[name="payment_method"]').val();
        
        if(!plan || !method){
            e.preventDefault();
            alert('Please select plan and payment method');
            return false;
        }
        
        if(!confirm('Process payment for ' + $('#offline_total_members').text() + ' members?')){
            e.preventDefault();
            return false;
        }
        
        $('#offline_process_payment_btn').prop('disabled', true)
            .html('<i class="fa fa-spinner fa-spin"></i> Processing...');
    });
    
    // Calculate total amount
    function calculateOfflineTotal(){
        var selected = $('#offline_plan_select').find(':selected');
        if(selected.val()){
            var amount = parseFloat(selected.data('amount'));
            var gst = parseFloat(selected.data('gst'));
            var members = parseInt($('#offline_total_members').text());
            
            var base = amount * members;
            var gst_amt = (base * gst) / 100;
            var total = base + gst_amt;
            
            $('#offline_rate_per_member').text('₹' + amount.toFixed(2));
            $('#offline_base_amount').text('₹' + base.toFixed(2));
            $('#offline_gst_percent').text(gst.toFixed(2));
            $('#offline_gst_amount').text('₹' + gst_amt.toFixed(2));
            $('#offline_total_amount').text('₹' + total.toFixed(2));
        } else {
            $('#offline_rate_per_member').text('₹0.00');
            $('#offline_base_amount').text('₹0.00');
            $('#offline_gst_percent').text('0');
            $('#offline_gst_amount').text('₹0.00');
            $('#offline_total_amount').text('₹0.00');
        }
    }
});
</script>
