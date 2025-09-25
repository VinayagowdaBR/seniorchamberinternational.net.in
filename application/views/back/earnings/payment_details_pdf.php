<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Payment Receipt</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 12px; }
        h2 { text-align: center; margin-bottom: 20px; }
        table { width: 100%; border-collapse: collapse; margin-top: 15px; }
        table, th, td { border: 1px solid #000; padding: 8px; }
        th { background: #f5f5f5; text-align: left; }
    </style>
</head>
<body>
    <h2>Payment Receipt</h2>

    <table>
        <tr>
            <th>Receipt No</th>
            <td><?= $payment->package_payment_id ?></td>
        </tr>
        <tr>
            <th>Member</th>
            <td><?= $member->first_name . ' ' . $member->last_name ?></td>
        </tr>
        <tr>
            <th>Email</th>
            <td><?= $member->email ?></td>
        </tr>
        <tr>
            <th>Plan</th>
            <td><?= $plan->name ?></td>
        </tr>
        <tr>
            <th>Amount</th>
            <td><?= currency('', 'def') . $payment->amount ?></td>
        </tr>
        <tr>
            <th>Status</th>
            <td><?= ucfirst($payment->payment_status) ?></td>
        </tr>
        <tr>
            <th>Payment Type</th>
            <td><?= $payment->payment_type ?></td>
        </tr>
        <tr>
            <th>Date</th>
            <td><?= date('d/m/Y h:i A', $payment->purchase_datetime) ?></td>
        </tr>
    </table>

    <p style="margin-top: 40px; text-align: center;">
        Thank you for your payment!
    </p>
</body>
</html>
