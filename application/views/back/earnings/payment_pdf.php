    <!DOCTYPE html>
    <html>
    <head>
    <meta charset="utf-8">
    <title>Payment Receipt</title>
    <style>
      @page { margin: 20px; size: A4 portrait; }
      body {
        font-family: DejaVu Sans, Arial, sans-serif;
        font-size: 12px;
        color: #333;
        margin: 0;
        padding: 0;
        line-height: 1.4;
      }
      table { border-collapse: collapse; width: 100%; }
      .bordered th, .bordered td {
        border: 1px solid #000;
        padding: 8px;
        font-size: 12px;
        vertical-align: top;
      }
      .bordered th {
        background: #f5f5f5;
        text-align: left;
        width: 35%;
      }
      .bordered td { width: 65%; }
      .amount { font-weight: bold; font-size: 13px; }
      .summary {
        border: 1px solid #000;
        padding: 10px;
        margin: 15px 0;
        font-size: 11px;
        background: #fafafa;
      }
      .thankyou {
        text-align: center;
        font-size: 13px;
        font-weight: bold;
        margin: 20px 0;
        padding: 8px;
        border: 1px solid #000;
        background: #f0f0f0;
      }
      .signature-line {
        border-bottom: 1px solid #000;
        width: 180px;
        display: inline-block;
        margin-bottom: 5px;
      }
    </style>
    </head>
    <body>

    <!-- HEADER -->
    <table width="100%" style="border-bottom:2px solid #000; margin-bottom:15px; text-align:center;">
      <tr>
        <td>
          <?php if(!empty($logoSrc)): ?>
            <img src="<?= $logoSrc ?>" width="500" style="margin-bottom:5px;">
          <?php endif; ?>
        </td>
      </tr>
      <!-- <tr>
        <td><strong style="font-size:18px;"><?= $system_title ?></strong></td>
      </tr> -->
      <tr>
        <td style="font-size:15px; font-weight:bold;">PAYMENT RECEIPT</td>
      </tr>
      <tr>
        <td style="font-size:12px;">
          Receipt No: <?= "PAY-".date('Y')."-".str_pad($payment->package_payment_id,5,"0",STR_PAD_LEFT) ?>
        </td>
      </tr>
    </table>

    <!-- INFO TABLE -->
    <table class="bordered">
      <tr><th>Receipt Number</th><td><?= "PAY-".date('Y')."-".str_pad($payment->package_payment_id,5,"0",STR_PAD_LEFT) ?></td></tr>
      <tr><th>Member Name</th><td><?= $member->first_name." ".$member->last_name ?></td></tr>
      <tr><th>Email Address</th><td><?= $member->email ?></td></tr>
      <tr><th>Plan/Service</th><td><?= $plan->name ?></td></tr>
      <tr><th>Total Amount</th><td class="amount"><?= currency('', 'def').$payment->amount ?></td></tr>
      <tr><th>Payment Status</th><td><?= strtoupper($payment->payment_status) ?></td></tr>
      <tr><th>Payment Method</th><td><?= $payment->payment_type ?></td></tr>
      <tr><th>Transaction Date</th><td><?= date('F d, Y h:i A', $payment->purchase_datetime) ?></td></tr>
      <tr><th>Transaction ID</th><td><?= $payment->payment_code ?? "N/A" ?></td></tr>
    </table>

    <!-- SUMMARY -->
    <!-- <div class="summary">
      <strong>Payment Summary</strong><br>
      This receipt confirms that payment has been successfully processed and received.
      Your subscription/service is now active. Please retain this receipt for your records
      and future reference. If you have any questions regarding this transaction, please
      contact our support team with your receipt number.
    </div> -->

    <!-- THANK YOU -->
    <!-- <div class="thankyou">THANK YOU FOR YOUR PAYMENT</div> -->


    <!-- FOOTER -->
    <!-- <table width="100%" style="margin-top:20px;">
      <tr>
        <td style="width:65%; font-size:11px; line-height:1.5; vertical-align:top;">
          <strong>For any inquiries or support:</strong><br>
          Email: support@yourcompany.com<br>
          Phone: (123) 456-7890<br>
          Business Hours: Monday - Friday, 9:00 AM - 6:00 PM
        </td>
        <td style="width:35%; text-align:right; font-size:11px; vertical-align:top;">
          <div class="signature-line"></div><br>
          Authorized Signature
        </td>
      </tr>
    </table> -->



    </body>
    </html>
