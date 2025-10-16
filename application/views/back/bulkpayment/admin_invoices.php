<div id="content-container">
    <div id="page-head">
        <div id="page-title">
            <h1 class="page-header text-overflow">
                <i class="fa fa-user"></i> Invoices by <?php echo $admin->name; ?>
            </h1>
        </div>
        <ol class="breadcrumb">
            <li><a href="<?=base_url()?>admin"><i class="fa fa-home"></i> Home</a></li>
            <li><a href="<?=base_url()?>admin/bulkpayment">Bulk Payment</a></li>
            <li><a href="<?=base_url()?>admin/bulkpayment/invoices">Invoices</a></li>
            <li class="active"><?php echo $admin->name; ?></li>
        </ol>
    </div>
    
    <div id="page-content">
        <div class="panel">
            <div class="panel-heading">
                <h3 class="panel-title">All Bulk Payments</h3>
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
                            <th>Action</th>
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
                                <a href="<?php echo base_url('admin/bulkpayment/invoice_detail/' . $invoice->invoice_id); ?>" 
                                   class="btn btn-sm btn-primary">
                                    <i class="fa fa-eye"></i> View Details
                                </a>
                            </td>
                        </tr>
                        <?php 
                            endforeach;
                        else:
                        ?>
                        <tr>
                            <td colspan="8" class="text-center">No invoices found</td>
                        </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    $('#admin-invoice-table').DataTable({
        order: [[2, 'desc']]
    });
});
</script>
