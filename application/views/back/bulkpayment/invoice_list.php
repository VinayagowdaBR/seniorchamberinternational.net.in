<div id="content-container">
    <div id="page-head">
        <div id="page-title">
            <h1 class="page-header text-overflow">
                <i class="fa fa-file-text"></i> Bulk Payment Invoices
            </h1>
        </div>
        <ol class="breadcrumb">
            <li><a href="<?=base_url()?>admin"><i class="fa fa-home"></i> Home</a></li>
            <li><a href="<?=base_url()?>admin/bulkpayment">Bulk Payment</a></li>
            <li class="active">Invoices</li>
        </ol>
    </div>
    
    <div id="page-content">
        <div class="panel">
            <div class="panel-heading">
                <h3 class="panel-title">Payment History by Admin</h3>
            </div>
            <div class="panel-body">
                <table class="table table-striped table-hover" id="invoice-table">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Admin Name</th>
                            <th>Email</th>
                            <th>Total Invoices</th>
                            <th>Total Amount Paid</th>
                            <th>Last Payment Date</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        if (!empty($invoice_summary)):
                            $sl = 1;
                            foreach ($invoice_summary as $summary): 
                        ?>
                        <tr>
                            <td><?php echo $sl++; ?></td>
                            <td><strong><?php echo $summary->admin_name; ?></strong></td>
                            <td><?php echo $summary->admin_email; ?></td>
                            <td><span class="badge badge-primary"><?php echo $summary->total_invoices; ?></span></td>
                            <td><strong class="text-success">₹<?php echo number_format($summary->total_paid, 2); ?></strong></td>
                            <td><?php echo date('d M Y, h:i A', strtotime($summary->last_payment)); ?></td>
                            <td>
                                <a href="<?php echo base_url('admin/bulkpayment/invoices_by_admin/' . $summary->paid_by_admin_id); ?>" 
                                   class="btn btn-sm btn-info">
                                    <i class="fa fa-eye"></i> View Invoices
                                </a>
                            </td>
                        </tr>
                        <?php 
                            endforeach;
                        else:
                        ?>
                        <tr>
                            <td colspan="7" class="text-center">
                                <i class="fa fa-inbox" style="font-size: 48px; color: #ccc; margin: 20px 0;"></i>
                                <p>No bulk payment invoices found</p>
                            </td>
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
    $('#invoice-table').DataTable({
        order: [[5, 'desc']]
    });
});
</script>
