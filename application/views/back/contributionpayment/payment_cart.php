<!--CONTENT CONTAINER-->
<div id="content-container">
    <div id="page-head">
        <div id="page-title">
            <h1 class="page-header text-overflow">
                <i class="fa fa-shopping-cart"></i> Contribution Payment Cart
            </h1>
        </div>
        <ol class="breadcrumb">
            <li><a href="<?=base_url()?>admin"><i class="fa fa-home"></i> Home</a></li>
            <li><a href="<?=base_url()?>admin/contributionpayment"><i class="fa fa-handshake-o"></i> Contribution Payment</a></li>
            <li class="active">Payment Cart</li>
        </ol>
    </div>
    
    <div id="page-content">
        <!-- Toast Container -->
        <div id="toast-container"></div>
        
        <div class="row">
            <!-- Main Content -->
            <div class="col-md-8">
                <!-- Package Selection -->
                <div class="card">
                    <div class="card-header bg-purple">
                        <i class="fa fa-gift"></i> Step 1: Choose Membership Package
                    </div>
                    <div class="card-body">
                        <div class="form-group">
                            <label>Select Package <span class="text-danger">*</span></label>
                            <select id="package-select" class="form-control" required>
                                <option value="">-- Choose Package --</option>
                                <?php 
                                if (isset($packages) && !empty($packages)) {
                                    foreach ($packages as $pkg): 
                                        // Calculate GST
                                        $gst_amount = ($pkg->amount * $pkg->gst) / 100;
                                        $total = $pkg->amount + $gst_amount;
                                        
                                        // Check if this package is selected
                                        $selected = '';
                                        if ($this->session->userdata('selected_contribution_package_id') == $pkg->plan_id) {
                                            $selected = 'selected';
                                        }
                                ?>
                                    <option value="<?php echo $pkg->plan_id; ?>" 
                                            data-name="<?php echo htmlspecialchars($pkg->name); ?>"
                                            data-price="<?php echo $total; ?>"
                                            data-amount="<?php echo $pkg->amount; ?>"
                                            data-gst-percentage="<?php echo $pkg->gst; ?>"
                                            data-gst-amount="<?php echo $gst_amount; ?>"
                                            <?php echo $selected; ?>>
                                        <?php echo $pkg->name; ?> - ₹<?php echo number_format($total, 2); ?>
                                    </option>
                                <?php endforeach; } ?>
                            </select>
                        </div>
                        
                        <!-- Package Details -->
                        <div id="package-details" style="display: none;">
                            <div class="package-info">
                                <div class="detail-header">
                                    <i class="fa fa-check-circle"></i> Package Details
                                </div>
                                <div class="info-grid">
                                    <div class="info-item">
                                        <small>Package Name</small>
                                        <strong id="pkg-name">-</strong>
                                    </div>
                                    <div class="info-item">
                                        <small>Base Amount</small>
                                        <strong>₹<span id="pkg-amount">0</span></strong>
                                    </div>
                                    <div class="info-item">
                                        <small>GST</small>
                                        <strong>₹<span id="pkg-gst">0</span></strong>
                                    </div>
                                    <div class="info-item highlight">
                                        <small>Price per Member</small>
                                        <strong class="price-large">₹<span id="pkg-price">0</span></strong>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Members List -->
                <div class="card">
                    <div class="card-header bg-pink">
                        <i class="fa fa-users"></i> Step 2: Review Selected Members (<?php echo count($cart_items); ?>)
                    </div>
                    <div class="card-body p-0">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th width="50">#</th>
                                    <th>Member Name</th>
                                    <th width="120">Member Code</th>
                                    <th width="200">Package</th>
                                    <th width="120">Amount</th>
                                    <th width="80"></th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php 
                                $sl = 1;
                                foreach($cart_items as $item): 
                                    $memberName = isset($item['member_name']) ? $item['member_name'] : 'Unknown';
                                    $memberCode = isset($item['member_code']) ? $item['member_code'] : 'N/A';
                                ?>
                                <tr data-member-id="<?php echo $item['member_id']; ?>">
                                    <td class="text-center"><?php echo $sl++; ?></td>
                                    <td><strong><?php echo htmlspecialchars($memberName); ?></strong></td>
                                    <td><?php echo htmlspecialchars($memberCode); ?></td>
                                    <td class="pkg-cell">
                                        <span class="badge badge-warning">Not Selected</span>
                                    </td>
                                    <td class="amt-cell">
                                        <strong>₹0.00</strong>
                                    </td>
                                    <td class="text-center">
                                        <button class="btn-delete remove-btn" 
                                                data-id="<?php echo $item['member_id']; ?>" 
                                                title="Remove">
                                            <i class="fa fa-trash"></i>
                                        </button>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            
            <!-- Sidebar -->
            <div class="col-md-4">
                <!-- Summary -->
                <div class="card">
                    <div class="card-header bg-cyan">
                        <i class="fa fa-calculator"></i> Payment Summary
                    </div>
                    <div class="card-body">
                        <div class="summary-row">
                            <span>Total Members</span>
                            <strong id="total-members"><?php echo count($cart_items); ?></strong>
                        </div>
                        <div class="summary-row">
                            <span>Package Selected</span>
                            <strong id="summary-pkg" class="text-muted">Not Selected</strong>
                        </div>
                        <hr>
                        <div class="summary-row">
                            <span class="total-label">Grand Total</span>
                            <strong class="total-value" id="grand-total">₹0.00</strong>
                        </div>
                        
                        <button class="btn-primary-custom" id="proceed-btn" disabled>
                            <i class="fa fa-credit-card"></i> Proceed to Payment
                        </button>
                        
                        <a href="<?php echo base_url('admin/contributionpayment'); ?>" class="btn-secondary-custom">
                            <i class="fa fa-arrow-left"></i> Back to Selection
                        </a>
                    </div>
                </div>
                
                <!-- Info -->
                <div class="card">
                    <div class="card-header bg-orange">
                        <i class="fa fa-info-circle"></i> Payment Information
                    </div>
                    <div class="card-body">
                        <div class="info-row">
                            <i class="fa fa-shield text-success"></i>
                            <span>Secure encrypted payment</span>
                        </div>
                        <div class="info-row">
                            <i class="fa fa-bolt text-warning"></i>
                            <span>Instant confirmation</span>
                        </div>
                        <div class="info-row">
                            <i class="fa fa-file-text text-info"></i>
                            <span>GST invoice included</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
/* Card Styles */
.card {
    background: white;
    border-radius: 8px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    margin-bottom: 20px;
    overflow: hidden;
}

.card-header {
    padding: 15px 20px;
    font-weight: 600;
    font-size: 15px;
    color: white;
}

.bg-purple {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
}

.bg-pink {
    background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
}

.bg-cyan {
    background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
}

.bg-orange {
    background: linear-gradient(135deg, #fa709a 0%, #fee140 100%);
}

.card-body {
    padding: 20px;
}

.card-body.p-0 {
    padding: 0;
}

/* Form */
.form-group {
    margin-bottom: 20px;
}

.form-group label {
    display: block;
    margin-bottom: 8px;
    font-weight: 600;
    color: #333;
}

.form-control {
    width: 100%;
    padding: 8px 15px;
    border: 2px solid #e1e8ed;
    border-radius: 6px;
    font-size: 14px;
    transition: all 0.3s;
}

.form-control:focus {
    border-color: #667eea;
    outline: none;
    box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
}

select.form-control {
    height: 43px !important;
}

/* Package Info */
.package-info {
    background: linear-gradient(135deg, #e8f5e9 0%, #c8e6c9 100%);
    border-radius: 8px;
    margin-top: 20px;
    overflow: hidden;
    animation: slideDown 0.3s;
}

@keyframes slideDown {
    from { opacity: 0; transform: translateY(-10px); }
    to { opacity: 1; transform: translateY(0); }
}

.detail-header {
    background: rgba(76, 175, 80, 0.15);
    padding: 12px 20px;
    font-weight: 600;
    color: #2e7d32;
    border-bottom: 2px solid #4caf50;
}

.info-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 15px;
    padding: 20px;
}

.info-item {
    background: white;
    padding: 15px;
    border-radius: 6px;
}

.info-item small {
    display: block;
    color: #666;
    font-size: 12px;
    margin-bottom: 5px;
}

.info-item strong {
    font-size: 16px;
    color: #333;
}

.info-item.highlight {
    grid-column: 1 / -1;
    background: linear-gradient(135deg, #c8e6c9 0%, #a5d6a7 100%);
    border: 2px solid #4caf50;
}

.price-large {
    color: #2e7d32 !important;
    font-size: 24px !important;
}

/* Table */
.table {
    width: 100%;
    margin: 0;
}

.table thead {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
}

.table thead th {
    color: white;
    font-weight: 600;
    font-size: 12px;
    text-transform: uppercase;
    padding: 12px;
    border: none;
}

.table tbody tr {
    border-bottom: 1px solid #eee;
    transition: all 0.2s;
}

.table tbody tr:hover {
    background: #f8f9fa;
}

.table tbody td {
    padding: 12px;
}

/* Badges */
.badge {
    display: inline-block;
    padding: 6px 12px;
    border-radius: 20px;
    font-size: 12px;
    font-weight: 600;
}

.badge-warning {
    background: #fff3cd;
    color: #856404;
}

.badge-success {
    background: #d4edda;
    color: #155724;
}

/* Buttons */
.btn-delete {
    background: white;
    border: 2px solid #e74c3c;
    color: #e74c3c;
    padding: 6px 12px;
    border-radius: 6px;
    cursor: pointer;
    transition: all 0.2s;
}

.btn-delete:hover {
    background: #e74c3c;
    color: white;
}

.btn-primary-custom {
    width: 100%;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    border: none;
    padding: 14px;
    border-radius: 8px;
    font-weight: 600;
    cursor: pointer;
    margin-top: 20px;
    transition: all 0.3s;
    box-shadow: 0 4px 15px rgba(102, 126, 234, 0.4);
}

.btn-primary-custom:hover:not(:disabled) {
    transform: translateY(-2px);
    box-shadow: 0 6px 20px rgba(102, 126, 234, 0.5);
}

.btn-primary-custom:disabled {
    background: #bdc3c7;
    cursor: not-allowed;
    box-shadow: none;
}

.btn-secondary-custom {
    display: block;
    width: 100%;
    background: white;
    color: #333;
    border: 2px solid #ddd;
    padding: 12px;
    border-radius: 8px;
    font-weight: 600;
    text-align: center;
    text-decoration: none;
    margin-top: 12px;
    transition: all 0.3s;
}

.btn-secondary-custom:hover {
    background: #f5f5f5;
    border-color: #333;
    text-decoration: none;
}

/* Summary */
.summary-row {
    display: flex;
    justify-content: space-between;
    padding: 12px 0;
    border-bottom: 1px solid #eee;
}

.total-label {
    font-size: 18px;
    font-weight: 700;
}

.total-value {
    font-size: 28px;
    color: #27ae60;
    font-weight: 700;
}

hr {
    border: none;
    border-top: 2px solid #eee;
    margin: 15px 0;
}

/* Info Rows */
.info-row {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 12px 0;
    border-bottom: 1px solid #eee;
}

.info-row:last-child {
    border: none;
}

.info-row i {
    font-size: 20px;
}

/* Toast */
#toast-container {
    position: fixed;
    top: 70px;
    right: 20px;
    z-index: 9999;
}

.toast {
    background: white;
    border-left: 4px solid;
    padding: 15px 20px;
    margin-bottom: 10px;
    border-radius: 6px;
    box-shadow: 0 4px 12px rgba(0,0,0,0.15);
    min-width: 300px;
    animation: slideIn 0.3s;
}

@keyframes slideIn {
    from { transform: translateX(100px); opacity: 0; }
    to { transform: translateX(0); opacity: 1; }
}

.toast.success { border-left-color: #27ae60; }
.toast.error { border-left-color: #e74c3c; }
.toast.info { border-left-color: #3498db; }

.toast strong {
    display: block;
    margin-bottom: 5px;
    font-size: 15px;
}

.toast span {
    font-size: 13px;
    color: #666;
}

/* Responsive */
@media (max-width: 768px) {
    .info-grid {
        grid-template-columns: 1fr;
    }
    .info-item.highlight {
        grid-column: 1;
    }
}
</style>

<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
<script>
var cartData = <?php echo json_encode($cart_items); ?>;
var selectedPackage = null;

function showToast(type, title, message) {
    var toast = $('<div class="toast ' + type + '"><strong>' + title + '</strong><span>' + message + '</span></div>');
    $('#toast-container').append(toast);
    setTimeout(function() {
        toast.fadeOut(300, function() { $(this).remove(); });
    }, 3500);
}

$(document).ready(function() {
    
    // Trigger change if package already selected
    if ($('#package-select').val()) {
        $('#package-select').trigger('change');
    }
    
    // Package selection
    $('#package-select').change(function() {
        var opt = $(this).find(':selected');
        var pkgId = $(this).val();
        var pkgName = opt.data('name');
        var pkgPrice = parseFloat(opt.data('price'));
        var pkgAmount = parseFloat(opt.data('amount'));
        var pkgGstPercentage = parseFloat(opt.data('gst-percentage'));
        var pkgGstAmount = parseFloat(opt.data('gst-amount'));
        
        if (!pkgId) {
            $('#package-details').slideUp();
            $('.pkg-cell').html('<span class="badge badge-warning">Not Selected</span>');
            $('.amt-cell').html('<strong>₹0.00</strong>');
            $('#summary-pkg').text('Not Selected').removeClass('text-success').addClass('text-muted');
            $('#grand-total').text('₹0.00');
            $('#proceed-btn').prop('disabled', true);
            selectedPackage = null;
            return;
        }
        
        $.ajax({
            url: '<?=base_url()?>admin/contributionpayment/update_cart_package',
            type: 'POST',
            data: { 
                package_id: pkgId,
                '<?= $this->security->get_csrf_token_name(); ?>': '<?= $this->security->get_csrf_hash(); ?>'
            },
            dataType: 'json',
            success: function(res) {
                if (res.status === 'success') {
                    selectedPackage = { id: pkgId, name: pkgName, price: pkgPrice };
                    
                    $('#pkg-name').text(pkgName);
                    $('#pkg-amount').text(pkgAmount.toFixed(2));
                    $('#pkg-gst').text(pkgGstAmount.toFixed(2) + ' (' + pkgGstPercentage + '%)');
                    $('#pkg-price').text(pkgPrice.toFixed(2));
                    $('#package-details').slideDown();
                    
                    $('.pkg-cell').html('<span class="badge badge-success">' + pkgName + '</span>');
                    $('.amt-cell').html('<strong style="color: #27ae60;">₹' + pkgPrice.toFixed(2) + '</strong>');
                    
                    var grandTotal = pkgPrice * cartData.length;
                    $('#summary-pkg').html('<span style="color: #27ae60; font-weight: 600;">' + pkgName + '</span>');
                    $('#grand-total').text('₹' + grandTotal.toFixed(2));
                    $('#proceed-btn').prop('disabled', false);
                    
                    showToast('success', 'Success!', 'Package applied to all members');
                } else {
                    showToast('error', 'Error', res.message || 'Failed to select package');
                }
            },
            error: function(xhr) {
                console.error('AJAX Error:', xhr.responseText);
                showToast('error', 'Error', 'Failed to update package');
            }
        });
    });
    
    // Remove member
    $('.remove-btn').click(function() {
        var memberId = $(this).data('id');
        var memberName = $(this).closest('tr').find('td:eq(1)').text();
        
        if (confirm('Remove ' + memberName + ' from cart?')) {
            $.ajax({
                url: '<?=base_url()?>admin/contributionpayment/remove_from_cart',
                type: 'POST',
                data: { 
                    member_id: memberId,
                    '<?= $this->security->get_csrf_token_name(); ?>': '<?= $this->security->get_csrf_hash(); ?>'
                },
                dataType: 'json',
                success: function(res) {
                    if (res.status === 'success') {
                        cartData = cartData.filter(item => item.member_id !== memberId);
                        
                        $('tr[data-member-id="' + memberId + '"]').fadeOut(300, function() {
                            $(this).remove();
                            
                            if (res.remaining_count === 0) {
                                showToast('info', 'Cart Empty', 'Redirecting...');
                                setTimeout(function() {
                                    window.location.href = '<?=base_url()?>admin/contributionpayment';
                                }, 1500);
                            } else {
                                $('#total-members').text(res.remaining_count);
                                if (selectedPackage) {
                                    var newTotal = selectedPackage.price * res.remaining_count;
                                    $('#grand-total').text('₹' + newTotal.toFixed(2));
                                }
                                showToast('info', 'Removed', memberName + ' removed');
                            }
                        });
                    } else {
                        showToast('error', 'Error', res.message);
                    }
                },
                error: function() {
                    showToast('error', 'Error', 'Failed to remove member');
                }
            });
        }
    });
    
    // Proceed to payment
    $('#proceed-btn').click(function() {
        if (!selectedPackage) {
            showToast('error', 'Error', 'Please select a package first');
            $('#package-select').focus();
            return;
        }
        
        var btn = $(this);
        btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Processing...');
        
        // Build form and submit
        var form = $('<form>', {
            'method': 'POST',
            'action': '<?=base_url()?>phonepe_contribution/initiate_payment'
        });
        
        var memberIds = cartData.map(item => item.member_id);
        var totalAmount = selectedPackage.price * cartData.length;
        
        form.append($('<input>', {type: 'hidden', name: 'amount', value: totalAmount}));
        form.append($('<input>', {type: 'hidden', name: 'payment_type', value: 'contribution_payment'}));
        form.append($('<input>', {type: 'hidden', name: 'payment_ids', value: JSON.stringify(memberIds)}));
        form.append($('<input>', {type: 'hidden', name: 'plan_id', value: selectedPackage.id}));
        form.append($('<input>', {type: 'hidden', name: 'return_url', value: '<?=base_url()?>phonepe_contribution/payment_return'}));
        form.append($('<input>', {type: 'hidden', name: 'callback_url', value: '<?=base_url()?>phonepe_contribution/payment_callback'}));
        form.append($('<input>', {type: 'hidden', name: 'cancel_url', value: '<?=base_url()?>admin/contributionpayment/payment_cart'}));
        form.append($('<input>', {type: 'hidden', name: '<?= $this->security->get_csrf_token_name(); ?>', value: '<?= $this->security->get_csrf_hash(); ?>'}));
        
        $('body').append(form);
        form.submit();
    });
});
</script>
