<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Invoice #<?= $invoice->invoice_number ?></title>
    <style>
        body {
            font-family: 'DejaVu Sans', sans-serif;
            font-size: 12px;
            line-height: 1.4;
            color: #333;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            border-spacing: 0;
            margin-bottom: 20px;
        }
        th, td {
            vertical-align: top;
            padding: 5px;
        }
        .invoice-title {
            font-size: 24px;
            font-weight: bold;
            color: #2c3e50;
            margin: 0;
        }
        .header-bg {
            background-color: #f8f9fa;
            border-bottom: 2px solid #ddd;
        }
        .text-right {
            text-align: right;
        }
        .text-center {
            text-align: center;
        }
        .section-header {
            font-size: 13px;
            font-weight: bold;
            color: #2980b9;
            text-transform: uppercase;
            border-bottom: 1px solid #eee;
            margin-bottom: 5px;
            padding-bottom: 2px;
        }
        .grand-total {
            font-size: 16px;
            font-weight: bold;
            color: #27ae60;
        }
        .footer {
            margin-top: 40px;
            text-align: center;
            font-size: 10px;
            color: #999;
            border-top: 1px solid #eee;
            padding-top: 10px;
        }
        /* Bordered items table */
        .items-table th {
            background-color: #f5f5f5;
            border-bottom: 1px solid #ccc;
            font-weight: bold;
            text-align: left;
        }
        .items-table td {
            border-bottom: 1px solid #eee;
        }
    </style>
</head>
<body>

    <!-- Unified Header Table to prevent overlap -->
    <table width="100%">
        <tr>
            <td width="60%">
                <!-- Company Info -->
                <h2 style="margin: 0; color: #2c3e50; font-size: 18px;"><?= $system_title ?></h2>
                <div style="font-size: 11px; margin-top: 5px;">
                    <?= $system_email ?><br>
                    Phone: <?= $system_phone ?>
                </div>
                
                <div style="margin-top: 15px;">
                    <div class="section-header" style="width: 200px;">Bill To</div>
                    <strong><?= isset($paid_by->name) ? $paid_by->name : 'Unknown' ?></strong><br>
                    <?php if(isset($paid_by->email)): ?>
                        <?= $paid_by->email ?><br>
                    <?php endif; ?>
                    Role: Admin
                </div>
            </td>
            <td width="40%" class="text-right">
                <!-- Invoice Info -->
                <h1 class="invoice-title">INVOICE</h1>
                <table width="100%" style="margin-top: 10px; font-size: 11px;">
                    <tr>
                        <td class="text-right"><strong>Invoice #:</strong></td>
                        <td class="text-right"><?= $invoice->invoice_number ?></td>
                    </tr>
                    <tr>
                        <td class="text-right"><strong>Date:</strong></td>
                        <td class="text-right"><?= date('d M Y', strtotime($invoice->payment_date)) ?></td>
                    </tr>
                    <tr>
                        <td class="text-right"><strong>Status:</strong></td>
                        <td class="text-right" style="color: green;">Paid</td>
                    </tr>
                </table>

                <div style="margin-top: 15px; text-align: right;">
                     <div class="section-header" style="margin-left: auto; width: 200px;">Payment Details</div>
                     <strong>Method:</strong> <?= ucfirst(str_replace('_', ' ', $invoice->payment_method)) ?><br>
                     <?php if(!empty($invoice->transaction_id)): ?>
                        <strong>xn ID:</strong> <?= $invoice->transaction_id ?><br>
                     <?php endif; ?>
                     <strong>Plan:</strong> <?= $invoice->plan_name ?>
                </div>
            </td>
        </tr>
    </table>

    <div style="height: 10px;"></div>

    <!-- Items Table -->
    <div class="section-header">Members Included (<?= count($members) ?>)</div>
    <table class="items-table" cellpadding="5">
        <thead>
            <tr>
                <th width="5%">#</th>
                <th width="20%">Member ID</th>
                <th width="35%">Name</th>
                <th width="20%">Legion</th>
                <th width="20%" class="text-right">Amount</th>
            </tr>
        </thead>
        <tbody>
            <?php 
            $i = 1; 
            foreach($members as $member): 
            ?>
            <tr>
                <td><?= $i++ ?></td>
                <td><?= $member['member_profile_id'] ?></td>
                <td><?= $member['member_name'] ?></td>
                <td><?= $member['legion_name'] ?></td>
                <td class="text-right">₹<?= number_format($member['amount'], 2) ?></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <!-- Totals Area -->
    <table style="margin-top: 10px;">
        <tr>
            <td width="60%">
                <?php if(!empty($invoice->notes)): ?>
                    <div class="section-header">Notes</div>
                     <div style="font-size: 11px; color: #555; background: #fefefe; padding: 5px; border: 1px solid #eee;">
                        <?= nl2br(htmlspecialchars($invoice->notes)) ?>
                     </div>
                <?php endif; ?>
            </td>
            <td width="5%"></td>
            <td width="35%">
                <table width="100%">
                    <tr>
                        <td class="text-right"><strong>Subtotal:</strong></td>
                        <td class="text-right">₹<?= number_format($invoice->base_amount, 2) ?></td>
                    </tr>
                    <tr>
                        <td class="text-right"><strong>GST (<?= $invoice->gst_percentage ?>%):</strong></td>
                        <td class="text-right">₹<?= number_format($invoice->gst_amount, 2) ?></td>
                    </tr>
                    <tr>
                        <td class="text-right header-bg" style="border-top: 1px solid #999; padding-top: 5px;">
                            <span class="grand-total">Total:</span>
                        </td>
                        <td class="text-right" style="border-top: 1px solid #999; padding-top: 5px;">
                            <span class="grand-total">₹<?= number_format($invoice->total_amount, 2) ?></span>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>

    <div class="footer">
        Generated on <?= date('d M Y, h:i A') ?> | Computer Generated Invoice
    </div>

</body>
</html>
