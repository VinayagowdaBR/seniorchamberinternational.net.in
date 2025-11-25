<style>
/* Alert Styling */
.alert {
    border-radius: 8px;
    border: none;
    padding: 15px 20px;
    margin-bottom: 20px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    animation: slideDown 0.3s ease-out;
}

@keyframes slideDown {
    from {
        opacity: 0;
        transform: translateY(-20px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.alert-success {
    background: linear-gradient(135deg, #5cb85c 0%, #4cae4c 100%);
    color: white;
}

.alert-danger {
    background: linear-gradient(135deg, #d9534f 0%, #c9302c 100%);
    color: white;
}

.alert i {
    margin-right: 10px;
    font-size: 18px;
}

.alert .close {
    color: white;
    opacity: 0.8;
    font-size: 24px;
    text-shadow: none;
}

.alert .close:hover {
    opacity: 1;
}

/* Delete Button Styling */
.btn-danger {
    transition: all 0.3s;
}

.btn-danger:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(231, 76, 60, 0.4);
}
</style>

<div id="content-container">
    <div id="page-head">
        <div id="page-title">
            <h1 class="page-header text-overflow">
                <i class="fa fa-user"></i> Invoices by <?php echo $admin->name; ?>
            </h1>
        </div>
        <ol class="breadcrumb">
            <li><a href="<?=base_url()?>admin"><i class="fa fa-home"></i> Home</a></li>
            <li><a href="<?=base_url()?>admin/contributionpayment">Contribution Payment</a></li>
            <li><a href="<?=base_url()?>admin/contributionpayment/invoices">Invoices</a></li>
            <li class="active"><?php echo $admin->name; ?></li>
        </ol>
    </div>
    
    <div id="page-content">
        <!-- Flash Messages -->
<?php 
        $success = $this->session->flashdata('success_alert');
        $danger  = $this->session->flashdata('danger_alert');
        ?>

        <?php if (!empty($success)): ?>
            <div class="alert alert-success alert-dismissible" id="success-alert">
                <button type="button" class="close" data-dismiss="alert">&times;</button>
                <i class="fa fa-check-circle"></i> <?php echo $success; ?>
            </div>
            <?php $this->session->unset_userdata('success_alert'); ?>

        <?php elseif (!empty($danger)): ?>
            <div class="alert alert-danger alert-dismissible" id="danger-alert">
                <button type="button" class="close" data-dismiss="alert">&times;</button>
                <i class="fa fa-exclamation-circle"></i> <?php echo $danger; ?>
            </div>
            <?php $this->session->unset_userdata('danger_alert'); ?>
        <?php endif; ?>

        
        <div class="panel">
            <div class="panel-heading">
                <h3 class="panel-title">All Contribution Payments</h3>
            </div>
            <div class="panel-body">
                <table class="table table-bordered table-hover" id="admin-invoice-table">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Invoice Number</th>
                            <th>Payment Date</th>
                            <th>Package</th>
                            <th>Total Members</th>
                            <th>Total Amount</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        if (!empty($invoices)):
                            $sl = 1;
                            foreach ($invoices as $invoice): 
                        ?>
                        <tr>
                            <td><?php echo $sl++; ?></td>
                            <td><strong><?php echo $invoice->invoice_number; ?></strong></td>
                            <td><?php echo date('d M Y, h:i A', strtotime($invoice->payment_date)); ?></td>
                            <td><?php echo $invoice->plan_name; ?></td>
                            <td><span class="badge badge-info"><?php echo $invoice->total_members; ?> Members</span></td>
                            <td><strong class="text-success">₹<?php echo number_format($invoice->total_amount, 2); ?></strong></td>
                            <td>
                                <?php if ($invoice->payment_status == 'completed'): ?>
                                    <span class="label label-success">Completed</span>
                                <?php else: ?>
                                    <span class="label label-warning"><?php echo ucfirst($invoice->payment_status); ?></span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <a href="<?php echo base_url('admin/contributionpayment/invoice_detail/' . $invoice->invoice_id); ?>" 
                                   class="btn btn-sm btn-primary" title="View Details">
                                    <i class="fa fa-eye"></i> View
                                </a>
                                
                                <button onclick="deleteInvoice(<?php echo $invoice->invoice_id; ?>, '<?php echo $invoice->invoice_number; ?>')" 
                                        class="btn btn-sm btn-danger" 
                                        title="Delete Invoice"
                                        style="margin-left: 5px;">
                                    <i class="fa fa-trash"></i> Delete
                                </button>
                            </td>
                        </tr>
                        <?php 
                            endforeach;
                        else:
                        ?>
                        <tr>
                            <td colspan="8" class="text-center" style="padding: 40px;">
                                <i class="fa fa-inbox" style="font-size: 48px; color: #ccc; display: block; margin-bottom: 10px;"></i>
                                <p>No invoices found</p>
                            </td>
                        </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div class="modal fade" id="deleteModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header" style="background: linear-gradient(135deg, #e74c3c 0%, #c0392b 100%); color: white;">
                <button type="button" class="close" data-dismiss="modal" style="color: white;">
                    <span>&times;</span>
                </button>
                <h4 class="modal-title">
                    <i class="fa fa-exclamation-triangle"></i> Confirm Delete
                </h4>
            </div>
            <div class="modal-body">
                <div style="text-align: center; padding: 20px;">
                    <i class="fa fa-trash" style="font-size: 60px; color: #e74c3c; margin-bottom: 20px;"></i>
                    <h4>Are you sure you want to delete this invoice?</h4>
                    <p><strong>Invoice Number: <span id="delete-invoice-number"></span></strong></p>
                    <div style="background: #fff3cd; padding: 15px; border-radius: 5px; margin-top: 20px; border-left: 4px solid #ffc107;">
                        <p style="margin: 0; color: #856404;">
                            <i class="fa fa-warning"></i> <strong>Warning:</strong> This action cannot be undone!
                        </p>
                        <p style="margin: 5px 0 0 0; color: #856404; font-size: 12px;">
                            All related member payment records will also be deleted.
                        </p>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">
                    <i class="fa fa-times"></i> Cancel
                </button>
                <button type="button" class="btn btn-danger" id="confirm-delete-btn">
                    <i class="fa fa-trash"></i> Yes, Delete Invoice
                </button>
            </div>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    // Initialize DataTable
    $('#admin-invoice-table').DataTable({
        order: [[2, 'desc']]
    });
    
    // Auto-hide success alert after 5 seconds
    if ($('#success-alert').length) {
        setTimeout(function() {
            $('#success-alert').fadeTo(800, 0).slideUp(500, function() {
                $(this).remove();
            });
        }, 5000);
    }

    // Auto-hide danger alert after 5 seconds
    if ($('#danger-alert').length) {
        setTimeout(function() {
            $('#danger-alert').fadeTo(800, 0).slideUp(500, function() {
                $(this).remove();
            });
        }, 5000);
    }
});

// Delete Invoice Function
var deleteInvoiceId = null;

function deleteInvoice(invoiceId, invoiceNumber) {
    deleteInvoiceId = invoiceId;
    $('#delete-invoice-number').text(invoiceNumber);
    $('#deleteModal').modal('show');
}

// Handle delete confirmation
$('#confirm-delete-btn').click(function() {
    var btn = $(this);
    btn.html('<i class="fa fa-spinner fa-spin"></i> Deleting...').prop('disabled', true);
    
    // Close modal
    $('#deleteModal').modal('hide');
    
    // Redirect to delete URL
    window.location.href = '<?php echo base_url(); ?>admin/contributionpayment/delete_invoice/' + deleteInvoiceId;
});
</script>
